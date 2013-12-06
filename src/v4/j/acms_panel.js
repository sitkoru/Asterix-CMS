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

    $(document).on("submit", "form.ajax", function(){
        var form_id=$(this).attr('id');
        $('#'+form_id+' li').removeClass('err');
        $.post(
            $(this).attr('action'),
            $(this).serialize(),
            function(data){
                if(data['result']=='error'){
                    for(var key in data['errors']){
                        $('#'+form_id+' #id_'+key).parent().addClass('err');
                        alert( data['errors'][key]);
                    }
                }else if(data['result']=='action'){
                    eval(data['action'])(data['params']);
                }else if(data['result']=='message'){
                    alert(data['message']);
                }else if(data['result']=='redirect'){
                    document.location.href=data['url'];
                }
                if(data['close']){
                    $('#'+form_id).parent('.interface').hide();
                }
            },
            "json");
        return false;
    });

});