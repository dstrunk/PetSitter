<?php

namespace App\Api\GraphQL\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Query
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
