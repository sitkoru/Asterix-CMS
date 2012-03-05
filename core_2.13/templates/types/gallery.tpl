	<label for="field_{$field.sid}">{$field.title}:</label>
	{assign var=field_key value=-1}

	<ol class="images sortable">
		<li class="new">
			<span class="delete" OnClick="
				$j(this).parent('li').remove();
			">x</span>
			<label for="field_{$field.sid}__title">Название изображения (Alt)</label>
			<input type="text" name="{$field.sid}_title[-1]" id="field_{$field.sid}__title" />
			<label for="field_{$field.sid}__file">Добавить изображение</label>
			<input type="file" name="{$field.sid}[-1]" id="field_{$field.sid}__file" />
		</li>
	{if $field.value}
	{foreach from=$field.value item=rec key=key}
		<li style="background:url({$rec.path}) center center">
			<input type="hidden" name="{$field.sid}_old_id[{$key}]" value="{$rec.path}" />
			<input type="hidden" name="{$field.sid}_delete[{$key}]" id="field_{$field.sid}_{$key}_delete" value="0" />
			<span class="delete" OnClick="
				$j('#field_{$field.sid}_{$key}_delete').val(1);
				$j(this).parent('li').hide('fast');
			">x</span>
			<label for="field_{$field.sid}__title">Название изображения (Alt)</label>
			<input type="text" name="{$field.sid}_title[{$key}]" id="field_{$field.sid}_{$key}_title" value="{$rec.title}" />
		</li>
	{/foreach}
	{/if}
	</ol>
	
