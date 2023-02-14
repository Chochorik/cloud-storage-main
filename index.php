<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With');

session_start();

if (!isset($_SESSION['authorized'])) {
    $_SESSION['authorized'] = false;
    $_SESSION['admin'] = false;
}

require_once './autoload.php';

use controllers\User;

$urlList = [
    '/' => 'home',
    '/user/registration' => 'registration',
    '/user/authorization' => 'authorization',
    '/user/exit' => 'logout',
];

$decodedValues = file_get_contents('php://input');
$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'], $query);
$logout = $query['logout'];

// возвращает путь запроса
function getRequestPath()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    return '/' . ltrim(str_replace('index.php', '', $path), '/');
}

function getMethod(array $urlList, $path)
{
    global $decodedValues, $logout;
    foreach ($urlList as $route => $method) {
        if ($path === $route) {
            if ($method === 'registration') {
                (new User)->registration($decodedValues);
            } elseif ($method === 'authorization') {
                (new User)->authorization($decodedValues);
            } elseif ($method === 'home' && $_SESSION['authorized'] === false) {
                header('location:' . '/pages/registration.html');
                exit;
            } elseif ($method === 'home' && $_SESSION['authorized'] === true) {
                header('location:' . '/pages/main.php');
                exit;
            } elseif ($method === 'logout') {
                (new User)->logout($logout);
            }
        }
    }
}

$path = getRequestPath();

if (!array_key_exists($path, $urlList)) {
    header('location:' . '/pages/error.html');
    exit;
}

getMethod($urlList, $path);



