<?php

use Spatie\PhpTypeGraph\Actions\GenerateTypeGraphAction;
use Spatie\PhpTypeGraph\Actions\ResolveInvertedClassReferenceMapAction;
use Spatie\PhpTypeGraph\Actions\TraverseNodesCollectionAction;

it('can test', function () {
    $action = new GenerateTypeGraphAction(
        new ResolveInvertedClassReferenceMapAction(),
        new TraverseNodesCollectionAction(),
    );

    ray($action->execute()[Spatie\PhpTypeGraph\Nodes\TypeNode::class]);
});
