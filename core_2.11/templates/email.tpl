<div class="tree">
<a href="#" OnClick="call_admin_interface('GET','add','/add/email/rec.html'); return false;">Добавить ещё почтовый ящик</a><br />
{if count($action.recs)}
<ol>
{foreach from=$action.recs item=rec}
	<li>
		{$rec.username} [{$rec.name}]
		{if 0}{if $rec.domain neq 'all'} - 
			<a href="#" OnClick="call_admin_interface('GET','edit','/email/{$rec.sid}.html'); return false;">изменить</a> или 
			<a href="#" OnClick="call_admin_interface('GET','delete','/email/{$rec.sid}.html'); return false;">удалить</a>.
		{else} - администратор Ситко.ру.{/if}{/if}
	</li>
{/foreach}
</ol>
{else}
<ol><li>Почтовые аккаунты не созданы для домена <a href="http://{$domain.host}/">http://{$domain.host}/</a></li></ol>
{/if}
</div>
