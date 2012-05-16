	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			{assign var=type value=$field.value.type}
			{if !$type}
				{assign var=type value='youtube'}
			{/if}
			<select id="select01" name="{$field.sid}[type]" OnChange="
var val = $(this).val();
$(this).parent('.controls').find('.video_field').hide();
$(this).parent('.controls').find('#field_{$field.sid}_'+val).show();
return false;
			">
				<option value="youtube"{if $type == 'youtube'} selected="selected"{/if}>Видео с YouTube</option>
				<option value="vimeo"{if $type == 'vimeo'} selected="selected"{/if}>Видео с Vimeo</option>
				<option value="other"{if $type == 'other'} selected="selected"{/if}>Видео с другого сайта</option>
				<option value="file"{if $type == 'file'} selected="selected"{/if}>Видео закачать файл на сайт</option>
			</select><br />
			<input id="field_{$field.sid}_youtube" class="video_field" name="{$field.sid}[link]" style="{if $type != 'youtube'}display:none;{/if}"{if $field.required} required="required"{/if} value="{$field.value.link|escape:html}" />
			<textarea id="field_{$field.sid}_vimeo" class="video_field" name="{$field.sid}[code]" style="{if $type != 'vimeo'}display:none;{/if}height:200px; display:none;"{if $field.required} required="required"{/if}>{$field.value.code|escape:html}</textarea>
			<textarea id="field_{$field.sid}_other" class="video_field" name="{$field.sid}[code]" style="{if $type != 'other'}display:none;{/if}height:200px; display:none;"{if $field.required} required="required"{/if}>{$field.value.code|escape:html}</textarea>
			<input id="field_{$field.sid}_file" class="video_field" name="{$field.sid}[file]" style="{if $type != 'file'}display:none;{/if}display:none;"{if $field.required} required="required"{/if} value="" />
		</div>
	</div>
