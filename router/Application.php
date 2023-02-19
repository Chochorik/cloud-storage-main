<?php

namespace router;

class Application {
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function run(string $url, string $method)
    {
        try {
            $this->router->dispatch($url, $method);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}