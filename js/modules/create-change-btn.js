import showDeleteDirModal from "./delete-dir.js";
import showRenameDirModal from "./rename-dir.js";

export default function createChangeBtn(name, id, fileOrDirName) { // функция для создания кнопки для открытия контекстного меню папки/файла
    const $changeContainer = document.createElement('div'); 
    $changeContainer.className = 'change__container';

    const $changeBtn = document.createElement('button');
    $changeBtn.className = 'btn__change';
    $changeBtn.classList.add(name);

    const $changeMenu = createChangeMenu(name, id, fileOrDirName);

    $changeContainer.append($changeBtn);
    $changeContainer.append($changeMenu);

    $changeBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const $menus = document.querySelectorAll('.change__menu');

        for (const menu of $menus) {
            if (menu.classList.contains('active')) {
                menu.classList.remove('active');
            }
        }

        $changeMenu.classList.add('active');
    })

    return $changeContainer;
}

function createChangeMenu(name, id, fileOrDirName) { // создает меню для взаимодействия с папкой/файлом
    const $changeMenu = document.createElement('div'); // контейнер для списка кнопок действий
    $changeMenu.className = 'change__menu';

    const $closeMenuBtn = document.createElement('button');
    $closeMenuBtn.classList.add('change-menu__close');

    const $actionsList = document.createElement('ul');
    $actionsList.classList.add('change__list');

    $actionsList.addEventListener('click', function(e) {
        e.preventDefault();

        $changeMenu.classList.remove('active');
    })
    
    const $actionRename = document.createElement('li'),
          $actionMove = document.createElement('li'),
          $actionDelete = document.createElement('li'),
          $actionShare = document.createElement('li');

    const $renameBtn = document.createElement('button'),
          $moveBtn = document.createElement('button'),
          $deleteBtn = document.createElement('button'),
          $shareBtn = document.createElement('button');

    $renameBtn.dataset.id = $moveBtn.dataset.id = $deleteBtn.dataset.id = $shareBtn.dataset.id = id;

    $renameBtn.className = $moveBtn.className = $deleteBtn.className = $shareBtn.className = 'action__btn';

    const $actionBtn = document.createElement('button');
    $actionBtn.classList.add('action__btn');

    $moveBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        showMoveModule(id);
    })

    const actions = [
        'Переименовать',
        'Переместить',
        'Удалить',
        'Поделиться'
    ];

    $renameBtn.textContent = actions[0];
    $moveBtn.textContent = actions[1];
    $deleteBtn.textContent = actions[2];
    $shareBtn.textContent = actions[3];

    if (name === 'file-change__btn') {
        $changeMenu.classList.add('file-change__menu');

        $renameBtn.classList.add('file-rename__btn');
        $moveBtn.classList.add('file-move__btn');
        $deleteBtn.classList.add('file-delete__btn');
        $shareBtn.classList.add('file-share__btn');

        $actionRename.append($renameBtn);
        $actionMove.append($moveBtn);
        $actionDelete.append($deleteBtn);
        $actionShare.append($shareBtn);

        $actionsList.append($actionRename);
        $actionsList.append($actionMove);
        $actionsList.append($actionDelete);
        $actionsList.append($actionShare);
    } 

    if (name === 'change-dir__btn') {
        $changeMenu.classList.add('dir-change__menu');

        $renameBtn.classList.add('dir-rename__btn');
        $deleteBtn.classList.add('dir-delete__btn');

        $actionRename.append($renameBtn);
        $actionDelete.append($deleteBtn);

        $renameBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            showRenameDirModal(id, fileOrDirName);
        })

        $deleteBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            showDeleteDirModal(id);
        })

        $actionsList.append($actionRename);
        $actionsList.append($actionDelete);
    }

    $changeMenu.append($closeMenuBtn);
    $changeMenu.append($actionsList);

    return $changeMenu;
}