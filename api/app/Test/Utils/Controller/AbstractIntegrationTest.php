<?php

namespace App\Test\Utils\Controller;

use Tempest\Framework\Testing\IntegrationTest;
use function Tempest\Support\Path\normalize;

abstract class AbstractIntegrationTest extends IntegrationTest
{
    private array $setupCallbacks = [];
    private array $tearDownCallbacks = [];

    protected function setUp(): void
    {
        $this->root ??= normalize(realpath(__DIR__ . '/../../../../'));

        parent::setUp();

        $this->registerTraits();

        foreach ($this->setupCallbacks as $callback) {
            $callback();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->tearDownCallbacks as $callback) {
            $callback();
        }

        parent::tearDown();
    }

    protected function addSetupCallback(callable $callback): void
    {
        $this->setupCallbacks[] = $callback;
    }

    protected function addTearDownCallback(callable $callback): void
    {
        $this->tearDownCallbacks[] = $callback;
    }

    private function registerTraits(): void
    {
        $reflection = new \ReflectionClass($this);
        $traits = [];
        do {
            $traits = array_merge($traits, $reflection->getTraitNames());
        } while ($reflection = $reflection->getParentClass());

        foreach ($traits as $trait) {
            $traitShortName = substr($trait, strrpos($trait, '\\') + 1);
            $registerMethod = 'register' . $traitShortName;

            if (method_exists($trait, $registerMethod)) {
                $this->$registerMethod();
            }
        }
    }
}
