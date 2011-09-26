<p class="a_field">
	<label for="field_{$field.sid}_type">Укажите тип формы:</label>
	<select name="{$field.sid}[type]" id="field_{$field.sid}_type">
		<option value="feedback"{if $field.value.protection eq 'feedback'} selected="selected"{/if}>Форма обратной связи</option>
	<!--	<option value="order"{if $field.value.protection eq 'order'} selected="selected"{/if}>Форма заказа продукции</option>-->
	</select>
</p>

<p class="a_field a_text">
	<label for="field_{$field.sid}_title">Название формы:</label>
	<input type="text" name="{$field.sid}[title]" id="field_{$field.sid}_title" value="{$field.value.title}" maxlength="250" />
</p>

<p class="a_field check">
	<label>Показывать форму:
		<input id="field_{$field.sid}_shw_yes" type="radio" name="{$field.sid}[shw]" value="1"{if $field.value.shw eq 1} checked="checked"{/if} />
		да
	</label>
	<label>
		<input id="field_{$field.sid}_shw_no" type="radio" name="{$field.sid}[shw]" value="0"{if $field.value.shw eq 0} checked="checked"{/if} />
		нет
	</label>
</p>

<p class="a_field">
	<label for="field_{$field.sid}_protection">Способ защиты от спама:</label>
	<select name="{$field.sid}[protection]" id="field_{$field.sid}_protection">
		<option value=""{if !$field.value.protection} selected="selected"{/if}>Защита отключена</option>
		<option value="captcha"{if $field.value.protection eq 'captcha'} selected="selected"{/if}>Captcha</option>
	</select>
</p>

<p class="a_field a_text">
	<label for="field_{$field.sid}_email">Email для уведомлений об отправке:</label>
	<input id="field_{$field.sid}_email" type="text" name="{$field.sid}[email]" value="{$field.value.email}" maxlength="250" />
</p>

<p class="a_field check">
	<label>Рассылать уведомления:
		<input id="field_{$field.sid}_mailto_yes" type="radio" name="{$field.sid}[mailto]" value="1"{if $field.value.mailto eq 1} checked="checked"{/if} />
		да
	</label>
	<label>
		<input id="field_{$field.sid}_mailto_no" type="radio" name="{$field.sid}[mailto]" value="0"{if $field.value.mailto eq 0} checked="checked"{/if} />
		нет
	</label>
</p>

<p class="a_field">
<h5>Поля формы:</h5>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Название поля</th>
				<th>Тип поля</th>
				<th>По умолчанию</th>
				<th>Обязательное поле</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$field.value.fields item=rec key=key}
			<tr>
				<td>{$key+1}.</td>
				<td>
					<input type="text" name="{$field.sid}[fields][{$key}][title]" value="{$rec.title}" style="width:250px" />
				</td>
				<td>
					<select name="{$field.sid}[fields][{$key}][type]">
						<option value="text"{if $rec.type eq 'text'} selected="selected"{/if}>Текстовое поле</option>
						<option value="textarea"{if $rec.type eq 'textarea'} selected="selected"{/if}>Многострочный текст</option>
						<option value="file"{if $rec.type eq 'file'} selected="selected"{/if}>Файл</option>
				<!--		<option value="check"{if $rec.type eq 'check'} selected="selected"{/if}>Галочка</option>-->
					</select>
				</td>
				<td>
					<input type="text" name="{$field.sid}[fields][{$key}][default]" value="{$rec.default}" style="width:150px" />
				</td>
				<td class="a_field check">
					<input id="field_{$field.sid}_{$rec.sid}_{$key}_yes" type="radio" name="{$field.sid}[fields][{$key}][required]" value="1"{if $rec.required eq 1} checked="checked"{/if} /> <label for="field_{$field.sid}_{$rec.sid}_{$key}_yes">да</label>
					<input id="field_{$field.sid}_{$rec.sid}_{$key}_no" type="radio" name="{$field.sid}[fields][{$key}][required]" value="0"{if $rec.required eq 0} checked="checked"{/if} /> <label for="field_{$field.sid}_{$rec.sid}_{$key}_no">нет</label>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</p>

{if $field.value.counter}
<p class="a_field">
	<label>Через форму отправлено сообщений: {$field.value.counter}</label>
</p>
{/if}
