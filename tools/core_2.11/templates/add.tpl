<div class="tree">
{if count($action.recs)}
<h3>Основные записи на сайте</h3>
<ol>
{foreach from=$action.recs item=rec}	
	<li>
		Ещё {$rec.structure} в модуль "{$rec.title}"
		<a href="#" OnClick="call_admin_interface('GET','add','/add/{$rec.module}/{$rec.structure_sid}.html'); return false;">добавить</a>
	</li>{/foreach}
</ol>
{/if}
</div>

<div class="tree">
{if count($action.subs)}
<h3>Также вы можете</h3>
<ol>
{foreach from=$action.subs item=rec}	
	<li>
		Ещё {$rec.structure} в модуль "{$rec.title}"
		<a href="#" OnClick="call_admin_interface('GET','add','/add/{$rec.module}/{$rec.structure_sid}.html'); return false;">добавить</a>
	</li>{/foreach}
</ol>
{/if}
</div>