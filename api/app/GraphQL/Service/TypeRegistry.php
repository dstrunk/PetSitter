<?php

namespace App\GraphQL\Service;

use GraphQL\Type\Definition\Type;

final class TypeRegistry
{
    private(set) array $types = [];

    public function get(string $name): ?Type
    {
        return $this->types[$name] ?? null;
    }

    public function register(string $name, Type $type): void
    {
        $this->types[$name] = $type;
    }

    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }

    public function getBuiltInType(string $phpType): ?Type
    {
        return match ($phpType) {
            'string' => Type::string(),
            'int', 'integer' => Type::int(),
            'float', 'double' => Type::float(),
            'boolean', 'bool' => Type::boolean(),
            'array' => Type::listOf(Type::string()),
            default => null,
        };
    }
}
