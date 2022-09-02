<?php

namespace Spatie\PhpTypeGraph\ValueObjects;

use ReflectionAttribute;

class PhpAttribute
{
    public function __construct(
        public readonly string $class,
        public readonly array $arguments
    ) {
    }

    public static function fromReflectionAttribute(
        ReflectionAttribute $reflection
    ): self {
        return new self(
            $reflection->getName(),
            $reflection->getArguments()
        );
    }
}
