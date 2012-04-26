<div class="tree">
{if count($action.recs)}
{preload prototype=users data=modulepath result=path}
<a href="#" OnClick="call_admin_interface('GET','add','/add{$path}/rec.html'); return false;">Добавить нового пользователя</a><br />
<ol class="acms_panel_groups">
{foreach from=$action.recs item=rec}
	<li>
		{if $rec.admin eq 1}<img src="http://src.sitko.ru/a/i/admin.png" alt="Администратор сайта" title="Администратор сайта" /> 
		{elseif $rec.moder eq 1}<img src="http://src.sitko.ru/a/i/user.png" alt="Модератор сайта" title="Модератор сайта" />
		{else}<img src="http://src.sitko.ru/a/i/user.png" alt="Зарегистрированный пользователь" title="Зарегистрированный пользователь" /> {/if}
		{if $rec.openid eq 1}<img src="http://src.sitko.ru/a/i/openid.png" alt="Зарегистрирован через OpenID" title="Зарегистрирован через OpenID" /> {/if}
		{if $rec.ip_protected eq 1}<img src="http://src.sitko.ru/a/i/home.gif" alt="Защита по внутреннему IP-адресу" title="Защита по внутреннему IP-адресу" /> {/if}
		{if $rec.active neq 1}<img src="http://src.sitko.ru/a/i/block.png" alt="Пользователь заблокирован" title="Пользователь заблокирован" /> {/if}
		{$rec.title}
		{if $rec.domain neq 'all'} - 
			<a href="#" OnClick="call_admin_interface('GET','edit','{$rec.url}.html'); return false;">изменить</a> или 
			<a href="#" OnClick="call_admin_interface('GET','delete','{$rec.url}.html'); return false;">удалить</a>.
		{else} - администратор Ситко.ру.{/if}
	</li>
{/foreach}
</ol>
{/if}
</div>
