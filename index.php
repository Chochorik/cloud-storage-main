<?php

session_start();

require_once './autoload.php';

use controllers\User;

$urlList = [
    '/user/registration' => 'registration',
    '/user/authorization' => 'authorization',
];

// возвращает путь запроса
function getRequestPath()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    return '/' . ltrim(str_replace('index.php', '', $path), '/');
}

function getMethod(array $urlList, $path)
{
    foreach ($urlList as $route => $method) {
        if ($path === $route) {
            if ($method === 'registration') {
                (new User)->registration();
            } elseif ($method === 'authorization') {
                (new User)->authorization();
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            header('location:' . 'error.html');
            exit;
        }
    }
}

$path = getRequestPath();

getMethod($urlList, $path);

//if (!isset($_SESSION['authorized'])) {
//    $_SESSION['authorized'] = false;
//}
//
//if ($_SESSION['authorized'] === false) {
//    header('location:' . '/registration.php');
//}

