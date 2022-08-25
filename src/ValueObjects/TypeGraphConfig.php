<?php

namespace Spatie\PhpTypeGraph\ValueObjects;

use Spatie\PhpTypeGraph\Collections\InvertedClassReferenceMap;
use Spatie\PhpTypeGraph\Collections\NodesCollection;

class TypeGraphConfig
{
    public function __construct(
        public NodesCollection $nodes,
        public InvertedClassReferenceMap $classReferences,
    )
    {
    }
}
