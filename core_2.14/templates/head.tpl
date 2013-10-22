{include file="`$paths.admin_templates`/doctype.tpl"}
<head>
	<title>{if strlen($content.seo_title)}{$content.seo_title}{else}{if isset($content.title) and ($content.sid neq 'start') }{$content.title|strip_tags} - {/if}{$settings.domain_title|strip_tags}{/if}</title>
	
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="{if strlen($content.seo_description)}{$content.seo_description|escape:'html'}{else}{$settings.meta_description|escape:'html'}{/if}" />
	<meta name="keywords" content="{if strlen($content.seo_keywords)}{$content.seo_keywords|escape:'html'}{else}{$settings.meta_keywords|escape:'html'}{/if}" />
	<meta name="application-name" content="Asterix CMS v{$config.version}" />
{if $settings.viewport}	<meta name="viewport" content="{$settings.viewport}" />
{/if}
{if $canonical}	<link rel="canonical" href="http://{$ask->host}{$content.url}" />
{/if}

{if strlen($settings.meta_add)}
<!-- additional meta from settings -->
{$settings.meta_add}{/if}

{if $block_ie6.ie6}
	<!--[if IE 6]>
	<script type="text/javascript">
		if( confirm('Сайт не предназначен для упросмотра на устаревшем браузере Internet Explorer 6, обновить браузер?') )
			location.replace("http://browsehappy.com/");
	</script>
	<![endif]-->
{/if}{if $block_ie6.ie7}
	<!--[if IE 7]>
	<script type="text/javascript">
		if( confirm('Сайт не предназначен для упросмотра на устаревшем браузере Internet Explorer 7, обновить браузер?') )
			location.replace("http://browsehappy.com/");
	</script>
	<![endif]-->
{/if}{if $block_ie6.ie8}
	<!--[if IE 8]>
	<script type="text/javascript">
		if( confirm('Сайт не предназначен для упросмотра на устаревшем браузере Internet Explorer 8, обновить браузер?') )
			location.replace("http://browsehappy.com/");
	</script>
	<![endif]-->
{/if}

	<!-- all js from core -->
{foreach from=$head_add.js_core item=lib}	<script type="text/javascript" src="{$lib.path}"></script>
{/foreach}

	<!-- all js from templates -->
{foreach from=$head_add.js item=lib}	<script type="text/javascript" src="{$lib.path}"></script>
{/foreach}
{include file="`$paths.admin_templates`/cnct_head_css.tpl"}

{foreach from=$head_add.tpl item=lib}{if strlen($lib.path)}{include file=$lib.path}{/if}{/foreach}

	<link rel="icon" type="image/png" href="/favicon.ico" />
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="search" type="application/opensearchdescription+xml" href="/opensearch_desc.xml" title="{$settings.domain_title}" />
    <link rel="alternate" type="application/rss+xml" title="Новости в RSS" href="/news.xml" />

</head>
