<?php

//error_reporting(~E_WARNING & ~E_NOTICE);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With');

session_start();

if (!isset($_SESSION['authorized'])) {
    $_SESSION['authorized'] = false;
}

require_once './autoload.php';

use controllers\User;
use router\Application;
use controllers\MainController;
use router\Router;

$router = new Router();

$router->get('', [MainController::class, 'index']);
$router->get('notFound', [MainController::class, 'notFound']);
$router->get('authorization', [MainController::class, 'authorization']);
$router->get('registration', [MainController::class, 'registration']);
$router->get('recovery', [MainController::class, 'recoverPassword']);
$router->get('user/logout', [User::class, 'logout']);

$router->post('user/registration', [User::class, 'registration']);
$router->post('user/authorization', [User::class, 'authorization']);

$app = new Application($router);
$app->run($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if (http_response_code() === 404) {
    header('location: /notFound');
    exit();
}