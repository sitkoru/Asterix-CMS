{include file="`$paths.admin_templates`/doctype.tpl"}
<head>
	<title>{if strlen($content.seo_title)}{$content.seo_title}{else}{if isset($content.title) and ($content.sid neq 'start') }{$content.title|strip_tags} - {/if}{$settings.domain_title|strip_tags}{/if}</title>
	
	<meta http-equiv="Content-type" value="text/html; charset=utf-8" />
	<meta name="description" content="{if strlen($content.seo_description)}{$content.seo_description}{else}{$settings.meta_description}{/if}" />
	<meta name="keywords" content="{if strlen($content.seo_keywords)}{$content.seo_keywords}{else}{$settings.meta_keywords}{/if}" />
	<meta name="cms" content="Asterix CMS v{$config.version}">
{if strlen($settings.meta_add)}    {$settings.meta_add}
{/if}


{foreach from=$head_add.js item=lib}	<script type="text/javascript" src="{$lib.path}"></script>
{/foreach}
{if addjs}{addjs val=$addjs result=addUserJS}{/if}
{foreach from=$addUserJS item=lib}	<script type="text/javascript" src="{$lib}"></script>
{/foreach}
	<script type="text/javascript" src="/j/j.js"></script>
	
{foreach from=$head_add.css item=lib}	<link rel="stylesheet" type="text/css" href="{$lib.path}"{if $lib.params.media} media="{$lib.params.media}"{/if} />
{/foreach}
{if addjs}{addcss val=$addcss result=addUserCSS}{/if}
{foreach from=$addUserCSS item=lib}	<link rel="stylesheet" type="text/css" href="{$lib}" />
{/foreach}	<link rel="stylesheet" type="text/css" href="/c/s.css" />

	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
</head>