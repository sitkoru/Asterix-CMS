$(document).ready(function () {

    // Выход из открытого модального окна
    $('.acms-content__background-cover').click(function () {
        $(this).fadeOut('fast');
        $('body').css('overflow', 'auto');
    });

    $('a[data-target=acms]').click(function () {

        var url = $(this).attr('href');

        if (url == '#') return false;

        $('.acms-content iframe').attr('src', url);

        $('body').css('overflow', 'hidden')
        $('.acms-content__background-cover').fadeIn('fast');

        return false;
    });

    $('.acms-content').click(function( e ){
        e.stopPropagation();
        return false;
    });

    $('.acms-cross').click(function( e ){
        $('.acms-content__background-cover').fadeOut('fast');
        $('body').css('overflow', 'auto');
    });

});