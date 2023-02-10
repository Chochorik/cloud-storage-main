<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script defer src="js/jquery.min.js"></script>
    <script defer src="js/registration.js"></script>
    <title>Авторизация</title>
</head>
<body>
    <h1>
        Страница регистрации
    </h1>
    <div class="result">
        <p class="text-of-result"></p>
    </div>
    <form name="registration">
        <label class="field">
            <input class="input__login input" type="text" placeholder="Логин" name="login">
        </label>
        <label class="field">
            <input class="input__email input" type="email" placeholder="Email" name="email">
        </label>
        <label class="field">
            <input class="input__password input" type="password" placeholder="Пароль" name="password" autocomplete="no">
        </label>
        <label class="field">
            <input class="input__password input" type="password" placeholder="Повторите пароль" name="password_confirm" autocomplete="no">
        </label>
        <button type="submit" name="btn-register">
            Зарегистрироваться
        </button>
    </form>
    <a href="<?='/auth.php'?>">
        <button>
            Войти
        </button>
    </a>
</body>
</html>