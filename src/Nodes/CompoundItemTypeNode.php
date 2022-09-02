<?php

namespace Spatie\PhpTypeGraph\Nodes;

use Illuminate\Support\Collection;
use ReflectionProperty;
use Spatie\PhpTypeGraph\Collections\PhpAttributesCollection;

class CompoundItemTypeNode extends TypeNode
{
    /**
     * @param string $name
     * @param string $compoundName
     * @param \Spatie\PhpTypeGraph\Nodes\BaseTypeNode|\Spatie\PhpTypeGraph\Nodes\UnionTypeNode|\Spatie\PhpTypeGraph\Nodes\CompoundTypeNode|\Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode|\Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode|\Spatie\PhpTypeGraph\Nodes\UnknownTypeNode|\Spatie\PhpTypeGraph\Nodes\CollectionTypeNode $node
     * @param \ReflectionProperty|null $reflection
     * @param \Spatie\PhpTypeGraph\Collections\PhpAttributesCollection $attributes
     */
    public function __construct(
        public string $name,
        public string $compoundName,
        public BaseTypeNode|UnionTypeNode|CompoundTypeNode|IntersectionTypeNode|ReferenceTypeNode|UnknownTypeNode|CollectionTypeNode $node,
        public ?ReflectionProperty $reflection,
        public PhpAttributesCollection $attributes,
    ) {
        parent::__construct();
    }

    public function toArray()
    {
        return [
            'kind' => 'compound_item',
            'name' => $this->name,
            'node' => $this->node->toArray(),
        ];
    }

    public function getReflection(): ReflectionProperty
    {
        return $this->reflection ??= new ReflectionProperty($this->compoundName, $this->name);
    }

    public function __toString()
    {
        return '$' . $this->name;
    }
}
