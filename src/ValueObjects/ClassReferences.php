<?php

namespace Spatie\PhpTypeGraph\ValueObjects;

class ClassReferences
{
    public function __construct(
        public string $name,
        public array $implementedBy = [],
        public array $extendedBy = [],
        public array $usedBy = [],
    )
    {
    }
}
