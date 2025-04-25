<?php

namespace App\Test\Utils\Controller\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RunDuring
{
    public function __construct(
        public readonly string $hook = 'setUp',
        public readonly int $priority = 10,
    ) {
    }
}
