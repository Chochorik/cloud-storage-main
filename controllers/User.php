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

    public function registration()
    {
        $errorField = [];

        $this->userName = $_POST['login'];
        $this->email = $_POST['email'];
        $this->pass = $_POST['password'];
        $this->passConfirm = $_POST['password_confirm'];
        $role = $this->role;

        if ($this->userName === '') {
            $errorField[] = 'login';
        }

        if ($this->email === '' && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errorField[] = 'email';
        }

        if ($this->pass === '') {
            $errorField[] = 'password';
        }

        if ($this->passConfirm === '') {
            $errorField[] = 'password_confirm';
        }

        if ($this->pass === $this->passConfirm) {
            $statement = $this->connection->prepare("INSERT INTO `users_list`(`id`, `login`, `email`, `pass_hash`, `role`) VALUES (:id, :login, :email, :hash, :role)");

            $pass = password_hash($this->pass, PASSWORD_BCRYPT);

            $statement->bindValue('id', null);
            $statement->bindValue('login', $this->userName);
            $statement->bindValue('email', $this->email);
            $statement->bindValue('role', $role);
            $statement->bindValue('hash', $pass);

            try {
                $statement->execute();

                $response = [
                    "status" => true,
                    "message" => 'Вы были успешно зарегистрированы'
                ];

                echo json_encode($response);
            } catch (\PDOException $exception) {
                $response = [
                    "status" => false,
                    "message" => $exception->getMessage()
                ];

                echo json_encode($response);
                die();
            }
        }

        if (!empty($errorField)) {
            $response = [
                "status" => false,
                "message" => 'Проверьте правильность полей',
                "fields" => $errorField
            ];

            echo json_encode($response);
            die();
        }
    }

    public function authorization() {
        $login = $_GET['login'];
        $pass = $_GET['password'];

        $statement = $this->connection->query("SELECT * FROM `users_list` WHERE `login` = :login");

        $statement->bindValue('login', $login);

        try {
            $statement->execute();

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $value) {
                if (!$value) {
                    return false;
                }

                if (password_verify($pass, PASSWORD_BCRYPT)) {
                    return true;
                }
            }
        } catch (\PDOException $exception) {
            exit;
        }
    }
}