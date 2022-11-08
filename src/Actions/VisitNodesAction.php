<?php

namespace Spatie\PhpTypeGraph\Actions;

use Exception;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Enums\NodeVisitorOperation;
use Spatie\PhpTypeGraph\Exceptions\NodeTraversalException;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\CollectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\Nodes\UnknownTypeNode;
use Spatie\PhpTypeGraph\Visitors\TypeNodeVisitor;
use Throwable;

class VisitNodesAction
{
    public function execute(
        NodesCollection $nodes,
        TypeNodeVisitor $visitor,
    ): void {
        $visitor->beforeTraverse($nodes);

        foreach ($nodes as $index => $node) {
            $nodes[$index] = $this->visitRootNode($visitor, $node) ?? $node;
        }

        $visitor->afterTraverse($nodes);
    }

    private function visitRootNode(
        TypeNodeVisitor $visitor,
        BaseTypeNode|CompoundTypeNode|UnknownTypeNode $node
    ): TypeNode|null {
        if ($node instanceof CompoundTypeNode) {
            $node = $visitor->enterNode($node) ?? $node;

            foreach ($node->items as $index => $item) {
                $node->items[$index] = $this->visitNode($visitor, $item) ?? $item;
            }

            foreach ($node->childNodes as $index => $child) {
                $node->childNodes[$index] = $this->visitNode($visitor, $child) ?? $child;
            }

            foreach ($node->parentNodes as $index => $parent) {
                $node->parentNodes[$index] = $this->visitNode($visitor, $parent) ?? $parent;
            }

            return $visitor->leaveNode($node) ?? $node;
        }

        return $this->visitNode($visitor, $node);
    }

    private function visitNode(
        TypeNodeVisitor $visitor,
        BaseTypeNode|CompoundTypeNode|CompoundItemTypeNode|CollectionTypeNode|ReferenceTypeNode|IntersectionTypeNode|UnionTypeNode|UnknownTypeNode $node
    ): TypeNode|null {
        if ($node instanceof CompoundTypeNode) {
            // Already executed by root check

            return $node;
        }

        $node = $visitor->enterNode($node) ?? $node;

        if ($node instanceof CompoundItemTypeNode) {
            $node->node = $this->visitNode($visitor, $node->node) ?? $node->node;
        }

        if ($node instanceof CollectionTypeNode) {
            $node->collectionType = $this->visitNode($visitor, $node->collectionType) ?? $node->collectionType;
            $node->keyType = $this->visitNode($visitor, $node->keyType) ?? $node->keyType;
            $node->valueType = $this->visitNode($visitor, $node->valueType) ?? $node->valueType;
        }

        if ($node instanceof IntersectionTypeNode || $node instanceof UnionTypeNode) {
            foreach ($node->nodes as $index => $childNode) {
                $node->nodes[$index] = $this->visitNode($visitor, $childNode) ?? $childNode;
            }
        }

        return $visitor->leaveNode($node) ?? $node;
    }
}
