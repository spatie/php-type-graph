<?php

namespace Spatie\PhpTypeGraph\Visitors;

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\NodeFactories\NodeFactory;
use Spatie\PhpTypeGraph\NodeFactories\PhpStanNodeFactory;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Throwable;

class AddDocTypesVisitor extends AbstractTypeNodeVisitor
{
    private NodesCollection $nodes;

    private PhpDocParser $parser;

    private Lexer $lexer;

    public function __construct(NodeFactory $nodeFactory)
    {
        parent::__construct($nodeFactory);

        $constExprParser = new ConstExprParser();
        $this->parser = new PhpDocParser(new TypeParser($constExprParser), $constExprParser);
        $this->lexer = new Lexer();
    }

    public function beforeTraverse(NodesCollection $nodes)
    {
        $this->nodes = $nodes;
    }

    public function leaveNode(TypeNode $node): TypeNode|null
    {
        if (! $node instanceof CompoundTypeNode) {
            return null;
        }

        $docTags = $this->resolveDocTags($node);

        if (count($docTags) === 0) {
            return null;
        }

        try {
            $contextFactory = new ContextFactory();
            $context = $contextFactory->createFromReflector($node->reflection);
        } catch (Throwable) {
            return null;
        }

        foreach ($docTags as $docTag) {
            $this->applyDocTypeToNode($node, $docTag, $context);
        }

        return $node;
    }

    /** @return array<PropertyTagValueNode|ParamTagValueNode|VarTagValueNode> */
    private function resolveDocTags(CompoundTypeNode $node): array
    {
        $docTags = [];

        if ($docNode = $this->parseDoc($node->reflection)) {
            $docTags = array_merge(
                $docTags,
                $docNode->getPropertyTagValues(),
                $docNode->getPropertyReadTagValues(),
                $docNode->getPropertyWriteTagValues(),
            );
        }

        foreach ($node->items as $item) {
            /** @var CompoundItemTypeNode $item */
            if ($docNode = $this->parseDoc($item->reflection)) {
                foreach ($docNode->getVarTagValues() as $docTag) {
                    if (empty($docTag->variableName)) {
                        $docTag->variableName = $item->name;
                    }

                    $docTags[] = $docTag;
                }
            }
        }

        if (! $node->reflection->hasMethod('__construct')) {
            return $docTags;
        }

        if ($docNode = $this->parseDoc($node->reflection->getMethod('__construct'))) {
            $docTags = array_merge(
                $docTags,
                $docNode->getParamTagValues(),
            );
        }

        return $docTags;
    }

    private function applyDocTypeToNode(
        CompoundTypeNode $compoundTypeNode,
        PropertyTagValueNode|ParamTagValueNode|VarTagValueNode $docTagNode,
        Context $context,
    ) {
        $name = match (true) {
            $docTagNode instanceof PropertyTagValueNode => $docTagNode->propertyName,
            $docTagNode instanceof ParamTagValueNode => $docTagNode->parameterName,
            $docTagNode instanceof VarTagValueNode => $docTagNode->variableName,
        };

        $name = ltrim($name, '$');

        /** @var CompoundItemTypeNode|null $propertyItemNode */
        $propertyItemNode = $compoundTypeNode->items->get($name);

        if ($propertyItemNode === null) {
            return;
        }

        $parsedTypeNode = (new PhpStanNodeFactory($this->nodes, $this->nodeFactory))->create($context, $docTagNode->type);

        if ($parsedTypeNode !== null) {
            $propertyItemNode->node = $parsedTypeNode;
        }
    }

    private function parseDoc(ReflectionMethod|ReflectionClass|ReflectionProperty $reflection): ?PhpDocNode
    {
        $docComment = $reflection->getDocComment();

        if (empty($docComment)) {
            return null;
        }

        return $this->parser->parse(new TokenIterator($this->lexer->tokenize($docComment)));
    }
}
