<?php

declare(strict_types=1);

namespace App\Company\Account\GraphQL;

use App\Api\GraphQL\Attribute\Query;
use App\Api\GraphQL\Attribute\Resolver;
use App\Company\Account\Model\Employee;
use Tempest\Database\Id;

#[Resolver(Employee::class)]
final readonly class EmployeeResolver
{
    #[Query('employee')]
    public function queryEmployees(array $args): Employee
    {
        $id = new Id($args['id']);

        return Employee::get($id);
    }
}
