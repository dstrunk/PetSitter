<?php

namespace App\Test\Utils\Controller\Trait;

use App\Api\GraphQL\Service\GraphQLHandler;
use App\Test\Utils\Controller\TestResponse;
use function Tempest\get;

trait MakesGraphQLRequests
{
    protected ?GraphQLHandler $graphQLHandler = null;

    protected function registerMakesGraphQLRequests(): void
    {
        if (method_exists(get_parent_class($this), 'addTearDownCallback')) {
            $this->addTearDownCallback(function () {
                restore_error_handler();
                restore_exception_handler();
            });
        }
    }

    protected function query(string $query, array $variables = [], ?string $operationName = null): TestResponse
    {
        $result = $this->getGraphQLHandler()->processRequest($query, $variables, $operationName);

        return new TestResponse($result);
    }

    /**
     * Basically an alias for `query`, but it makes tests more readable.
     */
    protected function mutation(string $query, array $variables = [], ?string $operationName = null): TestResponse
    {
        return $this->query($query, $variables, $operationName);
    }

    private function getGraphQLHandler(): GraphQLHandler
    {
        return $this->graphQLHandler ??= $this->container->get(GraphQLHandler::class);
    }
}
