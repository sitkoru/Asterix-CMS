<script type="text/javascript" src="http://stat.clientogenerator.ru/a.js"></script>
<script type="text/javascript" src="http://beta.clientogenerator.ru/cg.js"></script>

{if $user.admin}{include file="`$paths.admin_templates`/admin_bar.tpl"}
{else}
<div id="admin_hide" class="interface">
  <div id="admin_auth">
    {preload prototype=users data=modulepath result=path}
    <form id="admin_auth_form" action="{$path}" method="post" class="interface ajax">
      <input type="hidden" name="interface" value="login" />
      <h2>Авторизация</h2>
      <input type="text" name="login" /><br />
      <input type="password" name="password" /><br /><br />
      <input type="submit" value="Войти" /><br />
      <input type="button" class="cancel" value="Отмена" />
    </form>
    <div id="admin_news"><h1>Наши новости</h1></div>

    <script type="text/javascript" src="http://widgets.twimg.com/j/2/widget.js"></script>
    <script type="text/javascript" src="http://src.sitko.ru/a/j/twit.js"></script>

  </div>
</div>
{/if}

{if $settings.sape}
  <div style="width:100%; text-align:center;">{include_php file='../sape.php'}</div>
{/if}

