<?php

use Spatie\PhpTypeGraph\Actions\GenerateTypeGraphAction;
use Spatie\PhpTypeGraph\Actions\ResolveInvertedClassReferenceMapAction;
use Spatie\PhpTypeGraph\Actions\TraverseNodesCollectionAction;
use Spatie\PhpTypeGraph\Actions\VisitNodesAction;
use Spatie\PhpTypeGraph\Exceptions\NodeTraversalException;
use Spatie\PhpTypeGraph\Traversers\AddDocblockTypesTraverser;
use Spatie\PhpTypeGraph\Traversers\AddReferenceNodesTraverser;
use Spatie\PhpTypeGraph\Traversers\RemoveReflectionNodesTraverser;
use Spatie\PhpTypeGraph\Visitors\AddDocTypesVisitor;
use Spatie\PhpTypeGraph\Visitors\RemoveReflectionVisitorTypeNodeVisitor;

it('can test', function () {
    $action = new GenerateTypeGraphAction(
        new ResolveInvertedClassReferenceMapAction(),
        new VisitNodesAction(),
    );

    try {
        ray($action->execute([__DIR__ . '/Fakes'], [
//            AddDocTypesVisitor::class,
//            RemoveReflectionVisitorTypeNodeVisitor::class,
        ]));
//        ray($action->execute([__DIR__ . '/../vendor/spatie/data-transfer-object'], [
//            AddDocblockTypesTraverser::class,
//            RemoveReflectionNodesTraverser::class,
//        ]));
    }catch (NodeTraversalException $exception){
        ray($exception);
        throw $exception;
    }

});

