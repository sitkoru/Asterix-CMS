
<h3>Обновление ядра Asterix CMS</h3>

<p>
	Вердикт:
	{if $action.update.mode == 'auto'}
		 <strong>Доступно автоматическое обновление до версии {$action.update.next}</strong>
	{elseif $action.update.mode == 'manual'}
		 <strong>Доступно обновление до версии {$action.update.next} в ручном режиме</strong>
	{elseif $action.update.mode == 'none'}
		 <strong>Вы используете самую последнюю версию Asterix CMS {$action.update.current}</strong>
	{else}
		 <strong>Не найдено обновление для вашей версии Asterix CMS</strong>
	{/if}
	
	{if $action.update.next!='false'}
		{if $action.update.errors}
			<p style="margin-top:20px;">
				Найдены следующие ошибки:
				<ul style="margin:0 0 20px 20px;">
				{foreach from=$action.update.errors item=error}
					<li style="list-style: decimal none outside; margin:3px 0;">{$error}</li>
				{/foreach}
				</ul>
			</p>
			<p style="color:red">Все найденные ошибки необходимо устранить до продолжения обновления.</p>
		{else}
			<p style="color:green; margin-top:20px;">Все проверки пройдены, можно обновляться.</p>
			<script>
				
	function start_core_update(){literal}{{/literal}
		if( !confirm('Данная страница останется активная - не закрывайте её и не обновляйте, здесь будет отображаться статус обновления, и всегда можно будет всё отменить.\n\nНачинаем обновление?') )
			return false;

		$('#acms_core_update_status').fadeIn('fast').text('Ожидаю ответа сервера...');
		$.post('/', $('#acms_core_update_form').serialize(), function(data){
			$('#acms_core_update_status').text( data );
		})
		
		
	{literal}}{/literal}
					
			</script>
			<form method="post" id="acms_core_update_form">
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="mode" value="{$action.update.mode}" />
				<input type="hidden" name="current" value="{$action.update.current}" />
				<input type="hidden" name="next" value="{$action.update.next}" />
			
			{if $action.update.mode == 'auto'}
				<p style="margin-top:20px;">План <strong style="color:green;">автоматического</strong> обновления такой:</p>
				<ul style="margin:0 0 20px 20px;">
					<li style="list-style: decimal none outside; margin:3px 0;">Нажмите на кнопку "запустить автоматическое обновление" внизу.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Скачиваем новое ядро, распаковываем его в нужную директорию.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Обновляем структуру базы данных - это всё ещё безопасно, данные не удаляются.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Вам даётся секретная кнопка для отката всех изменений, если вам что-то не понравится.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Обновляем конфигурационный файл.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Обновление завершено.</li>
				</ul>
				<input type="button" value="Запустить автоматическое обновление" OnClick="start_core_update();" style="padding: 10px;font-size: 14px;" />
			{elseif $action.update.mode == 'manual'}
				<p style="margin-top:20px;">План <strong style="color:red;">ручного</strong> обновления такой:</p>
				<ul style="margin:0 0 20px 20px;">
					<li style="list-style: decimal none outside; margin:3px 0;">Нажмите на кнопку "запустить ручное обновление" внизу.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Автоматически скачивается новое ядро и распаковывается в нужную директорию.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Автоматически обновляется структура базы данных - это всё ещё безопасно, данные не удаляются.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Далее, <span style="color:red;">вам или вашему программисту</span> необходимо произвести все действия над сайтом, описанные в соответствующей инструкции по переходу от версии {$action.update.current} к версии {$action.update.next}, <br />которые вы можете найти <a href="http://asterix.opendev.ru" target="_blank">на нашем сайте</a>.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Теперь <span style="color:red;">вы или ваш программист</span> должны обновить номер версии ядра <a href="http://asterix.opendev.ru/docs/config.html" target="_blank">в файле конфигурации</a>.</li>
					<li style="list-style: decimal none outside; margin:3px 0;">Обновление завершено.</li>
				</ul>
				<input type="button" value="Запустить ручное обновление" OnClick="start_core_update();" style="padding: 10px;font-size: 14px;" />
			{/if}
			
			</form>
			<textarea id="acms_core_update_status" style="display:none; width:600px; height:400px; font-size:12px;">Ожидаю ответа сервера...</textarea>
		{/if}
	{/if}
	
</p>