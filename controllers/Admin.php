<?php

namespace controllers;

class Admin {
    private object $connection;

    public function __construct()
    {
        try {
            $this->connection = new \PDO('mysql:host=127.0.0.1;dbname=users;charset=utf8', 'root', 'root');
        } catch (\PDOException $exception) {
            echo json_encode($exception->getMessage());
        }
    }

    // проверка пользователя на права администратора
    private function checkAdmin($sessionId) : bool
    {
        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `session` = :token");
        $statement->bindValue('token', $sessionId);

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                if ($result[0]['role'] !== 'admin') {
                    return false;
                }
            }

            return true;
        }
    }

    // проверка на авторизованность
    private function checkAuth(array $array) : bool
    {
        if (isset($array['admin']) && isset($array['authorized'])) {
            if (!$array['admin'] || !$array['authorized']) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    // получение списка всех пользователей
    public function getUsersList()
    {
        session_start();

        if (!$this->checkAdmin(session_id())) {
            $response = [
                "status" => false,
                "message" => 'Вы не являетесь администратором!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $users = $this->connection->prepare("SELECT `id`, `login`, `email`, `role` FROM `users_list`");

        if ($users->execute()) {
            $usersList = $users->fetchAll(\PDO::FETCH_ASSOC);

            $response = [
                "status" => true,
                "array" => $usersList
            ];

            echo json_encode($response);
        }
    }

    // получение данных конкретного пользователя
    public function getUser(array $params)
    {
        session_start();

        if (!$this->checkAdmin(session_id())) {
            $response = [
                "status" => false,
                "message" => 'Вы не являетесь администратором!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $id = $params[0];

        $userPrepare = $this->connection->prepare("SELECT `id`, `login`, `email`, `role` FROM `users_list` WHERE `id` = :id");
        $userPrepare->bindParam('id', $id);

        if ($userPrepare->execute()) {
            $user = $userPrepare->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                $response = [
                    "status" => true,
                    "user" => $user
                ];

                echo json_encode($response);
            } else {
                $response = [
                    "status" => false,
                    "message" => 'Пользователя с таким id не найдено'
                ];

                echo json_encode($response);
                exit;
            }
        } else {
            $response = [
                "status" => false,
                "message" => 'Что-то пошло не так...'
            ];

            echo json_encode($response);
            die();
        }
    }

    // обновление данных пользователя
    public function updateUser(array $params)
    {
        session_start();

        if (!$this->checkAdmin(session_id())) {
            $response = [
                "status" => false,
                "message" => 'Вы не являетесь администратором!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $id = $params[0]; // id изменяемого пользователя
        $data = json_decode(file_get_contents('php://input'), true); // получение данных из формы

        // присваиваем новые значения
        $newLogin = $data['login'];
        $newEmail = $data['email'];
        $newRole = $data['role'];

        // получение старых данных пользователя
        $oldData = $this->connection->prepare("SELECT * FROM `users_list` WHERE `id` = :userId");
        $oldData->bindValue('userId', $id);

        $oldData->execute();
        $oldUser = $oldData->fetch(\PDO::FETCH_ASSOC);

        $oldLogin = $oldUser['login'];
        $oldEmail = $oldUser['email'];
        $oldRole = $oldUser['role'];

        // проверка на пустоту полей
        if ($newLogin === ''
            || $newEmail === ''
            || $newRole === '') {
            $response = [
                "status" => false,
                "message" => 'Заполните все поля!'
            ];

            echo json_encode($response);
            die();
        }

        // проверка логина
        if ($newLogin !== '' && $newLogin !== $oldLogin) {
            $pattern = '/^[a-z0-9-_]+$/i';

            if (preg_match($pattern, $newLogin)) {
                if (strlen($newLogin) > 30) {
                    $response = [
                        "message" => 'Логин слишком длинный! (не более 30 символов)'
                    ];

                    echo json_encode($response);
                    exit;
                }

                // проверка логина на оригинальность
                $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `login` = :login");
                $statement->bindValue('login', $newLogin);

                $statement->execute();

                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

                if (count($result) > 0) {
                    $response = [
                        "status" => false,
                        "message" => 'Пользователь с таким логином уже существует!'
                    ];

                    echo json_encode($response);
                    die();
                }
            } else {
                $response = [
                    "status" => false,
                    "message" => 'Некорректный формат записи логина!'
                ];

                echo json_encode($response);
                die();
            }
        }

        // проверка email
        if ($newEmail !== '' && $newEmail !== $oldEmail) {
            $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `email` = :email");
            $statement->bindValue('email', $newEmail);

            $statement->execute();

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            // проверка email на оригинальность
            if (count($result) > 0) {
                $response = [
                    "status" => false,
                    "message" => 'Данный email уже занят!'
                ];

                echo json_encode($response);
                die();
            }
        }

        // проверка роли на корректность
        if ($oldRole !== $newRole) {
            if ($newRole !== 'admin' && $newRole !== 'user') {
                $response = [
                    "status" => false,
                    "message" => 'Недопустимая роль! (только admin или user)'
                ];

                echo json_encode($response);
                die();
            }
        }

        $userPrepare = $this->connection->prepare("UPDATE `users_list` SET `login`= :newLogin, `email` = :newEmail, `role` = :newRole WHERE `id` = :userId");
        $userPrepare->bindValue('newLogin', $newLogin);
        $userPrepare->bindValue('newEmail', $newEmail);
        $userPrepare->bindValue('newRole', $newRole);
        $userPrepare->bindValue('userId', $id);

        if (!$userPrepare->execute()) {
            echo json_encode([
                "status" => false,
                "message" => 'Что-то пошло не так...'
            ]);
            die(http_response_code(500));
        }

        echo json_encode([
           "status" => true,
           "message" => 'Пользователь был успешно изменен'
        ]);
    }

    // удаление конкретного пользователя
    public function deleteUser(array $params)
    {
        session_start();

        if (!$this->checkAdmin(session_id())) {
            $response = [
                "status" => false,
                "message" => 'Вы не являетесь администратором!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];

            echo json_encode($response);
            die(http_response_code(403));
        }

        $id = $params[0];

        $getEncodedFilesNames = $this->connection->prepare("SELECT `encoded_name` FROM `files` WHERE `type` = 'file' AND `id` = :userId");
        $getEncodedFilesNames->bindValue('userId', $id);

        $deleteFromFiles= $this->connection->prepare("DELETE FROM `files` WHERE `id` = :userId");
        $deleteFromFiles->bindValue('userId', $id);

        $deleteFromDirs = $this->connection->prepare("DELETE FROM `directories` WHERE `user_id` = :userId");
        $deleteFromDirs->bindValue('userId', $id);

        $users = $this->connection->prepare("DELETE FROM `users_list` WHERE `id` = :id");
        $users->bindValue('id', $id);

        try {
            $this->connection->beginTransaction();

            $getEncodedFilesNames->execute();
            $fileNames = $getEncodedFilesNames->fetchAll(\PDO::FETCH_ASSOC);

            $newFilesNamesArray = [];

            array_walk_recursive($fileNames, function ($item) use (&$newFilesNamesArray) {
                $newFilesNamesArray[] = $item;
            });

            foreach ($newFilesNamesArray as $name) {
                unlink('./storage/' . $name);
            }

            $deleteFromFiles->execute();
            $deleteFromDirs->execute();

            $users->execute();

            $this->connection->commit();

            echo json_encode([
                "status" => true,
                "message" => 'Пользователь был успешно удален'
            ]);
        } catch (\PDOException $exception) {
            $this->connection->rollBack();
            echo json_encode([
                "status" => false,
                "message" => $exception->getMessage()
            ]);
        }
    }
}