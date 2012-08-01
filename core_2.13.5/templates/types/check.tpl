
<label for="field_{$field.sid}">{$field.title}:</label> 

<input id="field_{$field.sid}_yes" type="radio" name="{$field.sid}" value="1"{if $field.value eq 1} checked="checked"{/if} />
<label for="field_{$field.sid}_yes" style="display:inline;"> да</label>

<input id="field_{$field.sid}_no" type="radio" name="{$field.sid}" value="0"{if $field.value eq 0} checked="checked"{/if} />
<label for="field_{$field.sid}_no" style="display:inline;"> нет</label>
