<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use Exception;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class ReflectionTypeNodeFactory
{
    public function __construct(
        private readonly ReflectionNamedTypeNodeFactory $reflectionNamedTypeNodeFactory,
        private readonly ReflectionIntersectionTypeNodeFactory $reflectionIntersectionTypeNodeFactory,
        private readonly ReflectionUnionTypeNodeFactory $reflectionUnionTypeNodeFactory,
    ) {
    }

    public function create(
        ReflectionType $reflection
    ): TypeNode {
        return match ($reflection::class) {
            ReflectionNamedType::class => $this->reflectionNamedTypeNodeFactory->create($reflection),
            ReflectionIntersectionType::class => $this->reflectionIntersectionTypeNodeFactory->create($reflection),
            ReflectionUnionType::class => $this->reflectionUnionTypeNodeFactory->create($reflection),
            default => throw new Exception('Unknown reflection type')
        };
    }
}
