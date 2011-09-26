<fieldset class="a_field a_text">
	<label>{$field.title}:</label>
	Доверие: {$field.value.thanks}<br />
	Известность: {$field.value.popular}<br />
	Мобильность: {$field.value.mobile}<br />
	<input type="hidden" name="{$field.sid}[thanks]" value="{$field.value.thanks}" />
	<input type="hidden" name="{$field.sid}[popular]" value="{$field.value.popular}" />
	<input type="hidden" name="{$field.sid}[mobile]" value="{$field.value.mobile}" />
</fieldset>
