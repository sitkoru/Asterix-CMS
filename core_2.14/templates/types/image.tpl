	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
		{if $field.value.path}
			<ul class="thumbnails">
				<li>
					<a href="#" class="thumbnail">
						<img src="{$field.value.path}" alt="" style="max-height:200px;">
					</a>
				</li>
			{foreach from=$field.value.pre item=pre key=key}
				<li>
					<a href="#" class="thumbnail">
						{assign var=$val value=$field.value}
						<img src="{$field.value.$key}" alt="" style="max-height:100px;" />
					</a>
				</li>
			{/foreach}
			</ul>
		{/if}
			<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.old|escape}" />
			<input type="file" name="{$field.sid}" name="{$field.sid}_id" />
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
