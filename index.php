<?php

//error_reporting(~E_WARNING & ~E_NOTICE);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With');

if (!isset($_SESSION['authorized'])) {
    $_SESSION['authorized'] = false;
}

require_once './autoload.php';

use controllers\User;
use controllers\Admin;
use router\Application;
use controllers\MainController;
use router\Router;

$router = new Router();

// главная страница и ответ на 404 http response code
$router->get('', [MainController::class, 'index']);
$router->get('notFound', [MainController::class, 'notFound']);

// страницы для регистрации, авторизации и восстановления пароля
$router->get('authorization', [MainController::class, 'authorization']);
$router->get('registration', [MainController::class, 'registration']);
$router->get('recovery', [MainController::class, 'recoverPassword']);
$router->get("recovery/user/*", [MainController::class, 'newPass']);

// endpoint для выхода из уч. записи
$router->get('user/logout', [User::class, 'logout']);

// endpoints для базовых действий пользователя (регистрация, авторизация и выход из восстановление пароля)
$router->post('user/registration', [User::class, 'registration']);
$router->post('user/authorization', [User::class, 'authorization']);
$router->post('user/recovery', [User::class, 'sendLinkResetPass']);
$router->post('user/reset-password/*', [User::class, 'resetPassword']);

// endpoint'ы для администратора
$router->get('admin/panel', [MainController::class, 'adminPanel']);
$router->get('admin/users', [Admin::class, 'getUsersList']);
$router->get('admin/users/*', [Admin::class, 'getUser']);
$router->put('admin/users/*', [Admin::class, 'updateUser']);
$router->delete('admin/users/*', [Admin::class, 'deleteUser']);

$app = new Application($router);
$app->run($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

//if (http_response_code() === 404) {
//    header('location: /notFound');
//    exit();
//}