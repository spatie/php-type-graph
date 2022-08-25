<?php

namespace Spatie\PhpTypeGraph\Nodes;

use Illuminate\Contracts\Support\Arrayable;
use Stringable;

abstract class TypeNode implements Arrayable, Stringable
{
    public function __construct()
    {
    }
}
