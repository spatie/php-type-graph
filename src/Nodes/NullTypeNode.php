<?php

namespace Spatie\PhpTypeGraph\Nodes;

class NullTypeNode extends BaseTypeNode
{
    public function __construct()
    {
        parent::__construct('null');
    }
}
