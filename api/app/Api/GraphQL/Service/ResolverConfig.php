<?php

namespace App\Api\GraphQL\Service;

use Tempest\Container\Singleton;

#[Singleton]
final class ResolverConfig
{
    private(set) array $resolvers = [];
    private(set) array $queries = [];
    private(set) array $mutations = [];
    private(set) array $modelResolvers = [];

    public function addResolver(string $resolver, ?string $classToResolve = null): void
    {
        if (isset($this->resolvers[$resolver])) {
            return;
        }

        $this->resolvers[$resolver] = $classToResolve;

        if ($classToResolve !== null) {
            $this->modelResolvers[$classToResolve] = $resolver;
        }
    }

    public function addQuery(string $query, string $className, string $methodName): void
    {
        if (isset($this->queries[$query])) {
            return;
        }

        $this->queries[$query] = [
            'className' => $className,
            'methodName' => $methodName,
        ];
    }

    public function addMutation(string $mutation, string $className, string $methodName): void
    {
        if (isset($this->mutations[$mutation])) {
            return;
        }

        $this->mutations[$mutation] = [
            'className' => $className,
            'methodName' => $methodName,
        ];
    }

    public function getModelResolver(string $className): ?string
    {
        return $this->modelResolvers[$className] ?? null;
    }

    public function hasModelResolver(string $modelClass): bool
    {
        return isset($this->modelResolvers[$modelClass]);
    }
}
