<div class="tree">
	<p class="settings-groups">
	{foreach from=$action.groups key=key item=group name=settings_groups}
		{if $group.title != 'Все'}
		<a href="#" class="settings-group{if ! $key} active{/if}" id="link-settings-pan{$key}">{$group.title}</a>
		<span>|</span>
		{/if}
	{/foreach}
		<a href="#" class="settings-group" id="link-settings-all">Все</a>
	</p>

	<form method="post" enctype="multipart/form-data" action="{$content.url}" class="settings-form">
		<fieldset style="display:none">
			<input type="hidden" name="action" value="settings" />
			<input type="hidden" name="domain" value="{$rec.domain}" />
		</fieldset>
		<ol>
	{foreach from=$action.groups key=key item=group}
		{if $group.title != 'Все'}
		{foreach from=$group.recs item=field}
			<li class="settings-pan{$key} a_field"{if $key} style="display:none"{/if}>
			{php}
				$this->_tpl_vars['field']['sid'] = $this->_tpl_vars['field']['var'];
				if ( $this->_tpl_vars['field']['type'] == 'html' or $this->_tpl_vars['field']['type'] == 'robots' )
					$this->_tpl_vars['field']['type'] = 'textarea';
				else $this->_tpl_vars['field']['type'] = preg_replace( "/[^a-z]/", '', strtolower( $this->_tpl_vars['field']['type'] ) );
			{/php}
			{include file="$path_admin_templates/types/`$field.type`.tpl"}
			</li>
		{/foreach}
		{/if}
	{/foreach}
		</ol>
		<p class="submit">
			<button type="submit">Сохранить</button>
			<button class="cancel">Отменить</button>
		</p>
	</form>
</div>
