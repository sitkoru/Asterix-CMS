
{if !$content.welcome}


<h3>Модуль: {$content.title}</h3>
<p>Модуль: {if $content.active}включен и работает{elseif $content.installed}установлен но выключен{else}не установлен{/if}.

{if $content.structures}
    {foreach from=$content.structures item=structure}
        <br />
        <strong>Структура: {$structure.title}</strong>,
        {$structure.records_count} {numeric value=$structure.records_count form1='запись' form2='записи' form5='записей'}.
    {/foreach}
{else}
    <br />
    В модуле структур и записей нет.
{/if}

</p>

<div class="btn-group">
    {if !$content.installed}<a href="/dev.modules/{$content.sid}.install.html" class="btn btn-info btn-sm">Установить модуль</a>{/if}
    {if $content.installed && !$content.active}<a href="/dev.modules/{$content.sid}.activate.html" class="btn btn-success btn-sm">Включить модуль</a>{/if}
    {if $content.installed && $content.active}<a href="/dev.modules/{$content.sid}.deactivate.html" class="btn btn-warning btn-sm">Выключить модуль</a>{/if}
    {if $content.installed && !$content.active}<a href="/dev.modules/{$content.sid}.uninstall.html" class="btn btn-danger btn-sm">Удалить модуль и всю информацию</a>{/if}
</div>

{if $ask->mode.0}
    <hr />
    {if $ask->mode.0 == 'install'}

        <h2>Установка модуля: {$content.title}</h2>
        <ol>
            <li>Установка модуля в систему:</li>
            <li>Установка таблиц для структур модуля:</li>
            <li>Создание ссылки из главного модуля:</li>
        </ol>
        <button type="button" href="/dev.modules/{$content.sid}.install.ok.html" OnClick="$.post($(this).attr('href'),function(){ document.location.href='/dev.modules/{$content.sid}'; })" class="btn btn-default">Запустить установку</button>

    {/if}
    {if $ask->mode.0 == 'uninstall'}

        <h2>Удаление модуля: {$content.title}</h2>
        <ol>
            <li>Отключение ссылки на модуль из главного модуля</li>
            <li>Удаление модуля из таблицы модулей</li>
            <li>Удаление всех данных и таблиц модуля</li>
        </ol>
        <button type="button" href="/dev.modules/{$content.sid}.uninstall.ok.html" OnClick="$.post($(this).attr('href'),function(){ document.location.href='/dev.modules/{$content.sid}'; })" class="btn btn-default">Запустить удаление</button>

    {/if}
    {if $ask->mode.0 == 'activate'}

        <h2>Активация модуля: {$content.title}</h2>
        <ol>
            <li>Включение режима разработки</li>
            <li>Инсталляция таблиц модуля</li>
            <li>Выключение режима разработки</li>
        </ol>
        <button type="button" href="/dev.modules/{$content.sid}.activate.ok.html" OnClick="$.post($(this).attr('href'),function(){ document.location.href='/dev.modules/{$content.sid}'; })" class="btn btn-default">Активировать модуль</button>

    {/if}
    {if $ask->mode.0 == 'deactivate'}

        <h2>Деактивация модуля: {$content.title}</h2>
        <ol>
            <li>Отключение модуля в таблице модулей</li>
        </ol>
        <button type="button" href="/dev.modules/{$content.sid}.deactivate.ok.html" OnClick="$.post($(this).attr('href'),function(){ document.location.href='/dev.modules/{$content.sid}'; })" class="btn btn-default">Деактивировать модуль</button>

    {/if}

{/if}

{else}

    <h1>Установка и удаление модулей сайта</h1>

    <p>В этом разделе вы сможете включать и выключать функциональные модули на вашем сайте. Будьте внимательны: при удалении модуля, вся информация в нём безвозвратно уничтожается.</p>

{/if}