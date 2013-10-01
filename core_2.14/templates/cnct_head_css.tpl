{if $settings.bootstrap}
	<link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap-responsive.css" />
{/if}
<!--
	<link rel="stylesheet" type="text/css" href="http://src.sitko.ru/3.0/c/panel.css" />
	<link rel="stylesheet" type="text/css" href="http://src.sitko.ru/a/c/lightbox.css" />
	<link rel="stylesheet" type="text/css" href="{$paths.public_styles}/s.css" />
-->

	<!-- all css from core -->
{foreach from=$head_add.css_core item=lib}	<link rel="stylesheet" type="text/css" href="{$lib.path}"{if $lib.params.media} media="{$lib.params.media}"{else} media="print,screen,projection"{/if} />
{/foreach}

	<!-- all css from templates -->
{foreach from=$head_add.css item=lib}
	<link rel="stylesheet" type="text/css" href="{$lib.path}"{if $lib.params.media} media="{$lib.params.media}"{else} media="print,screen,projection"{/if} />
{/foreach}