<?php

namespace Pulsar\Core\Router;

final class Route
{
    private string $name;
    private string $path;

    /**
     * @var mixed
     */
    private $handler;

    /**
     * @var array<string>
     */
    private array $methods = [];

    /**
     * Route constructor.
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *    $handler = [
     *      0 => (string) Controller name : HomeController::class.
     *      1 => (string|null) Method name or null if invoke method
     *    ]
     * @param array $methods
     */
    public function __construct(string $name, string $path, $handler, array $methods = ['GET'])
    {
        if ($methods === []) {
            throw new \InvalidArgumentException('HTTP methods argument was empty; must contain at least one method');
        }
        $this->name = $name;
        $this->path = '/' . rtrim(ltrim(trim($path), '/'), '/');
        $this->handler = $handler;
        $this->methods = $methods;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public static function get(string $name, string $path, $handler): self
    {
        return new self($name, $path, $handler);
    }

    public static function post(string $name, string $path, $handler): self
    {
        return new self($name, $path, $handler, ['POST']);
    }

    public static function put(string $name, string $path, $handler): self
    {
        return new self($name, $path, $handler, ['PUT']);
    }

    public static function delete(string $name, string $path, $handler): self
    {
        return new self($name, $path, $handler, ['DELETE']);
    }
}
