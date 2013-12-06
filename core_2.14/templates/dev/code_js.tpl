
{if $content.code}

    <form action="" method="post">

        <div class="btn-group" style="margin-bottom: 10px;">
            <button type="submit" class="btn btn-success btn-sm" title="Сохранить файл js"><span class="glyphicon glyphicon-ok"></span></button>
            <a href="/dev.js/{$content.filename}" class="btn btn-default btn-sm" title="Отменить изменения"><span class="glyphicon glyphicon-refresh"></span></a>
            {if $content.backups}
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        Резервные копии <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        {foreach from=$content.backups item=rec key=key}
                            <li{if ($rec.filename == $get_vars['bkp']) || (!$key && !IsSet($get_vars['bkp']))} class="active"{/if}><a href="{$rec.url}">{$rec.title}{if $key == 1} (последняя){/if}</a></li>
                        {/foreach}
                        <li class="divider"></li>
                        <li><a href="{$content.backups.0.url}.all_backups">Смотреть все резервные копии</a></li>
                    </ul>
                </div>
            {/if}
        </div>

        <textarea id="editor" name="code">{$content.code}</textarea>


        <!-- Create a simple CodeMirror instance -->
        <script>

            jQuery(document).ready(function(){

                var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
                    lineNumbers: true,
                    mode: "text/javascript",
                    matchBrackets: true
                });

                editor.on("change", function( cm ) {
                    $('#editor').text( cm.getValue() );
                });

            });
        </script>

        {if $content.all_backups}

            <h4>Все резервные копии файла {$content.filename}</h4>
            <ol>
                {foreach from=$content.all_backups item=rec}
                    <li><a href="{$rec.url}">{$rec.title}</a></li>
                {/foreach}
            </ol>

        {/if}

        <div style="margin-top: 10px;">
            <button type="submit" class="btn btn-success btn-sm">Сохранить файл js</button>
            <button type="button" class="btn btn-default btn-sm" OnClick="

var filename = prompt('Укажите название нового файла js (без расширения)');
if( filename )
    document.location.href='/dev.js/'+filename;

    ">Добавить новый файл js</button>
        </div>


    </form>

{else}

    <h1>Управление js сайта</h1>

    <p>В этом разделе вы сможете найти полный список шаблонов, используемых на вашем сайте, и отредактировать их. Полный список шаблонов представлен слева на странице. Выше стоит список обязательных шаблонов страниц, и ниже - писок дополнительных шаблонов, созданных программистом.</p>
    <p>Дополнительные шаблоны могут быть подключены из основных шаблонов или из друг друга при помощи команды Smarty</p>
    <pre>&#123;include file='__filename__'&#125;</pre>

{/if}
