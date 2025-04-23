<?php

namespace App\Test\Utils\Model;

use Tempest\Database\IsDatabaseModel;

abstract class AbstractFactory
{
    protected \Faker\Generator $faker;

    public function __construct(
        private readonly string $modelClass,
    )
    {
        if (!isset($this->modelClass)) {
            throw new \LogicException('Factory class must define $modelClass property.');
        }

        if (!class_exists($this->modelClass)) {
            throw new \LogicException('Factory class "' . $this->modelClass . '" does not exist.');
        }

        $traits = class_uses($this->modelClass);
        if (!isset($traits[IsDatabaseModel::class])) {
            throw new \LogicException('Factory class "' . $this->modelClass . '" must use the IsDatabaseModel class.');
        }

        $this->faker = \Faker\Factory::create();
    }

    abstract protected function definition(): array;

    public function make(array $attributes = []): object
    {
        $finalAttributes = array_merge($this->definition(), $attributes);
        $reflection = new \ReflectionClass($this->modelClass);
        $constructor = $reflection->getConstructor();

        $parameters = $constructor?->getParameters() ?? [];
        $arguments = empty($parameters) ? [] : $this->prepareConstructorArguments($parameters, $finalAttributes);

        $instance = ($constructor?->getNumberOfRequiredParameters() ?? 0) === 0
            ? $reflection->newInstance()
            : $reflection->newInstanceArgs($arguments);

        foreach ($finalAttributes as $attribute => $value) {
            if (property_exists($instance, $attribute)) {
                $instance->$attribute = $value;
            }
        }

        return $instance;
    }

    public function create(array $attributes = []): object
    {
        $model = $this->make($attributes);

        $model->save();

        return $model;
    }

    protected function prepareConstructorArguments(array $parameters, array &$attributes = []): array
    {
        $arguments = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (isset($attributes[$name])) {
                // The attribute was provided directly, use it
                $arguments[] = $attributes[$name];
                unset($attributes[$name]);
            } // Handle related model (relationship)
            else if ($type instanceof \ReflectionNamedType &&
                !$type->isBuiltin() &&
                $this->isModelClass($type->getName())) {
                // This is a model relationship
                $arguments[] = $this->resolveRelationship($type->getName(), $name, $attributes);
            } else {
                // No value provided for this parameter and no handling strategy
                throw new \LogicException(
                    "Missing required parameter '{$name}' of type '{$type}' for model '{$this->modelClass}'. " .
                    'Ensure your factory provides all required constructor parameters.'
                );
            }
        }

        return $arguments;
    }

    protected function resolveRelationship(string $relatedModelClass, string $relationName, array &$attributes): object
    {
        // Check if explicit related instance was provided
        if (isset($attributes[$relationName])) {
            if (is_object($attributes[$relationName]) && is_a($attributes[$relationName], $relatedModelClass)) {
                // A model instance was provided directly
                $related = $attributes[$relationName];
                unset($attributes[$relationName]);

                return $related;
            }

            if (is_array($attributes[$relationName])) {
                // An array of attributes for the related model was provided
                $related = Factory::for($relatedModelClass)->create($attributes[$relationName]);
                unset($attributes[$relationName]);

                return $related;
            }

            if ($attributes[$relationName] instanceof AbstractFactory) {
                // A factory instance was provided
                $related = $attributes[$relationName]->create();
                unset($attributes[$relationName]);

                return $related;
            }
        }

        // Create a default related model
        return Factory::for($relatedModelClass)->create();
    }

    protected function isModelClass(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        $traits = class_uses($class);

        return in_array(IsDatabaseModel::class, $traits);
    }
}
