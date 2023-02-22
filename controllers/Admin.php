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

    }

    public function deleteUser(array $params)
    {

    }
}