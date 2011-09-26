$j = jQuery.noConflict();

function listenInterface() {
    $j('form.ajax').submit(function () {
        var form_id = $j(this).attr('id');
        $j('#' + form_id + ' li').removeClass('err');

		alert($j('#' + form_id).attr('action')+' '+$j('#' + form_id).serialize());

        $j.post($j('#' + form_id).attr('action'), $j('#' + form_id).serialize(), function (data) {
			alert('ie test 2');
            if (data['result'] == 'error') {
                for (var key in data['errors']) {
                    $j('#' + form_id + ' #id_' + key).parent().addClass('err');
                    alert(data['errors'][key]);
                }
            } else if (data['result'] == 'message') {
                alert(data['message']);
            } else if (data['result'] == 'redirect') {
                document.location.href = data['url'];
            }
            if (data['close']) {
                $j('#' + form_id).parent('.interface').hide();
            }
        }, "json");
        return false;
    });
}
$j().ready(function () {
    $j(document).keyup(function (e) {
        if (e.keyCode == 113 || e.keyCode == 117) {
            $j('#admin_hide').css('display', 'block');
        }
    });
    $j('input.cancel').click(function (e) {
        $j(this).parents('div.interface').css('display', 'none');
        return false;
    });
    $j('.out').live('click', function () {
        $j(this).attr('target', '_blank');
        return true;
    });
    $j('.content tbody tr:nth-child(even)').addClass('g');
    $j('.default-value').focus(function () {
        var value = $j(this).val().replace("\r", '');
        var def = $j(this).attr('defaultValue').replace("\r", '');
        if (value == def) $j(this).val('');
    });
    $j('.default-value').blur(function () {
        if (!$j(this).val()) $j(this).val($j(this).attr('defaultValue'));
    });
    listenInterface();
});