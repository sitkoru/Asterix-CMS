<div class="acms-login__background-cover" style="
    display: none;
    position: fixed;
    left:  0;
    top:  0;
    width: 100%;
    height: 100%;
    z-index: 1999;
    background-color: rgba(0, 0, 0, 0.9);
    cursor: pointer;
">
    <div class="acms-login__login_form" style="
        position: fixed;
        left: 50%;
        top: 43%;
        width: 500px;
        height: auto;
        margin-left: -250px;
        margin-top: -150px;
        background-color: white;
        box-shadow: 0 0 10px -2px black;
        z-index: 2000;
        cursor: default;
        border-radius: 10px;
    ">
        <style>
            .acms-login__login_form input[type=text], .acms-login__login_form input[type=password]{
                width: 220px;
                font-size: 14px;
                padding: 5px 5px;
                margin: 0;
                border: 1px solid #eee;
                border-radius: 0;
            }
            .acms-login__login_form label{
                display: inline-block;
                width: 150px;
                font-size: 14px;
                padding: 5px 0;
                text-align: right;
                font-weight: normal;
                margin-bottom: 3px;
            }
            .acms-login__login_form a{
                display: block;
                text-decoration: underline;
            }
        </style>

        <div style="text-align: center;">
            <h1 style="font-size: 26px !important; color: #ff7a00; font-weight: normal;line-height: 120%;margin-top: 20px; font-style: normal; font-family: Arial;">Панель управления сайтом</h1>
        </div>
        <form style="
            width: 100%;
            padding: 20px 0;
            margin: 0;
            background-color: #ff7a00;
            color: white;
        ">
            <fieldset style="border: 0; margin: 0;">
                <label>Логин: </label>
                <input type="text" name="login" required="required">
                <br />
                <label>Пароль: </label>
                <input type="password" name="password" required="required">
                <br />
                <label style="margin-left: 150px; text-align: left; font-weight: normal; font-size: 14px;">
                    <input type="checkbox" name="remember">
                    Чужой компьютер
                </label>
                <div style="margin-left: 152px;">
                    <button type="button" style="padding: 5px 25px; border:0;" class="acms-login__login_button">Войти</button>
                    {foreach from=$config_openid item=rec}
                        <a style="padding: 5px 10px; display: inline-block; color: white;" href="https://www.google.com/accounts/o8/ud?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.return_to=http://{$ask->host}/?login&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.realm=http://{$ask->host}/&openid.ui.ns=http://specs.openid.net/extensions/ui/1.0&openid.ui.icon=true&hd=sitko.ru&openid.ns.ax=http://openid.net/srv/ax/1.0&openid.ax.mode=fetch_request&openid.ax.required=firstname,lastname,email,language&openid.ax.type.firstname=http://axschema.org/namePerson/first&openid.ax.type.lastname=http://axschema.org/namePerson/last&openid.ax.type.email=http://axschema.org/contact/email&openid.ax.type.language=http://axschema.org/pref/language">Вход по домену</a>
                    {/foreach}
                </div>
            </fieldset>
        </form>

        <div style="margin: 10px 0; font-size: 12px;">
            <div style="display: inline-block; width: 150px; text-align: right; padding: 5px 0; vertical-align: sub;">
                <a href="http://asterix.opendev.ru"><img src="http://src.opendev.ru/a/i/asterix.png" alt="" style="vertical-align: baseline;"></a>
            </div>
            <div style="display: inline-block; width: 250px;">
                <a href="http://mishinoleg.ru" style="color: #428bca;">Официальный сайт разработчика</a>
                <a href="http://admin.opendev.ru" style="color:grey; margin-top: 10px;">Помощь по системе управления</a>
                <a href="http://asterix.opendev.ru" style="color:grey;">Система управления сайтом, версия 2.14</a>
            </div>
        </div>

    </div>
</div>
