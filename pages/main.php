<?php

session_start();

if (!$_SESSION['authorized']) {
    header('location: /authorization');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script defer src="/js/main.js"></script>
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/basic.css">
    <link rel="stylesheet" href="/css/fonts.css">
    <link rel="stylesheet" href="/css/main-style.css">
    <link rel="icon" href="/media/cloud.svg" type="image/svg+xml">
    <title>Cloud Storage</title>
</head>
<body class="body">
    <header class="header">
        <div class="container header__container">
            <div class="header__info">
                <h1 class="header__title">
                    Cloud Storage
                </h1>
                <p class="header__user">
                    Пользователь: <?= $_SESSION['user'] ?>
                </p>
            </div>
            <div class="header__actions">
                <?php if ($_SESSION['admin'] === true):?>
                    <a href="/admin/panel">
                        <button class="header__admin-panel">
                            Панель управления
                        </button>
                    </a>
                <?php endif ?>
                <button class="header__logout">
                    Выйти
                </button>
            </div>
        </div>
    </header>
    <main class="main">
        <section class="main__section">
            <div class="container main__container">
                    
            </div>
            <div class="main__menu">
                <ul class="main__list">
                    <li class="main__item">
                        <button class="main__btn-action" id="upload-file">
                            Загрузить файл
                        </button>
                    </li>
                    <li class="main__item">
                        <button class="main__btn-action" id="create-dir">
                            Создать папку
                        </button>
                    </li>
                </ul>
                <button class="main__menu-btn"></button>
            </div>
        </section>
    </main>
</body>
</html>
