<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Collections\PhpAttributesCollection;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode;
use Spatie\PhpTypeGraph\Tests\Fakes\AbstractClass;
use Spatie\PhpTypeGraph\ValueObjects\PhpAttribute;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class ReflectionClassNodeFactory
{
    public function __construct(
        private readonly TypeGraphConfig $config,
        private readonly ReflectionPropertyNodeFactory $reflectionPropertyNodeFactory,
    ) {
    }

    public function create(
        ReflectionClass $reflection,
    ): CompoundTypeNode {
        if ($this->config->nodes->has($reflection->name)) {
            return $this->config->nodes[$reflection->name];
        }

        $properties = NodesCollection::create($reflection->getProperties())
            ->reject(fn(ReflectionProperty $property) => $property->isStatic())
            ->keyBy(fn(ReflectionProperty $property) => $property->getName())
            ->map(fn(ReflectionProperty $property) => $this->reflectionPropertyNodeFactory->create($property))
            ->filter();

        $parentNodes = NodesCollection::create(class_implements($reflection->name))
            ->merge(class_parents($reflection->name))
            ->map(fn(string $class) => new ReferenceTypeNode($class))
            ->unique();

        $childNodes = NodesCollection::create([
            ...$this->config->classReferences[$reflection->name]->implementedBy ?? [],
            ...$this->config->classReferences[$reflection->name]->extendedBy ?? [],
        ])
            ->map(fn(string $class) => new ReferenceTypeNode($class))
            ->unique();

        $node = new CompoundTypeNode(
            $reflection->name,
            $reflection,
            PhpAttributesCollection::create($reflection->getAttributes())->map(
                fn(ReflectionAttribute $reflection) => PhpAttribute::fromReflectionAttribute($reflection)
            ),
            $properties,
            $childNodes,
            $parentNodes
        );

        return $this->config->nodes[$reflection->name] = $node;
    }
}
