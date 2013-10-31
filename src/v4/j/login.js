function acms_actions() {

    // Выход из открытого модального окна
    $('.acms-login__background-cover').click(function () {
        $(this).fadeOut('fast');
        $('body').css('overflow', 'auto');
    });

    $('.acms-login__login_form').click(function (e) {
        e.stopPropagation();
    });

    $('.acms-login__login_button').click(function () {
        var login = $('.acms-login__login_form input[name=login]').val();
        var password = $('.acms-login__login_form input[name=password]').val();
        $.post('/', { login: login, password: password }, function (data) {

            if (data.result == 'message')
                alert(data.message);
            else
                document.location.href = data.url;

        }, 'json');
    });

}

$(document).ready(function () {

    //Показать панель авторизации
    $(document).keyup(function (e) {
        if (e.keyCode == 113 || e.keyCode == 117) {

            // Вставляем форму авторизации в DOM
            $('.acms-login__background-cover').remove();
            $.get('/admin/login.html', function (login_form) {

                $('body').append(login_form).css('overflow', 'hidden');

                $('.acms-content__background-cover').css('height', window.height);

                $('.acms-content__background-cover').fadeIn('fast');

                acms_actions();
            });

        }
    });


});