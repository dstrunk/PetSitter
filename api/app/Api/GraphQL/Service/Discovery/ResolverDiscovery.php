<?php

namespace App\Api\GraphQL\Service\Discovery;

use App\Api\GraphQL\Attribute\Mutation;
use App\Api\GraphQL\Attribute\Query;
use App\Api\GraphQL\Attribute\Resolver;
use App\Api\GraphQL\Service\ResolverConfig;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ResolverDiscovery implements Discovery
{
    use IsDiscovery;

    private const string ITEM_RESOLVER = 'resolver';
    private const string ITEM_QUERY = 'query';
    private const string ITEM_MUTATION = 'mutation';

    public function __construct(
        private readonly ResolverConfig $resolverConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        $resolverAttr = $class->getAttribute(Resolver::class);
        if (!$resolverAttr) {
            return;
        }

        $className = $class->getName();
        $optionalResolvedClass = $resolverAttr->className;

        // Add entire resolver
        $this->discoveryItems->add($location, [self::ITEM_RESOLVER, $className, $optionalResolvedClass]);

        foreach ($class->getPublicMethods() as $method) {
            // Add queries
            $queryAttr = $method->getAttribute(Query::class);
            if (!$queryAttr) {
                continue;
            }

            $this->discoveryItems->add($location, [self::ITEM_QUERY, $queryAttr->name, $className, $method->getName()]);

            // Add mutations
            $mutationAttr = $method->getAttribute(Mutation::class);
            if (!$mutationAttr) {
                continue;
            }

            $this->discoveryItems->add($location, [self::ITEM_MUTATION, $mutationAttr->name, $className, $method->getName()]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $item) {
            $itemType = $item[0] ?? null;
            $itemName = $item[1] ?? null;
            $className = $item[2] ?? null;
            $methodName = $item[3] ?? null;

            match ($itemType) {
                self::ITEM_RESOLVER => $this->resolverConfig->addResolver($itemName, $className),
                self::ITEM_QUERY => $this->resolverConfig->addQuery($itemName, $className, $methodName),
                self::ITEM_MUTATION => $this->resolverConfig->addMutation($itemName, $className, $methodName),
                default => null,
            };
        }
    }
}
