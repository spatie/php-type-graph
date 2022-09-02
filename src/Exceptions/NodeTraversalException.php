<?php

namespace Spatie\PhpTypeGraph\Exceptions;

use Exception;
use Spatie\PhpTypeGraph\Collections\NodesCollection;
use Spatie\PhpTypeGraph\Nodes\TypeNode;
use Throwable;

class NodeTraversalException extends Exception
{
    public array $path;

    public function __construct(
        TypeNode|NodesCollection $node,
        Throwable $previous,
    ) {
        parent::__construct("Could not traverse nodes", previous: $previous);

        $this->addNodeToPath($node);
    }

    public function addNodeToPath(TypeNode|NodesCollection $node): self
    {
        $this->path[] = $node;

        return $this;
    }
}
