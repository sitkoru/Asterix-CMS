<fieldset class="a_field a_text">
	<label>{$field.title}:</label>
	Голосов ЗА: {$field.value.yes}<br />
	Голосов ПРОТИВ: {$field.value.no}<br />
	Всего голосов: {$field.value.total}<br />
	Средний балл: {$field.value.mark}<br />
	<input type="hidden" name="{$field.sid}[yes]" value="{$field.value.yes}" />
	<input type="hidden" name="{$field.sid}[no]" value="{$field.value.no}" />
	<input type="hidden" name="{$field.sid}[total]" value="{$field.value.total}" />
	<input type="hidden" name="{$field.sid}[mark]" value="{$field.value.mark}" />
</fieldset>
