{foreach from=$field.value.groups item=group key=key}
<p class="a_field">
		<label>
			<select name="{$field.sid}[{$key}]" id="field_{$field.sid}" style="width:300px">
				<option value="---"{if $group.access eq '---'} selected="selected"{/if}>запрещено</option>
				<option value="r--"{if $group.access eq 'r--'} selected="selected"{/if}>чтение</option>
				<option value="rw-"{if $group.access eq 'rw-'} selected="selected"{/if}>чтение и изменение</option>
				<option value="rwd"{if $group.access eq 'rwd'} selected="selected"{/if}>чтение, изменение и удаление</option>
			</select>
		{if $key eq 'admin'}Администраторы
		{elseif $key eq 'moder'}Модераторы
		{elseif $key eq 'all'}Неавторизованные пользователи
		{else}{$key}{/if}
		</label>
</p>
<!--
	<p class="a_field">
		<b>{$group.title}</b>:
		<blockquote style="padding-left:30px;">
			<input type="radio" id="field_{$field.sid}[{$key}][r]_yes" name="{$field.sid}[{$key}][r]" value="r"{if $group.access.r} checked="checked"{/if} />
			<label for="field_{$field.sid}[{$key}][r]_yes">да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" id="field_{$field.sid}[{$key}][r]_no" name="{$field.sid}[{$key}][r]" value="-"{if !$group.access.r} checked="checked"{/if} />
			<label for="field_{$field.sid}[{$key}][r]_no">нет</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			Чтение
			<br />

			<input type="radio" id="field_{$field.sid}[{$key}][w]_yes" name="{$field.sid}[{$key}][w]" value="w"{if $group.access.w} checked="checked"{/if} />
			<label for="field_{$field.sid}[{$key}][w]_yes">да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" id="field_{$field.sid}[{$key}][w]_no" name="{$field.sid}[{$key}][w]" value="-"{if !$group.access.w} checked="checked"{/if} />
			<label for="field_{$field.sid}[{$key}][w]_no">нет</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			Добавление/изменение
			<br />

			<input type="radio" id="field_{$field.sid}[{$key}][d]_yes" name="{$field.sid}[{$key}][d]" value="d"{if $group.access.d} checked="checked"{/if} />
			<label for="field_{$field.sid}[{$key}][d]_yes">да</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" id="field_{$field.sid}[{$key}][d]_no" name="{$field.sid}[{$key}][d]" value="-"{if !$group.access.d} checked="checked"{/if} />
			<label for="field_{$field.sid}[{$key}][d]_no">нет</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			Удаление
			<br />
		</blockquote>
	</p>
-->
{/foreach}
