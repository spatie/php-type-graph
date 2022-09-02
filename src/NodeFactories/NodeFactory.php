<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use ReflectionClass;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Support\ReferenceChecker;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;

class NodeFactory
{
    public function __construct(
        private TypeGraphConfig $config,
    ) {
    }

    public function create(string $type): CompoundTypeNode|BaseTypeNode
    {
        if (ReferenceChecker::exists($type)) {
            return $this->reflectionClass()->create(new ReflectionClass($type));
        }

        return $this->baseNode()->create($type);
    }

    public function baseNode(): BaseNodeFactory
    {
        return new BaseNodeFactory($this->config);
    }

    public function unknownNode(): UnknownTypeNodeFactory
    {
        return new UnknownTypeNodeFactory($this->config);
    }

    public function reflectionNamedTypeNode(): ReflectionNamedTypeNodeFactory
    {
        return new ReflectionNamedTypeNodeFactory(
            $this->baseNode(),
            $this->unknownNode()
        );
    }

    public function reflectionUnionTypeNode(): ReflectionUnionTypeNodeFactory
    {
        return new ReflectionUnionTypeNodeFactory(
            $this->baseNode(),
            $this->reflectionNamedTypeNode(),
        );
    }

    public function reflectionIntersectionNode(): ReflectionIntersectionTypeNodeFactory
    {
        return new ReflectionIntersectionTypeNodeFactory(
            $this->baseNode(),
            $this->reflectionNamedTypeNode(),
        );
    }

    public function reflectionTypeNode(): ReflectionTypeNodeFactory
    {
        return new ReflectionTypeNodeFactory(
            $this->reflectionNamedTypeNode(),
            $this->reflectionIntersectionNode(),
            $this->reflectionUnionTypeNode()
        );
    }

    public function reflectionProperty(): ReflectionPropertyNodeFactory
    {
        return new ReflectionPropertyNodeFactory(
            $this->baseNode(),
            $this->reflectionTypeNode(),
        );
    }

    public function reflectionClass(): ReflectionClassNodeFactory
    {
        return new ReflectionClassNodeFactory(
            $this->config,
            $this->reflectionProperty()
        );
    }
}
