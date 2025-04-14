<?php

namespace App\Api\GraphQL\Service;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionProperty;
use Tempest\Database\Id;
use Tempest\Database\IsDatabaseModel;

final readonly class ModelTypeMapper
{
    public function __construct(
        private TypeRegistry $typeRegistry,
    ) {
    }

    public function mapModelToType(string $modelClass): Type
    {
        $reflection = new ReflectionClass($modelClass);

        // Get model name for GraphQL type
        $typeName = $reflection->getShortName();

        // Check if we already have this type registered
        if ($this->typeRegistry->has($typeName)) {
            return $this->typeRegistry->get($typeName);
        }

        // Create a new ObjectType for this model
        $objectType = new ObjectType([
            'name' => $typeName,
            'description' => "GraphQL type for {$typeName} model",
            'fields' => fn() => $this->mapModelProperties($reflection),
        ]);

        // Register the new type
        $this->typeRegistry->register($typeName, $objectType);

        return $objectType;
    }

    private function mapModelProperties(ReflectionClass $reflection): array
    {
        $fields = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            // Skip non-public properties
            if (!$property->isPublic()) {
                continue;
            }

            $name = $property->getName();
            $type = $property->getType();

            if (!$type) {
                continue; // Skip properties without type hints
            }

            // Map property to GraphQL field
            $fields[$name] = [
                'type' => $this->mapPhpTypeToGraphQLType($type->getName(), $type->allowsNull()),
                'description' => "The {$name} of the " . $reflection->getShortName(),
                'resolve' => function ($source) use ($name) {
                    // Handle special case for ID
                    if ($name === 'id' && $source->id instanceof Id) {
                        return (string) $source->id;
                    }

                    return $source->{$name};
                }
            ];
        }

        return $fields;
    }

    private function mapPhpTypeToGraphQLType(string $phpType, bool $isNullable): Type
    {
        // Special case for Id
        if ($phpType === Id::class) {
            return $isNullable ? Type::id() : Type::nonNull(Type::id());
        }

        // Check if it's a built-in type
        $builtInType = $this->typeRegistry->getBuiltInType($phpType);
        if ($builtInType) {
            return $isNullable ? $builtInType : Type::nonNull($builtInType);
        }

        // Check if it's another model
        try {
            $reflection = new ReflectionClass($phpType);
            $traits = class_uses($phpType, true);

            if (isset($traits[IsDatabaseModel::class])) {
                $mappedType = $this->mapModelToType($phpType);
                return $isNullable ? $mappedType : Type::nonNull($mappedType);
            }
        } catch (\ReflectionException $e) {
            // Not a class, ignore
        }

        // Default to string if we can't determine the type
        return $isNullable ? Type::string() : Type::nonNull(Type::string());
    }
}
