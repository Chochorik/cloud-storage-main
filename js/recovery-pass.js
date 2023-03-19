import { link } from "./link.js";

(() => {
    const recoveryForm = document.getElementById('recovery__form');

    async function recoverPassword() {
        const login = document.querySelector('.recovery__input-login').value,
              messageArea = document.querySelector('.text-of-result');

        const formData = {
            'login': login
        };

        const request = await fetch(link + '/user/recovery', {
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

        await recoverPassword();
    })
})();