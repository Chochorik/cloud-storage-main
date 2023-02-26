(() => {
    const $logoutBtn = document.querySelector('.header__logout'),
          $mainSection = document.querySelector('.main__section'),
          $menuBtn = document.querySelector('.main__menu-btn'),
          $menu = document.querySelector('.main__list');

    const $uploadFileBtn = document.getElementById('upload-file'),
          $createDirBtn = document.getElementById('create-dir');

    async function logout() {
        const request = await fetch('http://www.cloud-storage.local/user/logout');

        const data = await request.json();

        if (data.status === true) {
            document.location.href = '/authorization';
        } else {
            console.log(data.message);
        }
    }

    // выход из учетной записи
    $logoutBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        logout();
    })

    // открытие и закрытие меню
    $menuBtn.addEventListener('click', function() {
        $menu.classList.toggle('active');
        $menuBtn.classList.toggle('active');
    })
})();
