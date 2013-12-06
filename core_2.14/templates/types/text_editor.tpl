	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<textarea id="field_{$field.sid}" class="html_editor_admin" name="{$field.sid}" style="height:400px; width:600px;">{$field.value}</textarea>
			<p class="help-block">
                <label>
                    <input type="checkbox" id="field_{$field.sid}uploader_watermark_notset" value="1"{if 'upload'|in_array:$settings.watermark_where} checked="checked"{/if} OnChange="

                            var value = $('#field_{$field.sid}uploader_watermark_notset').is(':checked');
                            $.post('/admin/start.settings.uploader_watermark_notset.html', { watermark_notset: value } );

                            ">
                    Ставить "Водный знак" на фотографии, загружаемые через визуальный редактор
                </label>
			</p>
		</div>
		<input type="hidden" name="{$field.sid}_meta" id="field_{$field.sid}_meta" value="{$field.meta}" />
	</div>
