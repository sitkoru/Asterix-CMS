{if $settings.bootstrap}
	<link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap-responsive.css" />
{/if}

	<!-- all css from core -->
{foreach from=$head_add.css_core item=lib}	<link rel="stylesheet" type="text/css" href="{$lib.path}"{if $lib.params.media} media="{$lib.params.media}"{else} media="print,screen,projection"{/if} />
{/foreach}

	<!-- all css from templates -->
{foreach from=$head_add.css item=lib}
	<link rel="stylesheet" type="text/css" href="{$lib.path}"{if $lib.params.media} media="{$lib.params.media}"{else} media="print,screen,projection"{/if} />
{/foreach}