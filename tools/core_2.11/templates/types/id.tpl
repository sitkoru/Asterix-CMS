<p class="a_field">
	<label for="field_{$field.sid}">{$field.title}: <input type="hidden"{if !$field.editable} id="field_{$field.sid}" {/if} name="{$field.sid}" value="{$field.value}" class="id" />{$field.value}</label>
</p>
