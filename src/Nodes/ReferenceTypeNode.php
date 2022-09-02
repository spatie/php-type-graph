<?php

namespace Spatie\PhpTypeGraph\Nodes;

class ReferenceTypeNode extends TypeNode
{
    public function __construct(
        public string $type,
    ) {
        parent::__construct();
    }

    public function toArray()
    {
        return [
            'kind' => 'reference',
            'type' => $this->type,
        ];
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
