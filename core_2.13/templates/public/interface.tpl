			<h1 id="title">{$form.title}</h1>
            {if $form.comment}<p style="font-size:1.4em; color:#FF631B;">{$form.comment}</p>{/if}
			<form method="post" enctype="multipart/form-data" id="interface_{$form.interface}" class="type-form reg-form interface{if $form.ajax} ajax{/if}" action="{$form.action}">
				<input type="hidden" name="interface" value="{$form.interface}" />
{foreach from=$form.fields item=rec}
	{if $rec.type eq 'id'}
				<input type="hidden" name="{$rec.sid}" value="{$rec.value}" />
			{elseif $rec.type eq 'hidden'}
				<input type="hidden" name="{$rec.sid}" value="{$rec.value}" />
	{else}
		{assign var=sid value=$rec.sid}
		{assign var=err value=$msg.$sid}
		{assign var=val value=$mem.$sid}
		{if isset($rec.template)}
				{include file=$rec.template}
		{elseif $rec.type eq 'text'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light invis big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<input type="text" name="{$rec.sid}" id="id_{$rec.sid}" value="{$rec.value}" class="{if $rec.required} required{/if}" /> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>					
		{elseif $rec.type eq 'link'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<select name="{$rec.sid}" id="id_{$rec.sid}"> 
							{foreach from=$rec.value item=value}
								<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.title}</option>
							{/foreach}
							</select> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'linkm'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<select name="{$rec.sid}" id="id_{$rec.sid}" multiple="multiple" size="6"> 
							{foreach from=$rec.value item=value}
								<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.title}</option>
							{/foreach}
							</select> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'datetime'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<input type="text" name="{$rec.sid}" id="id_{$rec.sid}" value="{$rec.value.date}" class="{if $rec.required} required{/if}" /> 
						<b class="lb"></b></div></div>	
						<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
						<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

						<script>
							$j(document).ready(function() {literal}{{/literal}
								$j("#id_{$rec.sid}").datepicker({literal}{{/literal} dateFormat: 'yy-mm-dd' {literal}}{/literal});
							{literal}}{/literal});
						</script>
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'menu'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<select name="{$rec.sid}" id="id_{$rec.sid}"> 
							{foreach from=$rec.value item=value}
								<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.value}</option>
							{/foreach}
							</select> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'menum'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<select name="{$rec.sid}[]" id="id_{$rec.sid}" multiple="multiple" size="6"> 
							{foreach from=$rec.value item=value}
								<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.value}</option>
							{/foreach}
							</select> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'domain'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<select name="{$rec.sid}[]" id="id_{$rec.sid}" multiple="multiple" size="6"> 
							{foreach from=$rec.value item=value}
								<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.title}</option>
							{/foreach}
							</select> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'user'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<select name="{$rec.sid}" id="id_{$rec.sid}"> 
							{foreach from=$rec.value item=value}
								<option value="{$value.id}"{if $value.selected} selected="selected"{/if}>{$value.title}</option>
							{/foreach}
							</select> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>				
		{elseif $rec.type eq 'image'}
				<fieldset> 
				{if $rec.value.path}
					<img src="{$rec.value.path}" alt="" style="max-width:250px; max-height:250px;"/>
				{/if}
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-3 light invis big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<input type="hidden" name="{$rec.sid}_old_id" value="{$rec.value.id}" />
							<input type="file" name="{$rec.sid}" id="id_{$rec.sid}" value="{$rec.value}" class="default-value{if $rec.required} required{/if}" /> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>					
		{elseif $rec.type eq 'file'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field-3 light invis big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<input type="hidden" name="{$rec.sid}_old_id" value="{$rec.value.id}" />
							<input type="file" name="{$rec.sid}" id="id_{$rec.sid}" value="{$rec.value}" class="default-value{if $rec.required} required{/if}" /> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>					
		{elseif $rec.type eq 'password'}
				<fieldset> 
					<label for="id_{$rec.sid}" class="half">{$rec.title}</label> 
					<label for="id_{$rec.sid}_copy" class="half">Повторите ещё разочек</label> 
					<div class="d d2">{if $rec.help}{$rec.help}{/if}</div> 
					<div class="field-3 light invis big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<input type="password" name="{$rec.sid}" id="id_{$rec.sid}"{if $rec.required} class="required"{/if} autocomplete="off" /> 
						<b class="lb"></b></div></div>	
					</div>						
					<div class="field-4 light invis big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<input type="password" name="{$rec.sid}_copy" id="id_{$rec.sid}_copy"{if $rec.required} class="required"{/if} autocomplete="off" /> 
						<b class="lb"></b></div></div>	
					</div>											
				</fieldset>	
		{elseif $rec.type eq 'textarea'}
				<fieldset> 
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field dark invis"> 
						<div class="wrap"><div><b class="rt"></b> 
							<textarea{if $rec.required} class="required"{/if} name="{$rec.sid}" id="id_{$rec.sid}" style="height:300px" cols="50" rows="8">{$rec.value}</textarea> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset> 
		{elseif $rec.type eq 'text_editor'}
				<fieldset> 
					<script src="http://src.sitko.ru/a/ckeditor/ckeditor.js" type="text/javascript"></script>
					<script src="http://src.sitko.ru/a/ckeditor/adapters/jquery.js" type="text/javascript"></script>
					<label for="id_{$rec.sid}">{$rec.title}</label> 
					<div class="field dark invis" style="width:100%"> 
						<div class="wrap"><div><b class="rt"></b> 
							<textarea class="html_editor{if $rec.required} required{/if}" name="{$rec.sid}" id="id_{$rec.sid}" cols="50" rows="8">{$rec.value}</textarea> 
						<b class="lb"></b></div></div>	
					</div>
					<script>
						init_user_ckeditor();
					</script>
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset> 
		{elseif $rec.type eq 'check'}
				<fieldset style="margin-bottom:15px;"> 
					{$rec.title}<br />
					<span class="radio"><input type="radio" name="{$rec.sid}" value="1" id="id_{$rec.sid}_y"{if $rec.value==1} checked="checked"{/if} /></span> <label class="inl" for="id_{$rec.sid}_y">Да</label> 
					<span class="radio"><input type="radio" name="{$rec.sid}" value="0" id="id_{$rec.sid}_n"{if $rec.value==0} checked="checked"{/if} /></span> <label class="inl" for="id_{$rec.sid}_n">Нет</label> 
				</fieldset>
		{/if}
		{if $err}
								<span class="err msg">{$err}</span>
		{/if}
						</li>
	{/if}
{/foreach}
					</ul>
{if $form.protection == 'captcha'}
				<fieldset> 
					<label for="captcha">Введите защитный код с картинки</label> 
					<div class="field-2 light big"> 
						<div class="wrap"><div><b class="rt"></b> 
							<img src="{$form.captcha}" alt="" />
							<input type="text" name="captcha" id="captcha" class="required" /> 
						<b class="lb"></b></div></div>	
					</div>						
					{if $rec.help}<div class="d">{$rec.help}</div>{/if}
				</fieldset>					
{/if}
					<p class="submit">
						<input type="submit" value="Отправить" />
						<input type="button" class="cancel" value="Отмена" />
					</p>
				</fieldset>
			</form>
