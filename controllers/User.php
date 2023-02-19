<?php

namespace controllers;

class User {
    protected string $userName, $email, $pass, $passConfirm, $role;
    protected $connection;

    public function __construct()
    {
        try {
            $this->connection = new \PDO('mysql:host=127.0.0.1;dbname=users;charset=utf8', 'root', 'root');
        } catch (\PDOException $exception) {
            http_response_code(418);
            echo json_encode($exception->getMessage());
        }

        $this->role = 'user';
    }

    public function registration()
    {
        $user = json_decode(file_get_contents('php://input'), true);
        $this->userName = $user['login'];
        $this->email = $user['email'];
        $this->pass = $user['password'];
        $this->passConfirm = $user['password_confirm'];
        $role = $this->role;

        if ($this->userName !== '') {
            $pattern = '/^[a-z0-9-_]+$/i';

            if (preg_match($pattern, $this->userName)) {
                $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `login` = :login");
                $statement->bindValue('login', $this->userName);

                $statement->execute();

                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

                if (count($result) > 0) {
                    $response = [
                        "message" => 'Пользователь с таким логином уже существует!'
                    ];

                    echo json_encode($response);
                    die();
                }
            } else {
                $response = [
                    "message" => 'Некорректный формат записи логина!'
                ];

                echo json_encode($response);
                die();
            }
        }

        if ($this->email !== '') {
            $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `email` = :email");
            $statement->bindValue('email', $this->email);

            $statement->execute();

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $response = [
                    "message" => 'Данный email уже занят!'
                ];

                echo json_encode($response);
                die();
            }
        }

        if ($this->userName !== ''
            && ($this->email !== '' && filter_var($this->email, FILTER_VALIDATE_EMAIL))
            && $this->pass !== ''
            && $this->passConfirm !== ''
            && ($this->pass === $this->passConfirm)) {

            $statement = $this->connection->prepare("INSERT INTO `users_list`(`id`, `login`, `email`, `pass_hash`, `role`) VALUES (:id, :login, :email, :hash, :role)");

            $pass = password_hash($this->pass, PASSWORD_BCRYPT);

            $statement->bindValue('id', null);
            $statement->bindValue('login', $this->userName);
            $statement->bindValue('email', $this->email);
            $statement->bindValue('role', $role);
            $statement->bindValue('hash', $pass);

            $statement->execute();

            $response = [
                "status" => true,
                "message" => 'Вы были успешно зарегистрированы',
            ];

            echo json_encode($response);
        } else if ($this->userName === ''
                   || $this->email === ''
                   || $this->pass === ''
                   || $this->passConfirm === '') {
            $response = [
                "status" => false,
                "message" => 'Заполните все поля!',
            ];

            echo json_encode($response);
            die();
        } else if ($this->pass !== $this->passConfirm) {
            $response = [
                "status" => false,
                "message" => 'Пароли не совпадают!',
            ];

            echo json_encode($response);
            die();
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $response = [
                "status" => false,
                "message" => 'Неверный формат email!',
            ];

            echo json_encode($response);
            die();
        }
    }

    public function authorization()
    {
        $user = json_decode(file_get_contents('php://input'), true);

        $this->userName = $user['login'];
        $this->pass = $user['password'];

        if ($this->userName !== '' && $this->pass !== '') {
            $pattern = '/^[a-z0-9-_]+$/i';

            if (preg_match($pattern, $this->userName)) {
                $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `login` = :login");
                $statement->bindValue('login', $this->userName);

                $statement->execute();

                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

                if (count($result) === 1) {
                    $hashed_password = $result[0]['pass_hash'];
                    if (password_verify($this->pass, $hashed_password)) {
                        $token = $this->connection->prepare("UPDATE `users_list` SET `session` = :session WHERE `login` = :login");

                        $token->bindValue('login', $this->userName);
                        $token->bindValue('session', session_id());

                        if ($token->execute()) {
                            if ($result[0]['login'] === 'Admin') {
                                $_SESSION['admin'] = true;
                            } else {
                                $_SESSION['admin'] = false;
                            }

                            $_SESSION['authorized'] = true;
                            $_SESSION['user'] = $this->userName;

                            http_response_code(200);

                            $response = [
                                "status" => true,
                                "message" => 'Вы успешно вошли',
                                "user" => $this->userName,
                                "token" => session_id()
                            ];

                            echo json_encode($response);
                        }
                    } else {
                        $response = [
                            "status" => false,
                            "message" => 'Пароль введён неверно!'
                        ];

                        echo json_encode($response);
                        die();
                    }
                } else {
                    $response = [
                        "status" => false,
                        "message" => 'Пользователя с таким логином не существует!'
                    ];

                    echo json_encode($response);
                    die();
                }
            } else {
                $response = [
                    "message" => 'Некорректный формат записи логина!'
                ];

                echo json_encode($response);
                die();
            }
        } else {
            $response = [
                "status" => false,
                "message" => 'Заполните все поля!'
            ];

            echo json_encode($response);
            die();
        }
    }

    public function logout()
    {
        $token = session_id();

        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `login` = :login AND `session` = :token");

        $statement->bindValue('login', $_SESSION['user']);
        $statement->bindValue('token', $token);

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $deleteToken = $this->connection->prepare("UPDATE `users_list` SET `session` = null WHERE `login` = :login");

                $deleteToken->bindValue('login', $_SESSION['user']);

                if ($deleteToken->execute()) {
                    session_unset();
                    session_destroy();

                    $response = [
                        "status" => true,
                        "message" => 'Вы успешно вышли'
                    ];

                    echo json_encode($response);
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
    }

    public function resetPassword()
    {

    }
}