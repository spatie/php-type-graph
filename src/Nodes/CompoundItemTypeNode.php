<?php

namespace Spatie\PhpTypeGraph\Nodes;

use ReflectionProperty;

class CompoundItemTypeNode extends TypeNode
{
    public function __construct(
        public string $name,
        public TypeNode $node,
        public ReflectionProperty $reflection,
    ) {
        parent::__construct();
    }

    public function toArray()
    {
        return [
            'kind' => 'compound_item',
            'name' => $this->name,
            'node' => $this->node->toArray(),
        ];
    }

    public function __toString()
    {
        return '$' . $this->name;
    }
}
