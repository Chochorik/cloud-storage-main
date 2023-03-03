(() => {
    const $logoutBtn = document.querySelector('.header__logout'),
          $mainSection = document.querySelector('.main__section'),
          $menuBtn = document.querySelector('.main__menu-btn'),
          $menu = document.querySelector('.main__list');

    // данный путь
    let path = '/';

    const $displayPath = document.querySelector('.main__display-folders');

    // кнопки меню
    const $uploadFileBtn = document.getElementById('upload-file'),
          $createDirBtn = document.getElementById('create-dir');

    // модальные окна и подложка
    const $modalCreateDir = document.querySelector('.modal-create-dir'),
          $overlay = document.getElementById('overlay-modal'),
          $closeCreateDirModal = document.querySelector('.modal__close-create'),
          $uploadFileModal = document.querySelector('.modal-upload-file'),
          $closeUploadModal = document.querySelector('.modal-upload-file-close');

    const $createDirConfirm = document.querySelector('.modal-create-dir__btn');

    async function logout() {
        const request = await fetch('http://www.cloud-storage.local/user/logout');

        const data = await request.json();

        if (data.status === true) {
            document.location.href = '/authorization';
        } else {
            console.log(data.message);
        }
    }

    window.addEventListener('DOMContentLoaded', async function() {
        let files,
            directories;

        const request = await fetch(`http://www.cloud-storage.local/directory?dirName=${path}`);
        const data = await request.json();

        console.log(data)
        // if (data.status) {
        //     path = data.path;
        //     files = data.filesArray;
        //     directories = data.dirArray;
        // } else {
        //     $displayPath.textContent = data.message;
        // }
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

        $uploadFileModal.classList.add('activeModal');
        $overlay.classList.add('activeModal');
    })

    $closeUploadModal.addEventListener('click', function(e) {
        e.preventDefault();

        $uploadFileModal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    // создание папки
    $createDirConfirm.addEventListener('click', async function(e) {
        e.preventDefault();

        const dirName = document.querySelector('.modal-create-dir__input').value;

        const request = await fetch('http://www.cloud-storage.local/directory', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                dirName: dirName
            })
        })

        const data = await request.json();

        console.log(data);
    })
})();
