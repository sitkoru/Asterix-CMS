<h1>Вы редактируете: <span style="color:green;">{$action.title}</span></h1>

<form name="edit_record" method="POST" action="{$content.url}" enctype="multipart/form-data" class="validate">
	<fieldset style="display:none">
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="module" value="{$action.module}" />
		<input type="hidden" name="structure_sid" value="{$action.structure_sid}" />
	</fieldset>

{foreach from=$action.groups item=field_group key=key}{if $field_group.fields}

	{if $key != 'main' and $key != 'media' and $key != 'show'}
		<ul class="a_fields_other">
			<li>
				<h6 class="toggle"><a name="{$field_group.title}">{$field_group.title}</a><span{if $field_group.open} class="up"{/if}></span></h6>
				<div id="fieldset_{$key}" style="display:{if $field_group.open}block{else}none{/if}">
	{elseif $key == 'show'}
		<fieldset class="a_fields_other">
			<h6>Отображение</h6>
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
		<button type="submit">Сохранить</button>
		<button class="cancel">Отменить</button>
	</p>
</form>

