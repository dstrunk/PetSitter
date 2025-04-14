<?php

namespace App\GraphQL\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Mutation
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
