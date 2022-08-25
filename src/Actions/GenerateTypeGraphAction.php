<?php

namespace Spatie\PhpTypeGraph\Actions;

use Spatie\PhpTypeGraph\Collections\InvertedClassReferenceMap;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\BoolTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\FailedTypeNode;
use Spatie\PhpTypeGraph\Nodes\FloatTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntTypeNode;
use Spatie\PhpTypeGraph\Nodes\MixedTypeNode;
use Spatie\PhpTypeGraph\Nodes\NullTypeNode;
use Spatie\PhpTypeGraph\Nodes\ReferenceNode;
use Spatie\PhpTypeGraph\Nodes\StringTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\Traversers\RemoveReferenceNodesTraverser;
use Spatie\PhpTypeGraph\Traversers\Traverser;
use Spatie\PhpTypeGraph\ValueObjects\ClassReferences;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;
use Exception;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class GenerateTypeGraphAction
{
    public function __construct(
        private ResolveInvertedClassReferenceMapAction $resolveInvertedClassReferenceMapAction,
        private TraverseNodesCollectionAction $traverseNodesCollectionAction,
    ) {
    }

    public function execute(
        array $traversers = [],
    ): NodesCollection {
        $nodes = new NodesCollection();

        /** @var InvertedClassReferenceMap $classReferences */
        $classReferences = $this->resolveInvertedClassReferenceMapAction->execute();

        $config = new TypeGraphConfig(
            $nodes,
            $classReferences,
        );

        $classReferences->map(function (ClassReferences $class) use (&$config) {
            try {
                return $this->createNode(
                    $config,
                    new ReflectionClass($class->name)
                );
            } catch (ReflectionException) {
                return $this->createFailedTypeNode($config, $class->name);
            }
        });

        collect($traversers)
            ->push(RemoveReferenceNodesTraverser::class)
            ->unique()
            ->map(fn(string $traverserClass) => new $traverserClass)
            ->each(function (Traverser $traverser) use (&$config) {
                $traverser->before($config);

                $this->traverseNodesCollectionAction->execute(
                    $config->nodes,
                    function (TypeNode $typeNode) use ($traverser, &$config) {
                        $handled = $traverser->handle($config, $typeNode);


                        return $handled;
                    },
                    $traverser->types(),
                    $traverser->traverseClassItems(),
                    $traverser->traverseClassChildren(),
                    $traverser->traverseClassParents()
                );

                $traverser->after($config);
            });

        return $nodes;
    }

    private function createNode(
        TypeGraphConfig $config,
        ReflectionClass|ReflectionType $reflection,
    ): TypeNode {
        if ($reflection instanceof ReflectionNamedType) {
            $node = match (true) {
                $reflection->isBuiltin() => $this->createBaseTypeNode($config, $reflection->getName()),
                class_exists($reflection->getName()) || interface_exists($reflection->getName()) => new ReferenceNode($reflection->getName()),
                default => throw new Exception("Unknown reflection named type {$reflection}")
            };

            if ($reflection->allowsNull()) {
                return new UnionTypeNode(NodesCollection::create([
                    $this->createBaseTypeNode($config, 'null'),
                    $node,
                ]));
            }

            return $node;
        }

        if ($reflection instanceof ReflectionUnionType) {
            $childNodes = NodesCollection::create($reflection->getTypes())
                ->map(fn(ReflectionNamedType $namedType) => $this->createNode(
                    $config,
                    $namedType
                ))
                ->flatMap(fn(TypeNode $node) => $node instanceof UnionTypeNode
                    ? $node->nodes
                    : [$node]
                )
                ->when($reflection->allowsNull(), fn(Collection $childNodes) => $childNodes->add($this->createBaseTypeNode($config, 'null')))
                ->unique();

            return new UnionTypeNode($childNodes);
        }

        if ($reflection instanceof ReflectionIntersectionType) {
            $types = NodesCollection::create($reflection->getTypes())->map(
                fn(ReflectionNamedType $namedType) => $this->createNode(
                    $config,
                    $namedType
                )
            );

            $node = new IntersectionTypeNode($types);

            if (! $reflection->allowsNull()) {
                return $node;
            }

            return new UnionTypeNode(NodesCollection::create([$this->createBaseTypeNode($config, 'null'), $node]));
        }

        if ($reflection instanceof ReflectionClass) {
            if ($config->nodes->has($reflection->name)) {
                return $config->nodes[$reflection->name];
            }

            $properties = NodesCollection::create($reflection->getProperties())
                ->reject(fn(ReflectionProperty $property) => $property->isStatic())
                ->keyBy(fn(ReflectionProperty $property) => $property->getName())
                ->map(function (ReflectionProperty $property) use (&$config) {
                    $node = $property->getType() === null
                        ? $this->createBaseTypeNode($config, 'mixed')
                        : $this->createNode(
                            $config,
                            $property->getType()
                        );

                    return new CompoundItemTypeNode($property->name, $node, $property);
                })
                ->filter();

            $parentNodes = NodesCollection::create(class_implements($reflection->name))
                ->merge(class_parents($reflection->name))
                ->map(fn(string $class) => new ReferenceNode($class))
                ->unique();

            $childNodes = NodesCollection::create([
                ...$config->classReferences[$reflection->name]->implementedBy ?? [],
                ...$config->classReferences[$reflection->name]->extendedBy ?? [],
            ])
                ->map(fn(string $class) => new ReferenceNode($class))
                ->unique();

            $node = new CompoundTypeNode(
                $reflection->name,
                $reflection,
                $properties,
                $childNodes,
                $parentNodes
            );

            return $config->nodes[$reflection->name] = $node;
        }

        throw new Exception('Unknown branch');
    }

    private function createBaseTypeNode(
        TypeGraphConfig $config,
        string $type,
    ): BaseTypeNode {
        if ($config->nodes->has($type)) {
            return $config->nodes[$type];
        }

        return $config->nodes[$type] = match ($type) {
            'string' => new StringTypeNode(),
            'bool' => new BoolTypeNode(),
            'int' => new IntTypeNode(),
            'float' => new FloatTypeNode(),
            'mixed' => new MixedTypeNode(),
            'null' => new NullTypeNode(),
            default => new BaseTypeNode($type),
        };
    }

    private function createFailedTypeNode(
        TypeGraphConfig $config,
        string $type,
    ): FailedTypeNode {
        if ($config->nodes->has($type)) {
            return $config->nodes[$type];
        }

        dump($type);

        return $config->nodes[$type] = new FailedTypeNode($type);
    }
}
