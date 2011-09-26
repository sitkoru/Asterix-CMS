<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Контроллер системы управления						*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 28 октября 2009 года						*/
/*															*/
/************************************************************/

require('default_controller.php');

class controller_admin extends default_controller
{
	public $groups = array(
		'main' => array('title' => 'Основные поля', 'open' => true),
		'media' => array('title' => 'Картинки, видео и звук', 'open' => false),
		'show' => array('title' => 'Отображение на сайте', 'open' => false),
		'system' => array('title' => 'Системные поля', 'open' => false),
		'additional' => array('title' => 'Дополнительные поля', 'open' => false),
		'links' => array('title' => 'Связи на сайте', 'open' => false),
		'access' => array('title' => 'Права доступа', 'open' => false),
		'templates' => array('title' => 'Шаблоны', 'open' => false),
		'feedback' => array('title' => 'Форма обратной связи на странице', 'open' => false),
		'seo' => array('title' => 'SEO, Оптимизация и продвижение', 'open' => false),
		'social' => array('title' => 'Социальный граф', 'open' => false)
	);

	//Основная цункция контроллера, получающая управление после инициализации
	public function start()
	{

		//Очистка кеша
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			//Собираем все хосты
			$hosts = array();
			if (!in_array($this->model->extensions['domains']->domain['host'], $hosts))
				$hosts[] = $this->model->extensions['domains']->domain['host'];
			if (!in_array($this->model->extensions['domains']->domain['host2'], $hosts))
				$hosts[] = $this->model->extensions['domains']->domain['host2'];
			if (!in_array($this->model->extensions['domains']->domain['host3'], $hosts))
				$hosts[] = $this->model->extensions['domains']->domain['host3'];
			if (!in_array($this->model->extensions['domains']->domain['host4'], $hosts))
				$hosts[] = $this->model->extensions['domains']->domain['host4'];
		}

		if (!$this->model->user->info['admin']) {
			pr('У вас нет доступа на это действие.');
			exit();
		}

		//Шаблон
		$current_template_file = 'admin.tpl';

		//Чуть ускоряем вызовы
		$config_paths = $this->model->config['path'];

		//Подключаем шаблонизатор
		require($config_paths['core'] . '/classes/templates.php');
		$tmpl = new templater($this->model);

		//Подготавливаем запись
		if ($this->vars['action'] != 'add') {
			//Убираем модификаторы из URL
			if(count($this->model->ask->mode))
				$this->model->ask->original_url=substr($this->model->ask->original_url,0,strrpos($this->model->ask->original_url,'/')+1).$this->model->ask->rec['sid'].'.'.$this->model->ask->output;

			//Теперь ищем запись по этому URL
			$main_record = $this->model->prepareMainRecord();
		}

		$url = $this->model->ask->full_url;

		//Настройки сайта
		if ($this->vars['action'] == 'settings') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showSettings();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveSettings();
			}
		}

		//Каталог
		if ($this->vars['action'] == 'catalog') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showCatalog();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveCatalog();
			}
		}

		//Добавление записи
		if ($this->vars['action'] == 'add') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				if (!IsSet($url[1]))
					$action_result = $this->showAdd();
				else
					$action_result = $this->showAdd2();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveAdd();
			}
		}

		//Редактирование записи
		if ($this->vars['action'] == 'edit') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showEdit();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveEdit();
			}
		}

		//Перемещение
		if ($this->vars['action'] == 'move_up') {
			$module        = $this->model->ask->module;
			$structure_sid = $this->model->ask->structure_sid;
			$record        = $this->model->ask->rec;
			$this->model->modules[$module]->moveUp($structure_sid, $record);
			
			$index = $this->model->modules[false]->getRecordBySid('rec','index');
			$this->model->ask->rec['id'] = $index['id'];
			
			$action_result = $this->showTree();
		}

		//Перемещение
		if ($this->vars['action'] == 'move_down') {
			$module        = $this->model->ask->module;
			$structure_sid = $this->model->ask->structure_sid;
			$record        = $this->model->ask->rec;
			$this->model->modules[$module]->moveDown($structure_sid, $record);
			
			$index = $this->model->modules[false]->getRecordBySid('rec','index');
			$this->model->ask->rec['id'] = $index['id'];
			
			$action_result = $this->showTree();
		}

		//Удаление
		if ($this->vars['action'] == 'delete') {
			if ($this->vars['method_marker'] == 'GET') {
				$action_result = $this->showDelete();
			} elseif ($this->vars['method_marker'] == 'POST') {
				$action_result = $this->saveDelete();
			}
		}

		//История действий
		if ($this->vars['action'] == 'bkp') {
			if ($this->vars['method_marker'] == 'GET') {
				$action_result = $this->showBkp();
			} elseif ($this->vars['method_marker'] == 'POST') {
				$action_result = $this->saveBkp();
			}
		}

		//Пользователи
		if ($this->vars['action'] == 'users') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showAdmins();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
			}
		}
		//Администраторы
		if ($this->vars['action'] == 'email') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showEmail();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
			}
		}

		//Шаблоны
		if ($this->vars['action'] == 'templates') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showTemplates();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveTemplates();
			}
		}

		//Стили
		if ($this->vars['action'] == 'css') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showCSS();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveCSS();
			}
		}

		//JavaScript
		if ($this->vars['action'] == 'js') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showJS();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveJS();
			}
		}

		//Модули
		if ($this->vars['action'] == 'modules') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showModules();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$action_result = $this->saveModules();
			}
		}

		//Выставляем путь к пакету шаблонов
		$action_result['content_template_file'] = $tmpl->correctTemplatePackPath($action_result['content_template_file'], 'admin_templates');

		//Пишем всё в шаблонизатор
		$tmpl->assign('content', $main_record);
		$tmpl->assign('action', $action_result);
		$tmpl->assign('paths', $this->model->config['path']);
		$tmpl->assign('settings', $this->model->settings);
		$tmpl->assign('path_admin_templates', $this->model->config['path']['admin_templates']);
		$tmpl->assign('domain', $this->model->extensions['domains']->domain);

		//Выдаём необходимые заголовки
		if (!headers_sent()) {
			//Кодировка
			header('Content-Type: text/html; charset=utf-8');
			header("HTTP/1.0 200 Ok");
		}

		//Файл шаблона существует
		$ready_html = $tmpl->fetch($current_template_file, 'admin_templates');

		//Что-то не так
		if (!$ready_html) {
			print('Файл шаблона не найден [' . $current_template_file . '].');
			exit();
		}
		print($ready_html);
	}


	private function showList()
	{
	}

	private function showAdd()
	{
		$res['title']                 = 'Добавление записи';
		$res['content_template_file'] = 'add.tpl';
		//Все возможные модули
		$recs                         = $this->listModule($this->model->modules);
		if ($recs)
			$res['recs'] = $recs['recs'];
		if ($recs)
			$res['subs'] = $recs['subs'];
		return $res;
	}

	private function showAdd2()
	{
		$this->model->ask->module        = $this->model->ask->full_url[1];
		$this->model->ask->structure_sid = $this->model->ask->full_url[2];

		$module        = $this->model->ask->module;
		$structure_sid = $this->model->ask->structure_sid;

		$res['title']                 = $this->model->modules[$module]->structure[$structure_sid]['title'];
		$res['module']                = $module;
		$res['structure_sid']         = $structure_sid;
		$res['content_template_file'] = 'add_record.tpl';

		//Группы полей
		$res['groups'] = $this->getRecordFields($this->model->modules[$module], $structure_sid, false, false);

		foreach ($res['groups'] as $group_sid => $group)
			if (count($group['fields'])) {
				foreach ($group['fields'] as $i => $field) {
					$res['groups'][$group_sid]['fields'][$i]['editable'] = ($group_sid == 'system' ? false : true);
					if (!$res['groups'][$group_sid]['fields'][$i]['value']){
						$res['groups'][$group_sid]['fields'][$i]['value'] = $this->model->types[$field['type']]->getAdmValueExplode($this->model->types[$field['type']]->getDefaultValue($field), $field, $record);
					}
					$res['groups'][$group_sid]['fields'][$i]['template_file'] = $this->model->types[$field['type']]->template_file;
				}
			}

		return $res;
	}

	private function saveAdd()
	{
		//Обознаём модуль
		$module = $this->vars['module'];

		//Опознаём структуру
		$structure_sid = $this->vars['structure_sid'];

		//Удаляем ненужные значения
		UnSet($this->vars['module']);
		UnSet($this->vars['structure_sid']);

		//Добавляем запись
		$result_url = $this->model->addRecord($module, $structure_sid, $this->vars);

		//Перенаправляем на запись
		header('Location: ' . $result_url);
		exit();
	}

	private function showEdit()
	{
		$res = array();

		$module        = $this->model->ask->module;
		$structure_sid = $this->model->ask->structure_sid;
		$record        = $this->model->ask->rec;

		$res['title']                 = $this->model->modules[$module]->structure[$structure_sid]['title'];
		$res['module']                = $module;
		$res['structure_sid']         = $structure_sid;
		$res['content_template_file'] = 'edit.tpl';

		//Группы полей
		$res['groups'] = $this->getRecordFields($this->model->modules[$module], $structure_sid, $record, false);

		foreach ($res['groups'] as $group_sid => $group)
			if (count($group['fields'])) {
				foreach ($group['fields'] as $i => $field){
					if($field['type']){
						$res['groups'][$group_sid]['fields'][$i]['value']         = $this->model->types[$field['type']]->getAdmValueExplode($field['value'], $field, $record);
						$res['groups'][$group_sid]['fields'][$i]['template_file'] = $this->model->types[$field['type']]->template_file;
					}
				}
			}

		return $res;
	}

	private function saveEdit()
	{
		//Обознаём модуль
		$module = $this->vars['module'];

		//Опознаём структуру
		$structure_sid = $this->vars['structure_sid'];

		//Удаляем ненужные значения
		UnSet($this->vars['module']);
		UnSet($this->vars['structure_sid']);

		//Добавляем запись
		$result_url = $this->model->updateRecord($module, $structure_sid, $this->vars);

		//Перенаправляем на запись
		header('Location: ' . $result_url);
		exit();
	}

	private function showDelete()
	{
		$res = array();

		$module        = $this->model->ask->module;
		$structure_sid = $this->model->ask->structure_sid;
		$record        = $this->model->ask->rec;

		$res['title']                 = 'Удаление записи ' . $this->model->modules[$module]->structure[$structure_sid]['title'] . '';
		$res['module']                = $module;
		$res['structure_sid']         = $structure_sid;
		$res['content_template_file'] = 'delete.tpl';
		return $res;
	}

	private function saveDelete()
	{
		$module        = $this->model->ask->module;
		$structure_sid = $this->model->ask->structure_sid;
		$record        = $this->model->ask->rec;

		$res = $this->model->modules[$module]->deleteRecord($structure_sid, $record);

		$this->model->ask->rec['id'] = 1;
		
		return $this->showTree();
	}

	//Показать настройки
	private function showSettings()
	{
		$res                          = array();
		$res['title']                 = 'Настройки сайта';
		$res['content_template_file'] = 'site_settings.tpl';

		//Получаем группы настроек
		$groups = $this->model->execSql('select distinct `group` from `settings` where ' . $this->model->extensions['domains']->getWhere() . ' order by pos');

		//Если групп нет (старая версия) - делаем группу по умолчанию
		$groups[]['group'] = 'Все';

		//Группы
		foreach ($groups as $i => $group) {
			$groups[$i]          = array();
			$groups[$i]['title'] = $group['group'];

			//Получаем настройки группы
			$recs = $this->model->execSql('select * from `settings` where ' . ($group['group'] == 'Все' ? '' : '`group`="' . mysql_real_escape_string($group['group']) . '" and ') . ' ' . $this->model->extensions['domains']->getWhere() . ' order by `pos`');
			if (is_array($recs))
				foreach ($recs as $j => $rec) {
					$recs[$j]['value'] = $this->model->types[$rec['type']]->getAdmValueExplode($rec['value'], false, $rec);
				}

			//Вставялем в группу
			$groups[$i]['recs'] = $recs;
		}

		$res['groups'] = $groups;

		//Получаем настройки домена
		$domain_id = $this->model->extensions['domains']->domain['id'];

		return $res;
	}
	//Сохранить настройки

	private function saveSettings()
	{
		$sets = $this->model->execSql('select * from `settings` where ' . $this->model->extensions['domains']->getWhere() . '', 'getall');
		foreach ($sets as $set) {
			if (IsSet($this->vars[$set['var']]) or ($set['type'] == 'check')) {
				$in_str = $this->model->types[$set['type']]->toSql($set['var'], $this->vars, $set);
				$in_str = str_replace('`' . $set['var'] . '`=', '`value`=', $in_str);
				$sql    = 'update `settings` set ' . $in_str . ' where ' . $this->model->extensions['domains']->getWhere() . ' and `var`="' . mysql_real_escape_string($set['var']) . '"';
				$this->model->execSql($sql, 'update');
			}
		}
		header('Location: /');
		exit();
	}

	private function showAdmins()
	{
		$res                          = array();
		$res['title']                 = 'Пользователи сайта';
		$res['content_template_file'] = 'admins.tpl';

		//Получаем администраторов
		$domain_id = $this->model->extensions['domains']->domain['id'];
		$recs      = $this->model->makeSql(array(
			'tables' => array(
				'users'
			),
			'order' => 'order by `admin` desc, `ip_protected`, `title`'
		), 'getall');

		if ($recs)
			$res['recs'] = $recs;
		return $res;
	}
	private function showEmail()
	{
		$res                          = array();
		$res['title']                 = 'Почтовые аккаунты';
		$res['content_template_file'] = 'email.tpl';

		//Получаем администраторов
		$domain_id = $this->model->extensions['domains']->domain['id'];
		$sql       = 'select * from `mailbox` where `domain`="' . mysql_real_escape_string($this->model->extensions['domains']->domain['mail_host']) . '" order by `username`';
		//		$sql='select * from `mailbox` where `domain`="spec-prom.ru" order by `username`';
		$recs      = $this->model->db['postfix']->GetAll($sql);

		if ($recs)
			$res['recs'] = $recs;
		return $res;
	}

	private function showTemplates()
	{
		$res                          = array();
		$res['title']                 = 'Шаблоны сайта';
		$res['content_template_file'] = 'bar_templates.tpl';

		$recs = array();
		
		//Должно быть шаблонов
		$templates = array();
		foreach($this->model->modules as $module_sid=>$module){
			$recs[ $module->info['prototype'].'_index.tpl' ] = array(
				'id' => count($recs),
				'title' => $module->info['title'].' - главная страница',
				'file' => $module->info['prototype'].'_index.tpl',
			);
			$recs[ $module->info['prototype'].'_content.tpl' ] = array(
				'id' => count($recs),
				'title' => $module->info['title'].' - страница записи',
				'file' => $module->info['prototype'].'_content.tpl',
			);
			//Много структур - добавляется ещё один шаблон
			if(count($module->structure)>1){
				$recs[ $module->info['prototype'].'_list.tpl' ] = array(
				'id' => count($recs),
					'title' => $module->info['title'].' - страница списка записей',
					'file' => $module->info['prototype'].'_list.tpl',
				);
			}
		}

		$root = $this->model->config['path']['templates'].'/';
		$files = $this->get_files($root, false, 0, 100000, false);
		
		foreach($files as $i=>$file)
			if( !IsSet($recs[ $file['file'] ]) ){
				$recs[ $file['file'] ] = array(
					'id' => count($recs),
					'title' => 'Дополнительный шаблон '.$file['file'],
					'file' => $file['file'],
				);
		}

		foreach($recs as $i=>$rec){
			if( file_exists($this->model->config['path']['templates'].'/'.$rec['file']) ){
				$content = @file_get_contents($this->model->config['path']['templates'].'/'.$rec['file']);
			}else{
				$content = '
{include file=\'head.tpl\'}
<body>
	<p>Пустой шаблон</p>
</body>
{include file=\'footer.tpl\'}
				';
			}
			$recs[$i]['content'] = $content;
		}
		
		if ($recs)
			$res['recs'] = $recs;
			
		return $res;
	}
	private function saveTemplates()
	{
		if( strlen($this->vars['new_file']) and strlen($this->vars['new_content']) ){
			$path = $this->model->config['path']['templates'].'/'.basename($this->vars['new_file']);
			file_put_contents($path, $this->vars['new_content']);
		}
		
		foreach($this->vars['content'] as $file => $content){
			$path = $this->model->config['path']['templates'].'/'.basename($file);
			file_put_contents($path, $content);
		}
		header('Location: /');
		exit();
	}


	private function showCSS()
	{
		$res                          = array();
		$res['title']                 = 'Стили сайта';
		$res['content_template_file'] = 'bar_css.tpl';

		$recs = array();
		
		$root = $this->model->config['path']['styles'].'/';
		$recs = $this->get_files($root, false, 0, 100000, false);
		
		foreach($recs as $i=>$rec){
			if( file_exists($this->model->config['path']['styles'].'/'.$rec['file']) ){
				$content = @file_get_contents($this->model->config['path']['styles'].'/'.$rec['file']);
			}else{
				$content = '/*Новый файл стилей для вашего сайта*/';
			}
			$recs[$i]['content'] = $content;
		}
		
		if ($recs)
			$res['recs'] = $recs;
			
		return $res;
	}
	private function saveCSS()
	{
		if( strlen($this->vars['new_file']) and strlen($this->vars['new_content']) ){
			$path = $this->model->config['path']['styles'].'/'.basename($this->vars['new_file']);
			file_put_contents($path, $this->vars['new_content']);
		}
		
		foreach($this->vars['content'] as $file => $content){
			$path = $this->model->config['path']['styles'].'/'.basename($file);
			file_put_contents($path, $content);
		}

		header('Location: /');
		exit();
	}


	private function showJS()
	{
		$res                          = array();
		$res['title']                 = 'JavaScript сайта';
		$res['content_template_file'] = 'bar_js.tpl';

		$recs = array();
		
		$root = $this->model->config['path']['javascript'].'/';
		$recs = $this->get_files($root, false, 0, 100000, false);
		
		foreach($recs as $i=>$rec){
			if( file_exists($this->model->config['path']['javascript'].'/'.$rec['file']) ){
				$content = @file_get_contents($this->model->config['path']['javascript'].'/'.$rec['file']);
			}else{
				$content = '//Новый javascript-файл для вашего сайта';
			}
			$recs[$i]['content'] = $content;
		}
		
		if ($recs)
			$res['recs'] = $recs;
			
		return $res;
	}
	private function saveJS()
	{
		if( strlen($this->vars['new_file']) and strlen($this->vars['new_content']) ){
			$path = $this->model->config['path']['javascript'].'/'.basename($this->vars['new_file']);
			file_put_contents($path, $this->vars['new_content']);
		}
		
		foreach($this->vars['content'] as $file => $content){
			$path = $this->model->config['path']['javascript'].'/'.basename($file);
			file_put_contents($path, $content);
		}

		header('Location: /');
		exit();
	}


	private function showModules()
	{
		$res                          = array();
		$res['title']                 = 'Модули сайта';
		$res['content_template_file'] = 'bar_modules.tpl';

		$recs = array();
		foreach($this->model->modules as $sid=>$module){
			$recs[] = array_merge($module->info, array('structure' => $module->structure) );
		}
		
		if ($recs)
			$res['recs'] = $recs;
		
		$all = file('http://src.opendev.ru/modules.txt');
		foreach($all as $a){
			$t = explode('|', trim($a));
			$res['recs_all'][] = array('title'=>$t[0], 'prototype'=>$t[1]);
		}
		
		return $res;
	}
	private function saveModules()
	{
		if( strlen($this->vars['new_file']) and strlen($this->vars['new_content']) ){
			$path = $this->model->config['path']['templates'].'/'.basename($this->vars['new_file']);
			file_put_contents($path, $this->vars['new_content']);
		}
		
		foreach($this->vars['content'] as $file => $content){
			$path = $this->model->config['path']['templates'].'/'.basename($file);
			file_put_contents($path, $content);
		}
		header('Location: /');
		exit();
	}




	//Укажите модуль, в который будет добавлена запись
	private function listModule($modules)
	{
		$recs = array();
		$subs = array();
		foreach ($modules as $module_sid => $module)
			if ($module->structure) {
				foreach ($module->structure as $structure_sid => $structure) {
					if ($structure_sid == 'rec')
						$recs[] = array(
							'title' => $module->info['title'],
							'structure' => $structure['title'],
							'structure_sid' => $structure_sid,
							'module' => $module_sid
						);
					else
						$subs[] = array(
							'title' => $module->info['title'],
							'structure' => $structure['title'],
							'structure_sid' => $structure_sid,
							'module' => $module_sid
						);
				}
			}
		return array(
			'recs' => $recs,
			'subs' => $subs
		);
	}





	//Получение подготовленного списка полей, по группам
	public function getRecordFields($module, $structure_sid, $record, $with_admin_fields)
	{
		$groups = $this->groups;

		//Перебераем поля данной структуры, рассовываем по группам
		if (IsSet($module->structure))
			if(is_array($module->structure))
			foreach ($module->structure[$structure_sid]['fields'] as $field) {
				//Если у поля не установлена группа - относим к основной
				if (!IsSet($field['group']))
					$field['group'] = 'main';

				//Если группа для поля существует
				if (IsSet($groups[$field['group']])) {
					//Вставляем значение, если есть в переданном массиве
					$field['value'] = $record[$field['sid']];

					//Добавляем поля в группы
					$groups[$field['group']]['fields'][] = $field;
				}
			}

		//Если структура записи предполагает зависимости, выводим поле вывода расположения новой записи
		if ( ($module->structure[$structure_sid]['dep_path']['structure']) || ($module->structure[$structure_sid]['type']=='tree') ) {

			//Если есть явно прописанная родительская структура
			if ( IsSet($module->structure[$structure_sid]['dep_path']['structure']) ){

				$dep_field_name = 'dep_path_' . $module->structure[$structure_sid]['dep_path']['structure'];
				$dep_field_type = $module->structure[$structure_sid]['dep_path']['link_type'];
				$dep_field_structure = $module->structure[$structure_sid]['dep_path']['structure'];

				//Текущее местоположение - вписываем значение
				$value = $record[$dep_field_name];

			//Иначе это просто дерево
			}else{

				$dep_field_name = 'dep_path_parent';
				$dep_field_type = 'tree';
				$dep_field_structure = $structure_sid;

				//Текущее местоположение - вписываем значение
				$value = $record[$dep_field_name];

			}

			//Если есть значение - формируем поле
//			if ($value) {

				$this_field = array(
					'title' => 'Расположение на сайте',
					'sid' => $dep_field_name,
					'type' => $dep_field_type,

					'module' => $module->info['sid'],
					'structure_sid' => $dep_field_structure,

					'dep_path_name' => $dep_field_name,
					'template_file' => $this->model->types[$dep_field_type]->template_file,
					'value' => $value,
				);

				$groups['main']['fields'][] = $this_field;

/*
				$groups['main']['fields'][] = array(
					'title' => 'Расположение на сайте',
					'type' => 'tree',
					'dep_path_name' => $dep_field_name,
					'template_file' => $this->model->types['tree']->template_file,
					'value' => $positions
				);
*/
//			}

		}

//		pr_r($groups);

		return $groups;
	}

	//Дерево, с отметкой где располагается текущая запись
	private function getRecordPositionOnTree($module, $structure_sid, $record)
	{
		$recs = false;

		//Значения для древовидных структур
		if ($module->structure[$structure_sid]['type'] == 'tree') {
			$dep_path = 'dep_path_parent';
//			$recs     = $module->getModuleShirtTree(false, $structure_sid, 0, false, $this->model);
			$recs     = $module->getStructureShirtTree(false, $structure_sid, 0, false, $this->model);//
		} elseif ($module->structure[$structure_sid]['type'] == 'simple') {
			if (IsSet($module->structure[$structure_sid]['dep_path']['structure'])) {
				$dep_path = 'dep_path_' . $module->structure[$structure_sid]['dep_path']['structure'];
				$recs     = $module->getShirtRecordsByWhere($module->structure[$structure_sid]['dep_path']['structure'], false);
			} else {
				//				$dep_path='dep_path_'.$module->structure[$structure_sid]['dep_path']['structure'];
				$recs = false; //=$module->getShirtRecordsByWhere('',false);
			}
		}

		//Если указана определённая запись - указываем это
		if ($record) {
			if ($recs)
				foreach ($recs as $i => $rec) {
					//Родитель
					if ($rec['sid'] == $record[$dep_path])
						$recs[$i]['selected'] = true;

					//Сама запись (отключаем при древовидной структуре)
					if ($module->structure[$structure_sid]['type'] == 'tree')
						if ($rec['id'] == $record['id'])
							$recs[$i]['disabled'] = true;
					//Сама запись
					if ($rec['id'] == $record['id'])
						$recs[$i]['mark'] = true;

					if (IsSet($rec['sub']))
						foreach ($rec['sub'] as $j => $sub2) {
							//Родитель
							if ($sub2['sid'] == $record[$dep_path])
								$recs[$i]['sub'][$j]['selected'] = true;

							//Сама запись (отключаем при древовидной структуре)
							if ($module->structure[$structure_sid]['type'] == 'tree')
								if ($sub2['id'] == $record['id'])
									$recs[$i]['sub'][$j]['disabled'] = true;
							//Сама запись
							if ($sub2['id'] == $record['id'])
								$recs[$i]['sub'][$j]['mark'] = true;

							//Подразделы текущей записи
							if ($recs[$i]['disabled'])
								$recs[$i]['sub'][$j]['disabled'] = true;

							if (IsSet($sub2['sub']))
								if (is_array($sub2['sub']))
									foreach ($sub2['sub'] as $k => $sub3) {
										//Родитель
										if ($sub3['sid'] == $record[$dep_path])
											$recs[$i]['sub'][$j]['sub'][$k]['selected'] = true;

										//Сама запись (отключаем при древовидной структуре)
										if ($module->structure[$structure_sid]['type'] == 'tree')
											if ($sub3['id'] == $record['id'])
												$recs[$i]['sub'][$j]['sub'][$k]['disabled'] = true;
										//Сама запись
										if ($sub3['id'] == $record['id'])
											$recs[$i]['sub'][$j]['sub'][$k]['mark'] = true;

										//Подразделы текущей записи
										if (@$recs[$i]['sub'][$j]['disabled'])
											$recs[$i]['sub'][$j]['sub'][$k]['disabled'] = true;

										if (IsSet($sub3['sub']))
											if (is_array($sub3['sub']))
												foreach ($sub3['sub'] as $l => $sub4) {
													//Родитель
													if ($sub4['sid'] == $record[$dep_path])
														$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['selected'] = true;

													//Сама запись (отключаем при древовидной структуре)
													if ($module->structure[$structure_sid]['type'] == 'tree')
														if ($sub4['id'] == $record['id'])
															$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['disabled'] = true;
													//Сама запись
													if ($sub4['id'] == $record['id'])
														$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['mark'] = true;

													//Подразделы текущей записи
													if (@$recs[$i]['sub'][$j]['disabled'])
														$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['disabled'] = true;

													if (IsSet($sub4['sub']))
														if (is_array($sub4['sub']))
															foreach ($sub4['sub'] as $m => $sub5) {
																//Родитель
																if ($sub5['sid'] == $record[$dep_path])
																	$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['selected'] = true;

																//Сама запись (отключаем при древовидной структуре)
																if ($module->structure[$structure_sid]['type'] == 'tree')
																	if ($sub5['id'] == $record['id'])
																		$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['disabled'] = true;
																//Сама запись
																if ($sub5['id'] == $record['id'])
																	$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['mark'] = true;

																//Подразделы текущей записи
																if (@$recs[$i]['sub'][$j]['disabled'])
																	$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['disabled'] = true;

																if (IsSet($sub5['sub']))
																	if (is_array($sub5['sub']))
																		foreach ($sub5['sub'] as $n => $sub6) {
																			//Родитель
																			if ($sub6['sid'] == $record[$dep_path])
																				$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['selected'] = true;

																			//Сама запись (отключаем при древовидной структуре)
																			if ($module->structure[$structure_sid]['type'] == 'tree')
																				if ($sub6['id'] == $record['id'])
																					$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['disabled'] = true;
																			//Сама запись
																			if ($sub6['id'] == $record['id'])
																				$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['mark'] = true;

																			//Подразделы текущей записи
																			if (@$recs[$i]['sub'][$j]['disabled'])
																				$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['disabled'] = true;

																			if (IsSet($sub6['sub']))
																				if (is_array($sub6['sub']))
																					foreach ($sub6['sub'] as $o => $sub7) {
																						//Родитель
																						if ($sub7['sid'] == $record[$dep_path])
																							$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['sub'][$o]['selected'] = true;

																						//Сама запись (отключаем при древовидной структуре)
																						if ($module->structure[$structure_sid]['type'] == 'tree')
																							if ($sub7['id'] == $record['id'])
																								$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['sub'][$o]['disabled'] = true;
																						//Сама запись
																						if ($sub7['id'] == $record['id'])
																							$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['sub'][$o]['mark'] = true;

																						//Подразделы текущей записи
																						if (@$recs[$i]['sub'][$j]['disabled'])
																							$recs[$i]['sub'][$j]['sub'][$k]['sub'][$l]['sub'][$m]['sub'][$n]['sub'][$o]['disabled'] = true;
																					}
																		}
															}
												}
									}
						}
				}
		} else {
			$recs[0]['selected'] = true;
		}

		if (!count($recs))
			$recs = false;
		return $recs;
	}

	//Показать настройки
	private function showTree()
	{
		$res                          = array();
		$res['title']                 = 'Дерево сайта';
		$res['content_template_file'] = 'tree.tpl';
		
		$levels=2;
		
		if($this->model->ask->rec['is_link_to_module'])$this->model->ask->rec['id']=false;
		
		//Получаем дерево
		$recs = $this->model->prepareShirtTree( $this->model->ask->module, $this->model->ask->structure_sid, $this->model->ask->rec['id'], $levels, array());
		
		//Если считаем поддерево - нужен префикс для ID дочерних элементов
		$res['prefix']=$this->model->ask->rec['id'];
		
		$recs = $this->addManage($recs);
		
		if ($recs)
			$res['recs'] = $recs;
			
		return $res;
	}
	
	//Добавляем управление к записям
	private function addManage($recs)
	{
		if ($recs)
			foreach ($recs as $i => $rec) {
				$recs[$i]['manage']['edit'] = array(
					'title' => 'изменить',
					'method' => 'GET',
					'action' => 'edit',
					'param' => ''
				);
				
				//Удаление, если раздел пуст
				if (!$rec['sub'])
					if (!$rec['is_link_to_module'])
						$recs[$i]['manage']['delete'] = array(
							'title' => 'удалить',
							'method' => 'GET',
							'action' => 'delete',
							'param' => ''
						);
				
				
				//				$recs[$i]['manage']['access']=array('title'=>'доступ','method'=>'GET','action'=>'access','param'=>'');
				
				//Чистый URL
				$recs[$i]['url_clear'] = $rec['url'];
				
				if ($i < count($recs) - 1){
					$recs[$i]['manage']['move_down'] = array(
						'title' => 'ниже',
						'method' => 'GET',
						'action' => 'move_down'
					);
					$recs[$i]['url'].='?method_marker=admin&action=move_down';
				}
				
				if ($i > 0){
					$recs[$i]['manage']['move_up'] = array(
						'title' => 'выше',
						'method' => 'GET',
						'action' => 'move_up'
					);
					$recs[$i]['url'].='?method_marker=admin&action=move_up';
				}
				
				if (IsSet($recs[$i]['sub'])) {
					$recs[$i]['sub'] = $this->addManage($rec['sub']);
				}
			}
		return $recs;
	}


	public function get_files($path, $current_dir, $level, $limit, $req = false){
		$files = array();

		$f = opendir($path);
		$i=0;
		while ( (($file = readdir($f)) !== false) and ($i<$limit) )
			if( !in_array($file, array('.','..')) ){

				//Тип записи
				$type = filetype($path . $file);
				
				$sub = false;
				$ext = false;
				
				//Если директория - рекурсивно читаем её
				if($type == 'dir'){
					//Рекурсия
					if($req)
						$sub = $this->get_files($path.$file.'/', $file, $level+1, $limit, $req);
					else
						$sub = array();
					//Добавляем
					$files = array_merge($files, $sub);
					
				//Просто файл
				}else{
					$info = pathinfo($path.$file);
					$ext = $info['extension'];
					//Добавляем
					$files[] = array(
						'file' => $file,
						'dir' => $current_dir,
						'path' => $path,
						'ext' => $ext,
						'sub'  => $sub,
					);
				}
				
				
				$i++;
			}

		return $files;
	}
	
}

?>
