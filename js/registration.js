(() => {
    const regForm = document.getElementById('registration__form');
          
    async function registration() {
        const login = document.querySelector('.input__login').value,
              email = document.querySelector('.input__email').value,
              password = document.querySelector('.input__password').value,
              passwordConfirm = document.querySelector('.input__password_conf').value,
              message = document.querySelector('.text-of-result');
        
        message.textContent = '';

        const user = {
            'login': login,
            'email': email,
            'password': password,
            'password_confirm': passwordConfirm
        };

        const request = await fetch('../index.php/user/registration', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(user)
        });

        const data = await request.json();

        if (data.status === true) {
            document.location.href = '/pages/auth.php';
        } else {
            message.textContent = data.message;
        }
    }

    regForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        await registration();
    })
})();