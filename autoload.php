<?php

require_once './vendor/autoload.php';

spl_autoload_register(function ($class) {
    if ($class === 'Router' || $class === 'Application' || $class === 'Route') {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/router/';
        require_once $path . $class . '.php';
    } else {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/controllers/';
        require_once $path . $class . '.php';
    }
});
