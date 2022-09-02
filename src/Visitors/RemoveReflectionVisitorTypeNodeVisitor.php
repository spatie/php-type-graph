<?php

namespace Spatie\PhpTypeGraph\Visitors;

use Spatie\PhpTypeGraph\Enums\NodeVisitorOperation;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;

class RemoveReflectionVisitorTypeNodeVisitor extends AbstractTypeNodeVisitor
{
    public function enterNode(TypeNode $node): TypeNode|null|NodeVisitorOperation
    {
        if ($node instanceof CompoundItemTypeNode || $node instanceof CompoundTypeNode) {
            $node->reflection = null;
        }

        return $node;
    }
}
