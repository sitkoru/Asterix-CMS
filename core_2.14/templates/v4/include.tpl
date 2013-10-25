{if !$user.id}
<!-- ACMS: Подключаем блок авторизации в админке -->
<script type="text/javascript" src="http://src.opendev.ru/v4/j/login.js"></script>
{else}
<!-- ACMS: Подключаем панель управления сайтом -->
{include file="`$paths.admin_templates`/v4/manage_bar.tpl"}
{/if}