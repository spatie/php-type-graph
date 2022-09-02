<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use Illuminate\Support\Collection;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class ReflectionUnionTypeNodeFactory
{
    public function __construct(
        private readonly BaseNodeFactory $baseNodeFactory,
        private readonly ReflectionNamedTypeNodeFactory $reflectionNamedTypeNodeFactory,
    ) {
    }

    public function create(
        ReflectionUnionType $reflection,
    ): UnionTypeNode {
        $childNodes = NodesCollection::create($reflection->getTypes())
            ->map(fn(ReflectionNamedType $namedType) => $this->reflectionNamedTypeNodeFactory->create(
                $namedType
            ))
            ->flatMap(fn(TypeNode $node) => $node instanceof UnionTypeNode
                ? $node->nodes
                : [$node]
            )
            ->when($reflection->allowsNull(), fn(Collection $childNodes) => $childNodes->add($this->baseNodeFactory->create('null')))
            ->unique();

        return new UnionTypeNode($childNodes);
    }
}
