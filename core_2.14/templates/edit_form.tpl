
<div class="acms_submit_out">
	<img class="acms_cancel" src="http://src.sitko.ru/i/error.png" alt="Закрыть без сохранения" />
	<img class="acms_save" src="http://src.sitko.ru/i/save.png" alt="Сохранить" />
</div>

<ul class="acms_tabs">
{assign var=group_key value=0}
{foreach from=$action.groups key=key item=group name=settings_groups}{if $group.fields}
	{assign var=group_key value=$group_key+1}
	<li{if $key == main} class="active"{/if}>
		<a id="acms_tab_{$group_key}" href="#">{$group.title}</a>
	</li>
{/if}{/foreach}
</ul>

<form name="edit_record" method="POST" action="{$content.url}" enctype="multipart/form-data" class="validate acms_panel_form">
	<input type="hidden" name="action" value="{$action.form_action}" />
	<input type="hidden" name="module" value="{$action.module}" />
	<input type="hidden" name="structure_sid" value="{$action.structure_sid}" />

{assign var=group_key value=0}
{foreach from=$action.groups item=group key=key}{if $group.fields}
	{assign var=group_key value=$group_key+1}

	{if $group.comment}
		<div class="acms_panel_groups acms_panel_group_{$group_key}" style="float:left; border-radius:10px; background-color:#eee; padding:10px; margin-bottom:10px;{if $key != main} display:none;{/if}">
			{$group.comment}
		</div>
	{/if}
	{if $group.warning}
		<div class="acms_panel_groups acms_panel_group_{$group_key}" style="float:left; border-radius:10px; color:white !important; background-color:#FC0082; padding:10px; margin-bottom:10px;{if $key != main} display:none;{/if}">
			{$group.warning}
		</div>
	{/if}
	{if $group.help}
		<div class="acms_panel_groups acms_panel_groups_help acms_panel_group_{$group_key}" style="float:right; width:35%; border-radius:10px; color:black !important; background-color:#eee; padding:10px;{if $key != main} display:none;{/if}">
			{$group.help}
		</div>
	{/if}

	<ol class="acms_panel_groups">
	{foreach from=$group.fields item=field}
	{if $field.type == 'hidden'}
		{include file="$path_admin_templates/types/`$field.type`.tpl"}
	{else}
		<li class="acms_panel_group_{$group_key} acms_field_{$field.type}"{if $key != main} style="display:none;"{/if}>
			{include file="$path_admin_templates/types/`$field.type`.tpl"}
		</li>
	{/if}
	{/foreach}
	</ol>

{/if}{/foreach}
	
	<div class="submit">
		<button type="acms_submit">Сохранить</button>
		<button class="acms_cancel">Отменить</button>
	</div>
</form>

