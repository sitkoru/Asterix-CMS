<div class="tree">
	<br /><h3>История последних действий на сайте:</h3>
	<p>
	<ol>
{foreach from=$action.recs item=rec}
		<li>{$rec.date}: <a href="#">{$rec.user.title}</a> - {$rec.action} <a href="{$rec.url}.html">{$rec.title}</a>, {if $rec.success}успешно{else}<strong>с ошибкой</strong>{/if}
			{if $rec.type neq 'add'} - <a href="#" OnClick="call_admin_interface('POST','bkp','{$rec.url}.html'); return false;">восстановить копию</a>{/if}.
		</li>
{/foreach}
	</ol>
	</p>
</div>
