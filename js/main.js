import getDirInfo from "./modules/get-dir-info.js";

export const $mainList = document.querySelector('.main__btn-list');
export const $mainSection = document.querySelector('.main__container');
export const $overlay = document.getElementById('overlay-modal');

const $displayPath = document.querySelector('.main__display-folders');

export function getPath() { // получение пути
    const dataPath = $displayPath.textContent;

    const normalPath = dataPath.trim();

    return normalPath;
}

(() => {
    const $logoutBtn = document.querySelector('.header__logout'),
          $menuBtn = document.querySelector('.main__menu-btn'),
          $menu = document.querySelector('.main__list');

    
    const $btnPathBack = document.getElementById('btn-back');

    // id кнопки
    let pathId;

    // кнопки меню
    const $uploadFileBtn = document.getElementById('upload-file'),
          $createDirBtn = document.getElementById('create-dir');

    // модальные окна и подложка
    const $modalCreateDir = document.querySelector('.modal-create-dir'),
          $closeCreateDirModal = document.querySelector('.modal__close-create'),
          $uploadFileModal = document.querySelector('.modal-upload-file'),
          $closeUploadModal = document.querySelector('.modal-upload-file-close');

    const $createDirForm = document.querySelector('.modal-create-dir__form');
    const $uploadFileBtnConfirm = document.getElementById('upload-file-form');
    const $uploadMessageArea = document.querySelector('.modal-upload-file__message');

    async function logout() {
        const request = await fetch('http://www.cloud-storage.local/user/logout');

        const data = await request.json();

        if (data.status === true) {
            document.location.href = '/authorization';
        } else {
            alert(data.message);
        }
    }

    window.addEventListener('DOMContentLoaded', async function() {
        const request = await fetch('http://www.cloud-storage.local/directory/root');
        const data = await request.json();

        pathId = data.rootDir[0]['dir_id'];

        getDirInfo(pathId);

        // отображение пути на экране
        $displayPath.textContent = data.rootDir[0]['path'];
    })

    // выход из учетной записи
    $logoutBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        logout();
    })

    // открытие и закрытие меню
    $menuBtn.addEventListener('click', function() {
        $menu.classList.toggle('active');
        $menuBtn.classList.toggle('active');
    })

    $createDirBtn.addEventListener('click', function(e) {
        e.preventDefault();

        $modalCreateDir.classList.add('activeModal');
        $overlay.classList.add('activeModal');
    })

    $closeCreateDirModal.addEventListener('click', function(e) {
        e.preventDefault();

        $modalCreateDir.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    $uploadFileBtn.addEventListener('click', function(e) {
        e.preventDefault();

        $uploadMessageArea.textContent = '';

        $uploadFileModal.classList.add('activeModal');
        $overlay.classList.add('activeModal');
    })

    $closeUploadModal.addEventListener('click', function(e) {
        e.preventDefault();

        $uploadFileModal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    // загрузка файла
    $uploadFileBtnConfirm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const fileInput = document.querySelector('.modal-upload-file__file').files[0];
        const path = getPath();

        let formData = new FormData();
        formData.append('file', fileInput);
        formData.append('path', path);

        const request = await fetch('http://www.cloud-storage.local/files', {
            method: 'POST',
            body: formData
        });
        const data = await request.json();

        if (data.status) {
            $uploadMessageArea.style = 'color: var(--usual-color)';
            $uploadMessageArea.textContent = data.message;

            // перезагрузка страницы после успешного создания папки
            setTimeout(function() {
                location.reload()
            }, 1500)
        } else {
            $uploadMessageArea.style = 'color: #FF4040';
            $uploadMessageArea.textContent = data.message;
        }
    })

    // создание папки
    async function createNewDir() {
        const path = getPath();

        const dirName = document.querySelector('.modal-create-dir__input').value;
        const newPath = path;

        const $messageArea = document.querySelector('.modal-create-dir__message');

        const request = await fetch('http://www.cloud-storage.local/directory', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                path: newPath,
                dirName: dirName
            })
        });
        const data = await request.json();

        if (data.status) {
            $messageArea.style = 'color: var(--usual-color)';
            $messageArea.textContent = data.message;

            // перезагрузка страницы после успешного создания папки
            setTimeout(function() {
                location.reload()
            }, 1000)
        } else {
            $messageArea.style = 'color: #FF4040';
            $messageArea.textContent = data.message;
        }
    }

    $createDirForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        createNewDir();
    })

    // возвращение назад 
    if (getPath() === '/') {
        $btnPathBack.setAttribute('disabled', '');

        $btnPathBack.classList.add('disabled');
    }

    $btnPathBack.addEventListener('click', async function(e) {
        e.preventDefault();

        $mainList.innerHTML = '';

        const request = await fetch('http://www.cloud-storage.local/directory/root');
        const data = await request.json();

        pathId = data.rootDir[0]['dir_id'];

        getDirInfo(pathId);

        // отображение пути на экране
        $displayPath.textContent = data.rootDir[0]['path'];
    })
})();
