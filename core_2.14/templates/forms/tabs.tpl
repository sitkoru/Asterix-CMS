
<ul class="nav nav-tabs">
{assign var=group_key value=0}
{foreach from=$action.groups key=key item=group name=settings_groups}{if $group.fields}
	{assign var=group_key value=$group_key+1}
	<li{if $group_key == 1} class="active"{/if}><a href="#{$group_key}" data-toggle="tab">{$group.title}</a></li>
{/if}{/foreach}
</ul>

{if $ask->mode.2 == 'success'}
<div class="alert alert-success">
	<a class="close" data-dismiss="alert" href="#">&times;</a>
	<strong>Ура!</strong> Ваши данные успешно сохранены.
</div>
{/if}