<?php

session_start();

if (!$_SESSION['authorized']) {
    header('location: /authorization');
}

if (!$_SESSION['admin']) {
    header('location: /');
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script defer src="/js/admin.js"></script>
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/basic.css">
    <link rel="stylesheet" href="/css/fonts.css">
    <link rel="stylesheet" href="/css/main-style.css">
    <link rel="icon" href="/media/cloud.svg" type="image/svg+xml">
    <title>Cloud Storage (admin)</title>
</head>

<body>
    <header class="header">
        <div class="container header__container">
            <div class="header__info">
                <h1 class="header__title">
                    Cloud Storage
                </h1>
                <p class="header__user">
                    Панель управления
                </p>
            </div>
            <div class="header__actions">
                <a href="/">
                    <button class="header__back">
                        Вернуться на главную
                    </button>
                </a>
            </div>
        </div>
    </header>
    <main class="main">
        <section class="main__section">
            <div class="container main__container">
                <table class="table admin__table">
                    <thead class="table__head">
                        <th class="table__th table__id" data-column="id">
                            ID
                        </th>
                        <th class="table__th table__login" data-column="login">
                            Login
                        </th>
                        <th class="table__th table__email" data-column="email">
                            Email
                        </th>
                        <th class="table__th table__role" data-column="role">
                            Role
                        </th>
                        <th class="table__th table__actions">
                            Действия
                        </th>
                    </thead>
                    <tbody class="table__body" id="table__body"></tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modals">
        <div class="modal modal__update">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal__title">
                Изменение данных клиента
            </h2>
            <p class="modal-update__id">
                #<span class="modal-update__span"></span>
            </p>
            <p class="modal__message"></p>
            <form class="modal__update">
                <label class="modal__label">
                    Login
                    <input class="modal__input input__login" type="text" placeholder="Логин" name="login" autocomplete="off">
                </label>
                <label class="modal__label">
                    Email
                    <input class="modal__input input__email" type="email" placeholder="Email" name="email" autocomplete="off">
                </label>
                <label class="modal__label">
                    Role
                    <input class="modal__input input__role" type="text" placeholder="Роль" name="role" autocomplete="off">
                </label>
            </form>
            <button class="modal__confirm-btn">
                Изменить
            </button>
        </div>
        <div class="modal modal__delete">
            <!--   Svg иконка для закрытия окна  -->
            <svg class="modal__cross modal__cross_delete" width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.2333 7.73333L21.2666 6.76666L14.4999 13.5334L7.73324 6.7667L6.76658 7.73336L13.5332 14.5L6.7666 21.2667L7.73327 22.2333L14.4999 15.4667L21.2666 22.2334L22.2332 21.2667L15.4666 14.5L22.2333 7.73333Z" fill="#B0B0B0" />
            </svg>
            <h2 class="modal__title">
                Подтверждение удаления пользователя
            </h2>
            <p class="modal__errors"></p>
            <button class="modal__confirm-delete-btn">
                Удалить
            </button>
        </div>
        <!-- Подложка под модальным окном -->
        <div class="overlay" id="overlay-modal"></div>
    </div>
</body>

</html>