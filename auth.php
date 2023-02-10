<?php

require_once 'autoload.php';

use controllers\User;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
</head>
<body>
    <h1>
        Авторизуйтесь
    </h1>
    <?php

        if (isset($_POST['login']) && isset($_POST['password'])) {
            $login = $_POST['login'];
            $pass = $_POST['password'];

            $array = [
                'login' => $login,
                'password' => $pass
            ];

            $user = new User();
            $result = $user->authorization($array);

            if ($result) {
                $_SESSION['authorized'] = true;
                header('location:' . '/index.html');
            } else {
    ?>
        <p class="error">
            Не удалось авторизоваться!
        </p>
    <?php
            }
        }
    ?>
    <form method="POST">
        <label>
            <input type="text" placeholder="Логин" name="login">
        </label>
        <label>
            <input type="password" placeholder="Пароль" name="password" autocapitalize="no">
        </label>
        <button type="submit" name="auth-btn">
            Войти
        </button>
    </form>
    <a href="<?='/registration.php'?>">
        <button>
            Зарегистрироваться
        </button>
    </a>
</body>
</html>
