<?php

namespace Spatie\PhpTypeGraph\Tests\Fakes\DocBlocks;

/**
 * @property array<string> $d
 */
class ArrayDockBlockTyped
{
    /**
     * @param array<string> $a
     * @param array<int, string> $b
     * @param string[] $c
     */
    public function __construct(
        public array $a,
        public array $b,
        public array $c,
        public array $d,
    )
    {
    }
}
