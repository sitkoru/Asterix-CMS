<div class="tree">
{if count($action.recs)}
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="catalog" />
<input type="hidden" name="domain" value="{$domain.id}" />
<ol>
{foreach from=$action.recs item=field key=key1}
	<li style="padding:5px;">
		{if $field.type eq 'text'}
			<input type="text" name="{$field.var}" style="width:400px;" value="{$field.value}" />
		{elseif $field.type eq 'textarea'}
			<textarea name="{$field.var}" style="width:400px; height:200px;">{$field.value}</textarea>
		{elseif $field.type eq 'text_editor'}
			<textarea name="{$field.var}" class="html_editor" style="width:100%; height:400px;">{$field.value}</textarea>
		{elseif $field.type eq 'check'}
			{include file="`$paths.admin_templates`/types/check.tpl"}
		{elseif $field.type eq 'file'}
			{include file="`$paths.admin_templates`/types/file.tpl"}
		{elseif $field.type eq 'datetime'}
			{include file="`$paths.admin_templates`/types/datetime.tpl"}
		{else}
			<textarea name="{$field.var}" style="width:400px; height:200px;">{$field.value}</textarea>
		{/if}
	</li>{/foreach}
</ol>
<input type="submit" value="Сохранить" />
</form>


<br /><br />
<h2>История заказов</h2>
<ol>
{foreach from=$action.orders item=order key=key}
	<li>
		<b>{$order.title}</b> от {$order.date_public} на сумму <b>{$order.summ2} руб.</b>, Посетитель: <b>{$order.name}</b>, email: <b><a href="mailto:{$order.email}">{$order.email}</a></b>, телефон: <b>{$order.phone}</b>, комментарий: <b>{$order.diff}</b>
		{if count($order.items) gt 0}
			<ol>
			{foreach from=$order.items item=item}
				<li><a href="{$item.url_parent}" target="_blank">{$item.title}</a> - {$item.counter} {$item.value} по {$item.price} руб., на общую сумму {math equation="price*counter" price=`$item.price*1` counter=`$item.counter`} руб., наименования: {$item.items}</li>
			{/foreach}
			</ol>
		{/if}
	</li>
{/foreach}
</ol>

{/if}
</div>
