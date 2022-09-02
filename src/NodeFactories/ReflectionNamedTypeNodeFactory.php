<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use Exception;
use ReflectionNamedType;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\Support\ReferenceChecker;

class ReflectionNamedTypeNodeFactory
{
    public function __construct(
        private readonly BaseNodeFactory $baseNodeFactory,
        private readonly UnknownTypeNodeFactory $unknownNodeFactory,
    ) {
    }

    public function create(
        ReflectionNamedType $reflection
    ): TypeNode {
        $node = match (true) {
            $reflection->isBuiltin() => $this->baseNodeFactory->create($reflection->getName()),
            ReferenceChecker::exists($reflection->getName()) => new ReferenceTypeNode($reflection->getName()),
            $reflection->getName() === 'self' => new ReferenceTypeNode($reflection->getName()),
            ! ReferenceChecker::exists($reflection->getName()) => $this->unknownNodeFactory->create($reflection->getName()),
            default => throw new Exception("Unknown reflection named type {$reflection}")
        };

        if ($reflection->allowsNull()) {
            return new UnionTypeNode(NodesCollection::create([
                $this->baseNodeFactory->create('null'),
                $node,
            ]));
        }

        return $node;
    }
}
