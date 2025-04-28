<?php

namespace App\Test\Utils\Controller;

use Tempest\Framework\Testing\IntegrationTest;
use function Tempest\Support\Path\normalize;

abstract class AbstractIntegrationTest extends IntegrationTest
{
    protected function setUp(): void
    {
        $this->root ??= normalize(realpath(__DIR__ . '/../../../../'));

        parent::setUp();
    }
}
