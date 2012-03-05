	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<textarea id="field_{$field.sid}" class="" name="{$field.sid}" style="height:200px;">{$field.value}</textarea>
			<p class="help-block">In addition to freeform text, any HTML5 text-based input appears like so.</p>
		</div>
	</div>
	<script>
		editAreaLoader.init({literal}{{/literal}
			id: 'field_{$field.sid}'	// id of the textarea to transform		
			,start_highlight: true	// if start with highlight
			,allow_resize: 'both'
			,allow_toggle: true
			,word_wrap: false
			,language: 'ru'
			,syntax: 'html'	
			,min_height: 400
		{literal}}{/literal});
	</script>
