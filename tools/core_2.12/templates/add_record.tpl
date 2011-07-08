<h1>Вы добавляете: <span style="color:green;">{$action.title}</span></h1>

<form name="add_record" method="POST" enctype="multipart/form-data" class="validate">

	<fieldset style="display:none">
		<input type="hidden" name="action" value="add" />
		<input type="hidden" name="module" value="{$action.module}" />
		<input type="hidden" name="structure_sid" value="{$action.structure_sid}" />
	</fieldset>

{foreach from=$action.groups item=field_group key=key}{if $field_group.fields}

	{if $key != 'main' and $key != 'media' and $key != 'show'}
		<ul class="a_fields_other">
			<li>
				<strong>{$field_group.title}</strong> <a id="fieldset_{$key}_switch" href="#" OnClick="JavaStript:$j('#fieldset_{$key}').slideToggle('fast');return false;">{if $field_group.open}свернуть{else}развернуть{/if}</a>
				<div id="fieldset_{$key}" style="display:{if $field_group.open}block{else}none{/if}">
	{elseif $key == 'show'}
		<fieldset class="a_fields_other">
			<strong>Отображение</strong>
	{/if}

	{foreach from=$field_group.fields item=field}
		{include file="$path_admin_templates/`$field.template_file`"}
	{/foreach}

	{if $key != 'main' and $key != 'media' and $key != 'show'}
				</div>
			</li>
		</ul>
	{elseif $key == 'show'}
		</fieldset>
	{/if}

{/if}{/foreach}
	<p class="submit">
		<button type="submit">Добавить</button>
		<button class="cancel">Отменить</button>
	</p>
</form>

