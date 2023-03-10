import { $mainList } from "../main.js";
import getDirInfo from "./get-dir-info.js";
import createChangeBtn from "./create-change-btn.js";

export default function createDirBtn(dir) { // создание кнопки для папки
    const $displayPath = document.querySelector('.main__display-folders');

    const $areaForDir = document.createElement('li');
    $areaForDir.className = 'list__item';

    const $changeBtn = createChangeBtn('change-dir__btn', dir['dir_id'], dir['real_name']);

    const $btnsDiv = document.createElement('div'); // обертка для кнопок
    $btnsDiv.className = 'btns__div';

    const $btnContainer = document.createElement('div');
    $btnContainer.className = 'dir__container dir';

    const $btn = document.createElement('button');
    $btn.className = 'dir__btn';
    $btn.dataset.id = dir['dir_id'];

    const $btnNameSpace = document.createElement('span');
    $btnNameSpace.className = 'dir__btn-name';
    $btnNameSpace.textContent = dir['real_name'];

    $btnsDiv.append($btn);
    $btnsDiv.append($changeBtn);

    $btnContainer.append($btnsDiv);
    $btnContainer.append($btnNameSpace);

    $areaForDir.append($btnContainer);

    $btn.addEventListener('click', async function(e) {
        e.preventDefault();

        $mainList.innerHTML = '';

        $displayPath.textContent = dir['dir_path'];

        getDirInfo(dir['dir_id']);
    })

    return $areaForDir;
}