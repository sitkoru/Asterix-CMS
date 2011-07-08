<ul id="admin_bar">

	<li class="a_logo"><img src="http://src.sitko.ru/i/logo_adm.png" style="height:29px;" alt="Asterix CMS" /></li>
	<li class="a_wide"><a href="/" rel="tree" class="call_admin_interface">Дерево сайта</a></li>
	<li class="a_narrow">
		<a rel="add" href="#" OnClick="return false;">Добавить</a>
		<ul class="a_sub">
{foreach from=$add.recs item=rec}
			<li><a href="/add/{$rec.module}/{$rec.structure_sid}.html" class="call_admin_interface" rel="add">{$rec.structure}</a></li>
{/foreach}
			<li><br /></li>
{foreach from=$add.subs item=rec}
			<li><a href="/add/{$rec.module}/{$rec.structure_sid}.html" class="call_admin_interface" rel="add">{$rec.structure}</a></li>
{/foreach}
		</ul>
	</li>
	<li class="a_wide"><a rel="edit" href="" class="call_admin_interface">Изменить</a></li>
	<li class="a_narrow">
		<a href="#">Дополнительно</a>
		<ul class="a_sub">
			<li><a rel="settings" href="" class="call_admin_interface">Настройки сайта</a></li>
			<li><a rel="users" href="" class="call_admin_interface">Пользователи</a></li>
		</ul>
	</li>
	<li class="a_narrow">
		<a href="#">Разработка</a>
		<ul class="a_sub">
			<li><a rel="css" href="" class="call_admin_interface">Стили</a></li>
			<li><a rel="js" href="" class="call_admin_interface">JavaScript</a></li>
<!--
			<li><a rel="modules" href="" class="call_admin_interface">Модули</a></li>
-->
			<li><a rel="templates" href="" class="call_admin_interface">Шаблоны</a></li>
<!--
			<li><a rel="preloads" href="" class="call_admin_interface">Компоненты</a></li>
			<li><a rel="themes" href="" class="call_admin_interface">Темы оформления</a></li>
			<li><a rel="config" href="" class="call_admin_interface">Конфигурация</a></li>
			<li><a rel="core" href="" class="call_admin_interface">Ядро</a></li>
-->
		</ul>
	</li>
	<li class="a_narrow">
		<a rel="about" href="#">О сайте</a>
		<ul class="a_sub">
			<li>Сайт: {$settings.domain_title|cut:40}</li>
			<li>Asterix CMS, <a href="http://asterix.opendev.ru/новости.html" class="out">версия {$config.version}</a></li>
			<li>Создан: {$domain.date_public.day} {$domain.date_public.month_title} {$domain.date_public.year} года.</li>
			<li><a href="http://help.sitko.ru/Рубрикатор/Сайты/СУ.html" class="out">Помощь по системе управления</a></li>
			<li><a href="http://sitko.ru" class="out">Подробнее о разработчике</a></li>
			<li><a href="http://asterix.opendev.ru" class="out">Подробнее о системе управления</a></li>
		</ul>
	</li>
	<li class="a_narrow">
		<a href="#">{$user.title}</a>
		<ul class="a_sub">
			<li><a rel="exit" href="?logout=yes">Выход</a></li>
		</ul>
	</li>
</ul>

<div id="bar">
	<a class="cancel" href="#" title="Закрыть"></a>
	<a class="save" href="#" title="Сохранить"></a>
	<div id="bar_container">
		<div id="bar_content"></div>
		<div id="bar_tools"></div>
	</div>
</div>
