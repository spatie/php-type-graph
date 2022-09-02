<?php

namespace Spatie\PhpTypeGraph\Nodes;

class StaticTypeNode extends BaseTypeNode
{
    public function __construct()
    {
        parent::__construct('static');
    }
}
