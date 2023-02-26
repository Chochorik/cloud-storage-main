(() => {
    const $form = document.getElementById('recovery__form');

    async function setNewPass() {
        const $password = document.querySelector('.recovery__new-pass').value,
              $repeatPass = document.querySelector('.recovery__repeat-pass').value;
        
        const $messageArea = document.querySelector('.text-of-result');

        const url = new URL(document.location);
        const path = url.pathname;
        const str = path.split('/');
        const salt = str[3];

        const newPass = {
            "new-password": $password,
            "repeat-new-password": $repeatPass
        };

        const request = await fetch(`http://www.cloud-storage.local/user/reset-password/${salt}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(newPass)
        });

        const data = await request.json();

        if (data.status) {
            $messageArea.style = 'color: var(--usual-color)';
            $messageArea.textContent = data.message;
            $form.style = 'display: none;';
        } else {
            $messageArea.style = 'color: #FF4040';
            $messageArea.textContent = data.message;
        }
    }

    $form.addEventListener('submit', async function(e) {
        e.preventDefault();

        await setNewPass();    
    })
})();