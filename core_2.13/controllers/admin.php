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
		'main' => array(
			'title' => 'Основные',
		),
		'media' => array(
			'title' => 'Мультимедиа',
		),
		'show' => array(
			'title' => 'Отображение на сайте',
			'comment' => 'Здесь вы можете настроить отображение записи на сайте, в главном меню, или в других предложенных ниже местах'
		),
		'seo' => array(
			'title' => 'SEO',
			'comment' => 'Следующие поля предназначены для поисковой оптимизации записи. Здесь можно настроить отличные от общих правил метатеги и заголовок страницы'
		),
		'system' => array(
			'title' => 'Системные',
			'warning' => 'В этом разделе содержатся важные системные настройки, меняйте их осторожно'
		),
		'additional' => array(
			'title' => 'Дополнительные',
		),
		'links' => array(
			'title' => 'Связи на сайте'
		),
		'access' => array(
			'title' => 'Права доступа'
		),
		'templates' => array(
			'title' => 'Шаблоны'
		),
		'feedback' => array(
			'title' => 'Форма обратной связи',
		),
		'social' => array(
			'title' => 'Социальный граф',
		)
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
			pr_r($this->model->user->info);
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
				$this->checkDemo();
				$action_result = $this->saveSettings();
			}
		}

		//Каталог
		if ($this->vars['action'] == 'catalog') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showCatalog();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
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
				$this->checkDemo();
				$action_result = $this->saveAdd();
			}
		}

		//Редактирование записи
		if ($this->vars['action'] == 'edit') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showEdit();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveEdit();
			}
		}

		//Перемещение
		if ($this->vars['action'] == 'move_up') {
			$this->checkDemo();
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
			$this->checkDemo();
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
				$this->checkDemo();
				$action_result = $this->saveDelete();
			}
		}

		//История действий
		if ($this->vars['action'] == 'bkp') {
			if ($this->vars['method_marker'] == 'GET') {
				$action_result = $this->showBkp();
			} elseif ($this->vars['method_marker'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveBkp();
			}
		}

		//Пользователи
		if ($this->vars['action'] == 'users') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showAdmins();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
			}
		}
		//Шаблоны
		if ($this->vars['action'] == 'templates') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showTemplates();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveTemplates();
			}
		}

		//Стили
		if ($this->vars['action'] == 'css') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showCSS();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveCSS();
			}
		}

		//JavaScript
		if ($this->vars['action'] == 'js') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showJS();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveJS();
			}
		}

		//Модули
		if ($this->vars['action'] == 'modules') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showModules();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveModules();
			}
		}
		//Модули
		if ($this->vars['action'] == 'modules_add') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->show_addModules();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->save_addModules();
			}
		}
		if ($this->vars['action'] == 'module_install') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->installModule();
			}
		}
		if ($this->vars['action'] == 'module_delete') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->deleteModule();
			}
		}

		//Дизайн
		if ($this->vars['action'] == 'themes') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showThemes();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveThemes();
			}
		}
		
		//Обновление ядра
		if ($this->vars['action'] == 'update') {
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$action_result = $this->showUpdate();
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$this->checkDemo();
				$action_result = $this->saveUpdate();
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
		$res['form_action']           = 'add';
		$res['content_template_file'] = 'edit_form.tpl';

		//Группы полей
		$res['groups'] = $this->getRecordFields($this->model->modules[$module], $structure_sid, false, false);

		foreach ($res['groups'] as $group_sid => $group)
			if (count($group['fields'])) {
				foreach ($group['fields'] as $i => $field) {
					$res['groups'][$group_sid]['fields'][$i]['editable'] = ($group_sid == 'system' ? false : true);
					if (!$res['groups'][$group_sid]['fields'][$i]['value']){
						if( IsSet($res['groups'][$group_sid]['fields'][$i]['default_value']) )
							$res['groups'][$group_sid]['fields'][$i]['value'] = $res['groups'][$group_sid]['fields'][$i]['default_value'];
						else
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
		$res['form_action']           = 'edit';
		$res['content_template_file'] = 'edit_form.tpl';
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
		$res['record'] 				  = $record;
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
		$res['form_action']           = 'settings';
		$res['content_template_file'] = 'edit_settings.tpl';

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
					$recs[$j]['sid'] = $rec['var'];
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
		if(IsSet( $this->vars['tmpl'] ))
			return $this->showTemplates_one();
		else
			return $this->showTemplates_all();
	}
	private function showTemplates_one(){
		
		list($module, $structure_sid) = explode( '_', substr( basename($this->vars['tmpl']), 0, strpos(basename($this->vars['tmpl']), '.') ) );
//		$module        = $this->model->ask->module;
//		$structure_sid = $this->model->ask->structure_sid;
		$record        = $this->model->ask->rec;

		$res['title']                 = 'Настройка шаблона '.$this->vars['tmpl'];
		$res['module']                = $module;
		$res['structure_sid']         = $structure_sid;
		$res['form_action']           = 'templates';
		$res['content_template_file'] = 'edit_form.tpl';

		$res['groups'] = array();
		
		$help_main = 'Шаблон создаётся на основе HTML и языка Smarty. Перед сохранением шаблон будет проверен, и если он содержит синтаксические ошибки - он не будет сохранён.
			<br /><br />
			<a href="#" OnClick="$j(\'#acms_help_vars\').toggle(\'fast\'); return false;">Переменные, доступные в шаблоне</a>
			<ol id="acms_help_vars" style="display:none;">
				<li>$content - текущая запись, на которой находится пользователь, массив</li>
				<li>$mainmenu - главное меню сайта, массив</li>
				<li>$original_url - полный url, запрашиваемый пользователем, строка</li>
				<li>$settings - настройки сайта, массив</li>
				<li>$paths - пути из файла конфигурации, массив</li>
				<li>$config - settings из файла конфигурации, массив</li>
				<li>$path - "Хлебные крошки", массив</li>
				<li>$domain - характеристики текущего домена, массив</li>
				<li>$user - данные текущего пользователя, массив</li>
				<li>$get_vars - переданные через $_GET параметры, массив</li>
			</ol>
			<br />
			<a href="#" OnClick="$j(\'#acms_help_preloads\').toggle(\'fast\'); return false;">Компоненты, доступные в шаблоне</a>
			<ol id="acms_help_preloads" style="display:none;">
				<li>recs - вывод записей из структуры, возможна разбивка на страницы</li>
				<li>anons - вывод последней записи из структуры</li>
				<li>anonslist - вывод последних записей из структуры</li>
				<li>random - вывод случайной записи из структуры</li>
				<li>randomlist - вывод случайных записей из структуры</li>
				<li>parent - родительский раздел для указанного</li>
			</ol>
			<br />
			<a href="#" OnClick="$j(\'#acms_help_smarty\').toggle(\'fast\'); return false;">Как писать шаблоны</a>
			<ul id="acms_help_smarty" style="display:none;">
				<li>Шаблоны делаются при помощи языка <a href="http://www.smarty.net/docsv2/ru/" target="_blank">Smarty</a></li>
				<li>Переменные доступны в виде {$variable} для строки или {$content.title} для элементов массива</li>
				<li>Обращение к компонентам происходит так: {preload module=news data=recs result=recs}, при этом результат выдаётся в массив {$recs}</li>
			</ul>
			';
/*		
		//Настройки для главной страницы не доступны
		if( $this->vars['tmpl'] != 'start_index.tpl'){
			$help_components='
				Компонент позволяет отображать некоторые выборки записей на странице.<br /><br />
				Вы можете включить на этой странице компоненты, которые будут обслуживать текущую запись.<br /><br />
				Компоненты будут доступны в качестве меню <strong>$menu_components</strong>, каждый компонент можно будет увидеть по адресу <strong>[record.url].[component_sid].html</strong><br /><br />
				Содержимое компонента будет доступно в <strong>{$content.component}</strong>
			';
			$help_interfaces='
				Интерфейс позволяет управлять одной или несколькими записями на странице.<br /><br />
				Вы можете включить на этой странице интерфейсы, которые будут обслуживать текущую запись<br /><br />
				Интерфейсы будут доступны в качестве меню <strong>$menu_interfaces</strong>, каждый интерфейс можно будет увидеть по адресу <strong>[record.url].[interface_sid].html</strong><br /><br />
				Чтобы интерфейс отобразился, после вывода {$content.text} должен быть подключен шаблон <strong>interface.tpl</strong><br /><br />
				Содержимое компонента будет доступно в <strong>{$content.interface}</strong>
			';

			//Загружаем настройки
			$settings = unserialize( file_get_contents( $this->model->config['path']['templates'].'/'.basename( $this->vars['tmpl'] ).'.cfg' ) );
			
			//Внутренние компоненты
			$components = array(
				'components_int' => array(
					'sid' => 'components_int',
					'title' => 'Внутренние компоненты',
					'type' => 'menum',
				),
				'components_ext' => array(
					'sid' => 'components_ext',
					'title' => 'Внешние компоненты',
					'type' => 'menum',
				),
			);

			//Внутренние интерфейсы
			$interfaces = array(
				'interfaces_int' => array(
					'sid' => 'interfaces_int',
					'title' => 'Внутренние интерфейсы',
					'type' => 'menum',
				),
				'interfaces_ext' => array(
					'sid' => 'interfaces_ext',
					'title' => 'Внешние интерфейсы',
					'type' => 'menum',
				),
			);

			//Внутренние компоненты и интерфейсы
			foreach($this->model->modules[ $module ]->prepares as $sid=>$component)if(!IsSet($component['hidden']))
				$components['components_int']['value'][] = array(
					'value' => $this->model->modules[ $module ]->info['prototype'].'|'.$sid,
					'title' => $this->model->modules[ $module ]->info['title'].' -> '.$component['title'],
					'selected' => @in_array( $this->model->modules[ $module ]->info['prototype'].'|'.$sid, $settings['components'] ),
				);
			foreach($this->model->modules[ $module ]->interfaces as $sid=>$interface)if(!IsSet($interface['hidden']))
				$interfaces['interfaces_int']['value'][] = array(
					'value' => $this->model->modules[ $module ]->info['prototype'].'|'.$sid,
					'title' => $this->model->modules[ $module ]->info['title'].' -> '.$interface['title'],
					'selected' => @in_array( $this->model->modules[ $module ]->info['prototype'].'|'.$sid, $settings['interfaces'] ),
				);
		
			//Внешние компоненты и интерфейсы
			foreach($this->model->modules as $module_sid => $mod)
				if($module != $module_sid){
					foreach($mod->prepares as $sid=>$component)if(!IsSet($component['hidden']))
						$components['components_ext']['value'][] = array(
							'value' => $mod->info['prototype'].'|'.$sid,
							'title' => $mod->info['title'].' -> '.$component['title'],
							'selected' => @in_array( $mod->info['prototype'].'|'.$sid, $settings['components'] ),
						);
					foreach($mod->interfaces as $sid=>$interface)if(!IsSet($interface['hidden']))
						$interfaces['interfaces_ext']['value'][] = array(
							'value' => $mod->info['prototype'].'|'.$sid,
							'title' => $mod->info['title'].' -> '.$interface['title'],
							'selected' => @in_array( $mod->info['prototype'].'|'.$sid, $settings['interfaces'] ),
						);
				}
		}
*/		
		$groups = array(
			'main'=> array(
				'title' => 'Код шаблона',
				'help' => $help_main,
				'fields' => array(
					'title' => array(
						'sid' => 'title',
						'type' => 'hidden',
						'value' => basename( $this->vars['tmpl'] ),
					),
					'html' => array(
						'title' => 'Исходный код',
						'sid' => 'html',
						'type' => 'html',
						'value' => @file_get_contents($this->model->config['path']['templates'].'/'.basename( $this->vars['tmpl'] )),
					),
				)
			),

			'components'=> array(
				'title' => 'Компоненты',
				'help' => $help_components,
				'fields' => $components,
			),

			'interfaces'=> array(
				'title' => 'Интерфейсы',
				'help' => $help_interfaces,
				'fields' => $interfaces,
			),
		);
		
		$res['groups'] = $groups;
		return $res;
	}
	private function showTemplates_all(){
		$res                          = array();
		$res['title']                 = 'Шаблоны сайта';
		$res['form_action']           = 'templates';
		$res['content_template_file'] = 'bar_templates.tpl';

		$recs = array();
		
		//Должно быть шаблонов
		$templates = array();
		foreach($this->model->modules as $module_sid=>$module){
			if($module->info['sid'])
				$recs[ $module->info['prototype'].'_index.tpl' ] = array(
					'id' => count($recs),
					'title' => $module->info['title'].' - главная страница',
					'file' => $module->info['prototype'].'_index.tpl',
				);
			else	
				$recs[ $module->info['prototype'].'_index.tpl' ] = array(
					'id' => count($recs),
					'title' => 'Главная страница сайта',
					'file' => 'start_index.tpl',
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
		$files = get_files($root, false, 0, 100000, false, 'tpl' );
		
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
	private function saveTemplates(){
		$path = $this->model->config['path']['tmp'].'/temp.tpl';
		file_put_contents($path, stripslashes( $this->vars['html'] ), LOCK_EX );

		//Подключаем шаблонизатор
		require_once($this->model->config['path']['core'] . '/classes/templates.php');
		$tmpl = new templater($this->model);
		$tmpl->assign('paths', $this->model->config['path']);

		//Создаём папку, если нет
		if( !file_exists( $this->model->config['path']['tmp'] ) ){
			mkdir( $this->model->config['path']['tmp'] );
			chmod( $this->model->config['path']['tmp'], '0775' );
		}
		
		//Проверяем корректность шаблона
		try {
			$ready_html = @$tmpl->fetch($path);
		} catch (Exception $e) {
			print('Шаблон содержит синтаксические ошибки.<br />');
			print('<textarea style="width:500px; height:400px;">'.stripslashes( $this->vars['html'] ).'</textarea>');
			pr($e);
			exit();
		}
		
		//Всё гуд - записываем
		$path = $this->model->config['path']['templates'].'/'.basename($this->vars['title']);
		$res = file_put_contents($path, stripslashes( $this->vars['html'] ), LOCK_EX);
		chmod($path, 0775);

		//Настройки компонентов и интерфейсов
		$settings = array(
			'components' => array_merge( (array)$this->vars['components_int'], (array)$this->vars['components_ext']),
			'interfaces' => array_merge( (array)$this->vars['interfaces_int'], (array)$this->vars['interfaces_ext']),
		);
		$path = $this->model->config['path']['templates'].'/'.basename($this->vars['title'].'.cfg');
		$res = file_put_contents($path, serialize($settings), LOCK_EX);
		chmod($path, 0775);
		
		header('Location: /');
		exit();
	}


	private function showCSS()
	{
		$res                          = array();
		$res['title']                 = 'Стили сайта';
		$res['form_action']           = 'css';
		$res['content_template_file'] = 'bar_css.tpl';

		$recs = array();
		
		$root = $this->model->config['path']['styles'].'/';
		$recs = get_files($root, false, 0, 100000, false);
		
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
			file_put_contents($path, stripslashes($this->vars['new_content']) );
		}
		
		foreach($this->vars['content'] as $file => $content){
			$path = $this->model->config['path']['styles'].'/'.basename($file);
			file_put_contents($path, stripslashes($content) );
		}

		header('Location: /');
		exit();
	}


	private function showJS()
	{
		$res                          = array();
		$res['title']                 = 'JavaScript сайта';
		$res['form_action']           = 'js';
		$res['content_template_file'] = 'bar_js.tpl';

		$recs = array();
		
		$root = $this->model->config['path']['javascript'].'/';
		$recs = get_files($root, false, 0, 100000, false);
		
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
			
			$str = $module->info;
			
			foreach($module->structure as $structure_sid=>$structure){
				$rows = $this->model->execSql('select * from `'.$module->getCurrentTable($structure_sid).'` order by `id` desc','getall');
				$str['structure'][$structure_sid] = array(
														'fields'=>$structure['fields'], 
														'recs' => $rows,
													);
			}
			
			$recs[ $module->info['prototype'] ] = $str;
		}
		
		if ($recs)
			$res['recs'] = $recs;
		
		$all = file('http://src.opendev.ru/modules.txt');
		foreach($all as $a){
			$t = explode('|', trim($a));
			if( !IsSet( $recs[$t[1]] ) )
				$res['recs_all'][] = array('title'=>$t[0], 'prototype'=>$t[1]);
		}
		
		$res['types'] = $this->model->types;
		
		$res['root_recs'] = $this->model->execSql('select * from `start_rec` where `dep_path_parent`="index" and `is_link_to_module`="" order by `left_key`','getall');
		
		return $res;
	}
	
	private function installModule()
	{
		$this->vars['module_id'] = basename($this->vars['module_id']);

		//Импортируем файл модуля
		$filename = $this->model->config['path']['modules'].'/'.$this->vars['module_id'].'.php';
		if( !file_exists($filename) ){
			$module_file = file_get_contents('http://src.opendev.ru/modules/get.php?m='.$this->vars['module_id'].'');
			file_put_contents($filename, $module_file);
			chmod($filename, 0775);
		}
				
		//Добавляем его в базу
		if( !in_array($this->vars['module_id'], $this->model->extensions['domains']->domain['modules']) ){
			//->modules
			$this->model->execSql('insert into `modules` set `sid`="'.$this->vars['module_id'].'", `prototype`="'.$this->vars['module_id'].'", `title`="'.mysql_real_escape_string($this->vars['module_title']).'", `ln`="1", `active`="1"','insert');
			//->tarifs
			$t = $this->model->execSql('select * from `tarifs` where `id`="'.$this->model->extensions['domains']->domain['tarif'].'"','getrow');
			$t = explode('|', $t['modules']);
			foreach($t as $i=>$ti)
				if(!$ti)
					UnSet($t[$i]);
			$t[] = $this->vars['module_id'];
			$this->model->execSql('update `tarifs` set `modules`="|0|'.implode('|',$t).'|" where `id`="'.$this->model->extensions['domains']->domain['tarif'].'" limit 1','update');
		}

		//Делаем стандартный шаблон
		if( !file_exists($this->model->config['path']['templates'].'/'.$this->vars['module_id'].'_index.tpl') ){
			$filename = $this->model->config['path']['templates'].'/'.$this->vars['module_id'].'_index.tpl';
			file_put_contents(
				$filename,
				file_get_contents($this->model->config['path']['templates'].'/start_content.tpl')
			);
			chmod($filename, 0775);
		}
		
		//Приклеиваем к разделу
		if($this->vars['to']){
			$record = $this->model->modules[0]->getRecordById('rec',$this->vars['to']);

			if( $record['is_link_to_module']!=$this->vars['module_id'] ){
				$record['is_link_to_module'] = $this->vars['module_id'];
				$record['sid'] = $this->vars['module_id'];
				UnSet($record['access']);//Педаль для Бага
				$url = $this->model->modules[0]->updateRecord('rec', $record);
			}
			
		//Создаём новый раздел
		}else{
			$record = array(
				'sid' => $this->vars['module_id'],
				'title' => $this->vars['module_title'],
				'url' => $this->vars['module_id'],
				'is_link_to_module' => $this->vars['module_id'],
				'dep_path_parent' => 'index',
				'date_public' => date("Y-m-d H:i:s"),
				'date_added' => date("Y-m-d H:i:s"),
				'date_modify' => date("Y-m-d H:i:s"),

				'ln' => 1,
				'domain' => $this->model->extensions['domains']->domain['id'],
				'shw' => 1,
				'show_in_menu' => 1,
			);
			$url = $this->model->modules[0]->addRecord('rec', $record);
		}
		
		//Инсталляция базы данных модуля
		$f = @file_get_contents('http://src.opendev.ru/modules/'.$this->vars['module_id'].'/install.sql.txt');
		if($f){
			$this->model->__construct($this->model->config, $this->model->log);
			$this->model->modules['catalog']->check();
			$sql = explode("\n", $f);
			foreach($sql as $q)
				$this->model->execSql($q, 'update');
		}

		//Первоначальное наполнение
		if( $this->vars['test_vals'] ){
			$f = @file_get_contents('http://src.opendev.ru/modules/'.$this->vars['module_id'].'/start.sql.txt');
			if($f){
				$this->model->__construct($this->model->config, $this->model->log);
				$this->model->modules['catalog']->check();
				$sql = explode("\n", $f);
				foreach($sql as $q)
					$this->model->execSql($q, 'insert');
			}
		}
		
		header('Location: '.$url);
		exit();
	}

	private function deleteModule()
	{
		$this->vars['module_id'] = basename($this->vars['module_id']);

		//Удаляем из раздела-ссылки
		$record = $this->model->execSql('select * from `start_rec` where `is_link_to_module`="'.$this->vars['module_id'].'" limit 1','getrow');
		if($record){
			//Удаляем раздел
			if($this->vars['delete_rec']){
				$this->model->modules[0]->deleteRecord('rec', $record);
				$url = '/';
			
			//Снимаем ссылку
			}else{
				$record['is_link_to_module'] = '';
				UnSet($record['access']);//Педаль для Бага
				$url = $this->model->modules[0]->updateRecord('rec', $record);
			}
		}
	
		//Удаляем из тарифа и модулей
		if( in_array($this->vars['module_id'], $this->model->extensions['domains']->domain['modules']) ){
			//->tarifs
			$t = $this->model->execSql('select * from `tarifs` where `id`="'.$this->model->extensions['domains']->domain['tarif'].'"','getrow');
			$t = explode('|', $t['modules']);
			foreach($t as $i=>$ti){
				if(!$ti)
					UnSet($t[$i]);
				if($ti == $this->vars['module_id'])
					UnSet($t[$i]);
			}
			$this->model->execSql('update `tarifs` set `modules`="|0|'.implode('|',$t).'|" where `id`="'.$this->model->extensions['domains']->domain['tarif'].'" limit 1','update');
			//->modules
			$this->model->execSql('delete from `modules` where `sid`="'.$this->vars['module_id'].'" and `prototype`="'.$this->vars['module_id'].'" limit 1','insert');
		}
		
		//Удаляем данных из базы данных
		if($this->vars['delete_data']){
			foreach($this->model->modules[ $this->vars['module_id'] ]->structure as $structure_sid=>$structure){
				$this->model->execSql('drop table `' . $this->vars['module_id'] . '_' . $structure_sid . '`','delete');
				pr($this->model->last_sql);
			}
		}
		
		//Удаляем файлы шаблонов
		if($this->vars['delete_templates'])
		if( file_exists($this->model->config['path']['templates'].'/'.$this->vars['module_id'].'_index.tpl') ){
			unlink($this->model->config['path']['templates'].'/'.$this->vars['module_id'].'_index.tpl');
			@unlink($this->model->config['path']['templates'].'/'.$this->vars['module_id'].'_list.tpl');
			@unlink($this->model->config['path']['templates'].'/'.$this->vars['module_id'].'_content.tpl');
		}
		
		//Удаляем файл модуля
		if($this->vars['delete_module'])
		if( file_exists($this->model->config['path']['modules'].'/'.$this->vars['module_id'].'.php') ){
			unlink($this->model->config['path']['modules'].'/'.$this->vars['module_id'].'.php');
		}

		header('Location: /'.$url);
		exit();
	}
	
	
	private function showThemes()
	{
		$res                          = array();
		$res['title']                 = 'Оформление сайта';
		$res['content_template_file'] = 'bar_themes.tpl';

		//Все темы
		$recs = array();
		$themes = file_get_contents('http://src.opendev.ru/themes.txt');
		$themes = explode("\n", $themes);
		foreach($themes as $theme)if(strlen($theme)){
			list($rec['id'], $rec['title'], $rec['date'], $rec['img'], $rec['preview']) = explode('|', $theme);
			
			if($id[0] != '#'){
				$recs[] = $rec;
			}
		}
		$res['recs_all'] = $recs;
		
		//Услановленные темы
		$recs = array();
		$path = $this->model->config['path']['templates'].'/../';
		
		if (is_dir($path)) {
			if ($dh = opendir($path)) {
				while (($file = readdir($dh)) !== false)
				if($file != '.')
				if($file != '..'){
					$recs[] = array('id'=>$file, 'title'=>$file); 
				}
				closedir($dh);
			}
		}
		$res['recs'] = $recs;
		
		return $res;
	}
	private function saveThemes()
	{
		pr_r($this->vars);
		header('Location: /');
		exit();
	}
	
	
	private function showUpdate()
	{
		$res                          = array();
		$res['title']                 = 'Обновление ядра сайта';
		$res['content_template_file'] = 'bar_update.tpl';

		include_once($this->model->config['path']['libraries'].'/core_update.php');
		$acms_core_update = new acms_core_update( $this->model );
		
		$res['update'] = $acms_core_update->checkUpdate();
		
		return $res;
	}
	private function saveUpdate()
	{
		include_once($this->model->config['path']['libraries'].'/core_update.php');
		$acms_core_update = new acms_core_update( $this->model );
		
		$result = $acms_core_update -> doUpdate($this->vars);
		
		print($result);
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

				//Вставляем значение, если есть в переданном массиве
				$field['value'] = $record[$field['sid']];
				//Добавляем поля в группы
				$groups[$field['group']]['fields'][] = $field;
				
				//Произвольные названия полей
				if( !IsSet( $groups[ $field['group'] ]['title'] ) )
					$groups[ $field['group'] ]['title'] = $field['group'];
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
		}
		
		//Статистика записи
		if( IsSet($groups['main']) ){
			$t = array(
				'index' => 'главная страница',
				'content' => 'страница записи',
				'list' => 'страница списка записей',
			);
			
			$groups['main']['help'] = '
				'.($record?'<a href="'.$this->model->ask->rec['url'].($this->model->ask->rec['sid'] != 'index'?'.html':'').'" target="_blank">Посмотреть на сайте</a><br />':'').'
				Модуль: '.$module->info['title'].'<br />
				'.(count($module->structure)>1?'Структура: '.$module->structure[ $structure_sid ]['title'].'<br />':'').'
				Прототип: '.$module->info['prototype'].'<br />
				Таблица данных: '.$module->getCurrentTable($structure_sid).'<br />
				Шаблон: <a href="#" OnClick="JavaScript: call2(\'admin\', \'templates\', \'/?tmpl='.$module->info['prototype'].'_'.$this->model->ask->output_type.'.tpl\'); return false;">'.$module->info['title'].' - '.$t[ $this->model->ask->output_type ].'</a><br />
				Файл шаблона: '.$module->info['prototype'].'_'.$this->model->ask->output_type.'.tpl<br />
			';
		}
		//SEO
		if( IsSet($groups['seo']) ){
			$groups['seo']['help'] = '
				Файл <a href="/sitemap.xml" target="_blank">sitemap.xml</a>, строится автоматически<br />
				Файл '.(IsSet($this->model->settings['robots'])?'<a href="/robots.txt" target="_blank">robots.txt</a>, меняется в настройках':'robots.txt отсутствует').'<br />
				Сайт <a href="http://yandex.ru/yandsearch?site='.$this->model->extensions['domains']->domain['host'].'" target="_blank">в Яндексе</a><br />
				Сайт <a href="http://www.google.ru/search?q=site:'.$this->model->extensions['domains']->domain['host'].'" target="_blank">в Google</a><br />
			';
		}
		
/*
		//Смотрим уже имеющиеся настройки
		$acms_settings = false;
		if($this->model->ask->rec['acms_settings'])
			$acms_settings = @unserialize( $this->model->ask->rec['acms_settings'] );
		
		$group_title = 'Интерфейсы';
		//Интерфейсы на текущей странице
		if( $module->interfaces ){
			
			$groups[ $group_title ]['title'] = $group_title;
			$groups[ $group_title ]['comment'] = 'Компоненты и интерфейсы, добавленные через это меню, будут отображаться сразу после полного текста записи.';
			
			//Проверка возможности использования интерфейсов
			$is_tmpl = file_exists( $this->model->config['path']['templates'].'/interface.tpl' );
			if( !$is_tmpl )
				$groups[ $group_title ]['warning'] = 'На сайте не найден шаблон interface.tpl, вместо него будет использован стандартный шаблон из ядра.';
			
			//Смотрим уже имеющиеся настройки
			//Перечисляем интерфейсы
			foreach( $module->interfaces as $interface_sid => $interface ){
			
				if( is_array($acms_settings['interface']) ){
					$value = $acms_settings['interface'][ $module->info['sid'].'|'.$interface_sid ]['shw'];
				}else{
					$value = false;
				}

				$groups[ $group_title ]['fields'][] = array(
					'type'	=>	'check',
					'sid'	=>	'interface['.$module->info['sid'].'|'.$interface_sid.'][shw]',
					'title'	=>	''.$interface['title'].' (интерфейс)',
					'value'	=>	$value,
					'default_value'	=>false,
				);		

			}
		}

		//Интерфейсы на текущей странице
		if( $this->model->ask->rec['is_link_to_module'] )
		if( $this->model->modules[ $this->model->ask->rec['is_link_to_module'] ]->interfaces){
			$module = $this->model->modules[ $this->model->ask->rec['is_link_to_module'] ];
			
			$groups[ $group_title ]['title'] = $group_title;
			$groups[ $group_title ]['comment'] = 'Компоненты и интерфейсы, добавленные через это меню, будут отображаться сразу после полного текста записи.';
			
			//Проверка возможности использования интерфейсов
			$is_tmpl = file_exists( $this->model->config['path']['templates'].'/interface.tpl' );
			if( !$is_tmpl )
				$groups[ $group_title ]['warning'] = 'На сайте не найден шаблон interface.tpl, вместо него будет использован стандартный шаблон из ядра.';
			
			//Перечисляем интерфейсы
			foreach( $module->interfaces as $interface_sid => $interface ){
				$groups[ $group_title ]['fields'][] = array(
					'type'	=>	'check',
					'sid'	=>	'interface['.$module->info['sid'].'|'.$interface_sid.'][shw]',
					'title'	=>	''.$interface['title'].' (интерфейс из зависимого модуля '.$module->info['title'].')',
					'value'	=>	@$acms_settings['interface'][ $module->info['sid'].'|'.$interface_sid ]['shw'],
					'default_value'	=>	false,
				);		
			}
		}
*/
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

	function checkDemo(){
	
		if($this->model->user->info['group'] == 'demo'){
		
			print('Вы находитесь в демонстрационном аккаунте. Данное действие вам не разрешено.');
			exit();
		
		}
	
	}
	
}

?>
