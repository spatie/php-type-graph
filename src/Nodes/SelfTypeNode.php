<?php

namespace Spatie\PhpTypeGraph\Nodes;

class SelfTypeNode extends BaseTypeNode
{
    public function __construct()
    {
        parent::__construct('self');
    }
}
