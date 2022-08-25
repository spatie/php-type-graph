<?php

namespace Spatie\PhpTypeGraph\Nodes;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Illuminate\Support\Collection;
use ReflectionClass;

class CompoundTypeNode extends TypeNode
{
    /**
     * @param string $type
     * @param NodesCollection<CompoundItemTypeNode> $items
     * @param NodesCollection<TypeNode> $childNodes
     * @param NodesCollection<TypeNode> $parentNodes
     */
    public function __construct(
        public string $type,
        public ReflectionClass $reflection,
        public NodesCollection $items,
        public NodesCollection $childNodes,
        public NodesCollection $parentNodes,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'kind' => 'compound',
            'items' => $this->items->toArray(),
            'child_nodes' => $this->childNodes->toArray(),
            'parent_nodes' => $this->parentNodes->toArray(),
        ];
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
