	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">
			{$field.title}
		</label>
		<br />
		<i class="icon-th-large"></i> <i class="icon-list"></i> 
		
		<div class="controls">
			<input type="file" name="{$field.sid}[]" id="field_{$field.sid}" multiple min="1" max="20" />
		{if $field.value}
			<ul class="thumbnails sortable">
			{foreach from=$field.value item=rec key=key}
				<li>
					<a href="#" class="thumbnail" style="width:150px; height:150px; background:url('{$rec.path}') center center no-repeat; background-size:cover; border:4px solid white;">
						<span class="label label-important acms_gallery_delete">Удалить</span>
					</a>
					<input type="hidden" name="{$field.sid}_old_id[{$key}]" value="{$field.value.old|escape}" />
					<input type="text" name="{$field.sid}_title[{$key}]" value="{$rec.title|escape}" style="width:150px;" />
				</li>
			{/foreach}
			</ul>
		{/if}
			<span class="help-block">
				Вы можете выбрать сразу несколько фотографий
			</span>
		</div>
	</div>
