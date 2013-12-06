$(document).ready(function () {

    // Открытие окна помощи
    $('.acms-dev-js__help').click(function () {

        var sid = $(this).attr('data-help');
        var dir = $(this).attr('data-dir');
        var title = $(this).text();

        $('#myModal').modal('show');
        $('.acms-dev-js__modal-title').text( title );
        $('.acms-dev-js__modal-content').load('/dev.help/'+dir+'?sid='+sid);

        $('#myModal').unbind('shown.bs.modal').on('shown.bs.modal', function(){

            $('.editor_modal').each(function(){

                var id = $(this).attr('id');
                var editor = CodeMirror.fromTextArea(document.getElementById( id ), {
                    lineNumbers: true,
                    mode: "text/html",
                    readOnly: true,
                    matchBrackets: true
                });

            });

        });


        return false;
    });

});