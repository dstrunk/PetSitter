<?php

namespace App\GraphQL\Service;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use ReflectionMethod;
use ReflectionNamedType;
use Tempest\Container\Container;

final class SchemaBuilder
{
    public function __construct(
        private readonly Container $container,
        private readonly TypeRegistry $typeRegistry,
        private readonly ModelTypeMapper $modelTypeMapper,
        private readonly SchemaFileLoader $schemaFileLoader,
        private readonly ResolverConfig $resolverConfig,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function buildSchema(): Schema
    {
        // Load schema from files
        $fileSchema = $this->schemaFileLoader->parseSchemaFiles();
        if (!$fileSchema instanceof Schema) {
            throw new \Exception('Failed to load GraphQL schema');
        }

        $fileSchema = $this->attachDiscoveredResolvers($fileSchema, $this->resolverConfig);
        $fileSchema = $this->attachDefaultResolvers($fileSchema, $this->resolverConfig);

        return $fileSchema;
    }

    private function attachDiscoveredResolvers(Schema $schema, ResolverConfig $resolverConfig): Schema
    {
        // Attach query field resolvers
        $queryType = $schema->getQueryType();
        if ($queryType) {
            foreach ($resolverConfig->queries as $fieldName => $fieldInfo) {
                if ($queryType->hasField($fieldName)) {
                    $field = $queryType->getField($fieldName);
                    $field->resolveFn = function ($root, $args) use ($fieldInfo) {
                        $instance = $this->container->get($fieldInfo['className']);
                        $method = new \ReflectionMethod($fieldInfo['className'], $fieldInfo['methodName']);
                        return $method->invoke($instance, ...$this->resolveArguments($method, $args));
                    };
                }
            }
        }

        // Attach mutation field resolvers
        $mutationType = $schema->getMutationType();
        if ($mutationType) {
            foreach ($resolverConfig->mutations as $fieldName => $fieldInfo) {
                if ($mutationType->hasField($fieldName)) {
                    $field = $mutationType->getField($fieldName);
                    $field->resolveFn = function ($root, $args) use ($fieldInfo) {
                        $instance = $this->container->get($fieldInfo['className']);
                        $method = new \ReflectionMethod($fieldInfo['className'], $fieldInfo['methodName']);
                        return $method->invoke($instance, ...$this->resolveArguments($method, $args));
                    };
                }
            }
        }

        return $schema;
    }

    private function attachDefaultResolvers(Schema $schema, ResolverConfig $resolverConfig): Schema
    {
        $typeMap = $schema->getTypeMap();

        foreach ($typeMap as $typeName => $type) {
            if (str_starts_with($typeName, '__') || !($type instanceof ObjectType)) {
                continue;
            }

            $fields = $type->getFields();

            foreach ($fields as $fieldName => $field) {
                if ($field->resolveFn === null) {
                    $field->resolveFn = $this->createFieldResolver($typeName, $fieldName, $resolverConfig);
                }
            }
        }

        return $schema;
    }

    private function createFieldResolver(string $typeName, string $fieldName, ResolverConfig $resolverConfig): callable
    {
        return function ($source, $args, $context, ResolveInfo $info) use ($typeName, $fieldName, $resolverConfig) {
            if ($source === null) {
                return null;
            }

            // Get the class of the source object
            $sourceClass = get_class($source);

            // Check if we have a resolver for this model class
            if ($resolverConfig->hasModelResolver($sourceClass)) {
                $resolverClass = $resolverConfig->getModelResolver($sourceClass);

                // Check if resolver has a method matching the field name
                if (method_exists($resolverClass, $fieldName)) {
                    $resolver = $this->container->get($resolverClass);
                    return $resolver->{$fieldName}();
                }
            }

            // Next priority: method on the model
            $getterMethod = 'get' . ucfirst($fieldName);
            if (method_exists($source, $getterMethod)) {
                return $source->{$getterMethod}();
            }

            if (method_exists($source, $fieldName)) {
                return $source->{$fieldName}();
            }

            // Final priority: property on the model
            if (property_exists($source, $fieldName)) {
                return $source->{$fieldName};
            }

            // Check for array access
            if (is_array($source) && array_key_exists($fieldName, $source)) {
                return $source[$fieldName];
            }

            return null;
        };
    }

    private function resolveArguments(ReflectionMethod $method, array $args): array
    {
        $resolvedArgs = [];

        foreach ($method->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();

            // If it's an array parameter, pass the entire args array
            if ($paramType && $paramType instanceof ReflectionNamedType && $paramType->getName() === 'array') {
                $resolvedArgs[] = $args;
                continue;
            }

            // Otherwise pass the specific arg value or default
            if (array_key_exists($paramName, $args)) {
                $resolvedArgs[] = $args[$paramName];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $resolvedArgs[] = $parameter->getDefaultValue();
            } else {
                $resolvedArgs[] = null;
            }
        }

        return $resolvedArgs;
    }
}
