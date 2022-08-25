<?php

namespace Spatie\PhpTypeGraph\Collections;

use Illuminate\Support\Collection;

class NodesCollection extends Collection
{
    public static function create(array $nodes): static
    {
        return new self($nodes);
    }
}
