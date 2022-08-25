<?php

namespace Spatie\PhpTypeGraph\Nodes;

class CollectionTypeNode extends TypeNode
{
    public function __construct(
        public TypeNode $collectionType,
        public TypeNode $keyType,
        public TypeNode $valueType,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
           'kind' => 'collection',
           'collection_type' => $this->collectionType->toArray(),
           'key_type' => $this->keyType->toArray(),
           'value_type' => $this->valueType->toArray(),
        ];
    }

    public function __toString(): string
    {
        return "{$this->collectionType}<{$this->keyType}, {$this->valueType}>";
    }
}
