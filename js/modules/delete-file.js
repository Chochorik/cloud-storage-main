import { $overlay } from "../main.js";
import { link } from "../link.js";

export default async function showDeleteFileModal(id) {
    const $modal = document.querySelector('.modal-delete-file'),
          $closeModal = document.querySelector('.modal-delete-file__close'),
          $submitBtn = document.querySelector('.modal-delete-file__sumbit'),
          $messageArea = document.querySelector('.modal-delete-file__message');

    $submitBtn.dataset.id = id;
    $messageArea.textContent = '';

    $modal.classList.add('activeModal');
    $overlay.classList.add('activeModal');

    $closeModal.addEventListener('click', function(e) {
        e.preventDefault();

        $modal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    $submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const request = await fetch(link + `/files/${$submitBtn.dataset.id}`, {
            method: 'DELETE'
        });
        const data = await request.json();

        if (data.status) {
            $messageArea.style = 'color: var(--usual-color)';
            $messageArea.textContent = data.message;
            
            // перезагрузка страницы после успешного удаления файла
            setTimeout(function() {
                location.reload()
            }, 500)
        } else {
            $messageArea.style = 'color: #FF4040';
            $messageArea.textContent = data.message;
        }
    })
}