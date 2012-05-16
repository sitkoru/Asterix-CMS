	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
<style>{literal}
.wikibar{
	width:100%;
	background-color: #EEE;
	padding: 4px 4px;
	border-radius: 3px 3px 0 0;
	border: 1px solid #CCC;
	border-bottom: 0;
}
.wikibar a, .wikibar i{
	margin-left: 6px;
	cursor: pointer;
	color: black;
	line-height: 20px;
	font-family: Georgia;
	text-decoration: none;
}
.wikibar .bold{font-weight:bold;}
.wikibar .italic{font-style:italic;}
.wikibar .underline{text-decoration:underline;}
.wikibar .strike{text-decoration: line-through;}
.wikibar .link{text-decoration: underline; color: blue;}
.wikiarea{
	width: 100%;
	height: 100px;
	border-top: 0px;
	border-radius: 0 0 3px 3px;
	resize: vertical;
}{/literal}
</style>
			<div class="wikibar" id="field_{$field.sid}_bar" for="field_{$field.sid}">
				<a class="bold">B</a>
				<a class="italic">I</a>
				<a class="underline">U</a>
				<a class="strike">S</a>
				<a class="link">A</a>
			</div>
			<textarea class="wikiarea" id="field_{$field.sid}" name="{$field.sid}">{$field.value}</textarea>
<script>
	$('.wikibar .bold').click(function(){
		addMarker( 'field_{$field.sid}', '**', '**' );
	});
	$('.wikibar .italic').click(function(){
		addMarker( 'field_{$field.sid}', '*', '*' );
	});
	$('.wikibar .underline').click(function(){
		addMarker( 'field_{$field.sid}', '__', '__' );
	});
	$('.wikibar .strike').click(function(){
		addMarker( 'field_{$field.sid}', '--', '--' );
	});
	$('.wikibar .link').click(function(){
		var link = prompt('Укажите адрес ссылки','http://');
		addMarker( 'field_{$field.sid}', '[', '](' + link + ')' );
	});
	
	function addMarker(id, mark_pre, mark_post){
		var textArea = $('#'+id);
		var len = textArea.val().length;
		var start = textArea[0].selectionStart;
		var end = textArea[0].selectionEnd;
		var selectedText = textArea.val().substring(start, end);
		if( selectedText.length > 0 ){
			replacement = mark_pre + selectedText + mark_post;
			textArea.val( textArea.val().substring(0, start) + replacement + textArea.val().substring(end, len));
		}
	}
</script>
		</div>
	</div>
