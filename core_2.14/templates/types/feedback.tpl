	<div class="control-group acms_panel_groups acms_panel_group_{$group_key} acms_field_{$field.type}">
		<label class="control-label" for="field_{$field.sid}">Форма обратной связи</label>
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" id="field_{$field.sid}_shw" name="{$field.sid}[shw]" value="1"{if $field.value.shw} checked="checked"{/if} />
				<span class="badge badge-warning">Показать форму на сайте</span>
			</label>
			<label class="checkbox">
				<input type="checkbox" id="field_{$field.sid}_captcha" name="{$field.sid}[captcha]" value="1"{if $field.value.captcha} checked="checked"{/if} />
				Включить защиту от ботов (Captcha)
			</label>
		</div>
	</div>
	<div class="control-group acms_panel_groups acms_panel_group_{$group_key} acms_field_{$field.type}">
		<label class="control-label" for="field_{$field.sid}">Заголовок формы</label>
		<div class="controls">
			<input type="text" class="input-xlarge" id="field_{$field.sid}_title" name="{$field.sid}[title]" value="{$field.value.title}" />
		</div>
	</div>
	<div class="control-group acms_panel_groups acms_panel_group_{$group_key} acms_field_{$field.type}">
		<label class="control-label" for="field_{$field.sid}">Почтовые ящики</label>
		<div class="controls">
			<input type="text" class="input-xlarge" id="field_{$field.sid}[email]" name="{$field.sid}[email]" value="{$field.value.email}" />
			<p class="help-block">Чтобы ввести несколько адресов, укажите их через пробел.</p>
		</div>
	</div>
	<div class="control-group acms_panel_groups acms_panel_group_{$group_key} acms_field_{$field.type}">
		<label class="control-label" for="field_{$field.sid}">Поля в форме</label>
		<div class="controls">
			<div>
				<span style="display:inline-block; margin-left:40px; width:200px;">Тип поля</span>
				<span style="display:inline-block; margin-left:20px; width:200px;">Заголовок поля</span>
				<span style="margin-left:20px;">Обязательное поле</span>
			</div>
			<hr />
			<ul id="field_{$field.sid}_params" class="unstyled sortable">
		{if $field.value.fields}
			{foreach from=$field.value.fields item=feedback_field key=key}
				<li>
					<i class="icon-resize-vertical"></i> 
					<a href="#" class="delete"><i class="icon-trash"></i></a> 
					<select id="field_{$field.sid}_fields_type_{$key}" name="{$field.sid}[fields][type][]"{if $field.required} required="required"{/if}>
						<option value="text"{if $feedback_field.type == 'text'} selected="selected"{/if}>Текстовое поле</option>
						<option value="textarea"{if $feedback_field.type == 'textarea'} selected="selected"{/if}>Многострочный текст</option>
						<option value="check"{if $feedback_field.type == 'check'} selected="selected"{/if}>Галочка да/нет</option>
<!--
						<option value="menu"{if $feedback_field.type == 'menu'} selected="selected"{/if}>Выбор из списка</option>
-->
						<option value="file"{if $feedback_field.type == 'file'} selected="selected"{/if}>Прикрепить файл</option>

					</select>
					<input type="text" id="field_{$field.sid}_fields_title_{$key}" name="{$field.sid}[fields][title][]" placeholder="Заголовок поля" value="{$feedback_field.title}" />
					<input type="checkbox" id="field_{$field.sid}_fields_required_{$key}" name="{$field.sid}[fields][required][]" class="required_field" value="{$feedback_field.required}" title="Обязательное поле" {if $feedback_field.required} checked{/if}>
					<span class="required{if $feedback_field.required} badge badge-warning{/if}" style="padding: 1px 9px 2px;">Обязательное</span>
				</li>
			{/foreach}
		{else}
				<li>
					<i class="icon-resize-vertical"></i> 
					<a href="#" class="delete"><i class="icon-trash"></i></a> 
					<select id="field_{$field.sid}_fields_type_0" name="{$field.sid}[fields][type][]"{if $field.required} required="required"{/if}>
						<option value="text"{if $feedback_field.type == 'text'} selected="selected"{/if}>Текстовое поле</option>
						<option value="textarea"{if $feedback_field.type == 'textarea'} selected="selected"{/if}>Многострочный текст</option>
						<option value="check"{if $feedback_field.type == 'check'} selected="selected"{/if}>Галочка да/нет</option>
<!--
						<option value="menu"{if $feedback_field.type == 'menu'} selected="selected"{/if}>Выбор из списка</option>
						<option value="file"{if $feedback_field.type == 'file'} selected="selected"{/if}>Прикрепить файл</option>
-->
					</select>
					<input type="text" id="field_{$field.sid}_fields_title_0" name="{$field.sid}[fields][title][]" placeholder="Заголовок поля" value="{$feedback_field.title}" />
					<input type="checkbox" id="field_{$field.sid}_fields_required_0" name="{$field.sid}[fields][required][]" class="required_field" value="{$feedback_field.required}" title="Обязательное поле">
					<span class="required{if $feedback_field.required} badge badge-warning{/if}" style="padding: 1px 9px 2px;">Обязательное</span>
				</li>
		{/if}
			</ul>
			<a class="btn add"><i class="icon-plus-sign"></i> Добавить ещё поле</a>
		</div>
	</div>
