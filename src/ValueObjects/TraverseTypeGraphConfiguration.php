<?php

namespace Spatie\PhpTypeGraph\ValueObjects;

class TraverseTypeGraphConfiguration
{
    public function __construct(
        public array | null $types,
        public bool $includeClassProperties,
        public bool $includeClassChildren,
        public bool $includeClassParents,
    )
    {
    }
}
