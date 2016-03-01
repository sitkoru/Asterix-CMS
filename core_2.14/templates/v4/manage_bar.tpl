<link rel="stylesheet" type="text/css" href="https://src.sitko.ru/v4/c/c_reset.css?v=3" />

<div class="acms acms-content__background-cover">
    <div class="acms-content">
        <div class="acms-cross">✖</div>
<!--        <div class="acms-save">✔</div>-->
        <iframe id="acms_iframe_id" style="border:0; width:100%; height:100%; min-height: 550px;" SCROLLING=YES></iframe>
    </div>
</div>

<div class="acms acms-panel__background-cover{if $settings.acms_position} acms-panel-{$settings.acms_position}{else} acms-panel-top{/if}">
    <div class="acms-panel{if $settings.test_mode} acms-test-mode{/if}">

        <ul class="acms-mainmenu hidden-print">

            <li style="background-color: transparent;"><img src="https://src.sitko.ru/i/logo_adm.png" style="margin: -6px 0;height: 27px;" alt="Asterix CMS" /></li>
            <li>
                <a href="#" data-target="acms">Дерево сайта</a>
                <ul class="a_sub">
                    {foreach from=$add.recs item=rec}
                        <li><a href="/admin.{$rec.module}.html" data-target="acms">{$rec.structure}</a></li>
                    {/foreach}
                {if $add.subs}
                    <li><br /></li>
                    {foreach from=$add.subs item=rec}
                        <li><a href="/admin.{$rec.module}.html" data-target="acms">{$rec.structure}</a></li>
                    {/foreach}
                {/if}
                </ul>
            </li>
            <li>
                <a rel="add" href="#" OnClick="return false;" data-target="acms">Добавить</a>
                <ul class="a_sub">
                    {foreach from=$add.recs item=rec}
                        <li><a href="/admin/{$rec.module}.addRecord.{$rec.structure_sid}.html" data-target="acms">{$rec.structure}</a></li>
                    {/foreach}
                {if $add.subs}
                    <li><br /></li>
                    {foreach from=$add.subs item=rec}
                        <li><a href="/admin/{$rec.module}.addRecord.{$rec.structure_sid}.html" data-target="acms">{$rec.structure}{if $rec.structure_sid != 'rec'} в {$rec.title}{/if}</a></li>
                    {/foreach}
                {/if}
                </ul>
            </li>
            <li><a href="/admin{$content.url_clear}.editRecord.html" data-target="acms">Изменить</a></li>
            <li>
                <a href="/admin/start.settings.html" data-target="acms">Настройки</a>
            </li>
            <li>
                <a rel="about" href="#">О сайте</a>
                <ul class="a_sub">
                    <li>Сайт: {$settings.domain_title|cut:40}</li>
                    {if $settings.date_start.day}<li>Создан: {$settings.date_start.day} {$settings.date_start.month_title} {$settings.date_start.year} года.</li>{/if}
                    <li>Asterix CMS, <a href="http://asterix.opendev.ru/about/news.html" class="out">версия {$config.version}</a></li>
                    <li>PHP {$config.phpversion}</li>
                    <li><a href="https://github.com/dekmabot/Asterix-CMS/commits/master" target="_blank">Обновления ядра на GitHub</a></li>
                </ul>
            </li>

        {admin data=update result=update}
        {if $update}
            <li>
                <a rel="update" href="#" style="color:red;">Обновление</a>
                <ul class="a_sub">
                    <li>Ваша версия: {$config.version}</li>
                    <li>Стабильная версия: {$update.version}</li>
                    <li></li>
                    <li><a href="/admin.update.html" data-target="acms">Обновить движок сайта</a></li>
                </ul>
            </li>
        {/if}

        {if !$settings.test_mode}
            <li>
                <a rel="exit" href="?logout=yes" style="color: red;">Выход</a>
            </li>
        {/if}
        </ul>

    </div>
</div>

