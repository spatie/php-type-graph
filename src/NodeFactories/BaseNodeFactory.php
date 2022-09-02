<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use Spatie\PhpTypeGraph\Nodes\ArrayTypeNode;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\BoolTypeNode;
use Spatie\PhpTypeGraph\Nodes\FloatTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntTypeNode;
use Spatie\PhpTypeGraph\Nodes\MixedTypeNode;
use Spatie\PhpTypeGraph\Nodes\NullTypeNode;
use Spatie\PhpTypeGraph\Nodes\SelfTypeNode;
use Spatie\PhpTypeGraph\Nodes\StaticTypeNode;
use Spatie\PhpTypeGraph\Nodes\StringTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class BaseNodeFactory
{
    public function __construct(
        private readonly TypeGraphConfig $config
    ) {
    }

    public function create(
        string $type,
    ): BaseTypeNode {
        if ($this->config->nodes->has($type)) {
            return $this->config->nodes[$type];
        }

        return $this->mapping()[$type] ?? new BaseTypeNode($type);
    }

    public function isBaseNodeType(string $type): bool
    {
        return array_key_exists($type, $this->mapping());
    }

    protected function mapping(): array
    {
        return [
            'string' => new StringTypeNode(),
            'bool' => new BoolTypeNode(),
            'int' => new IntTypeNode(),
            'float' => new FloatTypeNode(),
            'mixed' => new MixedTypeNode(),
            'null' => new NullTypeNode(),
            'array' => new ArrayTypeNode(),
            'self' => new SelfTypeNode(),
            'static' => new StaticTypeNode(),
        ];
    }
}
