<?php

namespace App\Company\Account\Model;

use App\Company\Account\Test\Model\EmployeeFactory;
use App\Test\Utils\Model\Trait\HasFactory;
use Tempest\Database\IsDatabaseModel;

#[HasFactory(EmployeeFactory::class)]
final class Employee
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $about,
    ) {
    }
}
