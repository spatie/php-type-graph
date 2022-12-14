<?php

namespace Spatie\PhpTypeGraph\Visitors;

use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;

class RemoveReflectionVisitorTypeNodeVisitor extends AbstractTypeNodeVisitor
{
    public function enterNode(TypeNode $node): TypeNode|null
    {
        if ($node instanceof CompoundItemTypeNode || $node instanceof CompoundTypeNode) {
            $node->reflection = null;
        }

        return $node;
    }
}
