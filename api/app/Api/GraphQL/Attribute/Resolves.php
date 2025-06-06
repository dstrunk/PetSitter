<?php

namespace App\Api\GraphQL\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Resolves
{
    public function __construct(
        public readonly ?string $className = null,
    ) {
    }
}
