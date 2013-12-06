	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">
			{$field.title}
        {if $field.value.watermark_set}
            <br />
            <span class="label label-warning" title="На картинку будет установлен водный знак.">+ водный знак</span>
        {/if}
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
					<textarea name="{$field.sid}_title[{$key}]" placeholder="Описание картинки" />{$rec.title|escape}</textarea>
				</li>
            {/foreach}
			</ul>
		{/if}
        {if $settings.watermark_on}
            <label class="checkbox" for="{$field.sid}_watermark_notset">не ставить "Водяной знак"
                <input type="checkbox" name="{$field.sid}_watermark_notset" id="{$field.sid}_watermark_notset" value="1" />
            </label>
        {/if}
            <span class="help-block">
				Вы можете выбрать сразу несколько фотографий
			</span>
		</div>
	</div>
