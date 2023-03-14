import { $overlay, getPath } from "../main.js";

export default async function showFileMoveModal(id) {
    const $modal = document.querySelector('.modal-move-file'),
          $closeModal = document.querySelector('.modal-move-file__close'),
          $submitBtn = document.querySelector('.modal-move-file__btn'),
          $form = document.querySelector('.modal-move-file__form'),
          $messageArea = document.querySelector('.modal-move-file__message');

    $modal.classList.add('activeModal');
    $overlay.classList.add('activeModal');
    
    $messageArea.textContent = '';

    $submitBtn.dataset.id = id;

    // элементы формы
    const $dirList = document.querySelector('.modal-move-file__list'); // селект, в котором будут находиться пути папок, в которые можно переместить файл
    $dirList.innerHTML = '';

    const currentPath = getPath(); // текущий путь

    const getDirs = await fetch('http://www.cloud-storage.local/dirList');
    const dirsData = await getDirs.json();

    for (const dir of dirsData.dirList) {
        if (dir['path'] !== currentPath) {
            const $select = document.createElement('input');
            $select.setAttribute('type', 'radio');
            $select.setAttribute('name', 'dir');
            $select.setAttribute('required', '');
            $select.className = 'modal-move-file__dir';

            const $label = document.createElement('label');
            $label.className = 'modal-move-file__label';
            $label.textContent = dir['path'];

            $select.value = dir['path'];

            $label.append($select);
            $dirList.append($label);
        }
    }

    $closeModal.addEventListener('click', function(e) {
        e.preventDefault();

        $modal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    $submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        let dir;

        const $inputs = document.querySelectorAll('input[name="dir"]');

        for (const input of $inputs) {
            if (input.checked) {
                dir = input.value;
            }
        }

        const request = await fetch(`http://www.cloud-storage.local/files/${$submitBtn.dataset.id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                method: 'move',
                currentPath: currentPath,
                newPath: dir
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
    }) 
}