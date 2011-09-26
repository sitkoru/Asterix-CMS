	<label for="field_{$field.sid}">{$field.title}:</label>
	<div class="image expand-image{if ! $field.value.path} plus-image{/if}">
		<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.id}" />
	{if $field.value.path}
		<a href="{$field.value.path}" class="lightbox out img"
			style="background:url({if $field.value.pre}{$field.value.pre}{elseif $field.value.190}{$field.value.190}{elseif $field.value.75}{$field.value.75}{else}{$field.value.path}{/if}) center no-repeat">
		</a>
	{else}
		<a href="#" class="img"></a>
	{/if}
		<label for="field_{$field.sid}_title">Название:</label>
		<input type="text" name="{$field.sid}_title" id="field_{$field.sid}_title" />
		<label for="field_{$field.sid}_file">Файл:</label>
		<input type="file" name="{$field.sid}" id="field_{$field.sid}_file" />
	{if $field.value.path}
		<label><input type="checkbox" name="{$field.sid}_delete" value="1" /> удалить</label>
	{/if}
	</div>
	<div class="clear"></div>
