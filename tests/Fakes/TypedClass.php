<?php

namespace Spatie\PhpTypeGraph\Tests\Fakes;

class TypedClass
{
    public function __construct(
        public $untyped,
        public mixed $mixed,
        public string $sting,
        public array $array,
        public bool $bool,
        public float $float,
        public int $int,
        public ?string $nullable,
        public self $self,
        public int|string $union,
        public int|string|null $nullableUnion,
        public BasicClass&Extended $intersection,
    ) {
    }
}
