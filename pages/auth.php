<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script defer src="../js/authorization.js"></script>
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="icon" href="../media/cloud.svg" type="image/svg+xml">
    <title>Авторизация</title>
</head>
<body>
    <section class="container">
        <div class="auth__container">
            <h1 class="auth__title">
                Авторизуйтесь
            </h1>
            <div class="registration__result">
                <p class="text-of-result"></p>
            </div>
            <form class="auth__form" id="auth__form">
                <label class="auth__label">
                    <input class="auth__input auth__login" type="text" placeholder="Логин" name="login">
                </label>
                <label class="auth__label">
                    <input class="auth__input auth__password" type="password" placeholder="Пароль" name="password" autocapitalize="no">
                </label>
                <button type="submit" name="auth-btn" class="auth__btn">
                    Войти
                </button>
            </form>
            <div class="actions">
                <a href="/registration" class="registration__link">
                    <button class="registration__btn-link">
                        Зарегистрироваться
                    </button>
                </a>
                <a href="/recovery" class="registration__link-recover-pass">
                    <button class="passowrd__recovery-btn">
                        Забыли пароль?
                    </button>
                </a>
            </div>
        </div>
    </section>
</body>
</html>
