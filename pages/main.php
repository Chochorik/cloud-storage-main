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
                <div class="main__display-folders">
                    <!-- <span class="main__path"></span> -->
                </div>
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
    <div class="modals">
    <div class="modal modal-upload-file">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-upload-file-close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-upload-file__title">
                Загрузить файл
            </h2>
            <form class="modal-upload-file__form" id="upload-file-form" enctype="multipart/form-data">
                <p class="modal-upload-file__message"></p>
                <label class="modal-upload-file__label">
                    Выберите файл
                    <input type="file">
                </label>
                <button type="submit" class="modal-upload-file__btn">
                    Загрузить
                </button>
            </form>
        </div>
        <div class="modal modal-create-dir">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal__close-create" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-create-dir__title">
                Создать папку
            </h2>
            <form class="modal-create-dir__form" id="create-dir-form">
                <p class="modal-create-dir__message"></p>
                <label class="modal-create-dir__label">
                    Введите название папки
                    <input class="modal-create-dir__input" type="text" placeholder="Название папки" name="dir-name">
                </label>
                <button type="submit" class="modal-create-dir__btn">
                    Создать
                </button>
            </form>
        </div>
        <!-- Подложка под модальным окном -->
        <div class="overlay" id="overlay-modal"></div>
    </div>
</body>
</html>
