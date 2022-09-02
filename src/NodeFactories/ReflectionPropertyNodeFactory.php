<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use ReflectionAttribute;
use ReflectionProperty;
use Spatie\PhpTypeGraph\Collections\PhpAttributesCollection;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\PhpAttribute;

class ReflectionPropertyNodeFactory
{
    public function __construct(
        private readonly BaseNodeFactory $baseNodeFactory,
        private readonly ReflectionTypeNodeFactory $reflectionTypeNodeFactory,
    ) {
    }

    public function create(
        ReflectionProperty $property
    ): CompoundItemTypeNode {
        $node = $property->getType() === null
            ? $this->baseNodeFactory->create('mixed')
            : $this->reflectionTypeNodeFactory->create(
                $property->getType()
            );

        return new CompoundItemTypeNode(
            $property->name,
            $property->class,
            $node,
            $property,
            PhpAttributesCollection::create($property->getAttributes())->map(
                fn (ReflectionAttribute $reflection) => PhpAttribute::fromReflectionAttribute($reflection)
            )
        );
    }
}
