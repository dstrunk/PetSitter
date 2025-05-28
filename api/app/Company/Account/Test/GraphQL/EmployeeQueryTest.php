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
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'about' => $employee->about,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'isAdmin' => true,
                ],
            ],
        ]);

        $response->assertJsonPath('data.employee.id', $employee->id);
    }

    #[Test]
    public function it_can_create_an_employee(): void
    {
        $input = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'about' => $this->faker->text(),
            'isAdmin' => $this->faker->boolean(),
        ];

        $response = $this->mutation(/** @lang GraphQL */'
            mutation CreateEmployee($input: EmployeeInput!) {
                employee(input: $input) {
                    name
                    email
                    phone
                    about
                    isAdmin
                }
            }
        ', [
            'input' => [
                'create' => $input,
            ],
        ]);

        $response->assertJson([
            'data' => [
                'employee' => [
                    'name' => $input['firstName'] . ' ' . $input['lastName'],
                    'email' => $input['email'],
                    'phone' => $input['phone'],
                    'about' => $input['about'],
                    'isAdmin' => $input['isAdmin'],
                ],
            ],
        ]);
    }
}
