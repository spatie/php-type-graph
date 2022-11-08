<?php

namespace Spatie\PhpTypeGraph\Visitors;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\NodeFactories\NodeFactory;
use Spatie\PhpTypeGraph\Nodes\TypeNode;

class AbstractTypeNodeVisitor implements TypeNodeVisitor
{
    public function __construct(
        protected readonly NodeFactory $nodeFactory,
    ) {
    }

    public function beforeTraverse(NodesCollection $nodes)
    {
        return null;
    }

    public function enterNode(TypeNode $node): TypeNode|null
    {
        return null;
    }

    public function leaveNode(TypeNode $node): TypeNode|null
    {
        return null;
    }

    public function afterTraverse(NodesCollection $nodes)
    {
        return null;
    }
}
