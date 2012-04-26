{include file="$path_admin_templates/forms/tabs.tpl"}

<div class="row">
	<div class="span12">

		<h2>Ограничения по количеству публикуемого контента</h2>
		
		<table class="table table-condensed">
			<thead>
				<tr class="well">
					<td></td>
				{foreach from=$action.groups.0.fields.0.groups item=group}
					<td style="width:120px;">{$group.title}</td>
				{/foreach}
				</tr>
			</thead>
			<tbody>
			{foreach from=$action.groups.0.fields item=field key=key}
			{if $key>0 and $key is div by 10}
				<tr class="well">
					<td></td>
				{foreach from=$action.groups.0.fields.0.groups item=group}
					<td style="width:120px;">{$group.title}</td>
				{/foreach}
				</tr>
			{/if}
				<tr>
					<td>{$field.title}</td>
				{foreach from=$field.groups item=group}
					<td>
						<select style="width:120px; font-size:0.8em;">
							<option>Запрещено добавлять</option>
							<option>Не более 4 за 30 дней</option>
							<option>Не более 3 за 7 дней</option>
							<option>Не более 1 в день</option>
							<option>Не более 3 в день</option>
							<option>Не более 5 в день</option>
							<option>Не более 10 в день</option>
							<option>Без ограничений</option>
						</select>
					</td>
				{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<div class="span4">
	</div>
	  
</div>
