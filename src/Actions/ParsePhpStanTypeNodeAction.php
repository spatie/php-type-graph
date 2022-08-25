<?php

namespace Spatie\PhpTypeGraph\Actions;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\CollectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Exception;
use Illuminate\Support\Enumerable;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode as PhpStanArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode as PhpStanArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode as PhpStanGenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode as PhpStanIdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode as PhpStanTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode as PhpStanUnionTypeNode;

class ParsePhpStanTypeNodeAction
{
    public function execute(
        NodesCollection $nodes,
        Context $context,
        PhpStanTypeNode $node,
    ): ?TypeNode {
        if ($node instanceof PhpStanIdentifierTypeNode) {
            return $this->resolveTypeNode($nodes, $context, $node->name);
        }

        if ($node instanceof PhpStanGenericTypeNode) {
            return $this->createGenericCollectionNode($nodes, $context, $node);
        }

        if ($node instanceof PhpStanUnionTypeNode) {
            return $this->createUnionTypeNode($nodes, $context, $node);
        }

        if ($node instanceof PhpStanArrayTypeNode) {
            $collectionTypeNode = $this->resolveTypeNode($nodes, $context, 'array');
            $valueTypeNode = $this->resolveTypeNode($nodes, $context, $node->type);

            if ($collectionTypeNode === null || $valueTypeNode === null) {
                return null;
            }

            return new CollectionTypeNode(
                $collectionTypeNode,
                $this->resolveDefaultArrayKeyNode($nodes, $context),
                $valueTypeNode
            );
        }

        if ($node instanceof PhpStanArrayShapeNode) {
            // TODO

            return null;
        }

        throw new Exception('Unknown PHPstan node');
    }

    private function createGenericCollectionNode(
        NodesCollection $nodes,
        Context $context,
        PhpStanGenericTypeNode $node
    ): ?TypeNode {
        $collectionNode = $this->execute($nodes, $context, $node->type);

        $isCollection = ($collectionNode instanceof BaseTypeNode && $collectionNode->type === 'array')
            || ($collectionNode instanceof CompoundTypeNode) && $collectionNode->parentNodes->has(Enumerable::class);

        if (! $isCollection) {
            return null;
        }

        $genericTypes = array_map(
            fn (PhpStanTypeNode $type) => $this->execute($nodes, $context, $type),
            $node->genericTypes
        );

        if (count($genericTypes) !== count(array_filter($genericTypes))) {
            return null;
        }

        return match (count($genericTypes)) {
            0 => $collectionNode,
            1 => new CollectionTypeNode(
                $collectionNode,
                $this->resolveDefaultArrayKeyNode($nodes, $context),
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
        NodesCollection $nodes,
        Context $context,
        PhpStanUnionTypeNode $node
    ): ?TypeNode {
        $types = array_map(
            fn (PhpStanTypeNode $type) => $this->execute($nodes, $context, $type),
            $node->types
        );

        return new UnionTypeNode(NodesCollection::create($types));
    }

    private function resolveTypeNode(
        NodesCollection $nodes,
        Context $context,
        string $name,
    ): ?TypeNode {
        return $nodes[$context->getNamespaceAliases()[$name] ?? ltrim($name, '\\')] ?? null;
    }

    private function resolveDefaultArrayKeyNode(
        NodesCollection $nodes,
        Context $context,
    ): UnionTypeNode {
        return new UnionTypeNode(NodesCollection::create([
            $this->resolveTypeNode($nodes, $context, 'string'),
            $this->resolveTypeNode($nodes, $context, 'int'),
        ]));
    }
}
