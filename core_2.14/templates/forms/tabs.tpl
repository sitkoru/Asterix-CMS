<div class="row">
	<ul class="nav acms-tabs {if isset($pulls)}nav-pills{else}nav-tabs{/if}">
	{assign var=group_key value=0}
	{foreach from=$action.groups key=key item=group name=settings_groups}{if $group.fields}
		{assign var=group_key value=$group_key+1}
		<li{if $key == main} class="active"{/if}><a id="acms_tab_{$group_key}" href="#">{$group.title}</a></li>
	{/if}{/foreach}
	</ul>
</div>

{if $ask->mode.2 == 'success'}
<div class="alert alert-success">
	<a class="close" data-dismiss="alert" href="#">&times;</a>
	<strong>Ура!</strong> Ваши данные успешно сохранены.
</div>
{/if}