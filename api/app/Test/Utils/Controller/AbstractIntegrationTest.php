<?php

namespace App\Test\Utils\Controller;

use Faker\Factory;
use Faker\Generator;
use Tempest\Framework\Testing\IntegrationTest;
use function Tempest\Support\Path\normalize;

abstract class AbstractIntegrationTest extends IntegrationTest
{
    protected private(set) ?Generator $faker;

    protected function setUp(): void
    {
        $this->root ??= normalize(realpath(__DIR__ . '/../../../../'));
        $this->faker = Factory::create();

        parent::setUp();
    }
}
