	{if $group.comment}
		<div class="alert-message info"{if $key != main} style="display:none;"{/if}>
			<p><strong>Информация</strong> {$group.comment}.</p>
		</div>
	{/if}
	{if $group.warning}
		<div class="alert-message error"{if $key != main} style="display:none;"{/if}>
			<p><strong>Важно</strong> {$group.warning}.</p>
		</div>
	{/if}
	{if $group.help}
		<div class="alert-message"{if $key != main} style="display:none;"{/if}>
			<p><strong>Помощь</strong> {$group.help}.</p>
		</div>
	{/if}
