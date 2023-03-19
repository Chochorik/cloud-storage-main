import createChangeBtn from "./create-change-btn.js";
import { link } from "../link.js";

export default async function createFileBtn(file) {
    const $areaForFile = document.createElement('li');
    $areaForFile.className = 'list__item';

    const $btnContainer = document.createElement('div');
    $btnContainer.className = 'file__container file';

    const $changeBtn = createChangeBtn('file-change__btn', file['file_id'], file['real_name']);

    const $btnsDiv = document.createElement('div'); // обертка для кнопок
    $btnsDiv.className = 'btns__div';

    const $fileLink = document.createElement('a');
    $fileLink.className = 'file__link';

    const $btn = document.createElement('btn');
    $btn.className = 'file__btn';
    $btn.dataset.id = file['file_id'];

    const $fileNameSpace = document.createElement('span');
    $fileNameSpace.className = 'file__btn-name';

    const request = await fetch(link + `/files/${$btn.dataset.id}`);
    const data = await request.json();

    if (!data.status) {
        alert(data.message);
        return;
    } 

    const fileName = data.file_info['real_name'];
    const fileLink = data.file_info['link'];

    $fileNameSpace.textContent = fileName;
    $fileLink.setAttribute('href', fileLink);
    $fileLink.setAttribute('download', '');

    $btnsDiv.append($btn);
    $btnsDiv.append($changeBtn);

    $fileLink.append($btnsDiv);

    $btnContainer.append($fileLink);
    $btnContainer.append($fileNameSpace);

    $areaForFile.append($btnContainer);

    return $areaForFile;
}