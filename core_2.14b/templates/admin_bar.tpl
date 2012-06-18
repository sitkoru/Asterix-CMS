<ul id="acms_bar" class="draggable">

	<li class="a_logo"><img src="http://src.sitko.ru/i/logo_adm.png" style="height:29px;" alt="Asterix CMS" /></li>
	<li class="a_wide">
		<a href="/admin.html" target="acms">Дерево сайта</a>
		<ul class="a_sub">
{foreach from=$add.recs item=rec}
			<li><a href="/admin.{$rec.module}.html" target="acms">{$rec.structure}</a></li>
{/foreach}
			<li><br /></li>
{foreach from=$add.subs item=rec}
			<li><a href="/admin.{$rec.module}.html" target="acms">{$rec.structure}</a></li>
{/foreach}
		</ul>
	</li>
	<li class="a_narrow">
		<a rel="add" href="#" OnClick="return false;">Добавить</a>
		<ul class="a_sub">
{foreach from=$add.recs item=rec}
			<li><a href="/admin/{$rec.module}.addRecord.{$rec.structure_sid}.html" target="acms">{$rec.structure}</a></li>
{/foreach}
			<li><br /></li>
{foreach from=$add.subs item=rec}
			<li><a href="/admin/{$rec.module}.addRecord.{$rec.structure_sid}.html" target="acms">{$rec.structure}{if $rec.structure_sid != 'rec'} в {$rec.title}{/if}</a></li>
{/foreach}
		</ul>
	</li>
	<li class="a_wide"><a href="/admin{$content.url_clear}.editRecord.html" target="acms">Изменить</a></li>
	<li class="a_narrow">
		<a href="/admin/start.settings.html" target="acms">Настройки</a>
		{if $settings.test_mode}
		<ul class="a_sub">
<!--
			<li><a href="/admin/start.access.html" target="acms">Уловни доступа</a></li>
-->
			<li><a href="/admin/start.css.html" target="acms">Стили</a></li>
			<li><a href="/admin/start.js.html" target="acms">JavaScript</a></li>
<!--
			<li><a href="/admin/start.modules.html" target="acms">Модули</a></li>
-->
			<li><a href="/admin/start.templates.html" target="acms">Шаблоны</a></li>
		</ul>
		{/if}
	</li>
	<li class="a_narrow">
		<a rel="help" href="#">Помощь</a>
		<ul class="a_sub">
			<li><a href="http://admin.sitko.ru/tree.html" class="out">Помощь по системе управления</a></li>
			<li><a href="http://sitko.ru" class="out">Подробнее о разработчике</a></li>
			<li><a href="http://asterix.opendev.ru" class="out">Подробнее о системе управления</a></li>
		</ul>
	</li>
	<li class="a_narrow">
		<a rel="about" href="#">О сайте</a>
		<ul class="a_sub">
			<li>Сайт: {$settings.domain_title|cut:40}</li>
			{if $domain.date_public.day}<li>Создан: {$domain.date_public.day} {$domain.date_public.month_title} {$domain.date_public.year} года.</li>{/if}
			<li>Asterix CMS, <a href="http://asterix.opendev.ru/about/news.html" class="out">версия {$config.version}</a></li>
			<li>PHP {$config.phpversion}</li>
			<li><a href="https://github.com/dekmabot/Asterix-CMS/commits/master" target="_blank">Обновления ядра на GitHub</a></li>
		</ul>
	</li>
	<li class="a_narrow">
		<a href="/admin{$user.url_clear}.editRecord.html" target="acms">{$user.title}</a>
		<ul class="a_sub">
			<li><a rel="exit" href="?logout=yes">Выход</a></li>
		</ul>
	</li>
</ul>

<div id="acms_content">
	<a class="acms_close" href="#">&times;</a>
	<iframe name="acms" src="" style="border:0; width:1000px; height:550px; padding:0 20px; float:left;" SCROLLING=YES></iframe>
</div>
