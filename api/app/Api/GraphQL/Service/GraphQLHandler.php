<?php

namespace App\Api\GraphQL\Service;

use GraphQL\GraphQL;
use ReflectionException;

class GraphQLHandler
{
    public function __construct(
        private readonly SchemaBuilder $schemaBuilder,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws \Exception
     */
    public function processRequest(string $query, ?array $variables = null, ?string $operationName = null): array
    {
        $schema = $this->schemaBuilder->buildSchema();

        try {
            if (empty($query)) {
                return [
                    'errors' => [
                        'message' => 'No query provided',
                    ],
                ];
            }

            $result = GraphQL::executeQuery(
                $schema,
                $query,
                null,
                null,
                $variables,
                $operationName,
            );

            return $result->toArray();
        } catch (\Exception $e) {
            return [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ],
            ];
        }
    }
}
