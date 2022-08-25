<?php

namespace Spatie\PhpTypeGraph\Actions;

use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\CollectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TraverseTypeGraphConfiguration;
use Closure;

class TraverseNodesCollectionAction
{
    private array $seen;

    public function execute(
        NodesCollection $nodes,
        Closure $closure,
        string | array | null $types = null,
        bool $traverseClassItems = true,
        bool $traverseClassChildren = true,
        bool $traverseClassParents = true,

    ): void {
        $this->seen = [];

        $types = is_string($types) ? [$types] : $types;

        $config = new TraverseTypeGraphConfiguration(
            $types,
            $traverseClassItems,
            $traverseClassChildren,
            $traverseClassParents
        );

        $this->traverseNodes($nodes, $closure, $config);
    }

    public function traverseNodes(
        NodesCollection $nodes,
        Closure $closure,
        TraverseTypeGraphConfiguration $config,
    ): NodesCollection {
        foreach ($nodes as $i => $node) {
            $nodes[$i] = $this->traverseSingleNode($node, $closure, $config);
        }

        return $nodes;
    }

    private function traverseSingleNode(
        TypeNode $node,
        Closure $closure,
        TraverseTypeGraphConfiguration $config,
    ): TypeNode {
        if(in_array(spl_object_id($node), $this->seen)){
            return $node;
        }

        $this->seen[] = spl_object_id($node);

        if ($node instanceof CollectionTypeNode) {
            $node->valueType = $this->traverseSingleNode($node->valueType, $closure, $config);
            $node->keyType = $this->traverseSingleNode($node->keyType, $closure, $config);
            $node->collectionType = $this->traverseSingleNode($node->collectionType, $closure, $config);
        }

        if($node instanceof CompoundTypeNode && $config->includeClassProperties){
            $node->items = $this->traverseNodes($node->items, $closure, $config);
        }

        if ($node instanceof CompoundTypeNode && $config->includeClassParents) {
            $node->parentNodes = $this->traverseNodes($node->parentNodes, $closure, $config);
        }

        if($node instanceof CompoundTypeNode && $config->includeClassChildren){
            $node->childNodes = $this->traverseNodes($node->childNodes, $closure, $config);
        }

        if($node instanceof CompoundItemTypeNode){
            $node->node = $this->traverseSingleNode($node->node, $closure, $config);
        }

        if ($node instanceof IntersectionTypeNode || $node instanceof UnionTypeNode) {
            $node->nodes = $this->traverseNodes($node->nodes, $closure, $config);
        }

        if ($config->types === null || in_array($node::class, $config->types)) {
            return $closure($node) ?? $node;
        }

        return $node;
    }
}
