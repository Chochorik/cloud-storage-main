<?php

namespace controllers;

class User {
    protected $userName, $email, $pass, $passConfirm, $role;
    protected $connection;

    public function __construct()
    {
        try {
            $this->connection = new \PDO('mysql:host=127.0.0.1;dbname=users;charset=utf8', 'root', 'root');
        } catch (\PDOException $exception) {
            http_response_code(418);
        }

        $this->role = 'user';
    }

    public function registration($array)
    {
        $user = json_decode($array, true);

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

    public function authorization($array) {
        $user = json_decode($array, true);

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
                        $response = [
                            "status" => true,
                            "message" => 'Вы успешно вошли'
                        ];

                        $_SESSION['authorized'] = true;
                        $_SESSION['user'] = $this->userName;
                        setcookie('user', $_SESSION['user'], time() + 3600 /* срок действия 1 час */);

                        if ($result[0]['login'] === 'Admin') {
                            $_SESSION['admin'] = true;
                        }

                        echo json_encode($response);
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

    public function logout($logout)
    {
        if ($logout === true) {
            unset($_SESSION['authorized']);
            session_destroy();
            header('location:' . '/auth.php');
            exit;
        }
    }
}