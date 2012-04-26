<div class="acms_panel_delete">

	<p>Вы собираетесь удалить запись:</p>
	<h1>{$action.record.title}</h1>
	<p>Вы уверены?</p>
	<a href="#" OnClick="call_admin_interface('POST','delete','{$content.url}'); return false;">удалить</a> 
	<a href="#" class="acms_cancel" OnClick="document.getElementById('bar').style.display='none'; return false;">отмена</a>

</div>