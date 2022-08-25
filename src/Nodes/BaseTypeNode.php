<?php

namespace Spatie\PhpTypeGraph\Nodes;

class BaseTypeNode extends TypeNode
{
    public function __construct(public string $type)
    {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
           'kind' => 'base',
           'type' => $this->type,
        ];
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
