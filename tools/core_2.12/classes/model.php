<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Модель данных										*/
/*															*/
/*	Версия ядра 2.0											*/
/*	Версия скрипта 1.3										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 26 мая 2010 года							*/
/*															*/
/************************************************************/

class model
{
	//Здесь будем собирать ошибки
	public $keep_error = array();

	//Зарезервированные ссылки на системные модули
	public $reserved_modules_sids = array('users', 'email');

	//Поддерживаемые типы
	public $types = array('html', 'xml', 'pdf');

	function __construct($config, $log, $cache = false)
	{
		//Запоминаем конфигурацию
		$this->config = $config;

		//Система статистики
		$this->log        = $log;
		$this->log->model = $this;

		//Кеширование
		$this->cache = $cache;

		//Подключаем базы данных
		$this->loadDatabase($this);

		/*Системное сообщение*/
		$this->log->step('установлены соединения с БД');

		//Подгружаем подерживаемые типы данных
		$this->loadTypes();

		/*Системное сообщение*/
		$this->log->step('инициализированы типы данных');

		//Авторизация пользователя
		$this->authUser();

		/*Системное сообщение*/
		$this->log->step('пользователь авторизован');

		//Подключаем расширения модели
		$this->loadExtensions();

		/*Системное сообщение*/
		$this->log->step('загружены расширения');

		//Читаем и подключаем модули сайта
		$this->loadModules();
		
		/*Системное сообщение*/
		$this->log->step('загружены модули');

		//Загрузка настроек домена
		$this->loadSettings();

		/*Системное сообщение*/
		$this->log->step('загружены настройки домена');

		//Подключаем расширения модели
		$this->executeExtensions();

		/*Системное сообщение*/
		$this->log->step('расширения скорректировали модель');

		//Подключаем класс отправки email-сообщений
		$this->initEmail();

		/*Системное сообщение*/
		$this->log->step('подключен класс отправки email');

		//Подключаем класс фильтров
		$this->initFilters();

		/*Системное сообщение*/
		$this->log->step('подключен класс фильтров');

		//Проверка no-www
		$this->no_www_check();
		
		//Проверка таблиц в базе данных
		if ($this->config['settings']['modules_check'])
			foreach ($this->modules as $module)
				$module->check();

		//Анализ запроса
		$this->prepareAsk();
		
		/*Системное сообщение*/
		$this->log->step('разобран запрос пользователя');

	}



	//Подключение базы данных
	private function loadDatabase()
	{
		//Обработка ошибки отсутствия файла
		if (!file_exists($this->config['path']['core'] . '/classes/db.php'))
			$this->log->err('file_not_found', array(
				'path' => $this->config['path']['core'] . '/classes/db.php',
				'file' => 'model',
				'function' => 'loadDatabase',
				'vars' => $this->config
			));

		//Подключение файла
		if( !$this->db )
			$this->db = require($this->config['path']['core'] . '/classes/db.php');
	}

	//Подгружаем типы данных
	private function loadTypes()
	{
		$this->types = array();

		//Обработка ошибки отсутствия файла
		if (!file_exists($this->config['path']['core'] . '/classes/types/load.php'))
			$this->log->err('file_not_found', array(
				'path' => $this->config['path']['core'] . '/classes/types/load.php',
				'file' => 'model',
				'function' => 'loadTypes',
				'vars' => $this->config
			));

		//Загружаем библиотеку типов данных
		$supported_types = include($this->config['path']['core'] . '/classes/types/load.php');

		//Инициализируем типы данных
		foreach ($supported_types as $type_sid => $path) {
			//Обработка ошибки отсутствия файла
			if (!file_exists($this->config['path']['core'] . '/classes/types/' . $path))
				$this->log->err('file_not_found', array(
					'path' => $this->config['path']['core'] . '/classes/types/' . $path,
					'file' => 'model',
					'function' => 'loadTypes',
					'vars' => $this->config
				));

			//Подключение класса
			require_once($this->config['path']['core'] . '/classes/types/' . $path);

			//Инициализация типа
			$type_name              = 'field_type_' . $type_sid;
			$this->types[$type_sid] = new $type_name($this);
		}
	}

	//Подключение базы данных
	public function authUser()
	{
		//Обработка ошибки отсутствия файла
		if (!file_exists($this->config['path']['core'] . '/classes/user.php'))
			$this->log->err('file_not_found', array(
				'path' => $this->config['path']['core'] . '/classes/user.php',
				'file' => 'model',
				'function' => 'authUser',
				'vars' => $this->config
			));

		//Подключение класса
		require_once($this->config['path']['core'] . '/classes/user.php');

		//Инициализация пользователя
		$this->user = new user($this);
	}

	//Подключение модулей
	private function loadModules()
	{
		$this->modules = array();

		//Если установлено расширение доменов
		//в нём прописаны необходимые для домена модули
		if (IsSet($this->extensions['domains'])) {
			//Подгружаем ля них данные из базы
			$modules = $this->execSql('select * from `modules` where `sid` IN ("' . implode('", "', $this->extensions['domains']->domain['modules']) . '") and `active`=1 order by `pos`');

		//Если расширение не установлено - берём все модули из базы
		} else {
			//Загружаем из базы
			$modules = $this->execSql('select * from `modules` where `active`=1 order by `id`');
		}

		//Встроенный модуль разделов
		$default_module[] = array(
			'id' => false,
			'sid' => false,
			'prototype' => 'default',
			'path' => $this->config['path']['core'] . '/classes/default_module.php',
			'pos' => 0,
			'title' => 'Разделы',
			'ln' => 1,
			'active' => true
		);

		//Приклеиваем встроенный модуль разделов на первое место
		$modules = array_merge($default_module, $modules);

		//Читаем и подключаем модули сайта
		foreach ($modules as $module) {
			//Путь до файла модуля
			if (IsSet($module['path']))
				$module_path = $module['path'];
			else
				$module_path = $this->config['path']['modules'] . '/' . $module['prototype'] . '.php';

			//Обработка ошибки отсутствия файла
			if (!file_exists($module_path))
				$this->log->err('file_not_found', array(
					'path' => $module_path,
					'file' => 'model',
					'function' => 'loadModules',
					'vars' => $module
				));

			//Если файл найден
			if (file_exists($module_path)) {
				//Подключаем файл
				require_once($module_path);

				//Заводим модуль
				$module['url']  = '/' . $module['sid'];
				$module['path'] = $module_path;

				//Название для обхъекта модуля
				$n = $module['prototype'] . '_module';

				//Инициализируем модуль
				$this->modules[$module['sid']] = new $n($this, $module);
			}
			
			//Так как инициализация пользователя произошла раньше чем подгружены расширения и модули
			//Необходимо провести дополнительные действия с этим модулем
			if( ($module['prototype'] == 'users') and IsSet($this->extensions['graph']) ){
				$this->user->info['graph_top']=$this->modules[$module['sid']]->getGraphTop($this->user->info['id']);
				$this->user->info['graph_top_text']=implode('|', $this->user->info['graph_top']);
				
				//Учитываем когда пользователь последний раз авторизовывался
				if($this->user->info['id'])
					//Если в модуле предусмотрено такое поле
					if(IsSet($this->modules[$module['sid']]->structure['fields']['date_logged']))
						//Учитываем
						$this->model->execSql('update `'.$this->modules[$module['sid']]->getCurrentTable('rec').'` set `date_logged`=NOW() where `id`="'.$this->user->info['id'].'" limit 1','update');
			}
		}
	}

	//Подгрузка расширений к модулю
	public function loadExtensions()
	{
		$this->extensions = array();
		//Подгрузка библиотеки расширений к модулям, инициализация
		foreach ($this->config['extensions'] as $extention_sid => $filename) {
			//Подключаем файл библиотеки
			require_once($this->config['path']['extensions'] . '/' . $filename);
			//Создаём
			$name                             = 'extention_' . $extention_sid;
			$this->extensions[$extention_sid] = new $name($this);
		}
	}

	//Загрузка настроек домена
	public function loadSettings()
	{
		//Загружаем настройки домена
		$sql      = 'select * from `settings` where ' . $this->extensions['domains']->getWhere();
		$res      = $this->execSql($sql);
		$settings = array();
		foreach ($res as $r)
			$settings[$r['var']] = $this->types[$r['type']]->getValueExplode($r['value'], false, $res);
		$this->settings = $settings;
	}

	//Инициализация расширений к модулю
	public function executeExtensions()
	{
		foreach ($this->extensions as $extention_sid => $extention)
			$extention->execute();
	}

	//Динамический контроллер обработки интерфейса
	public function controlInterface($vars)
	{
		pr('controlInterface');
		pr_r($vars);
		exit();
	}




	//Загрузка запрашиваемых параметров
	public function prepareAsk()
	{
		//Разбираем что к чему в запросе
		require_once($this->config['path']['core'] . '/classes/ask.php');
		$this->ask = new ask($this);
	}




	//Подготовка данных для ввода в шаблонизатор
	public function prepareMainRecord($prefered_module = false)
	{

		$sql = array();
		$rec = false;
		foreach ($this->modules as $module_sid => $module)
			if (!$rec) {
				if ($module->structure)
					foreach ($module->structure as $structure_sid => $structure)
						if (!$rec) {
							//Условия поиска
							$where               = array();
							$where['and']['url'] = '`url`="' . mysql_real_escape_string($this->ask->rec['url']) . '"';
							if (!$this->user->info['admin'])
								$where['and']['shw'] = '`shw`=1';
							//Поиск
							$rec = $this->makeSql(
								array(
									'tables' => array( $module->getCurrentTable($structure_sid)	),
									'where' => $where
								), 
								'getrow',
								'system',
								false
							);
							
							if ($rec) {
								$found_module_sid            = $module_sid;
								$found_structure_sid         = $structure_sid;
								$this->ask->module           = $found_module_sid;
								$this->ask->structure_sid    = $structure_sid;
								$this->ask->rec['structure'] = $structure_sid;
								if ($structure_sid != 'rec')
									$this->ask->output_type = 'list';
							}
						}
			}
		if ($rec) {
			//Пишем основную запись в ASK, пускай другие модули пользуют
			$this->ask->rec = array_merge($this->ask->rec, $rec);
			//Разворачиваем значения записи перед выводом в браузер
			$rec            = $this->modules[$found_module_sid]->explodeRecord($rec, $found_structure_sid);
			//Дополнительная обработка записи при типе вывода "content"
			$rec            = $this->modules[$found_module_sid]->contentPrepare($rec);
			//Вставляем окончание .html
			$rec            = $this->modules[$found_module_sid]->insertRecordUrlType($rec);
		}
		
		//Время выполнения
		$t         = explode(' ', microtime());
		$time_stop = $t[1] + $t[0];

		//Нашли
		if (is_array($rec)) {
		
			//Если ссылка - перенаправляем в модуль - сразу в главную страницу модуля
			if ( $rec['is_link_to_module'] and ( in_array($this->ask->method, array('get','xhr','tree')) ) ){// or ($this->ask->original_url != $rec['url'])) ) {

				$prefered_module        = $rec['is_link_to_module'];
				$this->ask->module      = $prefered_module;
				$this->ask->output_type = 'index';

				//В модуле указываем что для него есть ссылка.
				//Этот URL будет участвовать в построении URL`ов для входящих в модуль элементов
				$this->modules[$prefered_module]->info['url'] = '/' . $this->ask->url[0] . ($this->ask->url[1] ? '/' . $this->ask->url[1] : '');

				//Переразбираем запрос по URL (за вычетом ссылки) в новом модуле
				$u = $this->ask->url;
				UnSet($u[0]);
				$u = '/' . implode('', $u);

				$this->ask->readParts($this, '/', $prefered_module);

				//Ищем запись в этом модуле
				if (is_object($this->modules[$prefered_module]))
					$rec = $this->modules[$prefered_module]->getRecordByAsk();

				$found_rec = $rec;

			} else {
				$found_rec = $rec;
			}

			
		//Ничего не найдено - продолжаем искать (перебираем части URL, без перенаправления)
		} elseif (!$rec) {
			//Перебираем все подразделы - ищем ссылку на другой модуль внутри дерева
			$sub_url  = false;
			$link_url = false;
			for ($i = count($this->ask->url) - 1; $i > 0; $i--)
				if (!$rec) {
					//Забираем URL раздела - одного из родителей
					$u = array_chunk($this->ask->url, $i);
					$u = $u[0];
					
					//Переразбираем URL
					$this->ask->readParts($this, '/' . implode('/', $u) . (count($this->ask->rec['mode']) ? '.' . implode('.', $this->ask->rec['mode']) : '') . '.' . $this->ask->output, $prefered_module);
					//Получаем запись
					$rec = $this->modules[$prefered_module]->getRecordByAsk();

					//Если запись получили - запоминаем оставшиёся URL
					if ($rec) {
						//Нашли
						$found_rec = $rec;

						$sub_url  = array_slice($this->ask->full_url, $i);
						$link_url = array_slice($this->ask->full_url, 0, $i);
						if (count($sub_url)) {
							$sub_url                = '/' . implode('/', $sub_url) . (count($this->ask->rec['mode']) ? '.' . implode('.', $this->ask->rec['mode']) : '') . '.' . $this->ask->output;
							$this->ask->output_type = 'index';
						}
					}
				}

			//Если нашли ссылку
			if (IsSet($found_rec['is_link_to_module']))
				if (IsSet($this->modules[$found_rec['is_link_to_module']])) {
					//Переключаемся в новый модуль
					$prefered_module   = $found_rec['is_link_to_module'];
					$this->ask->module = $prefered_module;

					//В модуле указываем что для него есть ссылка.
					//Этот URL будет участвовать в построении URL`ов для входящих в модуль элементов
					$this->modules[$prefered_module]->info['url'] = '/' . $this->ask->url[0] . ($this->ask->url[1] ? '/' . $this->ask->url[1] : '');

					//Переразбираем запрос по URL (за вычетом ссылки) в новом модуле
					$this->ask->readParts($this, $sub_url, $prefered_module);

					//Ищем запись в этом модуле
					$found_rec = $this->modules[$prefered_module]->getRecordByAsk();
				}
		}

		//Всё равно страница не найдена - выводим ошибку 404
		if (!$found_rec) {
			return '404 not found';
		}

		//Проверяем сходится ли найденная запись с зарпашиваемым URL`ом
		if (!$this->config['settings']['approximate_check_disabled'])
			if ($found_rec['url'] != '/' . implode('/', $this->ask->full_url) . (count($this->ask->full_url) ? '.' . $this->ask->output : '')) {
				header('301 Moved Permanently');
				header('Location: ' . $found_rec['url']);
				exit();
				/*
				pr('Запрашиваемый URL не идентичен найденной записи');
				pr($found_rec['url']);
				pr_r($this->ask->full_url);

				//pr_r($found_rec);

				$this->ask->approximate=true;
				*/
			}

		return $found_rec;
	}

	//Подготовка данных для ввода в шаблонизатор
	public function prepareMainMenu($levels_to_show)
	{
		//Рекурсивная функция по расставлению статусов
		function setOpenAndActiveStatus($recs, $current_user_url)
		{
			if ($recs)
				foreach ($recs as $i => $rec) {
					//Чистый url - без окончания
					if (substr_count($rec['url'], '.'))
						$url = substr($rec['url'], 0, strpos($rec['url'], '.'));
					else
						$url = $rec['url'];

					//Главная страница
					if ($rec['url'] == '/')
						$rec['url'] = '';

					//Пользователь сейчас здесь
					if ($current_user_url == $rec['url']) {
						$recs[$i]['show_subs'] = true;
						$recs[$i]['show_link'] = false;
						$recs[$i]['mark']      = true;
						if (IsSet($rec['sub']))
							$recs[$i]['sub'] = setOpenAndActiveStatus($rec['sub'], $current_user_url);

						//Пользователь в подразделе текущей записи
					} elseif (substr_count($current_user_url, $url)) {
						$recs[$i]['show_subs'] = true;
						$recs[$i]['show_link'] = true;
						$recs[$i]['mark']      = false;
						if (IsSet($rec['sub']))
							$recs[$i]['sub'] = setOpenAndActiveStatus($rec['sub'], $current_user_url);

						//Обычный пункт меню
					} else {
						$recs[$i]['show_subs'] = false;
						$recs[$i]['show_link'] = true;
						$recs[$i]['mark']      = false;
					}

				}
			return $recs;
		}

		//Получаем дерево
		$recs = $this->prepareShirtTree(false, 'rec', false, $levels_to_show, array(
			'and' => array(
				'`show_in_menu`=1',
				'`shw`=1'
			)
		));
		
		//Расславляем статусы открытия и активности
		$recs = setOpenAndActiveStatus($recs, $this->ask->original_url);

		return $recs;
	}

	//Готовим путь на сайте, основываясь на разобранных данных запроса ASK
	public function prepareModelPath()
	{
		$path = array();

		//Текущий модуль и его структура
		$current_module = false;
		$structure_sid  = 'rec';

		//Перебираем все части url
		foreach ($this->ask->full_url as $i => $url) {
			//Получаем все основные поля модуля
			$fields = $this->modules[$current_module]->getMainFields($structure_sid);
			//Если коренной модуль - отслеживаем перенаправление на другой модуль
			if (!$current_module)
				$fields[] = 'is_link_to_module';
			//Если уже не
			//Получаем запись
			$rec = $this->makeSql(array(
				'tables' => array(
					$this->modules[$current_module]->getCurrentTable($structure_sid)
				),
				'fields' => $fields,
				'where' => array(
					'and' => array(
						'`sid`="' . mysql_real_escape_string($url) . '"'
					)
				)
			), 'getrow');
			
			//Дочерние структуры записей
			if( ($structure_sid != 'rec') and (!$rec) ){
				$rec = $this->makeSql(array(
					'tables' => array(
						$this->modules[$current_module]->getCurrentTable('rec')
					),
					'fields' => $fields,
					'where' => array(
						'and' => array(
							'`sid`="' . mysql_real_escape_string($url) . '"'
						)
					)
				), 'getrow');
			}
			
			//Записываем
			$path[$i] = $rec;

			//Если коренной модуль - отслеживаем перенаправление на другой модуль
			if ( strlen($rec['is_link_to_module'])>0 ) {
				//Меняем текущий модуль
				$current_module = $rec['is_link_to_module'];

				//Определяем его текущую структуру
				$tree           = array_reverse($this->modules[$current_module]->getLevels('rec', array()));
				$tree_index     = -1;
				$structure_sid  = $tree[$tree_index];

				//Если имела место смена модуля
				if ($current_module) {
					//Следующая структура
					$tree_index++;
					//Запоминаем
					$structure_sid = $tree[$tree_index];
				}
			}


		}

		//Вставляем окончания к URL`ам
		$path = $this->modules[$current_module]->insertRecordUrlType($path);

		//Готово
		return $path;
	}


	//Создание краткого дерева модели
	public function prepareShirtTree($module_sid = false, $structure_sid = 'rec', $root_record_id, $levels_to_show, $conditions = array('and' => array('`shw`=1')))
	{
		$recs = $this->modules[$module_sid]->getModuleShirtTree($root_record_id, $structure_sid, $levels_to_show, $conditions);
		return $recs;
	}

	//Добавление записи в структуру модуля
	public function addRecord($module, $structure_sid, $values)
	{
		//Режим демонстрации
		if ($this->config['settings']['demo_mode']) {
			print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
			exit();
		}

		//Дополняем системным полем
		if (!IsSet($values['domain']))
			$values['domain'] = '|' . $this->extensions['domains']->domain['id'] . '|';
		if (!IsSet($values['ln']))
			$values['ln'] = '1';
		if (!IsSet($values['date_public']))
			$values['date_public'] = date("Y-m-d H:i:s");
		if (!IsSet($values['date_added']))
			$values['date_added'] = date("Y-m-d H:i:s");

		if (!$module)
			$module = 0;
		$url = $this->modules[$module]->addRecord($structure_sid, $values);

		//Если системный модуль - перебрасываем на главную страницу
		if (in_array($module, $this->reserved_modules_sids) && $module)
			return '/';
		//Иначе на страницу, которую вернул сам модуль
		else
			return $url;
	}

	//Добавление записи в структуру модуля
	public function updateRecord($module, $structure_sid, $values, $conditions = false)
	{
		//Режим демонстрации
		if ($this->config['settings']['demo_mode']) {
			print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
			exit();
		}

		if (!$module)
			$module = 0;
			
		$url = $this->modules[$module]->updateRecord($structure_sid, $values, $conditions);
		if (!strlen($url))
			$url = '/';

		//Если системный модуль - перебрасываем на главную страницу
		if (in_array($module, $this->reserved_modules_sids) && $module)
			return '/';
		//Иначе на страницу, которую вернул сам модуль
		else
			return $url;
	}

	//Добавление записей в структуру модуля, с проверкой существования этих записей (проверка по SID)
	public function importRecords($module, $structure_sid, $records, $check_unique_field = 'sid', $conditions = false)
	{
		//Режим демонстрации
		if ($this->config['settings']['demo_mode']) {
			print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
			exit();
		}

		//Собираем значения уникального поля записей
		$unique = array();
		foreach ($records as $rec)
			$unique[] = $rec[$check_unique_field];

		//Переданные условия
		$where=$conditions;
			
		//Условия выборки уникальности
		$where[] = '`' . mysql_real_escape_string($check_unique_field) . '` IN ("' . implode('", "', $unique) . '")';
		
		//Если на домене открыто изменение домена - импорт производится без учёта данных текущего домена
		if($this->config['settings']['domain_switch'])
			$where['domain']=false;
			
		//Забираем записи которые уже существуют, их будем обновлять
		$update_recs = $this->makeSql(array(
			'tables' => array(
				$this->modules[$module]->getCurrentTable($structure_sid)
			),
			'fields' => array(
				$check_unique_field,
				'id'
			),
			'where' => array(
				'and' => $where,
			)
		), 'getall');
		
		//Уникальные поля этих записей
		$update = array();
		foreach ($update_recs as $rec)
			$update[$rec[$check_unique_field]] = $rec['id'];

		//Заливаем записи
		foreach ($records as $record) {
			//Запись подлежит обновлению
			if (IsSet($update[$record[$check_unique_field]])) {
				//Вставляем ID существующей записи
				$record['id'] = $update[$record[$check_unique_field]];
				//Обновляем
				$this->updateRecord($module, $structure_sid, $record, $conditions);

				//Запись подлежит добавлению
			} else {
				//Добавляем
				$this->addRecord($module, $structure_sid, $record);
			}
		}
	}


	//Выполнить готовый запрос к базе данных
	public function execSql($sql, //готовый sql-запрос
		$query_type = 'getall', //варианты: getraw, getall, insert, update, delete
		$database = 'system', //нужная база данных.
		$no_cache = false		//Не использовать кеш запроса
		)
	{
		//Засекаем время выполнения запроса
		$t         = explode(' ', microtime());
		$sql_start = $t[1] + $t[0];
/*
		if( $no_cache )
			pr('NO_CACHE: '.$sql.'|'.$query_type);
*/			
		//Используется кеширование - запрашиваем кеш
        $result = false;

        if( $this->config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
            $result = $this->cache->load( $sql.'|'.$query_type, $this->config['cache']['cache_timeout'] );
        }//pr($sql);

        //Если кеша не найдено - собираем все данные заново
        if($result === false){

//			pr('sql:exec ['.$sql.'|'.$query_type.']');
		
            //Получение одной записи
            if ($query_type == 'getrow') {
                $result = $this->db[$database]->GetRow($sql);

                //Получение списка данных
            } elseif ($query_type == 'getall') {
                $result = $this->db[$database]->GetAll($sql);

                //Обновление данных
            } elseif ($query_type == 'update') {
                //			pr($sql);
                if ($this->config['settings']['demo_mode']) {
                    print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
                    exit();
                } else {
                    $result = $this->db[$database]->Execute($sql);
                }

                //Вставка данных
            } elseif ($query_type == 'insert') {
                //			pr($sql);
                if ($this->config['settings']['demo_mode']) {
                    print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
                    exit();
                } else {
                    $result = $this->db[$database]->Execute($sql);
                }

                //Удаление данных
            } elseif ($query_type == 'delete') {
                if ($this->config['settings']['demo_mode']) {
                    print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
                    exit();
                } else {
                    $result = $this->db[$database]->Execute($sql);
                }
            }

            //Используется кеширование - записываем результат
            if( $this->config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
                $this->cache->save( $result, $sql.'|'.$query_type );
            }
        }else{
//			pr('sql:cache');
		}

		//Сколько прошло
		$t        = explode(' ', microtime());
		$sql_stop = $t[1] + $t[0];
		$time     = $sql_stop - $sql_start;

		//Статистика
		$this->log->sql($sql, $time, $result, $query_type, $database);

		//Запоминаем последний запрос
		$this->last_sql = $sql;

		//Готово
		return $result;
	}

	//Подготовить запрос к базе данных на основе предоставленных характеристик
	public function makeSql(
		$sql_conditions = array('fields' => array(), 'tables' => array(), 'where' => array(), 'group' => array(), 'order' => '', 'limit' => false), //массив условий для sql-запроса
		$query_type = 'getall', //варианты: getrow, getall, insert, update, delete
		$database = 'system', 	//нужная база данных.
		$no_cache = false		//Не использовать кеш запроса
		)
	{
		//Обработка условий расширениями
		if ($this->extensions)
			foreach ($this->extensions as $ext)
				list($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit']) = $ext->onSql($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit'], $query_type);

		//Что запрашиваем
		if ($query_type == 'getrow' or $query_type == 'getall') {
			
			//Не указано что запрашивать - забираем всё
			if( !is_array( $sql_conditions['fields'] ) ){
				$fields = '*';
			
			//Указано что конкретно забирать			
			}else{
				//Склеиваем с ковычками
				$fields = '';
				foreach($sql_conditions['fields'] as $field){
					if($fields != '')$fields .= ', ';
					
					if(substr_count($field, ' as '))
						$fields .= $field;
					else
						$fields .= '`'.$field.'`';
						
				}
			}

		} elseif ($query_type == 'insert' or $query_type == 'update') {
			//Склеиваем без ковычек
			$fields = implode(', ', $sql_conditions['fields']);
		}

		//Доступ
		if (!IsSet($sql_conditions['where']['and']['access']))
			$sql_conditions['where']['and']['access'] = $this->checkAccess();

		//Условия
		if ($sql_conditions['where']) {
			if (is_array($sql_conditions['where'])) {
				$res = '';
				foreach ($sql_conditions['where'] as $logic => $vars) {
					$res_logic = '';
					if (is_array($vars))
						foreach ($vars as $i => $val)
							if (strlen($val)) {
								if (strlen($res_logic))
									$res_logic .= ' ' . $logic . ' ';
								$res_logic .= $val;
							}
					if (strlen($res))
						$res .= ' ' . $logic . ' ';
					$res .= '(' . $res_logic . ')';
				}
				$where = $res;
			}
		}

		//Таблицы
		$tables = '`'.implode('`, `', $sql_conditions['tables']).'`';

		//Таблицы
		$order = $sql_conditions['order'];

		//Ограничения
		$limit = $sql_conditions['limit'];

		//Получение одной записи
		if ($query_type == 'getrow') {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' limit 1';

			//Получение списка данных
		} elseif ($query_type == 'getall') {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' ' . $limit . '';

			//Обновление данных
		} elseif ($query_type == 'update') {
			$sql = 'update ' . $tables . ' set ' . $fields . ' where ' . $where . ' ' . $limit . '';

			//Вставка данных
		} elseif ($query_type == 'insert') {
			$sql = 'insert into ' . $tables . ' set ' . $fields . '';

			//Удаление данных
		} elseif ($query_type == 'delete') {
			$sql = 'delete from ' . $tables . ' where ' . $where . '';
		}

		//Режим демонстрации
		if ($this->config['settings']['demo_mode'] and (in_array($query_type, array(
			'update',
			'insert',
			'delete'
		)))) {
			print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
			exit();
		} else {
			//Выполняем запрос
			$result = $this->execSql($sql, //готовый sql-запрос
				$query_type, 	//варианты: getraw, getall, insert, update, delete
				$database, 		//нужная база данных.
				$no_cache		//Не использовать кеш запроса
			);
		}

		return $result;
	}

	//Получить список групп пользователей сайта
	public function getUserGroups()
	{
		$groups          = array();
		//Системные группы
		$groups['admin'] = array(
			'title' => 'Администраторы',
			'field' => 'admin',
			'value' => true
		);
		$groups['moder'] = array(
			'title' => 'Модераторы',
			'field' => 'moder',
			'value' => true
		);
		//Пользователи
		$user_groups     = $this->execSql('select distinct `group` as `title`, "group" as `field`, `group` as `value` from `users` where (not(`group`="")) and ' . $this->extensions['domains']->getWhere() . ' order by `group`', 'getall');
		//Объединяем
		if (is_array($user_groups))
			foreach ($user_groups as $group)
				$groups[$group['title']] = $group;
		//		$groups=array_merge($groups,$user_groups);
		//Другие пользователи
		$groups['all'] = array(
			'title' => 'Все остальные',
			'field' => 'all',
			'value' => true
		);
		//Готово
		return $groups;
	}

	//Вставить проверку доступа
	public function checkAccess()
	{
		$groups = array();
		//Определяем группу
		if ($this->user->info['admin'])
			$groups[] = 'admin';
		if ($this->user->info['moder'])
			$groups[] = 'moder';
		if (strlen($this->user->info['group']))
			$groups[] = $this->user->info['group'];
		//Гость
		if (!count($groups))
			$groups[] = 'all';

		//Формируем запрос
		$where = array();
		foreach ($groups as $group)
			$where[] = '(`access` LIKE "%|' . $group . '=r__|%")';

		//Запрос
		$sql = '(' . implode(' or ', $where) . ')';

		//		pr($sql);

		//Готово
		return $sql;
	}

	//Получить SID модуля, установленный в системе, зная его прототип
	public function getModuleSidByPrototype($prototype)
	{
		//Перебираем все установленные модули
		foreach ($this->modules as $module_sid => $module)
			//Ищем указанный
			if ($module->info['prototype'] == $prototype) {
				//Возвращаем его SID
				if($return_module)
					return $module;
				//Возвращаем весь модуль
				else
					return $module_sid;
			}
		return false;
	}
	//Получить SID модуля, установленный в системе, зная его прототип
	public function getModuleByPrototype($prototype)
	{
		//Возвращаем целый модуль
		return $this->modules[$this->getModuleSidByPrototype($prototype)];
	}

	//Проверка no-www
	private function no_www_check(){
		if( $this->config['settings']['no_www'] )
		if( substr_count($_SERVER['HTTP_HOST'], 'www.') ){
			$new_host = str_replace('www.','',$_SERVER['HTTP_HOST']);
			header ('HTTP/1.1 301 Moved Permanently');
			header ('Location: http://'.$new_host.$_SERVER['REQUEST_URI']);
			exit();
		}
	}
	
	//инициализируем спец.библиотеку для работы с файлами Microsoft Excel
	public function initExcel()
	{
		//Загружаем класс
		include_once($this->config['path']['libraries'] . '/excel/excel.php');
		//Инициализируем
		return new excel($this->config['path']);
	}

	//инициализируем спец.библиотеку для работы с файлами Zip
	public function initZip()
	{
		//Загружаем класс
		include_once($this->config['path']['libraries'] . '/zipper.php');
		//Инициализируем
		return new zipper($this->config['path']);
	}

	//инициализируем спец.класс для отправки email
	public function initEmail()
	{
		//Загружаем класс
		include_once($this->config['path']['core'] . '/classes/email.php');
		//Инициализируем
		$this->email = new email( $this );
	}

	//инициализируем фильтры
	public function initFilters()
	{
		//Загружаем класс
		include_once($this->config['path']['core'] . '/classes/filters.php');
		//Инициализируем
		$this->filters = new filters($this);
	}

	//инициализируем rss - ридер
	public function initRss()
	{
		//Загружаем класс
		include_once($this->config['path']['libraries'] . '/rss.php');
		//Инициализируем
		return new rss($this->config['path']);
	}

}

?>