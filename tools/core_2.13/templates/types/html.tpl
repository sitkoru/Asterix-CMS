	<label for="field_{$field.sid}">{$field.title}:</label>
	<textarea id="field_{$field.sid}" name="{$field.sid}" style="width:100%; height:400px;">{$field.value|escape:html}</textarea>
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
