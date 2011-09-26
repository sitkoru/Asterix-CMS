{foreach from=$field.value.groups item=group key=key}
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
{/foreach}
