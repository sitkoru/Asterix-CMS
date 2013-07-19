{if $user.admin}
	{include file="`$paths.admin_templates`/admin_bar.tpl"}
{else}
  <div id="acms_lb-background" class="acms_lb-background"></div>
  <div id="acms_lb-contentWrap" class="acms_lb-contentWrap">
    <div class="acms_lb-content">
      <div class="acms_lb-content-header">Панель управления сайтом</div>
      <form id="admin_auth_form" method="post" action="/users.html" class="acms_lb-content-form interface ajax">
        <input type="hidden" name="interface" value="login" />
        <input type="hidden" name="host" id="acms_login_host" value="localhost" />
        <input type="hidden" name="openid" id="acms_login_openid" value="localhost" />
        <table cellspacing="0" cellpadding="0" class="acms_lb-content-formTable">
          <tbody>
            <tr>
              <td class="acms_lb-content-formCell acms_lb-content-formCell_first acms_lb-content-leftCol"><span class="acms_lb-content-formLabel">Компания</span></td>
				<td class="acms_lb-content-formCell acms_lb-content-formCell_first acms_lb-content-rightCol">
					<span id="amcs_js_companySelector-chosen" class="acms_companySelector-chosen">{$domain.title|cut:35}</span><span class="acms_companySelector-link" id="amcs_js_companySelector-link">...</span>
               
					<ul class="acms_companySelector-list" style="display:none;">
						<li class="acms_companySelector-list-item" data-name="localhost" data-type="localhost">{$domain.title|cut:35}</li>
					{foreach from=$openid item=access key=dom}{if $access == admin}
						<li class="acms_companySelector-list-item" data-name="openid" data-type="{$dom}">Аккаунт «{$dom}»</li>
					{/if}{/foreach}
					</ul>
				
				</td>
            </tr>
            <tr class="acms_lb-content-form_local">
              <td class="acms_lb-content-formCell acms_lb-content-leftCol"><label for="acms_form-inputLogin" class="acms_lb-content-formLabel">Логин</label></td>
              <td class="acms_lb-content-formCell acms_lb-content-rightCol"><input type="text" name="login" id="acms_form-inputLogin" class="acms_lb-content-formInput" /></td>
            </tr>
            <tr class="acms_lb-content-form_local">
              <td class="acms_lb-content-formCell acms_lb-content-leftCol"><label for="acms_form-inputPassword" class="acms_lb-content-formLabel">Пароль</label></td>
              <td class="acms_lb-content-formCell acms_lb-content-rightCol"><input type="password" name="password" id="acms_form-inputPassword" class="acms_lb-content-formInput" /></td>
            </tr>
            <tr class="acms_lb-content-form_local">
              <td class="acms_lb-content-formCell acms_lb-content-leftCol"></td>
              <td class="acms_lb-content-formCell acms_lb-content-rightCol"><label for="acms_form-inputCheckbox" class="acms_lb-content-formLabel acms_lb-content-formLabel_otherPC"><input id="acms_form-inputCheckbox" type="checkbox" name="no_cookie" value="1" class="acms_lb-content-formCheckbox" />чужой компьютер</label></td>
            </tr>
            <tr class="acms_lb-content-form_local">
              <td class="acms_lb-content-formCell acms_lb-content-leftCol"></td>
              <td class="acms_lb-content-formCell acms_lb-content-rightCol"><input type="submit" value="Войти" class="acms_form-inputButton" /></td>
            </tr>
            <tr class="acms_lb-content-form_openid" style="display:none;">
              <td class="acms_lb-content-formCell acms_lb-content-leftCol"></td>
              <td class="acms_lb-content-formCell acms_lb-content-rightCol"><input type="button" value="Войти" class="acms_form-inputButton" OnClick="
				document.location.href='\
					https://www.google.com/accounts/o8/ud\
						?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0\
						&amp;openid.mode=checkid_setup\
						&amp;openid.return_to=http://{$domain.current_host}/?login\
						&amp;openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select\
						&amp;openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select\
						&amp;openid.realm=http://{$domain.current_host}/\
						&amp;openid.ui.ns=http://specs.openid.net/extensions/ui/1.0\
						&amp;openid.ui.icon=true\
						&amp;hd=sitko.ru\
						&amp;openid.ns.ax=http://openid.net/srv/ax/1.0\
						&amp;openid.ax.mode=fetch_request\
						&amp;openid.ax.required=firstname,lastname,email,language\
						&amp;openid.ax.type.firstname=http://axschema.org/namePerson/first\
						&amp;openid.ax.type.lastname=http://axschema.org/namePerson/last\
						&amp;openid.ax.type.email=http://axschema.org/contact/email\
						&amp;openid.ax.type.language=http://axschema.org/pref/language\
						';
			  " /></td>
            </tr>
          </tbody>
        </table>
      </form>
      <ul class="acms_listLinks">
        <li class="acms_listLinks-item">
          <a href="http://admin.sitko.ru/">Помощь по системе управления</a>
        </li>
        <li class="acms_listLinks-item">
          <a href="http://sitko.ru" class="acms_listLinks-linkSitko"><img src="http://src.sitko.ru/a/i/sitko.png" alt="Sitko.ru" class="acms_listLinks-imgSitko" />Официальный сайт разработчика</a>
        </li>
        <li class="acms_listLinks-item acms_listLinks-item_last">
          <a href="http://asterix.opendev.ru" class="acms_listLinks-linkAsterix"><img src="http://src.sitko.ru/a/i/asterix.png" alt="Asterix CMS" class="acms_listLinks-imgAsterix" />Сайт системы управления</a>, <a href="http://asterix.opendev.ru/новости.html">Версия {$config.version}</a>
<!--
          <a href="http://asterix.opendev.ru/help/update.html" class="acms_listLinks-linkVersion" title="Проверьте обновление вашей Asterix CMS">
            <span class="acms_listLinks-imgVersion"></span>
          </a>
-->
        </li>
      </ul>
      <span id="acms_lb-close" class="acms_lb-close"></span>
    </div>
  </div>
  
{/if}
