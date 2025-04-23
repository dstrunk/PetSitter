<?php

namespace App\Test\Utils\Controller;

use PHPUnit\Framework\Assert;

class TestResponse
{
    public function __construct(
        protected array $response {
            get {
                return $this->response;
            }
        },
    ) {
    }

    public function assertJson(array $data): self
    {
        $this->assertArraySubsetRecursive(
            $data,
            $this->response,
            'Response JSON does not include the expected subset.',
        );

        return $this;
    }

    /**
     * Assert that the response contains JSON data at the specified path.
     *
     * @param string $path Dot notation path (e.g., 'data.employee.id')
     */
    public function assertJsonPath(string $path, mixed $expected): self
    {
        $actual = $this->getJsonPath($path);

        Assert::assertEquals(
            $expected,
            $actual,
            "Response JSON value at path [{$path}] does not match expected value."
        );

        return $this;
    }

    public function assertHasError(?string $message = null): self
    {
        Assert::assertArrayHasKey(
            'errors',
            $this->response,
            'Response does not contain any errors.'
        );

        if ($message !== null) {
            $found = false;
            foreach ($this->response['errors'] as $error) {
                if (isset($error['message']) && $error['message'] === $message) {
                    $found = true;
                    break;
                }
            }

            Assert::assertTrue(
                $found,
                "Response does not contain error with message: {$message}"
            );
        }

        return $this;
    }

    /**
     * Get a value from the response using dot notation.
     */
    private function getJsonPath(string $path): mixed
    {
        $data = $this->response;
        $segments = explode('.', $path);

        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return null;
            }

            $data = $data[$segment];
        }

        return $data;
    }

    private function assertArraySubsetRecursive(array $subset, array $array, string $message): void
    {
        foreach ($subset as $key => $value) {
            Assert::assertArrayHasKey($key, $array, $message);

            if (is_array($value) && is_array($array[$key])) {
                $this->assertArraySubsetRecursive($value, $array[$key], $message);
            } else {
                Assert::assertSame($value, $array[$key], $message);
            }
        }
    }
}
