<?php

namespace Spatie\PhpTypeGraph\Actions;

use App\Context\Order\Events\OrderCheckedOut;
use Exception;
use PhpParser\Node;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Enums\NodeVisitorOperation;
use Spatie\PhpTypeGraph\Exceptions\NodeTraversalException;
use Spatie\PhpTypeGraph\Nodes\BaseTypeNode;
use Spatie\PhpTypeGraph\Nodes\CollectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\UnknownTypeNode;
use Spatie\PhpTypeGraph\Nodes\IntersectionTypeNode;
use Spatie\PhpTypeGraph\Nodes\ReferenceTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Spatie\PhpTypeGraph\Nodes\UnionTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TraverseTypeGraphConfiguration;
use Closure;
use Spatie\PhpTypeGraph\Visitors\TypeNodeVisitor;
use Throwable;

class VisitNodesAction
{
    private array $seen;

    public function execute(
        NodesCollection $nodes,
        TypeNodeVisitor $visitor,
    ): void {
        $this->seen = [];

        $visitor->beforeTraverse($nodes);

        $this->visitCollection($nodes, $visitor);

        $visitor->afterTraverse($nodes);
    }

    private function visitNode(
        TypeNode $typeNode,
        TypeNodeVisitor $visitor,
    ): ?TypeNode {
        if (array_key_exists(spl_object_id($typeNode), $this->seen)) {
            return $typeNode;
        }

        $this->seen[spl_object_id($typeNode)] = null;

        try {
            $enteredNode = $visitor->enterNode($typeNode);

            if ($enteredNode === null) {
                $enteredNode = $typeNode; // No changes happened
            }

            if ($enteredNode === NodeVisitorOperation::RemoveNode) {
                return null;
            }

            if ($enteredNode !== NodeVisitorOperation::DontTraverseChildren) {
                $enteredNode = $this->visitNodeChildren($enteredNode, $visitor);
            } else {
                $enteredNode = $typeNode; // Keep going on without visiting children
            }

            $leavedNode = $visitor->leaveNode($enteredNode);

            if ($leavedNode === null) {
                $leavedNode = $enteredNode; // No changes happened
            }

            if ($leavedNode === NodeVisitorOperation::RemoveNode) {
                return null;
            }

            return $leavedNode;
        } catch (NodeTraversalException $exception) {
            $exception->addNodeToPath($typeNode);

            throw $exception;
        } catch (Throwable $throwable) {
            throw new NodeTraversalException($typeNode, $throwable);
        }
    }

    private function visitNodeChildren(
        TypeNode $typeNode,
        TypeNodeVisitor $visitor,
    ): TypeNode {
        if ($typeNode instanceof CompoundTypeNode) {
            $typeNode->parentNodes = $this->visitCollection($typeNode->parentNodes, $visitor);
            $typeNode->childNodes = $this->visitCollection($typeNode->childNodes, $visitor);
            $typeNode->items = $this->visitCollection($typeNode->items, $visitor);

            return $typeNode;
        }

        if ($typeNode instanceof CompoundItemTypeNode) {
            $visited = $this->visitNode($typeNode->node, $visitor);

            if ($visited === null) {
                throw new Exception('CompoundItemTypeNode should always have a node');
            }

            $typeNode->node = $visited;

            return $typeNode;
        }

        if ($typeNode instanceof UnionTypeNode || $typeNode instanceof IntersectionTypeNode) {
            $typeNode->nodes = $this->visitCollection($typeNode->nodes, $visitor);

            return $typeNode;
        }

        if ($typeNode instanceof CollectionTypeNode) {
            $visitedCollectionType = $this->visitNode($typeNode->collectionType, $visitor);
            $visitedKeyType = $this->visitNode($typeNode->keyType, $visitor);
            $visitedValueType = $this->visitNode($typeNode->valueType, $visitor);

            if ($visitedCollectionType === null) {
                throw new Exception('CollectionTypeNode should always have a collection type node');
            }

            if ($visitedKeyType === null) {
                throw new Exception('CollectionTypeNode should always have a key type node');
            }

            if ($visitedValueType === null) {
                throw new Exception('CollectionTypeNode should always have a value type node');
            }

            $typeNode->collectionType = $visitedCollectionType;
            $typeNode->keyType = $visitedKeyType;
            $typeNode->valueType = $visitedValueType;

            return $typeNode;
        }

        return $typeNode;
    }

    private function visitCollection(
        NodesCollection $nodes,
        TypeNodeVisitor $visitor,
    ): NodesCollection {
        foreach ($nodes as $i => $node) {
            $visited = $this->visitNode($node, $visitor);

            if ($visited === null) {
                unset($nodes[$i]);
                continue;
            }

            $nodes[$i] = $visited;
        }

        return $nodes;
    }
}
