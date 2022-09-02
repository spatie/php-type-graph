<?php

namespace Spatie\PhpTypeGraph\Nodes;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Illuminate\Support\Collection;

class UnionTypeNode extends TypeNode
{
    /**
     * @param NodesCollection<\Spatie\PhpTypeGraph\Nodes\BaseTypeNode|\Spatie\PhpTypeGraph\Nodes\CompoundTypeNode|CollectionTypeNode|\Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode|\Spatie\PhpTypeGraph\Nodes\UnknownTypeNode> $nodes
     */
    public function __construct(
        public NodesCollection $nodes,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'kind' => 'union',
            'nodes' => $this->nodes->toArray(),
        ];
    }

    public function __toString(): string
    {
        return $this->nodes->implode('|');
    }
}
