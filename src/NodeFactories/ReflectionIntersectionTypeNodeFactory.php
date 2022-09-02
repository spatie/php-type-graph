<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use ReflectionIntersectionType;
use ReflectionNamedType;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class ReflectionIntersectionTypeNodeFactory
{
    public function __construct(
        private readonly BaseNodeFactory $baseNodeFactory,
        private readonly ReflectionNamedTypeNodeFactory $reflectionNamedTypeNodeFactory,
    ) {
    }

    public function create(
        ReflectionIntersectionType $reflection,
    ): IntersectionTypeNode|UnionTypeNode {
        $types = NodesCollection::create($reflection->getTypes())
            ->map(
                fn(ReflectionNamedType $namedType) => $this->reflectionNamedTypeNodeFactory->create($namedType)
            );

        $node = new IntersectionTypeNode($types);

        if (! $reflection->allowsNull()) {
            return $node;
        }

        return new UnionTypeNode(NodesCollection::create([$this->baseNodeFactory->create('null'), $node]));
    }
}
