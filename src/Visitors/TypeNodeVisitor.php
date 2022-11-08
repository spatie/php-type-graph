<?php

namespace Spatie\PhpTypeGraph\Visitors;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Enums\NodeVisitorOperation;
use Spatie\PhpTypeGraph\NodeFactories\NodeFactory;
use Spatie\PhpTypeGraph\Nodes\TypeNode;

interface TypeNodeVisitor
{
    public function __construct(NodeFactory $nodeFactory);

    public function beforeTraverse(NodesCollection $nodes);

    public function enterNode(TypeNode $node): TypeNode|null;

    public function leaveNode(TypeNode $node): TypeNode|null;

    public function afterTraverse(NodesCollection $nodes);
}
