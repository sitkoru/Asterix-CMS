
<div class="acms_submit_out">
	<img class="acms_cancel" src="http://src.sitko.ru/i/error.png" alt="Закрыть без сохранения" />
	<img class="acms_save" src="http://src.sitko.ru/i/save.png" alt="Сохранить" />
</div>

	<ul class="acms_tabs">
	{foreach from=$action.groups key=key item=group name=settings_groups}
	{if $group.title != 'Все'}
		<li{if !$key} class="active"{/if}>
			<a id="acms_tab_{$key}" href="#">{$group.title}</a>
		</li>
	{/if}
	{/foreach}
		<li>
			<a id="acms_tab_all" href="#">Все</a>
		</li>
	</ul>

	<form method="post" enctype="multipart/form-data" action="{$content.url}" class="acms_panel_form">
		<input type="hidden" name="action" value="settings" />
		<input type="hidden" name="domain" value="{$rec.domain}" />

		<ol class="acms_panel_groups">
	
	{foreach from=$action.groups key=key item=group}
		{if $group.title != 'Все'}
		{foreach from=$group.recs item=field}
			<li class="acms_panel_group_{$key} acms_field_{$field.type}"{if $key} style="display:none"{/if}>
			
			{if ($field.type == 'html') or ($field.type == 'robots') or ($field.type == 'text_editor') }
				{include file="$path_admin_templates/types/textarea.tpl"}
			{else}
				{include file="$path_admin_templates/types/`$field.type`.tpl"}
			{/if}
			
			</li>
		{/foreach}
		{/if}
	{/foreach}
	
		</ol>
		<div class="submit">
			<button type="acms_submit">Сохранить</button>
			<button class="acms_cancel">Отменить</button>
		</div>
	</form>

	