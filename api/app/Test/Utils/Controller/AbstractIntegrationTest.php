<?php

namespace App\Test\Utils\Controller;

use App\Test\Utils\Controller\Attribute\RunDuring;
use Tempest\Framework\Testing\IntegrationTest;
use function Tempest\Support\Path\normalize;

abstract class AbstractIntegrationTest extends IntegrationTest
{
    private array $setupCallbacks = [];
    private array $tearDownCallbacks = [];
    private static array $setupBeforeClassCallbacks = [];
    private static array $teardownAfterClassCallbacks = [];
    private static array $instanceMethodsToRun = [];
    private static bool $staticMethodsExecuted = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::registerTraits();

        ksort(self::$setupBeforeClassCallbacks);
        foreach (self::$setupBeforeClassCallbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $callback();
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        krsort(self::$teardownAfterClassCallbacks);
        foreach (self::$teardownAfterClassCallbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $callback();
            }
        }
    }

    protected function setUp(): void
    {
        $this->root ??= normalize(realpath(__DIR__ . '/../../../../'));

        parent::setUp();

        if (!static::$staticMethodsExecuted) {
            $this->executeStaticPhaseInstanceMethods();
            static::$staticMethodsExecuted = true;
        }

        foreach (static::$instanceMethodsToRun['setUp'] ?? [] as $methodData) {
            $methodName = $methodData['method'];
            $priority = $methodData['priority'];
            $this->addSetupCallback(fn () => $this->$methodName(), $priority);
        }

        ksort($this->setupCallbacks);

        foreach ($this->setupCallbacks as $priorityGroup) {
            foreach ($priorityGroup as $callback) {
                $callback();
            }
        }
    }

    protected function tearDown(): void
    {
        foreach (static::$instanceMethodsToRun['tearDown'] ?? [] as $methodData) {
            $methodName = $methodData['method'];
            $priority = $methodData['priority'];
            $this->addTearDownCallback(fn () => $this->$methodName(), $priority);
        }

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

    protected static function addSetupBeforeClassCallback(callable $callback, int $priority): void
    {
        self::$setupBeforeClassCallbacks[$priority][] = $callback;
    }

    protected static function addTearDownAfterClassCallback(callable $callback, int $priority): void
    {
        self::$teardownAfterClassCallbacks[$priority][] = $callback;
    }

    private function executeStaticPhaseInstanceMethods(): void
    {
        foreach (static::$instanceMethodsToRun['setupBeforeClass'] ?? [] as $methodData) {
            $methodName = $methodData['method'];
            $this->$methodName();
        }

        foreach (static::$instanceMethodsToRun['tearDownAfterClass'] ?? [] as $methodData) {
            $methodName = $methodData['method'];
            $priority = $methodData['priority'];

            $instance = $this;
            static::addTearDownAfterClassCallback(
                function() use ($instance, $methodName) {
                    $instance->$methodName();
                },
                $priority,
            );
        }
    }

    private static function registerTraits(): void
    {
        $reflection = new \ReflectionClass(static::class);
        $methods = $reflection->getMethods();

        static::$instanceMethodsToRun = [
            'setUp' => [],
            'tearDown' => [],
            'setupBeforeClass' => [],
            'tearDownAfterClass' => []
        ];

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(RunDuring::class);
            foreach ($attributes as $attribute) {
                $runDuring = $attribute->newInstance();
                $methodName = $method->getName();

                if ($method->isStatic()) {
                    match ($runDuring->hook) {
                        'setupBeforeClass' => static::addSetupBeforeClassCallback(
                            fn () => static::$methodName(),
                            $runDuring->priority,
                        ),
                        'tearDownAfterClass' => static::addTearDownAfterClassCallback(
                            fn () => static::$methodName(),
                            $runDuring->priority,
                        ),
                        'setUp' => static::$instanceMethodsToRun['setUp'][] = [
                            'method' => $methodName,
                            'priority' => $runDuring->priority,
                        ],
                        'tearDown' => static::$instanceMethodsToRun['tearDown'][] = [
                            'method' => $methodName,
                            'priority' => $runDuring->priority,
                        ],
                    };
                } else {
                    static::$instanceMethodsToRun[$runDuring->hook][] = [
                        'method' => $methodName,
                        'priority' => $runDuring->priority,
                    ];
                }
            }
        }
    }
}
