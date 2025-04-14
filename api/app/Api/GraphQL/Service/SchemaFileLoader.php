<?php

namespace App\Api\GraphQL\Service;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaExtender;
use GraphQL\Utils\SchemaPrinter;

final readonly class SchemaFileLoader
{
    public function __construct(
        private SchemaFileConfig $schemaFileConfig,
    ) {
    }

    public function parseSchemaFiles(): ?Schema
    {
        try {
            $schemaContent = $this->schemaFileConfig->getSchemaFileContents();
            if (empty($schemaContent)) {
                throw new Exception('No schema content found. Make sure you have at least one .graphql file.');
            }

            return BuildSchema::build($schemaContent);
        } catch (Exception $e) {
            // TODO Log the error or handle it as appropriate
            return null;
        }
    }

    /**
     * @throws SyntaxError
     * @throws Error
     * @throws SerializationError
     * @throws \JsonException
     * @throws Exception
     */
    public function mergeSchemaWithExisting(Schema $existingSchema, Schema $fileSchema): Schema
    {
        $printer = new SchemaPrinter();
        $sdl = $printer->doPrint($fileSchema);
        $ast = Parser::parse($sdl);

        return SchemaExtender::extend($existingSchema, $ast);
    }
}
