<?php

use Spatie\PhpTypeGraph\Actions\CacheTypeGraphAction;
use Spatie\PhpTypeGraph\Actions\GenerateTypeGraphAction;
use Spatie\PhpTypeGraph\Actions\ResolveInvertedClassReferenceMapAction;
use Spatie\PhpTypeGraph\Actions\VisitNodesAction;
use Spatie\PhpTypeGraph\Exceptions\NodeTraversalException;
use Spatie\PhpTypeGraph\Traversers\AddDocblockTypesTraverser;
use Spatie\PhpTypeGraph\Traversers\RemoveReflectionNodesTraverser;
use Spatie\PhpTypeGraph\Visitors\AddDocTypesVisitor;
use Spatie\PhpTypeGraph\Visitors\RemoveReflectionVisitorTypeNodeVisitor;

it('can test', function () {
    $action = new GenerateTypeGraphAction(
        new ResolveInvertedClassReferenceMapAction(),
        new VisitNodesAction(),
    );

    try {
        $nodes = $action->execute([__DIR__ . '/../vendor'], [
            AddDocTypesVisitor::class,
            RemoveReflectionVisitorTypeNodeVisitor::class,
        ]);

        (new CacheTypeGraphAction())->execute($nodes);
    } catch (NodeTraversalException $exception) {
        ray($exception);

        throw $exception;
    }
});
