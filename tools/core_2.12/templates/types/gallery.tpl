<fieldset class="a_field a_gallery">
	<h6>{$field.title}:</h6>
{assign var=field_key value=-1}
{foreach from=$field.value item=img}
	{if $img.path}
	{assign var=field_key value=$field_key+1}
	<div class="image">
		<div>
			<input type="hidden" name="{$field.sid}_old_id[{$field_key}]" value="{$img.id}" />
			<a href="{$img.path}" class="out img lightbox"
				style="background:#fff url('{if $img.190}{$img.190}{elseif $img.pre}{$img.pre}{elseif $img.75}{$img.75}{else}{$img.path}{/if}') center no-repeat"
				title="{$img.title}">
			</a>
			<p>
				<label for="{$field.sid}_title_{$field_key}">Название:</label>
				<input type="text" name="{$field.sid}_title[{$field_key}]" id="{$field.sid}_title_{$field_key}" value="{$img.title}" />
				<label for="{$field.sid}_{$field_key}">Файл:</label>
				<input type="hidden" name="{$field.sid}[{$field_key}]" class="file" id="{$field.sid}_{$field_key}" />
				<label><input type="checkbox" name="{$field.sid}_delete[{$field_key}]" value="1" /> удалить</label>
			</p>
		</div>
	</div>
	{/if}
{/foreach}
	<div class="clear"></div>
	<div class="image expand-image plus-image">
			<input type="hidden" name="{$field.sid}_old_id[{$field_key+1}]" value="0" />
			<a href="#" class="img"></a>
			<label>Название:</label>
			<input type="text" name="{$field.sid}_title[{$field_key+1}]" />
			<label>Файл:</label>
			<input type="file" name="{$field.sid}[{$field_key+1}]" />
	</div>
	<div class="clear"></div>
</fieldset>
