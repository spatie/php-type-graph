<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use Spatie\PhpTypeGraph\Nodes\UnknownTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class UnknownTypeNodeFactory
{
    public function __construct(
        private readonly TypeGraphConfig $config
    )
    {
    }

    public function create(
        string $type,
    ): UnknownTypeNode {
        return $this->config->nodes[$type] = new UnknownTypeNode($type);
    }
}
