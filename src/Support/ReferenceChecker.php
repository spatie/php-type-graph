<?php

namespace Spatie\PhpTypeGraph\Support;

use Throwable;

class ReferenceChecker
{
    public static array $references = [];

    public static function exists(string $class): bool
    {
        if (in_array($class, BlackList::$entries)) {
            return false;
        }

        try {
            return class_exists($class)
                || interface_exists($class)
                || enum_exists($class)
                || trait_exists($class);
        } catch (Throwable) {
            return class_exists($class, false)
                || interface_exists($class, false)
                || enum_exists($class, false)
                || trait_exists($class, false);
        }
    }
}
