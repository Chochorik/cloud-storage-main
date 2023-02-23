<?php

namespace controllers;

class Admin {
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new \PDO('mysql:host=127.0.0.1;dbname=users;charset=utf8', 'root', 'root');
        } catch (\PDOException $exception) {
            echo json_encode($exception->getMessage());
        }

    }

    public function getUsersList()
    {
        session_start();

        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `session` = :token");

        $statement->bindValue('token', session_id());

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                if ($result[0]['role'] === 'admin') {
                    if (isset($_SESSION['admin']) && isset($_SESSION['authorized'])) {
                        if ($_SESSION['admin'] === true && $_SESSION['authorized'] === true) {
                            $users = $this->connection->prepare("SELECT `id`, `login`, `email`, `role` FROM `users_list`");

                            if ($users->execute()) {
                                $usersList = $users->fetchAll(\PDO::FETCH_ASSOC);

                                $response = [
                                    "status" => true,
                                    "array" => $usersList
                                ];

                                echo json_encode($response);
                            }
                        } else {
                            http_response_code(403);

                            $response = [
                                "status" => false,
                                "message" => 'Вы не являетесь администратором!'
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
                        exit;
                    }
                }
            }
        }
    }

    public function getUser(array $params)
    {
        session_start();

        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `session` = :token");

        $statement->bindValue('token', session_id());

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                if ($result[0]['role'] === 'admin') {
                    if (isset($_SESSION['admin']) && isset($_SESSION['authorized'])) {
                        if ($_SESSION['admin'] === true && $_SESSION['authorized'] === true) {
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
                            }
                        } else {
                            http_response_code(403);

                            $response = [
                                "status" => false,
                                "message" => 'Вы не являетесь администратором!'
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
                        exit;
                    }
                }
            }
        }
    }

    public function updateUser(array $params)
    {
        session_start();

        $data = json_decode(file_get_contents('php://input'), true);

        $newId = $data['id'];
        $newLogin = $data['login'];
        $newEmail = $data['email'];
        $newRole = $data['role'];

        $oldData = $data['oldData'];
        $oldId = $oldData['id'];
        $oldLogin = $oldData['login'];
        $oldEmail = $oldData['email'];
        $oldRole = $oldData['role'];

        if ($newId === ''
            || $newLogin === ''
            || $newEmail === ''
            || $newRole === '') {
            $response = [
                "status" => false,
                "message" => 'Заполните все поля!'
            ];

            echo json_encode($response);
            die();
        }

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

        if ($newEmail !== '' && $newEmail !== $oldEmail) {
            $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `email` = :email");
            $statement->bindValue('email', $newEmail);

            $statement->execute();

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $response = [
                    "status" => false,
                    "message" => 'Данный email уже занят!'
                ];

                echo json_encode($response);
                die();
            }
        }

        if ($newId !== '' && $newId !== $oldId) {
            $pattern = '#^[0-9]+$#';

            if (preg_match($pattern, $newId)) {
                if (strlen($newId) > 10) {
                    $response = [
                        "status" => false,
                        "message" => 'Длина id превышает допустимое значение! (максимум 10 символов)'
                    ];

                    echo json_encode($response);
                    die();
                }

                $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `id` = :id");
                $statement->bindValue('id', $newId);

                $statement->execute();

                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

                if (count($result) > 0) {
                    $response = [
                        "status" => false,
                        "message" => 'Пользователь с таким id уже существует!'
                    ];

                    echo json_encode($response);
                    die();
                }
            } else {
                $response = [
                    "status" => false,
                    "message" => 'В id должны быть только цифры!'
                ];

                echo json_encode($response);
                die();
            }
        }

        if ($newRole !== '' && $newRole !== $oldRole) {
            if ($newRole !== 'admin' || $newRole !== 'user') {
                $response = [
                    "status" => false,
                    "message" => 'Недопустимая роль! (только admin или user)'
                ];

                echo json_encode($response);
                die();
            }
        }

        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `session` = :token");

        $statement->bindValue('token', session_id());

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                if ($result[0]['role'] === 'admin') {
                    if (isset($_SESSION['admin']) && isset($_SESSION['authorized'])) {
                        if ($_SESSION['admin'] === true && $_SESSION['authorized'] === true) {
                            $id = $params[0];

                            $userPrepare = $this->connection->prepare("UPDATE `users_list` SET `id` = :newId, `login`= :newLogin, `email` = :newEmail, `role` = :newRole WHERE `id` = :userId");
                            $userPrepare->bindValue('newId', $newId);
                            $userPrepare->bindValue('newLogin', $newLogin);
                            $userPrepare->bindValue('newEmail', $newEmail);
                            $userPrepare->bindValue('newRole', $newRole);
                            $userPrepare->bindValue('userId', $id);

                            if ($userPrepare->execute()) {
                                $response = [
                                    "status" => true,
                                    "message" => 'Данные пользователя успешно изменены'
                                ];

                                echo json_encode($response);
                            }
                        }
                    }
                }
            }
        }
    }

    public function deleteUser(array $params)
    {
        session_start();

        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `session` = :token");

        $statement->bindValue('token', session_id());

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                if ($result[0]['role'] === 'admin') {
                    if (isset($_SESSION['admin']) && isset($_SESSION['authorized'])) {
                        if ($_SESSION['admin'] === true && $_SESSION['authorized'] === true) {
                            $id = $params[0];

                            $users = $this->connection->prepare("DELETE FROM `users_list` WHERE `id` = :id");
                            $users->bindValue('id', $id);

                            if ($users->execute()) {
                                $response = [
                                    "status" => true,
                                    "Пользователь был удалён"
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
                        }
                    }
                } else {
                    http_response_code(403);

                    $response = [
                        "status" => false,
                        "message" => 'Вы не являетесь администратором!'
                    ];

                    echo json_encode($response);
                    exit;
                }
            }
        }
    }
}