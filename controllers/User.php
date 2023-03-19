<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User {
    private string $userName, $email, $pass, $passConfirm, $role;
    private string $salt;
    private object $connection;

    public function __construct()
    {
        try {
            $this->connection = new \PDO('mysql:host=127.0.0.1;dbname=users;charset=utf8', 'login', 'password');
        } catch (\PDOException $exception) {
            http_response_code(418);
            echo json_encode($exception->getMessage());
        }

        $this->role = 'user';
    }

    // регистрация пользователя
    public function registration()
    {
        $user = json_decode(file_get_contents('php://input'), true);
        $this->userName = $user['login'];
        $this->email = $user['email'];
        $this->pass = $user['password'];
        $this->passConfirm = $user['password_confirm'];

        if ($this->userName !== '') {
            $pattern = '/^[a-z0-9-_]+$/i';

            // проверка на корректность вводимых данных
            if (preg_match($pattern, $this->userName)) {
                // проверка на максимальную допустимую длину
                if (strlen($this->userName) > 30) {
                    $response = [
                        "message" => 'Логин слишком длинный! (не более 30 символов)'
                    ];

                    echo json_encode($response);
                    exit;
                }

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

        if ($this->pass !== '') {
            if (strlen($this->pass) > 30) {
                $response = [
                    "message" => 'Длина пароля слишком длинная! (не более 30 символов)'
                ];

                echo json_encode($response);
                die();
            }

            if (strlen($this->pass) < 8) {
                $response = [
                    "message" => 'Длина пароля слишком маленькая! (не менее 8 символов)'
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

            if (strlen($this->pass) > 30) {
                $response = [
                    "status" => false,
                    "message" => 'Длина пароля слишком длинная! (не более 30 символов)'
                ];

                echo json_encode($response);
                exit;
            }

            $statement = $this->connection->prepare("INSERT INTO `users_list`(`id`, `login`, `email`, `pass_hash`, `salt`, `role`) VALUES (:id, :login, :email, :hash, :salt, :role)");

            $pass = password_hash($this->pass, PASSWORD_BCRYPT);
            $this->salt = md5($this->userName . $this->pass);

            $statement->bindValue('id', null);
            $statement->bindValue('login', $this->userName);
            $statement->bindValue('email', $this->email);
            $statement->bindValue('hash', $pass);
            $statement->bindValue('salt', $this->salt);
            $statement->bindValue('role', $this->role);

            if ($statement->execute()) {
                $getUserId = $this->connection->prepare("SELECT `id` FROM `users_list` WHERE `salt` = :salt");
                $getUserId->bindValue('salt', $this->salt);

                // получение id только что созданного пользователя
                $getUserId->execute();
                $user = $getUserId->fetchAll(\PDO::FETCH_ASSOC);
                $userId = $user[0]['id'];

                // создание корневой папки пользователя
                $createRootDir = $this->connection->prepare("INSERT INTO `directories`(`path`, `name`, `user_id`) VALUES (:path, :dirName, :userId)");
                $createRootDir->bindValue('path', '/');
                $createRootDir->bindValue('dirName', 'root');
                $createRootDir->bindParam('userId', $userId);

                if ($createRootDir->execute()) {
                    $response = [
                        "status" => true,
                        "message" => 'Вы были успешно зарегистрированы',
                    ];

                    echo json_encode($response);
                }
            }
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

    // авторизация пользователя
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
                    $hashedPassword = $result[0]['pass_hash'];

                    if (!password_verify($this->pass, $hashedPassword)) {
                        $response = [
                            "status" => false,
                            "message" => 'Пароль введён неверно!'
                        ];

                        echo json_encode($response);
                        die();
                    }

                    session_start();
                    $token = $this->connection->prepare("UPDATE `users_list` SET `session` = :session WHERE `login` = :login");

                    $token->bindValue('login', $this->userName);
                    $token->bindValue('session', session_id());

                    if ($token->execute()) {
                        if ($result[0]['role'] === 'admin') {
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

    // выход пользователя из учетной записи
    public function logout()
    {
        session_start();

        $token = session_id();

        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `login` = :login AND `session` = :token");

        $statement->bindValue('login', $_SESSION['user']);
        $statement->bindValue('token', $token);

        if ($statement->execute()) {
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $deleteToken = $this->connection->prepare("UPDATE `users_list` SET `session` = null WHERE `login` = :login");

                $deleteToken->bindValue('login', $_SESSION['user']);

                if ($deleteToken->execute() && session_status() == PHP_SESSION_ACTIVE) {
                    unset($_SESSION);
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

    // восстановление забытого пароля
    public function sendLinkResetPass()
    {
        // получение данных из input для email
        $post = json_decode(file_get_contents('php://input'), true);

        $login = $post['login'];

        if ($login === '') {
            $response = [
                "status" => false,
                "message" => 'Заполните все поля!'
            ];

            echo json_encode($response);
            exit;
        }

        // проверка на существование пользователя с данным логином
        $statement = $this->connection->prepare("SELECT * FROM `users_list` WHERE `login` = :login");
        $statement->bindParam('login', $login);

        $statement->execute();

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            // восстановление пароля
            $mail = new PHPMailer(true);

            $email = $result[0]['email'];
            $salt = $result[0]['salt'];

            // ссылка на восстановление пароля
            $recoveryLink = "http://www.cloud-storage.local/recovery/user/$salt";

            // формирование письма
            $title = 'Cloud Storage';

            $body = <<<MAIL
                <h2>Восстановление пароля</h2><br>
                <p>
                    Чтобы восстановить пароль, перейдите по <a href="$recoveryLink">ссылке</a>
                </p><br>
                <p>
                   Если это не вы пытаетесь восстановить пароль, то не переходите по ссылке.
                </p><br>
            MAIL;

            try {
                //Server settings
                $mail->isSMTP();                                            //Send using SMTP
                $mail->CharSet    = "UTF-8";
                $mail->Host       = 'ssl://smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'email';                     //SMTP username
                $mail->Password   = 'token';                               //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom('email', 'Cloud Storage');
                $mail->addAddress($email, $result[0]['login']);     //Add a recipient

                //Content
                $mail->Subject = $title;
                $mail->msgHTML($body);

                $mail->send();

                $response = [
                    "status" => true,
                    "message" => 'Письмо с ссылкой для восстановления пароля было отправлено на почту'
                ];

                echo json_encode($response);
            } catch (Exception $exception) {
                echo json_encode($exception->getMessage());
                die();
            }
        } else {
            $response = [
                "status" => false,
                "message" => 'Пользователя с таким логином не найдено!'
            ];

            echo json_encode($response);
            exit;
        }
    }

    // метод для задачи нового пароля пользователя
    public function resetPassword(array $params)
    {
        // "соль" пользователя, выступает идентификатором для восстановления пароля
        $salt = $params[0];

        // получение данных из input для email
        $post = json_decode(file_get_contents('php://input'), true);

        $this->pass = $post['new-password'];
        $this->passConfirm = $post['repeat-new-password'];

        if ($this->pass === '' || $this->passConfirm === '') {
            $response = [
                "status" => false,
                "message" => 'Заполните все поля!'
            ];

            echo json_encode($response);
            die();
        }

        if ($this->pass !== $this->passConfirm) {
            $response = [
                "status" => false,
                "message" => 'Пароли не совпадают!'
            ];

            echo json_encode($response);
            die();
        }

        $user = $this->connection->prepare("SELECT * FROM `users_list` WHERE `salt` = :salt");
        $user->bindValue('salt', $salt);

        if ($user->execute()) {
            $result = $user->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) == 0) {
                $response = [
                    "status" => false,
                    "message" => 'Пользователя не существует'
                ];

                echo json_encode($response);
                die();
            }

            $id = $result[0]['id'];
            $this->salt = $result[0]['salt'];
            $this->userName = $result[0]['login'];

            $newPass = password_hash($this->pass, PASSWORD_BCRYPT);

            $statement = $this->connection->prepare("UPDATE `users_list` SET `pass_hash` = :newPass, `salt` = :newSalt WHERE `id` = :id");
            $statement->bindValue('newPass', $newPass);
            $statement->bindValue('newSalt', md5($this->userName . $this->pass));
            $statement->bindValue('id', $id);

            if ($statement->execute()) {
                $response = [
                    "status" => true,
                    "message" => 'Пароль был успешно изменен!'
                ];

                echo json_encode($response);
            } else {
                http_response_code(500);

                $response = [
                    "status" => false,
                    "message" => 'Что-то пошло не так...'
                ];

                echo json_encode($response);
                die();
            }
        } else {
            http_response_code(500);

            $response = [
                "status" => false,
                "message" => 'Что-то пошло не так...'
            ];

            echo json_encode($response);
            die();
        }
    }

    // получаем id пользователя, которому хотим открыть доступ к файлу
    public function getUserByEmail()
    {
        session_start();

        if (!$this->checkAuth($_SESSION)) {
            $response = [
                "status" => false,
                "message" => 'Необходимо авторизоваться!'
            ];
            echo json_encode($response);
            die(http_response_code(403));
        }

        // получаем информацию с формы
        $input = json_decode(file_get_contents('php://input'), true);

        $email = trim($input['email']); // email с формы

        if ($email === '') {
            $response = [
                "status" => false,
                "message" => 'Поле не должно быть пустым!'
            ];
            echo json_encode($response);
            die();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = [
                "status" => false,
                "message" => 'Неверный формат почты!'
            ];
            echo json_encode($response);
            die();
        }

        // ищем пользователя с такой почтой
        $findUser = $this->connection->prepare("SELECT * FROM `users_list` WHERE `email` = :email");
        $findUser->bindValue('email', $email);
        $findUser->execute();

        $result = $findUser->fetch(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            $response = [
                "status" => false,
                "message" => 'Пользователь не найден'
            ];
            echo json_encode($response);
            die();
        }

        $userId = $result['id'];

        $response = [
            "status" => true,
            "message" => 'Подождите...',
            "id" => $userId
        ];
        echo json_encode($response);
    }

    // проверка на авторизованность
    protected function checkAuth(array $array) : bool
    {
        if (isset($array['authorized'])) {
            if (!$array['authorized']) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }
}
