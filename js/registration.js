(() => {
    const regForm = document.getElementById('registration__form');
          
    async function registration() {
        const login = document.querySelector('.input__login').value,
              email = document.querySelector('.input__email').value,
              password = document.querySelector('.input__password').value,
              passwordConfirm = document.querySelector('.input__password_conf').value,
              message = document.querySelector('.text-of-result');
        
        let $inputs = document.querySelectorAll('.registration__input');
        
        message.textContent = '';

        const user = {
            'login': login,
            'email': email,
            'password': password,
            'password_confirm': passwordConfirm
        };

        const request = await fetch('http://www.cloud-storage.local/user/registration', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(user)
        });

        const data = await request.json();

        if (data.status === true) {
            message.style = 'color: var(--usual-color)';
            message.textContent = data.message; 
            
            for (const input of $inputs) {
                input.value = '';
            }
        } else {
            message.style = 'color: #FF4040';
            message.textContent = data.message;
        }
    }

    regForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        await registration();
    })
})();