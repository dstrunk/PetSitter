<?php

declare(strict_types=1);

namespace App\Company\Account\GraphQL;

use App\Api\GraphQL\Attribute\Mutation;
use App\Api\GraphQL\Attribute\Query;
use App\Api\GraphQL\Attribute\Resolves;
use App\Company\Account\Model\Employee;
use Tempest\Database\Id;

#[Resolves(Employee::class)]
final readonly class EmployeeResolver
{
    #[Query('employee')]
    public function queryEmployee(array $args): Employee
    {
        $id = new Id($args['id']);

        return Employee::get($id);
    }

    #[Mutation('employee')]
    public function mutationEmployee(array $args): Employee
    {
        $input = $args['input'];

        if (isset($input['create'])) {
            return $this->createEmployee($input['create']);
        }

        throw new \InvalidArgumentException('Mutation does not support this action.');
    }

    public function name(Employee $employee): string
    {
        return $employee->first_name . ' ' . $employee->last_name;
    }

    private function createEmployee(array $args): Employee
    {
        $firstName = $args['firstName'];
        $lastName = $args['lastName'];
        $email = $args['email'];
        $phone = $args['phone'];
        $about = $args['about'];
        $isAdmin = $args['isAdmin'];

        return Employee::create(
            first_name: $firstName,
            last_name: $lastName,
            email: $email,
            phone: $phone,
            about: $about,
            is_admin: $isAdmin,
        );
    }
}
