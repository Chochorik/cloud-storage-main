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
    <script defer type="module" src="/js/main.js"></script>
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
                <a href="/shared-files">
                    <button class="header__shared">
                        Доступные файлы
                    </button>
                </a>
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
                <div class="main__btn-back">
                    <button class="main__btn-last-path" id="btn-back"></button>
                    <div class="main__display-folders"></div>
                    <ul class="main__btn-list"></ul>
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
                <div class="modal-upload-file__wrapper">
                    <input type="file" class="modal-upload-file__file" name="file" id="modal-upload-file__file">
                    <label class="modal-upload-file__label" for="modal-upload-file__file">
                        <span class="modal-upload-file__icon-wrapper">
                            <img class="modal-upload-file__icon" src="/media/arrow_down.svg" alt="Загрузить файл">
                        </span>
                        <span class="modal-upload-file__select-btn">
                            Выберите файл
                        </span>
                    </label>
                </div>
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
        <div class="modal modal-rename-dir">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-rename-dir__close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-rename-dir__title">
                Переименовать папку
            </h2>
            <form class="modal-rename-dir__form" id="create-dir-form">
                <p class="modal-rename-dir__message"></p>
                <label class="modal-rename-dir__label">
                    Введите новое имя папки
                    <input class="modal-rename-dir__input" type="text" placeholder="Название папки" name="dir-name">
                </label>
                <button type="submit" class="modal-rename-dir__btn">
                    Переименовать
                </button>
            </form>
        </div>
        <div class="modal modal-delete-dir">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-delete-dir__close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-delete-dir__title">
                Удалить папку?
            </h2>
            <p class="modal-delete-dir__message"></p>
            <button class="modal-delete-dir__sumbit"> 
                Подтвердите удаление папки
            </button>
        </div>
        <div class="modal modal-delete-file">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-delete-file__close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-delete-file__title">
                Удалить файл?
            </h2>
            <p class="modal-delete-file__message"></p>
            <button class="modal-delete-file__sumbit"> 
                Подтвердите удаление файла
            </button>
        </div>
        <div class="modal modal-rename-file">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-rename-file__close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-rename-file__title">
                Переименовать файл
            </h2>
            <form class="modal-rename-file__form" id="create-file-form">
                <p class="modal-rename-file__message"></p>
                <label class="modal-rename-file__label">
                    Введите новое файла
                    <input class="modal-rename-file__input" type="text" placeholder="Название файла" name="file-name">
                </label>
                <button type="submit" class="modal-rename-file__btn">
                    Переименовать
                </button>
            </form>
        </div>
        <div class="modal modal-move-file">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-move-file__close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-move-file__title">
                Переместить файл
            </h2>
            <p class="modal-move-file__message"></p>
            <form class="modal-move-file__form" id="create-file-form">
                <div class="modal-move-file__list"></div>
                <button type="submit" class="modal-move-file__btn">
                    Переместить
                </button>
            </form>
        </div>
        <div class="modal modal-share-file">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal-share-file__close" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal-share-file__title">
                Поделиться файлом
            </h2>
            <p class="modal-share-file__message"></p>
            <p class="modal-share-file__message-2"></p>
            <div class="modal-share-file__container share-container">
                <div class="share-container__left">
                    <p class="share-container__descr">
                        Список пользователей
                    </p>
                    <ul class="share-container__list"></ul>
                </div>
                <div class="share-container__right">
                    <form class="share-container__form">
                        <label class="share-container__label">
                            Введите email пользователя
                            <input class="share-container__input" type="email" placeholder="Email" name="email" autocomplete="off">
                        </label>
                        <button class="share-container__btn" type="submit">
                            Добавить пользователя
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Подложка под модальным окном -->
        <div class="overlay" id="overlay-modal"></div>
    </div>
</body>
</html>
