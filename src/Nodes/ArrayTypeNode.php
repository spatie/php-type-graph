<?php

namespace Spatie\PhpTypeGraph\Nodes;

class ArrayTypeNode extends BaseTypeNode
{
    public function __construct()
    {
        parent::__construct('array');
    }
}
