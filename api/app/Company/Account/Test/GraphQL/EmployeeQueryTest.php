<?php

namespace App\Company\Account\Test\GraphQL;

use App\Company\Account\Model\Employee;
use App\Test\Utils\Controller\AbstractIntegrationTest;
use App\Test\Utils\Controller\Trait\MakesGraphQLRequests;
use App\Test\Utils\Controller\Trait\RefreshesDatabase;
use App\Test\Utils\Model\Factory;
use PHPUnit\Framework\Attributes\Test;

final class EmployeeQueryTest extends AbstractIntegrationTest
{
    use RefreshesDatabase;
    use MakesGraphQLRequests;

    #[Test]
    public function it_can_fetch_an_employee_by_id(): void
    {
        $employee = Factory::for(Employee::class)->admin()->create();

        $response = $this->query(/** @lang GraphQL */'
            query GetEmployee($id: ID!) {
                employee(id: $id) {
                    id
                    name
                    about
                    email
                    phone
                    isAdmin
                }
            }
        ', [
            'id' => (string)$employee->id,
        ]);

        $response->assertJson([
            'data' => [
                'employee' => [
                    'id' => (string)$employee->id,
                    'name' => $employee->getName(),
                    'about' => $employee->about,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'isAdmin' => true,
                ],
            ],
        ]);
    }
}
