<?php

namespace Spatie\PhpTypeGraph\NodeFactories;

use Exception;
use Illuminate\Support\Enumerable;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode as PhpStanArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode as PhpStanArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode as PhpStanGenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode as PhpStanIdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode as PhpStanIntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\OffsetAccessTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode as PhpStanTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode as PhpStanUnionTypeNode;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\CollectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;

class PhpStanNodeFactory
{
    public function __construct(
        private readonly NodesCollection $nodes,
        private readonly NodeFactory $nodeFactory,
    ) {
    }

    public function create(
        Context $context,
        PhpStanTypeNode $node,
    ): ?TypeNode {
        if ($node instanceof PhpStanIdentifierTypeNode) {
            return $this->resolveTypeNode($context, $node->name);
        }

        if ($node instanceof PhpStanGenericTypeNode) {
            return $this->createGenericCollectionNode($context, $node);
        }

        if ($node instanceof PhpStanUnionTypeNode) {
            return $this->createUnionTypeNode($context, $node);
        }

        if ($node instanceof PhpStanIntersectionTypeNode) {
            return $this->createIntersectionTypeNode($context, $node);
        }

        if ($node instanceof PhpStanArrayTypeNode) {
            $collectionTypeNode = $this->resolveTypeNode($context, 'array');
            $valueTypeNode = $this->resolveTypeNode($context, $node->type);

            if ($collectionTypeNode === null || $valueTypeNode === null) {
                return null;
            }

            return new CollectionTypeNode(
                $collectionTypeNode,
                $this->resolveDefaultArrayKeyNode($context),
                $valueTypeNode
            );
        }

        if ($node instanceof NullableTypeNode && $subNode = $this->create($context, $node->type)) {
            return new UnionTypeNode(NodesCollection::create([
                $this->resolveTypeNode($context, 'null'),
                $subNode,
            ]));
        }

        if ($node instanceof PhpStanArrayShapeNode
            || $node instanceof CallableTypeNode
            || $node instanceof ConstTypeNode
            || $node instanceof OffsetAccessTypeNode
            || $node instanceof NullableTypeNode
        ) {
            // TODO

            return null;
        }

        ray($node, $context);

        throw new Exception('Unknown PHPstan node');
    }

    private function createGenericCollectionNode(
        Context $context,
        PhpStanGenericTypeNode $node
    ): ?TypeNode {
        $collectionNode = $this->create($context, $node->type);

        $isCollection = ($collectionNode instanceof BaseTypeNode && $collectionNode->type === 'array')
            || ($collectionNode instanceof CompoundTypeNode) && $collectionNode->parentNodes->has(Enumerable::class);

        if (! $isCollection) {
            return null;
        }

        $genericTypes = array_map(
            fn (PhpStanTypeNode $type) => $this->create($context, $type),
            $node->genericTypes
        );

        if (count($genericTypes) !== count(array_filter($genericTypes))) {
            return null;
        }

        return match (count($genericTypes)) {
            0 => $collectionNode,
            1 => new CollectionTypeNode(
                $collectionNode,
                $this->resolveDefaultArrayKeyNode($context),
                $genericTypes[0]
            ),
            2 => new CollectionTypeNode(
                $collectionNode,
                $genericTypes[0],
                $genericTypes[1],
            ),
            default => throw new Exception(),
        };
    }

    private function createUnionTypeNode(
        Context $context,
        PhpStanUnionTypeNode $node
    ): ?TypeNode {
        $types = array_map(
            fn (PhpStanTypeNode $type) => $this->create($context, $type),
            $node->types
        );

        if (in_array(null, $types)) {
            return null;
        }

        return new UnionTypeNode(NodesCollection::create($types));
    }

    private function createIntersectionTypeNode(
        Context $context,
        PhpStanIntersectionTypeNode $node
    ): ?TypeNode {
        $types = array_map(
            fn (PhpStanTypeNode $type) => $this->create($context, $type),
            $node->types
        );

        if (in_array(null, $types)) {
            return null;
        }

        return new IntersectionTypeNode(NodesCollection::create($types));
    }

    private function resolveTypeNode(
        Context $context,
        string $name,
    ): ?TypeNode {
        $type = $context->getNamespaceAliases()[$name] ?? ltrim($name, '\\');

        return $this->nodes[$type] ?? $this->nodeFactory->create($type);
    }

    private function resolveDefaultArrayKeyNode(
        Context $context,
    ): UnionTypeNode {
        return new UnionTypeNode(NodesCollection::create([
            $this->resolveTypeNode($context, 'string'),
            $this->resolveTypeNode($context, 'int'),
        ]));
    }
}
