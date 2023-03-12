(() => {
    const table = document.querySelector('.table');
    const $overlay = document.querySelector('.overlay');

    let column = 'id'; // сортировка таблицы по id (по умолчанию)
    let columnDirection = true; // направление сортировки таблицы по возрастанию (по умолчанию)

    async function newUserTr(user) { // функция создания строки пользователя в таблице
        const $userTR = document.createElement('tr'),
            $userId = document.createElement('td'),
            $userLogin = document.createElement('td'),
            $userEmail = document.createElement('td'),
            $userRole = document.createElement('td'),
            $userActions = document.createElement('td');

        $userId.textContent = user.id;
        $userLogin.textContent = user.login;
        $userEmail.textContent = user.email;
        $userRole.textContent = user.role;

        $userActions.append(await createUpdateBtn(user));
        $userActions.append(await createDeleteBtn(user));

        $userTR.append($userId);
        $userTR.append($userLogin);
        $userTR.append($userEmail);
        $userTR.append($userRole);
        $userTR.append($userActions);

        return $userTR;
    }

    async function getSortedUsers(property, direction) { // функция сортировки пользователей
        const response = await fetch('http://www.cloud-storage.local/admin/users');
        const data = await response.json();

        if (data.status === true) {
            const users = data.array;

            return users.sort(function (userA, userB) {
                if ((!direction == false ? userA[property] < userB[property] : userA[property] > userB[property]))
                    return -1;
            })
        }
    }

    // прорисовка таблицы
    async function renderingTable() {
        // запрос на сервер для получения всех пользователей
        const response = await fetch('http://www.cloud-storage.local/admin/users');
        const data = await response.json();

        if (data.status === true) {
            let users = data.array;

            let usersList = document.getElementById('table__body'); // тело таблицы

            usersList.innerHTML = ''; // обнуляем содержимое таблицы

            // сортировка таблицы
            users = await getSortedUsers(column, columnDirection);

            // добавление всех полученных пользователей в таблицу
            for (const user of users) {
                usersList.append(await newUserTr(user));
            }
        }
    }

    async function createUpdateBtn(user) {
        const $button = document.createElement('button');

        $button.dataset.id = user.id;

        const $modal = document.querySelector('.modal__update'),
            $closeModal = document.querySelector('.modal__cross'),
            $confirmBtn = document.querySelector('.modal__confirm-btn');

        const $id = document.querySelector('.modal-update__span'),
            $login = document.querySelector('.input__login'),
            $email = document.querySelector('.input__email'),
            $role = document.querySelector('.input__role');

        $button.className = 'admin__change-btn';
        $button.textContent = 'Изменить';

        $button.addEventListener('click', async function (e) {
            e.preventDefault();

            $confirmBtn.dataset.id = $button.dataset.id;

            const request = await fetch(`http://www.cloud-storage.local/admin/users/${$button.dataset.id}`);
            const data = await request.json();
    
            if (data.status) {
                const array = data.user;
    
                $id.textContent = array['id'];
                $login.value = array['login'];
                $email.value = array['email'];
                $role.value = array['role'];
            }

            $modal.classList.add('modal_active');
            $overlay.classList.add('modal_active');
        })

        $confirmBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            const $error = document.querySelector('.modal__message');

            const updateRequest = await fetch(`http://www.cloud-storage.local/admin/users/${$confirmBtn.dataset.id}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    login: $login.value,
                    email: $email.value,
                    role: $role.value,
                })
            })

            const newdata = await updateRequest.json();

            if (newdata.status) {
                document.location.reload();
            } else {
                $error.textContent = newdata.message;
            }
        })

        $closeModal.addEventListener('click', function (e) {
            e.preventDefault();

            $modal.classList.remove('modal_active');
            $overlay.classList.remove('modal_active');
        });

        return $button;
    }

    async function createDeleteBtn(user) {
        const $button = document.createElement('button');

        $button.dataset.id = user.id;

        const $modal = document.querySelector('.modal__delete'),
            $closeModal = document.querySelector('.modal__cross_delete'),
            $confirmBtn = document.querySelector('.modal__confirm-delete-btn'),
            $errorBlock = document.querySelector('.modal__errors');

        $button.addEventListener('click', function (e) {
            e.preventDefault();

            $errorBlock.textContent = '';

            $confirmBtn.dataset.id = $button.dataset.id;

            $modal.classList.add('modal_active');
            $overlay.classList.add('modal_active');
        })

        $closeModal.addEventListener('click', function (e) {
            e.preventDefault();

            $modal.classList.remove('modal_active');
            $overlay.classList.remove('modal_active');
        })

        $confirmBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            const request = await fetch(`http://www.cloud-storage.local/admin/users/${$confirmBtn.dataset.id}`, {
                method: 'DELETE'
            })

            const data = await request.json();

            if (data.status) {
                document.location.reload();
            } else {
                $errorBlock.textContent = data.message;
            }
        })

        $button.className = 'admin__delete-btn';
        $button.textContent = 'Удалить';

        return $button;
    }

    renderingTable();
})();