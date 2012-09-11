	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">
			{$field.title}
		</label>
		<label class="control-label" style="clear: both;">
			<a class="icon-th-large" 	OnClick="
	$('#{$field.sid}_list').removeClass('acms_field_gallery_as_list')
	$('#{$field.sid}_list a').addClass('thumbnail');
			"></a> 
			<a class="icon-list" 		OnClick="
	$('#{$field.sid}_list').addClass('acms_field_gallery_as_list');
	$('#{$field.sid}_list a').removeClass('thumbnail');
			"></a> 
		</label>
		
		<div class="controls">
			<input type="file" name="{$field.sid}[]" id="field_{$field.sid}" multiple min="1" max="20" />
		{if $field.value}
			<ul class="thumbnails sortable acms_field_gallery" id="{$field.sid}_list">
			{foreach from=$field.value item=rec key=key}
				<li>
					<a href="#" class="thumbnail" style="background:url('{$rec.path}') center center no-repeat;">
						<span class="label label-important acms_gallery_delete">Удалить</span>
					</a>
					<input type="hidden" name="{$field.sid}_old_id[{$key}]" value="{$field.value.old|escape}" />
					<textarea name="{$field.sid}_title[{$key}]" />{$rec.title|escape}</textarea>
				</li>
			{/foreach}
			</ul>
		{/if}
			<span class="help-block">
				Вы можете выбрать сразу несколько фотографий
			</span>
		</div>
	</div>
