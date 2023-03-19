import { link } from "../link.js";
import { $overlay, getPath } from "../main.js";

export default async function showRenameDirModal(id, dirName) {
    const $modal = document.querySelector('.modal-rename-dir'),
          $closeModal = document.querySelector('.modal-rename-dir__close'),
          $submitBtn = document.querySelector('.modal-rename-dir__btn'),
          $messageArea = document.querySelector('.modal-rename-dir__message');

    const $newDirName = document.querySelector('.modal-rename-dir__input');
    $newDirName.value = dirName;

    $submitBtn.dataset.id = id;
    $messageArea.textContent = ''; // обнуление поля сообщений

    $modal.classList.add('activeModal');
    $overlay.classList.add('activeModal');

    $closeModal.addEventListener('click', function(e) {
        e.preventDefault();

        $modal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    $submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const newDirName = $newDirName.value;

        const dir = {
            "newDirName": newDirName,
            "oldDirName": dirName,
            "currentPath": getPath()
        };

        const request = await fetch(link + `/directory/${$submitBtn.dataset.id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(dir)
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