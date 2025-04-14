<?php

namespace App\GraphQL\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Resolver
{
    public function __construct(
        public readonly ?string $className = null,
    ) {
    }
}
