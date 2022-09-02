<?php

namespace Spatie\PhpTypeGraph\Nodes;

use ReflectionClass;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Collections\PhpAttributesCollection;

class CompoundTypeNode extends TypeNode
{
    /**
     * @param string $type
     * @param \ReflectionClass|null $reflection
     * @param \Illuminate\Support\Collection<\Spatie\PhpTypeGraph\ValueObjects\PhpAttribute> $attributes
     * @param NodesCollection<CompoundItemTypeNode> $items
     * @param NodesCollection<\Spatie\PhpTypeGraph\Nodes\CompoundTypeNode|\Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode|\Spatie\PhpTypeGraph\Nodes\UnknownTypeNode> $childNodes
     * @param NodesCollection<\Spatie\PhpTypeGraph\Nodes\CompoundTypeNode|\Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode|\Spatie\PhpTypeGraph\Nodes\UnknownTypeNode> $parentNodes
     */
    public function __construct(
        public string $type,
        public ?ReflectionClass $reflection,
        public PhpAttributesCollection $attributes,
        public NodesCollection $items,
        public NodesCollection $childNodes,
        public NodesCollection $parentNodes,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'kind' => 'compound',
            'items' => $this->items->toArray(),
            'child_nodes' => $this->childNodes->toArray(),
            'parent_nodes' => $this->parentNodes->toArray(),
        ];
    }

    public function getItemsRecursively(): NodesCollection
    {
        return $this->parentNodes->filter(
            fn (TypeNode $node) => $node instanceof CompoundTypeNode
        )->flatMap(
            fn (CompoundTypeNode $node) => $node->items
        )->merge($this->items);
    }

    public function getAttributesRecursively(): PhpAttributesCollection
    {
        return new PhpAttributesCollection($this->parentNodes->filter(
            fn (TypeNode $node) => $node instanceof CompoundTypeNode
        )->flatMap(
            fn (CompoundTypeNode $node) => $node->attributes
        )->merge($this->attributes));
    }

    public function getReflection(): ReflectionClass
    {
        return $this->reflection ??= new ReflectionClass($this->type);
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
