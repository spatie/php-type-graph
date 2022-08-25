<?php

namespace Spatie\PhpTypeGraph\Traversers;

use Spatie\PhpTypeGraph\Actions\ParsePhpStanTypeNodeAction;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\CompoundItemTypeNode;
use Spatie\PhpTypeGraph\Nodes\CompoundTypeNode;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;
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
use Throwable;

class AddDocblockTypesTraverser extends Traverser
{
    public function types(): string | array | null
    {
        return CompoundTypeNode::class;
    }

    public function traverseClassChildren(): bool
    {
        return false;
    }

    public function traverseClassParents(): bool
    {
        return false;
    }

    public function handle(
        TypeGraphConfig $typeGraphConfig,
        CompoundTypeNode $node,
    ) {
        $docTags = $this->resolveDocTags($node);

        if (count($docTags) === 0) {
            return $node;
        }

        try {
            $contextFactory = new ContextFactory();
            $context = $contextFactory->createFromReflector($node->reflection);
        } catch (Throwable) {
            return $node;
        }

        foreach ($docTags as $docTag) {
            $this->applyDocTypeToNode($typeGraphConfig->nodes, $node, $docTag, $context);
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
        NodesCollection $nodes,
        CompoundTypeNode $compoundTypeNode,
        PropertyTagValueNode | ParamTagValueNode | VarTagValueNode $docTagNode,
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

        $parsedTypeNode = (new ParsePhpStanTypeNodeAction())->execute(
            $nodes,
            $context,
            $docTagNode->type,
        );

        if ($parsedTypeNode !== null) {
            $propertyItemNode->node = $parsedTypeNode;
        }
    }

    private function parseDoc(ReflectionMethod | ReflectionClass | ReflectionProperty $reflection): ?PhpDocNode
    {
        $docComment = $reflection->getDocComment();

        if (empty($docComment)) {
            return null;
        }

        $lexer = new Lexer();
        $constExprParser = new ConstExprParser();
        $parser = new PhpDocParser(new TypeParser($constExprParser), $constExprParser);

        return $parser->parse(new TokenIterator($lexer->tokenize($docComment)));
    }
}
