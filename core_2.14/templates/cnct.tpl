<!-- all js from core -->
{foreach from=$head_add.js_core item=lib}<script type="text/javascript" src="{$lib.path}"></script>
{/foreach}

<!-- all js from templates -->
{foreach from=$head_add.js item=lib}<script type="text/javascript" src="{$lib.path}"></script>
{/foreach}

{include file="`$paths.admin_templates`/v4/include.tpl"}