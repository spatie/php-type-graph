<?php

namespace Spatie\PhpTypeGraph\Collections;

use Illuminate\Support\Collection;

/***
 * @extends Collection<int, \Spatie\PhpTypeGraph\ValueObjects\PhpAttribute>
 */
class PhpAttributesCollection extends Collection
{
    public static function create(array $attributes): static
    {
        return new self($attributes);
    }
}
