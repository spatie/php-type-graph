<?php

namespace Spatie\PhpTypeGraph\Collections;

use Closure;
use Exception;
use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Collection<TKey, TValue>
 */
class NodesCollection extends Collection
{
    public static function create(array $nodes): static
    {
        if (in_array(null, $nodes)) {
            throw new Exception('Tried creating a nodes collection with a `null` node');
        }

        return new self($nodes);
    }

    public function transformFilter(Closure $closure): static
    {
        $this->items = array_filter(
            $this->items,
            $closure,
        );

        return $this;
    }
}
