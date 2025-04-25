<?php

namespace App\Test\Utils\Controller;

use App\Test\Utils\Controller\Attribute\RunDuring;
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

        ksort($this->setupCallbacks);

        foreach ($this->setupCallbacks as $priorityGroup) {
            foreach ($priorityGroup as $callback) {
                $callback();
            }
        }
    }

    protected function tearDown(): void
    {
        krsort($this->tearDownCallbacks);

        foreach ($this->tearDownCallbacks as $priorityGroup) {
            foreach ($priorityGroup as $callback) {
                $callback();
            }
        }

        parent::tearDown();
    }

    protected function addSetupCallback(callable $callback, int $priority): void
    {
        $this->setupCallbacks[$priority][] = $callback;
    }

    protected function addTearDownCallback(callable $callback, int $priority): void
    {
        $this->tearDownCallbacks[$priority][] = $callback;
    }

    private function registerTraits(): void
    {
        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(RunDuring::class);
            foreach ($attributes as $attribute) {
                $runDuring = $attribute->newInstance();
                $methodName = $method->getName();

                match ($runDuring->hook) {
                    'setUp' => $this->addSetupCallback(fn () => $this->$methodName(), $runDuring->priority),
                    'tearDown' => $this->addTearDownCallback(fn () => $this->$methodName(), $runDuring->priority),
                };
            }
        }
    }
}
