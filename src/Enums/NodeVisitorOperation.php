<?php

namespace Spatie\PhpTypeGraph\Enums;


enum NodeVisitorOperation
{
    case DontTraverseChildren;
    case RemoveNode;
}
