<?php

namespace router;

use Closure;

class Route {
    private string $method, $path;
    private Closure $callback;

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
            $replace,
            $_GET
        ];

        return call_user_func_array($this->callback, $params);
    }

    public function match($uri, $method) : bool
    {
        $newPath = str_replace('/', '\/', $this->getPath());
        $expression = str_replace(['*', '/'], ['\w+', '/'], $newPath);

        echo $expression . '<br>';

        preg_match_all("/(?<={).+?(?=})/", $expression, $params);

        print_r($params) . '<br>';

        return (preg_match('/^' . $expression . '$/', $uri) && strtolower($this->getMethod()) === $method);
    }
}