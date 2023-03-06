<?php

namespace router;

use Closure;

class Route {
    private string $method, $path;
    private Closure $callback;
    private array $array;

    public function __construct($method, $path, $callback)
    {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $this->prepareCallback($callback);
    }

    private function prepareCallback(array $callback) : Closure
    {
        return function (...$params) use ($callback)
        {
            list($class, $method) = $callback;

            return (new $class)->{$method}(...$params);
        };
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function run($uri = null)
    {
        $id = parse_url($_SERVER['REQUEST_URI']);

        $exp = explode("/", $id['path']);

        $replace = preg_replace("/&.*/", '', end($exp));
        $params[] = [
            $replace
        ];

        return call_user_func_array($this->callback, $params);
    }

    public function match($uri, $method) : bool
    {
        $newPath = str_replace('/', '\/', $this->getPath());
        $expression = str_replace(['*', '/'], ['\w+', '/'], $newPath);

        return (preg_match('/^' . $expression . '$/', $uri) && strtolower($this->getMethod()) === $method);
    }
}