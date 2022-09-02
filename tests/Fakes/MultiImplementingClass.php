<?php

namespace Spatie\PhpTypeGraph\Tests\Fakes;

use JsonSerializable;

class MultiImplementingClass implements ClassInterface, JsonSerializable
{
    public function jsonSerialize(): mixed
    {
    }
}
