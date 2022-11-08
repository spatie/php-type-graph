<?php

namespace Spatie\PhpTypeGraph\Visitors;

use ReflectionClass;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Support\ReferenceChecker;

class RemoveTypeNodeVisitorReferenceNodesVisitor extends AbstractTypeNodeVisitor
{
    private NodesCollection $nodes;

    public function beforeTraverse(NodesCollection $nodes)
    {
        $this->nodes = $nodes;
    }

    public function leaveNode(TypeNode $node): TypeNode|null
    {
        if (! $node instanceof ReferenceTypeNode) {
            return null;
        }

        $found = $this->nodes[$node->type] ?? null;

        if ($found) {
            return $found;
        }

        if (ReferenceChecker::exists($node->type)) {
            return $this->nodeFactory->reflectionClass()->create(new ReflectionClass($node->type));
        }

        if ($this->nodeFactory->baseNode()->isBaseNodeType($node->type)) {
            return $this->nodeFactory->baseNode()->create($node->type);
        }

        return $this->nodeFactory->unknownNode()->create($node->type);
    }
}
