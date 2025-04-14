<?php


namespace App\GraphQL\Service\Discovery;

use App\GraphQL\Service\SchemaFileConfig;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use function Tempest\Support\Str\ends_with;

final class SchemaFileDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly SchemaFileConfig $schemaFileConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        return;
    }

    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        if (!ends_with($path, '.graphql')) {
            return;
        }

        $this->discoveryItems->add($location, $path);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $path) {
            $this->schemaFileConfig->addSchemaFile($path);
        }
    }
}
