<?php

namespace Spatie\PhpTypeGraph\Nodes;

class UnknownTypeNode extends TypeNode
{
    public function __construct(
        public string $type,
    )
    {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'kind' => 'failed',
            'type' => $this->type,
        ];
    }

    public function __toString()
    {
        return "Failed<{$this->type}>";
    }
}
