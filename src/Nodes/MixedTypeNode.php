<?php

namespace Spatie\PhpTypeGraph\Nodes;

class MixedTypeNode extends BaseTypeNode
{
    public function __construct()
    {
        parent::__construct('mixed');
    }
}
