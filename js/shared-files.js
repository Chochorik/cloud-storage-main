(() => {
    async function getSharedList() {
        const $mainContainer = document.querySelector('.main__container');

        const request = await fetch('http://www.cloud-storage.local/files-list/shared');
        const data = await request.json();

        const $message = document.createElement('p');
        $message.className = 'main__message';
        $message.textContent = '';

        if (data.status === 'empty') {
            $message.textContent = data.message;
            $mainContainer.append($message);

            return $mainContainer;
        }

        const $list = await createSharedFilesList(data.list);

        $mainContainer.append($list);
    }

    async function createSharedFilesList(filesList) {
        const $list = document.createElement('ul');
        $list.className = 'main__btn-list';

        for (const file of filesList) {
            const $li = document.createElement('li');
            $li.className = 'list__item';

            const $btnContainer = document.createElement('div');
            $btnContainer.className = 'file__container file';

            const $link = document.createElement('a');
            $link.className = 'file__link';
            $link.href = file['link'];

            const $fileBtn = document.createElement('button');
            $fileBtn.className = 'file__btn';

            const $fileName = document.createElement('span');
            $fileName.className = 'file__btn-name';
            $fileName.textContent = file['real_name'];

            $link.append($fileBtn);
            $link.append($fileName);

            $btnContainer.append($link);

            $li.append($btnContainer);

            $list.append($li);
        }

        return $list;
    }

    getSharedList();
})();