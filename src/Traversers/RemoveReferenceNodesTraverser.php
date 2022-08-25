<?php

namespace Spatie\PhpTypeGraph\Traversers;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\ReferenceNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class RemoveReferenceNodesTraverser extends Traverser
{
    public function types(): string | array | null
    {
        return ReferenceNode::class;
    }

    public function handle(
        TypeGraphConfig $typeGraphConfig,
        ReferenceNode $node
    ): TypeNode {
        return $typeGraphConfig->nodes[$node->type] ?? $node;
    }

    public function after(TypeGraphConfig $typeGraphConfig): void
    {
        unset($typeGraphConfig->nodes[ReferenceNode::class]);
    }
}
