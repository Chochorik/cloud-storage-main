import { $overlay, getPath } from "../main.js";

export default async function showUpdateFileModal(id) {
    const $modal = document.querySelector('.modal-rename-file'),
          $closeModal = document.querySelector('.modal-rename-file__close'),
          $submitBtn = document.querySelector('.modal-rename-file__btn'),
          $messageArea = document.querySelector('.modal-rename-file__message');

    const $fileName = document.querySelector('.modal-rename-file__input');
    $fileName.value = '';

    $submitBtn.dataset.id = id;

    $modal.classList.add('activeModal');
    $overlay.classList.add('activeModal');

    $closeModal.addEventListener('click', function(e) {
        e.preventDefault();

        $modal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    $submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const newFileName = $fileName.value;

        const request = await fetch(`http://www.cloud-storage.local/files/${$submitBtn.dataset.id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                newFileName: newFileName,
                method: 'rename',
                path: getPath()
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