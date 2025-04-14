<?php

namespace App\Api\GraphQL\Service;

use Tempest\Container\Singleton;

#[Singleton]
final class SchemaFileConfig
{
    private(set) array $files = [];

    public function addSchemaFile(string $file): void
    {
        if (in_array($file, $this->files)) {
            return;
        }

        $this->files[] = $file;
    }

    public function getSchemaFileContents(): string
    {
        $schemaDefinitions = '';
        foreach ($this->files as $file) {
            $contents = file_get_contents($file);
            if ($contents !== false) {
                $schemaDefinitions .= $contents . "\n";
            }
        }

        return $schemaDefinitions;
    }
}
