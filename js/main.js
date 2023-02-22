(() => {
    const logoutBtn = document.querySelector('.header__logout');
    const mainSection = document.querySelector('.main__section');
    const adminPanelBtn = document.querySelector('.header__admin-panel');

    async function logout() {
        const request = await fetch('http://www.cloud-storage.local/user/logout');

        const data = await request.json();

        if (data.status === true) {
            document.location.href = '/authorization';
        } else {
            console.log(data.message);
        }
    }

    logoutBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        logout();
    })
})();
