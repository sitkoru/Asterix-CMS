	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
		{if $field.value.path}
			<ul class="thumbnails">
				<li>
					<a href="#" id="{$field.sid}_first" class="thumbnail" style="width:auto; height:auto; min-width:150px; min-height:100px; background-size:contain;">
						<img src="{$field.value.path}" alt="" style="max-width:200px; max-height:100px;">
					</a>
				</li>
			{foreach from=$field.pre item=pre key=key}
				<li>
					<a href="#" class="thumbnail">
						{assign var=$val value=$field.value}
						{if $pre.resize_type == 'inner'}{assign var=size_hint value="Изображение не более $pre.resize_width x $pre.resize_height px"}
						{elseif $pre.resize_type == 'outer'}{assign var=size_hint value="Изображение не менее $pre.resize_width x $pre.resize_height px"}
						{elseif $pre.resize_type == 'width'}{assign var=size_hint value="Изображение шириной $pre.resize_width px"}
						{elseif $pre.resize_type == 'height'}{assign var=size_hint value="Изображение высотой $pre.resize_height px"}
						{elseif $pre.resize_type == 'exec'}{assign var=size_hint value="Изображение с точным размером размером $pre.resize_width x $pre.resize_height px"}
						{/if}
						<img src="{$field.value.$key}" alt="" style="max-height:100px;" title="{$size_hint}" />
					</a>
				</li>
			{/foreach}
			</ul>
		{/if}
			<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.old|escape}" />
			<input type="text" name="{$field.sid}_title" value="{$field.value.title|escape}" placeholder="Альтернативный текст" /><br />
			<input type="file" name="{$field.sid}" id="{$field.sid}_id"{if $field.required} required="required"{/if} OnChange="

function onChangeImagefile( image_field_id, image_id ){
    var imagefile = document.getElementById( image_field_id );
 
    // HTML5 FileAPI: Firefox 3.6+, Chrome 6+
	if(typeof(FileReader)!='undefined'){
		var reader = new FileReader();
		reader.onload = function(e){
			$('#'+image_id).css('background','url(' + e.target.result + ') center center no-repeat');
			$('#'+image_id).find('img').remove()
		}
		reader.readAsDataURL(imagefile.files.item(0));
	}
}

onChangeImagefile( '{$field.sid}_id', '{$field.sid}_first' );

			" />
{if $field.value.path}
			<span style="display:block; font-size:0.8em;">
				загружен файл: <a href="{$field.value.path}" target="_blank">{$field.value.path}</a><br />
				размер: {$field.value.size|number_format:0:",":" "} байт<br />
				тип mime: {$field.value.type}<br />
			</span>
			<label class="checkbox" for="{$field.sid}_delete">удалить файл
				<input type="checkbox" name="{$field.sid}_delete" id="{$field.sid}_delete" value="1" />
			</label>
{/if}
		</div>
	</div>
