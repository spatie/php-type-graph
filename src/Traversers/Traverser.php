<?php

namespace Spatie\PhpTypeGraph\Traversers;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\ReferenceNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

abstract class Traverser
{
    public function types(): string | array | null
    {
        return null;
    }

    public function traverseClassParents(): bool
    {
        return true;
    }

    public function traverseClassChildren(): bool
    {
        return true;
    }

    public function traverseClassItems(): bool
    {
        return true;
    }

    public function before(
        TypeGraphConfig $typeGraphConfig,
    ): void {

    }

    public function after(
        TypeGraphConfig $typeGraphConfig,
    ): void {

    }
}
