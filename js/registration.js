$('.btn-register').click(function (e) {
    e.preventDefault();

    let login = $('input[name="login"]').val,
        email = $('input[name="email"]').val,
        password = $('input[name="password"]').val,
        passwordConf = $('input[name="password_confirm"]').val;

    let formData = new FormData();
    formData.append('login', login);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('password_confirm', passwordConf);

    $.ajax({
        url: 'index.php/user/registration', 
        type: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        cache: false,
        data: formData,
        success(data) {
            if (data.status) {
                document.location.href = './auth.php';
            } else {
                if (data.type === 1) {
                    data.fields.forEach(function (field) {
                        $(`input[name="${field}}"]`).addClass('error');
                    })
                }

                $('.text-of-result').text(data.message);
            }
        }
    })
});