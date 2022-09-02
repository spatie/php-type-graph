<?php

namespace Spatie\PhpTypeGraph\Nodes;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\PhpTypeGraph\Meta\NodeMeta;
use Stringable;

abstract class TypeNode implements Arrayable, Stringable
{
    public function __construct(
        public array $meta = []
    ) {
    }

    public function addMeta(NodeMeta $meta)
    {
        $this->meta[$meta::name()] = $meta;
    }
}
