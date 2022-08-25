<?php

namespace Spatie\PhpTypeGraph\Nodes;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Illuminate\Support\Collection;

class IntersectionTypeNode extends TypeNode
{
    /**
     * @param NodesCollection<TypeNode> $nodes
     */
    public function __construct(
        public NodesCollection $nodes,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'kind' => 'intersection',
            'nodes' => $this->nodes->toArray(),
        ];
    }

    public function __toString(): string
    {
        return $this->nodes->implode('&');
    }
}
