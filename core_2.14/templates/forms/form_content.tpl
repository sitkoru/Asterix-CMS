<div class="tabbable">

	{include file="$path_admin_templates/forms/tabs.tpl"}


		<form name="edit_record" method="POST" action="/admin{if $content.url}{$content.url}{else}/start{/if}.{$action.form_action}.{$action.structure_sid}.html" enctype="multipart/form-data" class="acms_panel_form form-horizontal">
			<input type="hidden" name="action" value="{$action.form_action}" />
			<input type="hidden" name="module" value="{$action.module}" />
			<input type="hidden" name="structure_sid" value="{$action.structure_sid}" />
		{foreach from=$action.groups item=group key=key}
			{foreach from=$group.fields item=field}
				{if $field.type == 'hidden'}
					{if isset( $field.template_userfile ) }
						[{$field.template_userfile}]
					{else}
						{include file="$path_admin_templates/`$field.template_file`"}
					{/if}
				{/if}
			{/foreach}
		{/foreach}

			<fieldset>
				<legend>{$action.title}{if $action.form_action == edit}: {$content.title}{/if}</legend>

		<div class="tab-content">
			{assign var=group_key value=0}
			{foreach from=$action.groups item=group key=key}{if $group.fields}
				{assign var=group_key value=$group_key+1}

				<div class="tab-pane{if $group_key == 1} active{/if}" id="{$group_key}">

				{foreach from=$group.fields item=field}
				{if $field.type != 'hidden'}
					{if isset( $field.template_userfile ) }
						[{$field.template_userfile}]
					{else}
						{include file="$path_admin_templates/`$field.template_file`"}
					{/if}
				{/if}
				{/foreach}
				
				</div>
			{/if}{/foreach}
		</div>

				<div class="form-actions">
					<button type="submit" class="btn btn-large btn-primary">{if $action.button_title}{$action.button_title}{else}Сохранить изменения{/if}</button>&nbsp;
					<button type="reset" class="btn">Отмена</button>
				</div>
				
			</fieldset>
		</form>

		
</div>


