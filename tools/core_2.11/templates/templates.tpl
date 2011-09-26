<div class="tree">
{if count($action.recs)}
<ol>
{foreach from=$action.recs item=rec}	
	<li>{$rec.title} [{$rec.filename}]</li>{/foreach}
</ol>
{/if}
</div>