
{if count($recs)}
<ol class="acms_tree">
{foreach from=$recs item=rec key=key1}
	<li class="acms_sub">
		<span class="acms_title">{$rec.title}</span>
	</li>{/foreach}
</ol>
{/if}
