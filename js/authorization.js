(() => {
    const authForm = document.getElementById('auth__form');

    async function authorization() {
        const login = document.querySelector('.auth__login').value,
              password = document.querySelector('.auth__password').value,
              message = document.querySelector('.text-of-result');

        message.textContent = '';

        const user = {
            'login': login,
            'password': password
        };

        const request = await fetch('../index.php/user/authorization', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(user)
        });

        const data = await request.json();

        if (data.status === true) {
            document.location.href = '/pages/main.php';
        } else {
            message.textContent = data.message;
        }
    }

    authForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        await authorization();
    })
})();