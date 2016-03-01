{if !$user.id}
<!-- ACMS: Подключаем блок авторизации в админке -->
<script type="text/javascript" src="https://src.sitko.ru/v4/j/login.js"></script>
{elseif $user.admin}
<!-- ACMS: Подключаем панель управления сайтом -->
{include file="`$paths.admin_templates`/v4/manage_bar.tpl"}
{/if}