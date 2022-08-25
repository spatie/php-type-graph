<?php

namespace Spatie\PhpTypeGraph\Nodes;

class StringTypeNode extends BaseTypeNode
{
    public function __construct()
    {
        parent::__construct('string');
    }
}
