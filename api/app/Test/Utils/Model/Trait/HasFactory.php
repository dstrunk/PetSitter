<?php

namespace App\Test\Utils\Model\Trait;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class HasFactory
{
    public function __construct(
        public string $factoryClass,
    ) {
    }
}
