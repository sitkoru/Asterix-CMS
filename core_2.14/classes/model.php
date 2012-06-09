<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Модель данных										*/
/*															*/
/*	Версия ядра 2.14										*/
/*	Версия скрипта 2.0										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 15 декабря 2011 года						*/
/*															*/
/************************************************************/

class model{

	public static $config;
	public static $db;
	public static $types;
	public static $modules;
	public static $extensions;
	public static $settings;
	public static $ask;
	public static $last_sql;
	public static $active_database = 'system';

	function __construct($config, $log, $cache = false){

		require_once($config['path']['core'] . '/classes/model_loader.php');
		require_once($config['path']['core'] . '/classes/model_sql.php');
		require_once($config['path']['core'] . '/classes/model_finder.php');
		require_once($config['path']['core'] . '/classes/user.php');

		$this->log        = 	$log;
		$this->log->model = 	$this;
		$this->cache = $cache;

		self::$config = 		ModelLoader::loadConfig( $config );
		self::$db = 			ModelLoader::loadDatabase( $this );
		self::$types = 			ModelLoader::loadTypes();
		self::$modules =     	ModelLoader::loadModules();
		self::$extensions = 	ModelLoader::loadExtensions();
		self::$settings = 		ModelLoader::loadSettings();
		self::$ask = 			ModelLoader::loadAsk();
		self::$ask->rec = 		ModelFinder::getRecordByAsk( self::$ask->url );

		ModelLoader::makeBackCompatible();

		$this->unittest_modules();
		$this->check_no_www();
		$this->authUser();

		
	}


	//Подключение базы данных
	public function authUser(){
		$this->user = new user($this);
	}

	//Загрузка дополнительных элементов к записи
	private function loadRecordSettings($found_rec){
		//Раскрытие настроек записи, если есть
		if( $found_rec['acms_settings'] ){
			$acms_settings = unserialize($found_rec['acms_settings']);
			
			//Подсоединяем все интерфейсы
			if( is_array($acms_settings['interface']) )
			foreach($acms_settings['interface'] as $interface_sid => $interface)
			if( $interface['shw'] ){
				list($module_sid, $interface_sid) = explode('|', $interface_sid);
				
				//Загружаем интерфейс
				$params = array(
					'id' => self::$ask->rec['id'],
				);
				$result = self::$modules[$module_sid]->prepareInterface($interface_sid, $params);
				if( $result ){
					$found_rec['acms_interface'][$interface_sid] = $result;
					$found_rec['acms_interface'][$interface_sid]['sid'] = $interface_sid;
					$found_rec['acms_interface'][$interface_sid]['url'] = str_replace('.html', '.'.$interface_sid.'.html', $found_rec['url']);
				}
			}
		}
		return $found_rec;
	}

	//Подготовка данных для ввода в шаблонизатор
	public function prepareMainMenu($levels_to_show){
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
		$recs = $this->prepareShirtTree('start', 'rec', false, $levels_to_show, array(
			'and' => array(
				'`show_in_menu`=1',
				'`shw`=1'
			)
		));
		
		//Расславляем статусы открытия и активности
		$recs = setOpenAndActiveStatus($recs, self::$ask->original_url);

		return $recs;
	}

	//Готовим путь на сайте, основываясь на разобранных данных запроса ASK
	public function prepareModelPath(){
		$path = array();

		//Текущий модуль и его структура
		$current_module = 'start';
		$structure_sid  = 'rec';

		//Перебираем все части url
		if( is_array(self::$ask->url) )
		foreach (self::$ask->url as $i => $url)
		if( IsSet(self::$modules[$current_module]) ){
		
			//Получаем все основные поля модуля
			$fields = self::$modules[$current_module]->getMainFields($structure_sid);
			//Если коренной модуль - отслеживаем перенаправление на другой модуль
			if (!$current_module)
				$fields[] = 'is_link_to_module';
			//Если уже не
			//Получаем запись
			$rec = $this->makeSql(array(
				'tables' => array(
					self::$modules[$current_module]->getCurrentTable($structure_sid)
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
						self::$modules[$current_module]->getCurrentTable('rec')
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
				if( IsSet(self::$modules[$current_module]) ){
					$tree           = array_reverse(self::$modules[$current_module]->getLevels('rec', array()));
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


		}

		//Вставляем окончания к URL`ам
		if( IsSet(self::$modules[$current_module]) )
			$path = self::$modules[$current_module]->insertRecordUrlType($path);

		//Готово
		return $path;
	}

	//Создание краткого дерева модели
	public function prepareShirtTree($module_sid = 'start', $structure_sid = 'rec', $root_record_id = false, $levels_to_show = 2, $conditions = array('and' => array('`shw`=1'))){
		if( is_object( self::$modules[$module_sid] ) )
			return self::$modules[$module_sid]->getModuleShirtTree($root_record_id, $structure_sid, $levels_to_show, $conditions);
	}

	//Добавление записи в структуру модуля
	public function addRecord($module_sid = 'start', $structure_sid, $values){
		return self::$modules[$module_sid]->addRecord($values, $structure_sid);
	}

	//Добавление записи в структуру модуля
	public function editRecord($module_sid = 'start', $structure_sid, $values, $conditions = false){
		return self::$modules[$module_sid]->editRecord($values, $structure_sid, $conditions);
	}
	//Старое название функции
	public function updateRecord($module_sid = 'start', $structure_sid, $values, $conditions = false){
		return self::$modules[$module_sid]->editRecord($values, $structure_sid, $conditions);
	}


	//Добавление записей в структуру модуля, с проверкой существования этих записей (проверка по SID)
	public function importRecords($module, $structure_sid, $records, $check_unique_field = 'sid', $conditions = false){

		//Собираем значения уникального поля записей
		$unique = array();
		foreach ($records as $rec)
			$unique[] = $rec[ $check_unique_field ];

		//Условия
		$where=$conditions;
		$where[] = '`' . mysql_real_escape_string($check_unique_field) . '` IN ("' . implode('", "', $unique) . '")';
		
		//Если на домене открыто изменение домена - импорт производится без учёта данных текущего домена
		if(self::$config['settings']['domain_switch'])
			$where['domain']=false;
			
		//Забираем записи которые уже существуют, их будем обновлять
		$update_recs = self::makeSql(array(
			'tables' => array(
				self::$modules[$module]->getCurrentTable($structure_sid)
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
			$update[ $rec[ $check_unique_field ] ] = $rec['id'];

		//Заливаем записи
		foreach ($records as $record) {
			//Запись подлежит обновлению
			if ( IsSet( $update[ $record[ $check_unique_field ] ] ) ) {
				//Вставляем ID существующей записи
				$record['id'] = $update[ $record[ $check_unique_field ] ];
				//Обновляем
				self::editRecord($module, $structure_sid, $record, $conditions);

				//Запись подлежит добавлению
			} else {
				//Добавляем
				self::addRecord($module, $structure_sid, $record);
			}
		}
	}

	//Выполнить готовый запрос к базе данных
	public function execSql($sql, $query_type = 'getall', $database = 'system', $no_cache = false){
		return ModelSql::execSql($sql, $query_type, $database, $no_cache);
	}

	//Подготовить запрос к базе данных на основе предоставленных характеристик
	public function makeSql($sql_conditions, $query_type = 'getall', $database = 'system', $no_cache = false	){		
		return ModelSql::makeSql($sql_conditions, $query_type, $database, $no_cache);
	}

	//Получить SID модуля, установленный в системе, зная его прототип
	public function getModuleSidByPrototype($prototype){
		foreach (self::$modules as $module_sid => $module)
			if ($module->info['prototype'] == $prototype)
				return $module_sid;
	}

	//Получить SID модуля, установленный в системе, зная его прототип
	public function getModuleByPrototype($prototype){
		return self::$modules[ $this->getModuleSidByPrototype($prototype) ];
	}

	//Проверка no-www
	private function check_no_www(){
		if( model::$settings['no_www'] ){

			//Без www
			if( substr_count($_SERVER['HTTP_HOST'], 'www.') and (model::$settings['no_www'] == 'no_www') ){
				$new_host = str_replace('www.','',$_SERVER['HTTP_HOST']);
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: http://'.$new_host.$_SERVER['REQUEST_URI']);
				exit();
				
			//Без www
			}elseif( !substr_count($_SERVER['HTTP_HOST'], 'www.') and (model::$settings['no_www'] == 'www') and (substr_count($_SERVER['HTTP_HOST'], '.')<2) ){
				$new_host = 'www.'.$_SERVER['HTTP_HOST'];
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: http://'.$new_host.$_SERVER['REQUEST_URI']);
				exit();
			}
		}
	}
	
	//Режим демонстрации
	public function check_demo(){
		if( self::$config['settings']['demo_mode'] ) {
			print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
			exit();
		}
	}
	
	//Уточнить текущий домен, если необходимо
	public static function pointDomain(){
		if( IsSet( self::$extensions['domains'] ) )
			return self::$extensions['domains']->getWhere();
		else
			return '1';
	}
	//Уточнить текущий домен, если необходимо
	public static function pointDomainID(){
		if( IsSet( self::$extensions['domains'] ) )
			return self::$extensions['domains']->domain['id'];
		else
			return '1';
	}
	//Уточнить текущий домен, если необходимо
	public static function getDomain(){
		if( IsSet( self::$extensions['domains'] ) )
			return self::$extensions['domains']->domain;
		else
			return array(
				'title' => self::$settings['domain_title'],
				'host' => self::$ask->host,
				'current_host' => self::$ask->host,
			);
	}
	
	//Тесты модулей - запускаются после окончания всех инициплизаций
	public function unittest_modules(){
		foreach(self::$modules as $module)
			$module->unitTests();
	}
	
	//Класс работы с Excel
	public function initExcel(){
		include_once(self::$config['path']['core'] . '/../libs/excel/excel.php');
		return new excel(self::$config['path']);
	}

	//Класс работы с Zip
	public function initZip(){
		include_once(self::$config['path']['core'] . '/../libs/zipper.php');
		return new zipper(self::$config['path']);
	}

	//Класс работы с Email
	public function initEmail(){
		include_once(self::$config['path']['core'] . '/../libs/email.php');
		return new email( $this );
	}

	//Класс работы с фильтрами HTML
	public function initFilters(){
		include_once(self::$config['path']['core'] . '/../libs/filters.php');
		return new filters( $this );
	}

	//Класс работы с RSS/XML
	public function initRss(){
		include_once(self::$config['path']['core'] . '/../libs/rss.php');
		return new rss( self::$config['path'] );
	}

	//Класс определения IP-адреса
	public function initGeoip(){
		include_once(self::$config['path']['core'] . '/../libs/geoip.php');
		return new geoip( self::$config['path'] );
	}

	
}

?>