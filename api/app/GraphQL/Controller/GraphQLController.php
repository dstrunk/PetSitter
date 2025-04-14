<?php

namespace App\GraphQL\Controller;

use App\GraphQL\Service\GraphQLHandler;
use Tempest\Router\ContentType;
use Tempest\Router\Post;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;

final readonly class GraphQLController
{
    public function __construct(
        private GraphQLHandler $graphQLHandler,
    ) {
    }

    #[Post('/graphql')]
    public function __invoke(Request $request): Response
    {
        $query = $request->get('query', '');
        $variables = $request->get('variables', [])?->toArray();
        $operationName = $request->get('operationName');

        $result = $this->graphQLHandler->processRequest($query, $variables, $operationName);

        return new Ok($result)->setContentType(ContentType::JSON);
    }
}
