<div class="tabbable">

	{include file="$path_admin_templates/forms/tabs.tpl"}

		<form id="acms_form" name="edit_record" method="POST" action="/admin{if $content.url}{$content.url}{else}/start{/if}.{$action.form_action}.{$action.structure_sid}.html" enctype="multipart/form-data" class="acms_panel_form form-horizontal" data-rec-id="{$content.id}">
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
				<legend>{$action.title}{if $action.form_action == 'editRecord'}: {$content.title}{/if}</legend>

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
					<button class="btn btn-remove-rec">Удалить запись</button>
			{literal}
				<script>
					$('#acms_form .btn-remove-rec').click(function () {
						if (confirm('Удалить запись безвозвратно?')) {
							var rec_id = $(this).parents('form').data('rec-id');
							$.post(
									'/admin/start.delete.html',
									{
										'module_sid': $(this).parents('form').find("input[name='module']").val(),
										'structure_sid': $(this).parents('form').find("input[name='structure_sid']").val(),
										'record': rec_id
									},
									function (data) {
										history.back();
									}
							);
						}
						return false;
					});

				</script>
			{/literal}
<!--
					<button type="reset" class="btn">Отмена</button>
-->
				</div>
				
			</fieldset>
		</form>

		
</div>


