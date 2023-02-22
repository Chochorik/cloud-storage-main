(() => {
    const recoveryForm = document.getElementById('recovery__form'),
          messageArea = document.querySelector('.text-of-result');

    async function recoverPassword() {
        const login = document.querySelector('.recovery__input').value;

        const formData = {
            'login': login
        };

        const request = await fetch('http://www.cloud-storage.local/user/recovery', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });

        const data = await request.json();

        if (data.status === true) {
            messageArea.style = 'color: var(--usual-color)';
            messageArea.textContent = data.message;
        } else {
            messageArea.style = 'color: #FF4040';
            messageArea.textContent = data.message;
        }
    }

    recoveryForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        recoverPassword();
    })
})();