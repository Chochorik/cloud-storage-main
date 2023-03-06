export default function createChangeBtn() { // функция для создания кнопки для открытия контекстного меню папки/файла
    const $changeBtn = document.createElement('button');
    $changeBtn.className = 'btn__change';

    return $changeBtn;
}