<div class="tree">
	<ul>
		<br />
		<li><strong>Гость</strong> <a href="#">изменить</a></li>
		<br />
{foreach from=$action.groups item=group}{if count($group.users) gt 0}
		<li><strong>{$group.title}</strong> <a href="#" onclick="call_admin_interface('ADMIN','access','/{$group.id}'); return false;">изменить</a>
			<ol>
	{foreach from=$group.users item=user}
				<li>{$user.title} <a href="#" onclick="call_admin_interface('ADMIN','access','/{$group.id}/{$user.id}'); return false;">изменить</a> <a href="#">блокировать</a> <a href="#">удалить</a></li>
	{/foreach}
			</ol>
		</li>
{/if}{/foreach}
	</ul>
</div>