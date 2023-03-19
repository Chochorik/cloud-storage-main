<?php

error_reporting(~E_WARNING & ~E_NOTICE);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With');

if (!isset($_SESSION['authorized'])) {
    $_SESSION['authorized'] = false;
}

require_once './autoload.php';

$router = new Router();

// главная страница и ответ на 404 http response code
$router->get('', [MainController::class, 'index']); // главная страница
$router->get('not-found', [MainController::class, 'notFound']); // страница, которая отображается при коде ответа 404

// страницы
$router->get('authorization', [MainController::class, 'authorization']); // страница авторизации пользователя
$router->get('registration', [MainController::class, 'registration']); // страница регистрации пользователя
$router->get('recovery', [MainController::class, 'recoverPassword']); // страница для отправки ссылки для восстановления пароля
$router->get("recovery/user/*", [MainController::class, 'newPass']); // страница для установки нового пароля (отправляется на почту)
$router->get('shared-files', [MainController::class, 'sharedFiles']); // страница, где показываются файлы других пользователей, к которым у пользователя есть доступ

$router->get('user/logout', [User::class, 'logout']); // endpoint для выхода из уч. записи

// endpoints для базовых действий пользователя (регистрация, авторизация и выход из восстановление пароля)
$router->post('user/registration', [User::class, 'registration']);
$router->post('user/authorization', [User::class, 'authorization']);
$router->post('user/recovery', [User::class, 'sendLinkResetPass']);
$router->post('user/reset-password/*', [User::class, 'resetPassword']);

// endpoint'ы для администратора
$router->get('admin/panel', [MainController::class, 'adminPanel']); // страница панели администратора
$router->get('admin/users', [Admin::class, 'getUsersList']); // получение списка всех пользователей
$router->get('admin/users/*', [Admin::class, 'getUser']); // получение информации о конкретном пользователе
$router->put('admin/users/*', [Admin::class, 'updateUser']); // обновление данных пользователя
$router->delete('admin/users/*', [Admin::class, 'deleteUser']); // удаление пользователя

// endpoint'ы для файлов
$router->get('files/*', [Files::class, 'getFileInfo']); // получение информации о конкретном файле
$router->post('files', [Files::class, 'createFile']); // создание файла
$router->put('files/*', [Files::class, 'updateFile']); // обновление информации о файле
$router->delete('files/*', [Files::class, 'deleteFile']); // удаление файла

// endpoint'ы для директорий
$router->get('directiries', [Directories::class, 'getDirList']); // получение списка папок, в которые можно перемещать файлы
$router->get('directory/*', [Directories::class, 'getDirInfo']); // получение информации о конкретной папке
$router->post('directory', [Directories::class, 'createDir']); // создание папки
$router->put('directory/*', [Directories::class, 'updateDir']); // обновление информации о папке
$router->delete('directory/*', [Directories::class, 'deleteDir']); // удаление папки
$router->get('dirList', [Directories::class, 'getDirList']); // получение списка папок

// endpoint'ы для открытия доступа к файлу другим пользователям
$router->post('get-user', [User::class, 'getUserByEmail']); // получение информации о пользователе по его email
$router->post('files/share', [Files::class, 'giveAccessToFile']); // предоставление доступа к файлу
$router->get('files/share/*', [Files::class, 'getSharedUsersList']); // получение списка пользователей, которым был предоставлен доступ к файлу
$router->delete('files/deny-access', [Files::class, 'denyAccessForUser']); // запрет на доступ к файлу пользователя
$router->get('files-list/shared', [Files::class, 'getSharedFilesList']); // получение списка файлов, к которым есть доступ у пользователя

$app = new Application($router);
$app->run($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
