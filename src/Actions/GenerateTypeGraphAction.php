<?php

namespace Spatie\PhpTypeGraph\Actions;

use ReflectionClass;
use ReflectionException;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\NodeFactories\NodeFactory;
use Spatie\PhpTypeGraph\ValueObjects\TypeGraphConfig;
use Spatie\PhpTypeGraph\Visitors\RemoveTypeNodeVisitorReferenceNodesVisitor;
use Spatie\PhpTypeGraph\Visitors\TypeNodeVisitor;

class GenerateTypeGraphAction
{
    public function __construct(
        private ResolveInvertedClassReferenceMapAction $resolveInvertedClassReferenceMapAction,
        private VisitNodesAction $visitNodesAction,
    ) {
    }

    public function execute(
        array $directories,
        array $visitors = [],
    ): NodesCollection {
        $nodes = new NodesCollection();

        $classReferences = $this->resolveInvertedClassReferenceMapAction->execute($directories);

        $config = new TypeGraphConfig(
            $nodes,
            $classReferences,
        );

        $factory = new NodeFactory($config);

        $reflectionClassFactory = $factory->reflectionClass();
        $failedNodeFactory = $factory->unknownNode();

        foreach ($classReferences as $classReference) {
            try {
                $reflectionClassFactory->create(new ReflectionClass($classReference->name));
            } catch (ReflectionException) {
                $failedNodeFactory->create($classReference->name);
            }
        }

        collect($visitors)
            ->prepend(RemoveTypeNodeVisitorReferenceNodesVisitor::class)
            ->unique()
            ->map(fn (string $visitorClass) => new $visitorClass(
                $factory,
            ))
            ->each(function (TypeNodeVisitor $visitor) use (&$nodes) {
                $this->visitNodesAction->execute($nodes, $visitor);
            });

        return $nodes;
    }
}
