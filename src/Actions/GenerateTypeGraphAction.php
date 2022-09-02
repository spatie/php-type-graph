<?php

namespace Spatie\PhpTypeGraph\Actions;

use Spatie\PhpTypeGraph\Collections\InvertedClassReferenceMap;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\NodeFactories\BaseNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\UnknownTypeNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\NodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\ReflectionClassNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\ReflectionIntersectionTypeNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\ReflectionNamedTypeNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\ReflectionPropertyNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\ReflectionTypeNodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\ReflectionUnionTypeNodeFactory;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\BoolTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\UnknownTypeNode;
use Spatie\PhpTypeGraph\Nodes\FloatTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntTypeNode;
use Spatie\PhpTypeGraph\Nodes\MixedTypeNode;
use Spatie\PhpTypeGraph\Nodes\NullTypeNode;
use Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode;
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
use Spatie\PhpTypeGraph\Visitors\RemoveTypeNodeVisitorReferenceNodesVisitor;
use Spatie\PhpTypeGraph\Visitors\TypeNodeVisitor;

class GenerateTypeGraphAction
{
    public function __construct(
        private ResolveInvertedClassReferenceMapAction $resolveInvertedClassReferenceMapAction,
        private VisitNodesAction $visitNodesAction,
    ) {
    }

    public function execute(
        array $directories,
        array $visitors = [],
    ): NodesCollection {
        $nodes = new NodesCollection();

        $classReferences = $this->resolveInvertedClassReferenceMapAction->execute($directories);

        $config = new TypeGraphConfig(
            $nodes,
            $classReferences,
        );

        $factory = new NodeFactory($config);

        $reflectionClassFactory = $factory->reflectionClass();
        $failedNodeFactory = $factory->unknownNode();

        foreach ($classReferences as $classReference) {
            try {
                $reflectionClassFactory->create(new ReflectionClass($classReference->name));
            } catch (ReflectionException) {
                $failedNodeFactory->create($classReference->name);
            }
        }

        collect($visitors)
            ->prepend(RemoveTypeNodeVisitorReferenceNodesVisitor::class)
            ->unique()
            ->map(fn(string $visitorClass) => new $visitorClass(
                $factory,
            ))
            ->each(function (TypeNodeVisitor $visitor) use (&$nodes) {
                $this->visitNodesAction->execute($nodes, $visitor);
            });

        return $nodes;
    }
}
