	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<label>
				<input id="field_{$field.sid}_yes" type="radio" name="{$field.sid}" value="1"{if $field.value eq 1} checked="checked"{/if} />
				<span>да</span>
			</label>
			<label>
				<input id="field_{$field.sid}_no" type="radio" name="{$field.sid}" value="0"{if $field.value eq 0} checked="checked"{/if} />
				<span>нет</span>
			</label>
		</div>
	</div>
