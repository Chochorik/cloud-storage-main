import { $overlay } from "../main.js";

export default async function showShareFileModal(id) {
    const $modal = document.querySelector('.modal-share-file'),
          $closeModal = document.querySelector('.modal-share-file__close'),
          $shareWithUserBtn = document.querySelector('.share-container__btn'),
          $messageArea = document.querySelector('.modal-share-file__message'),
          $messageArea2 = document.querySelector('.modal-share-file__message-2'),
          $emailInput = document.querySelector('.share-container__input');

    $modal.classList.add('activeModal');
    $overlay.classList.add('activeModal');
    
    $messageArea.textContent = '';
    $messageArea2.textContent = '';
    $shareWithUserBtn.dataset.id = id;
    $emailInput.value = '';

    // наполнение таблицы пользователей, у которых есть доступ к файлу
    
    createUserList($shareWithUserBtn.dataset.id, $messageArea);

    // закрытие модального окна
    $closeModal.addEventListener('click', function(e) {
        e.preventDefault();

        $modal.classList.remove('activeModal');
        $overlay.classList.remove('activeModal');
    })

    // добавление пользователю прав доступа к файлу
    $shareWithUserBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const email = $emailInput.value;

        const request = await fetch('http://www.cloud-storage.local/get-user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                email: email
            })
        });
        const data = await request.json();

        if (data.status) {
            $messageArea2.textContent = '';
            $messageArea.style = 'color: var(--usual-color)';
            $messageArea.textContent = data.message;
        } else {
            $messageArea2.textContent = '';
            $messageArea.style = 'color: #FF4040';
            $messageArea.textContent = data.message;

            return;
        }

        const giveAccess = await fetch('http://www.cloud-storage.local/files/share', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                fileId: $shareWithUserBtn.dataset.id,
                userId: data.id
            })
        });

        const response = await giveAccess.json();
        
        if (response.status) {
            $messageArea.textContent = '';
            $messageArea2.style = 'color: var(--usual-color)';
            $messageArea2.textContent = response.message;

            setTimeout(function() {
                $modal.classList.remove('activeModal');
                $overlay.classList.remove('activeModal');
            }, 1000)
        } else {
            $messageArea.textContent = '';
            $messageArea2.style = 'color: #FF4040';
            $messageArea2.textContent = response.message;

            return;
        }
    })
}

async function createUserList(fileId, $messageArea) {
    const getUsersList = await fetch(`http://www.cloud-storage.local/files/share/${fileId}`);
    const usersList = await getUsersList.json();

    const users = usersList.users;

    const $usersList = document.querySelector('.share-container__list');
    $usersList.innerHTML = '';

    for (const user of users) {
        const $li = document.createElement('li');
        $li.className = 'share-container__item';

        const $name = document.createElement('span');
        $name.className = 'share-container__user-name';

        const $deleteBtn = document.createElement('button');
        $deleteBtn.className = 'share-container__delete-btn';
        $deleteBtn.dataset.id = user['id'];
        
        $deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();

            denyAccessForUser($deleteBtn.dataset.id, fileId, $messageArea);
        })

        $name.textContent = user['login'];

        $li.append($name);
        $li.append($deleteBtn);
        $usersList.append($li);
    }

    return $usersList;
}

async function denyAccessForUser(userId, fileId, $messageArea) {
    const request = await fetch('http://www.cloud-storage.local/files/deny-access', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            fileId: fileId,
            userId: userId
        })
    });
    const data = await request.json();

    if (data.status) {
        $messageArea.textContent = '';
        $messageArea.style = 'color: var(--usual-color)';
        $messageArea.textContent = data.message;

        createUserList(fileId, $messageArea);
    } else {
        $messageArea.textContent = '';
        $messageArea.style = 'color: #FF4040';
        $messageArea.textContent = data.message;
    }
}