<?php

declare(strict_types=1);

namespace App\Company\Account\Test\Model;

use App\Test\Utils\Model\AbstractFactory;

class EmployeeFactory extends AbstractFactory
{
    protected function definition(): array
    {
        return [
            'first_name' => $this->faker->name(),
            'last_name' => $this->faker->name(),
            'about' => $this->faker->sentence(10),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->e164PhoneNumber(),
            'is_admin' => false,
        ];
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes) => ['is_admin' => true]);
    }
}
