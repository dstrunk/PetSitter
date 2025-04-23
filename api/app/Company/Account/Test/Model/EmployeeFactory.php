<?php

declare(strict_types=1);

namespace App\Company\Account\Test\Model;

use App\Test\Utils\Model\AbstractFactory;

class EmployeeFactory extends AbstractFactory
{
    protected function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'about' => $this->faker->sentence(10),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
