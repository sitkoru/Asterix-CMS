	<label for="field_{$field.sid}">{$field.title}:</label>
	<div class="image expand-image{if ! $field.value.path} plus-image{/if}">
		
		<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.path}" />
	{if $field.value.path}
		<a href="{$field.value.path}" class="lightbox out img"
			style="background:url({if $field.value.pre}{$field.value.pre}{elseif $field.value.190}{$field.value.190}{elseif $field.value.75}{$field.value.75}{else}{$field.value.path}{/if}) center no-repeat">
		</a>
	{else}
		<a href="#" class="img"></a>
	{/if}
		<label for="field_{$field.sid}_title">Название:</label>
		<input type="text" name="{$field.sid}_title" id="field_{$field.sid}_title" />
		<label for="field_{$field.sid}_file">Файл:</label>
		<input type="file" name="{$field.sid}" id="field_{$field.sid}_file" />
	{if $field.value.path}
		<label><input type="checkbox" name="{$field.sid}_delete" value="1" /> удалить</label>
	{/if}
	
		<div class="acms_sub_tabs">
			<a href="#" OnClick="$j('#field_{$field.sid}_detail').toggle('fast'); $j(this).toggleClass('marked')">подробно</a>
			<a href="#" OnClick="$j('#field_{$field.sid}_filters').toggle('fast'); $j(this).toggleClass('marked')">фильтры</a>
			<a href="#" OnClick="$j('#field_{$field.sid}_watermark').toggle('fast'); $j(this).toggleClass('marked')">водный знак</a>
			<a href="#" OnClick="$j('#field_{$field.sid}_cut').toggle('fast'); $j(this).toggleClass('marked')">обрезать по маске</a>
		</div>
		
		<div class="acms_field_tabs" id="field_{$field.sid}_detail">
			Размеры <a href="{$field.value.path}" target="_blank">основной</a> картинки - 
				{if $field.resize_type == 'inner'}вписан в область {$field.resize_width|intval} x {$field.resize_height|intval} {numeric value=$field.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $field.resize_type == 'outer'}описан вокруг области {$field.resize_width|intval} x {$field.resize_height|intval} {numeric value=$field.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $field.resize_type == 'width'}подогнан по ширине {$field.resize_width|intval} {numeric value=$field.resize_width|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $field.resize_type == 'height'}подогнан по высоте {$field.resize_height|intval} {numeric value=$field.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $field.resize_type == 'exec'}уменьшен до размеров {$field.resize_width|intval} x {$field.resize_height|intval} {numeric value=$field.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{/if}
			{foreach from=$field.pre item=pre key=key}
			<br />&nbsp;&nbsp;&nbsp;&nbsp;копия <a href="{$field.value.$key}" target="_blank">{$key}</a> - 
				{if $pre.resize_type == 'inner'}вписан в область {$pre.resize_width|intval} x {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $pre.resize_type == 'outer'}описан вокруг области {$pre.resize_width|intval} x {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $pre.resize_type == 'width'}подогнан по ширине {$pre.resize_width|intval} {numeric value=$pre.resize_width|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $pre.resize_type == 'height'}подогнан по высоте {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{elseif $pre.resize_type == 'exec'}уменьшен до размеров {$pre.resize_width|intval} x {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
				{/if}
			{/foreach}
		</div>
		<div class="acms_field_tabs" id="field_{$field.sid}_filters">
			<label>Сделать картинки чёрно-белыми:</label>
			{foreach from=$field.pre item=pre key=key}
				<br />
				<input type="checkbox" name="{$field.sid}_filter[bw][]" id="field_{$field.sid}_filter_bw_{$key}" value="{$key}" /> - 
				<label for="field_{$field.sid}_filter_bw_{$key}" style="display:inline-block;">
					копия <a href="{$field.value.$key}" target="_blank">{$key}</a> - 
					{if $pre.resize_type == 'inner'}вписан в область {$pre.resize_width|intval} x {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
					{elseif $pre.resize_type == 'outer'}описан вокруг области {$pre.resize_width|intval} x {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
					{elseif $pre.resize_type == 'width'}подогнан по ширине {$pre.resize_width|intval} {numeric value=$pre.resize_width|intval form1='пиксель' form2='пикселя' form5='пикселей'}
					{elseif $pre.resize_type == 'height'}подогнан по высоте {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
					{elseif $pre.resize_type == 'exec'}уменьшен до размеров {$pre.resize_width|intval} x {$pre.resize_height|intval} {numeric value=$pre.resize_height|intval form1='пиксель' form2='пикселя' form5='пикселей'}
					{/if}
				</label>
			{/foreach}
		</div>
		<div class="acms_field_tabs" id="field_{$field.sid}_watermark">
			<label for="field_{$field.sid}_watermark_side">Где отображать водный знак:</label>
			<select id="field_{$field.sid}_watermark_side" name="{$field.sid}_watermark_side">
				<option value="lt">В левом верхнем углу</option>
				<option value="lb">В левом нижнем углу</option>
				<option value="rl">В правом верхнем углу</option>
				<option value="rb" selected="selected">В правом нижнем углу</option>
				<option value="cc">В центре изображения</option>
			</select>
			<label for="field_{$field.sid}_watermark">Файл (PNG):</label>
			<input type="file" name="{$field.sid}_watermark" id="field_{$field.sid}_watermark" />
		</div>
		<div class="acms_field_tabs" id="field_{$field.sid}_cut">
			<label for="field_{$field.sid}_cut_mask">Маска для обрезки файла (PNG с прозрачностью):</label>
			<input type="file" name="{$field.sid}_cut_mask" id="field_{$field.sid}_cut_mask" />
		</div>
	
	</div>
	<div class="clear"></div>
