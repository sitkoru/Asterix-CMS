<p class="a_field check">
	<label for="field_{$field.sid}_yes">{$field.title}: <input id="field_{$field.sid}_yes" type="radio" name="{$field.sid}" value="1"{if $field.value eq 1} checked="checked"{/if} /> да</label>
	<label for="field_{$field.sid}_no"><input id="field_{$field.sid}_no" type="radio" name="{$field.sid}" value="0"{if $field.value eq 0} checked="checked"{/if} /> нет</label>
</p>
