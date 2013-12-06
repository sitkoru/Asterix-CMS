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

require_once( 'model_sql.php' );

class model
{
	use db;

	public static $config;
	public static $db;
	public static $types;
	public static $modules;
	public static $extensions;
	public static $settings;
	public static $cache;
	public static $ask;
	public static $last_sql;
	public static $active_database = 'system';

	function __construct( $config, $log, $cache = false )
	{

		require_once( $config[ 'path' ][ 'core' ] . '/classes/model_loader.php' );
		require_once( $config[ 'path' ][ 'core' ] . '/classes/model_sql.php' );
		require_once( $config[ 'path' ][ 'core' ] . '/classes/user.php' );
		require_once( $config[ 'path' ][ 'core' ] . '/classes/default_module.php' );
		require_once( $config[ 'path' ][ 'core' ] . '/tests/compatibility.php' );

		$this->log        = $log;
		$this->log->model = $this;
		$this->cache      = $cache;
		self::$cache      = $cache;

		self::$config = ModelLoader::loadConfig( $config );
		self::$db     = ModelLoader::loadDatabase( self::$db );
		self::$types  = ModelLoader::loadTypes();

		// Локальная авторизация, возможна до инициализации модулей
		user::authUser_fast();

		self::preloadModules();

		self::$extensions = ModelLoader::loadExtensions();
		self::$settings   = ModelLoader::loadSettings();
		self::$ask        = ModelLoader::loadAsk();
		self::$ask->rec   = self::getRecordByAsk( self::$ask->url );

		$this->unittest_modules();
		$this->check_no_www();

		// Авторизация по OAuth возможна только после инициализации модулей
		if( !user::is_authorized() )
			user::authUser_long();

		// Инициализация компонентов и интерфейсов
		foreach( self::$modules as $module_sid => $module )
			$module->init();

		// Включаем все необходимые режимы совместимости
		compatibility::init();

	}

	public function preloadModules()
	{
		ModelLoader::loadModules();
	}

	//Подключение базы данных
	public function authUser()
	{
		$this->user = new user( $this );
	}

	//Подготовка данных для ввода в шаблонизатор
	public static function prepareMainMenu( $levels_to_show )
	{
		//Рекурсивная функция по расставлению статусов
		function setOpenAndActiveStatus( $recs, $current_user_url )
		{
			if( $recs )
				foreach( $recs as $i => $rec ) {
					//Чистый url - без окончания
					if( substr_count( $rec[ 'url' ], '.' ) )
						$url = substr( $rec[ 'url' ], 0, strpos( $rec[ 'url' ], '.' ) );
					else
						$url = $rec[ 'url' ];

					//Главная страница
					if( $rec[ 'url' ] == '/' )
						$rec[ 'url' ] = '';

					//Пользователь сейчас здесь
					if( $current_user_url == $rec[ 'url' ] ) {
						$recs[ $i ][ 'show_subs' ] = true;
						$recs[ $i ][ 'show_link' ] = false;
						$recs[ $i ][ 'mark' ]      = true;
						if( IsSet( $rec[ 'sub' ] ) )
							$recs[ $i ][ 'sub' ] = setOpenAndActiveStatus( $rec[ 'sub' ], $current_user_url );

						//Пользователь в подразделе текущей записи
					} elseif( substr_count( $current_user_url, $url ) ) {
						$recs[ $i ][ 'show_subs' ] = true;
						$recs[ $i ][ 'show_link' ] = true;
						$recs[ $i ][ 'mark' ]      = false;
						if( IsSet( $rec[ 'sub' ] ) )
							$recs[ $i ][ 'sub' ] = setOpenAndActiveStatus( $rec[ 'sub' ], $current_user_url );

						//Обычный пункт меню
					} else {
						$recs[ $i ][ 'show_subs' ] = false;
						$recs[ $i ][ 'show_link' ] = true;
						$recs[ $i ][ 'mark' ]      = false;
					}

				}

			return $recs;
		}

		//Получаем дерево
		$recs = self::prepareShirtTree( 'start', 'rec', false, $levels_to_show, array(
			'and' => array(
				'`show_in_menu`=1',
				'`shw`=1'
			)
		) );

		//Расславляем статусы открытия и активности
		$recs = setOpenAndActiveStatus( $recs, model::$ask->original_url );

//		pr_r($recs);

		return $recs;
	}

	//Готовим путь на сайте, основываясь на разобранных данных запроса ASK
	public static function prepareModelPath()
	{
		$path = array();

		//Текущий модуль и его структура
		$current_module = 'start';
		$structure_sid  = 'rec';

		//Перебираем все части url
		if( is_array( self::$ask->url ) )
			foreach( self::$ask->url as $i => $url )
				if( IsSet( self::$modules[ $current_module ] ) ) {

					//Получаем все основные поля модуля
					$fields = self::$modules[ $current_module ]->getMainFields( $structure_sid );
					//Если коренной модуль - отслеживаем перенаправление на другой модуль
					if( !$current_module )
						$fields[ ] = 'is_link_to_module';
					//Если уже не
					//Получаем запись
					$rec = model::makeSql( array(
						'tables' => array(
							self::$modules[ $current_module ]->getCurrentTable( $structure_sid )
						),
						'fields' => $fields,
						'where'  => array(
							'and' => array(
								'`sid`="' . mysql_real_escape_string( $url ) . '"'
							)
						)
					), 'getrow' );

					//Дочерние структуры записей
					if( ( $structure_sid != 'rec' ) and ( !$rec ) ) {
						$rec = model::makeSql( array(
							'tables' => array(
								self::$modules[ $current_module ]->getCurrentTable( 'rec' )
							),
							'fields' => $fields,
							'where'  => array(
								'and' => array(
									'`sid`="' . mysql_real_escape_string( $url ) . '"'
								)
							)
						), 'getrow' );
					}

					//Записываем
					$path[ $i ] = $rec;

					//Если коренной модуль - отслеживаем перенаправление на другой модуль
					if( strlen( $rec[ 'is_link_to_module' ] )>0 ) {
						//Меняем текущий модуль
						$current_module = $rec[ 'is_link_to_module' ];

						//Определяем его текущую структуру
						if( IsSet( self::$modules[ $current_module ] ) ) {
							$tree          = array_reverse( self::$modules[ $current_module ]->getStructure_allLevels() );
							$tree_index    = -1;
							$structure_sid = $tree[ $tree_index ];

							//Если имела место смена модуля
							if( $current_module ) {
								//Следующая структура
								$tree_index++;
								//Запоминаем
								$structure_sid = $tree[ $tree_index ];
							}
						}
					}


				}

		//Вставляем окончания к URL`ам
		if( IsSet( self::$modules[ $current_module ] ) )
			$path = self::$modules[ $current_module ]->insertRecordUrlType( $path );

		//Готово
		return $path;
	}

	//Создание краткого дерева модели
	public static function prepareShirtTree( $module_sid = 'start', $structure_sid = 'rec', $root_record_id = false, $levels_to_show = 2, $conditions = array( 'and' => array( '`shw`=1' ) ) )
	{
		if( is_object( self::$modules[ $module_sid ] ) )
			return self::$modules[ $module_sid ]->getModuleShirtTree( $root_record_id, $structure_sid, $levels_to_show, $conditions );
	}

	//Добавление записи в структуру модуля
	public function addRecord( $module_sid = 'start', $structure_sid, $values )
	{
		return self::$modules[ $module_sid ]->addRecord( $values, $structure_sid );
	}

	//Добавление записи в структуру модуля
	public function editRecord( $module_sid = 'start', $structure_sid, $values, $conditions = false )
	{
		return self::$modules[ $module_sid ]->editRecord( $values, $structure_sid, $conditions );
	}

	//Старое название функции
	public function updateRecord( $module_sid = 'start', $structure_sid, $values, $conditions = false )
	{
		return self::$modules[ $module_sid ]->editRecord( $values, $structure_sid, $conditions );
	}


	//Добавление записей в структуру модуля, с проверкой существования этих записей (проверка по SID)
	public function importRecords( $module, $structure_sid, $records, $check_unique_field = 'sid', $conditions = false )
	{

		//Собираем значения уникального поля записей
		$unique = array();
		foreach( $records as $rec )
			$unique[ ] = $rec[ $check_unique_field ];

		//Условия
		$where    = $conditions;
		$where[ ] = '`' . mysql_real_escape_string( $check_unique_field ) . '` IN ("' . implode( '", "', $unique ) . '")';

		//Если на домене открыто изменение домена - импорт производится без учёта данных текущего домена
		if( self::$config[ 'settings' ][ 'domain_switch' ] )
			$where[ 'domain' ] = false;

		//Забираем записи которые уже существуют, их будем обновлять
		$update_recs = self::makeSql( array(
			'tables' => array(
				self::$modules[ $module ]->getCurrentTable( $structure_sid )
			),
			'fields' => array(
				$check_unique_field,
				'id'
			),
			'where'  => array(
				'and' => $where,
			)
		), 'getall', 'system', true );

		//Уникальные поля этих записей
		$update = array();
		foreach( $update_recs as $rec )
			$update[ $rec[ $check_unique_field ] ] = $rec[ 'id' ];

		//Заливаем записи
		foreach( $records as $record ) {
			//Запись подлежит обновлению
			if( IsSet( $update[ $record[ $check_unique_field ] ] ) ) {
				//Вставляем ID существующей записи
				$record[ 'id' ] = $update[ $record[ $check_unique_field ] ];
				//Обновляем
				self::editRecord( $module, $structure_sid, $record, $conditions );

				//Запись подлежит добавлению
			} else {
				//Добавляем
				self::addRecord( $module, $structure_sid, $record );
			}
		}
	}

	//Получить SID модуля, установленный в системе, зная его прототип
	public function getModuleSidByPrototype( $prototype )
	{

		// Фикс для обратной совместимости с версиями до 2.13
		if( $prototype == 'false' )
			$prototype = 'start';

		foreach( self::$modules as $module_sid => $module )
			if( $module->info[ 'prototype' ] == $prototype )
				return $module_sid;

		return $prototype;
	}

	//Получить SID модуля, установленный в системе, зная его прототип
	public function getModuleByPrototype( $prototype )
	{
		return self::$modules[ $this->getModuleSidByPrototype( $prototype ) ];
	}

	//Поиск записи в модели
	public static function getRecordByAsk( $url, $prefered_module = 'start' )
	{

		// Сначала ищем в корневом модуле
		$record = false;
		if( is_object( model::$modules[ $prefered_module ] ) ) {
			$structures = model::$modules[ $prefered_module ]->getStructures();
			if( $structures )
				foreach( $structures as $structure_sid => $structure ) if( !$record ) {

					$last_structure_sid = $structure_sid;

					if( $url )
						$url_string = '/' . implode( '/', $url );
					else
						$url_string = '';

					$where = '(`url`="' . mysql_real_escape_string( $url_string ) . '"';
					if( $prefered_module == 'start' )
						$where .= ' or `url_alias`="' . mysql_real_escape_string( $url_string ) . '"';
					$where .= ')';

					$record = self::makeSql(
						array(
							'tables' => array( model::$modules[ $prefered_module ]->getCurrentTable( $last_structure_sid ) ),
							'where'  => array( 'and' => array( 'url' => $where ) )
						),
						'getrow'
					);
				}
		}

		// Нашли запись в стандартном модуле
		if( $record ) {
			if( $record[ 'is_link_to_module' ] ) {
				model::$ask->module        = $record[ 'is_link_to_module' ];
				model::$ask->structure_sid = end( array_keys( model::$modules[ $prefered_module ]->structure ) );
				model::$ask->output_type   = 'index';
			} else {
				model::$ask->module        = $prefered_module;
				model::$ask->structure_sid = $last_structure_sid;

				if( $url_string == '' )
					model::$ask->output_type = 'index';
				elseif( $last_structure_sid != 'rec' )
					model::$ask->output_type = 'list';
				else
					model::$ask->output_type = 'content';
			}

			// Не нашли, ищем глубже
		} else {
			for( $i = 0; $i<count( $url ); $i++ )
				if( !$record )
					if( IsSet( model::$modules[ $url[ $i ] ] ) )
						if( $url[ $i ] != $prefered_module ) {
							$record = self::getRecordByAsk( $url, $url[ $i ] );
						}
		}

		// Готово
		return $record;
	}

	//Проверка no-www
	private function check_no_www()
	{
		if( model::$settings[ 'no_www' ] ) {

			//Без www
			if( substr_count( $_SERVER[ 'HTTP_HOST' ], 'www.' ) and ( model::$settings[ 'no_www' ] == 'no_www' ) ) {
				$new_host = str_replace( 'www.', '', $_SERVER[ 'HTTP_HOST' ] );
				header( 'HTTP/1.1 301 Moved Permanently' );
				header( 'Location: http://' . $new_host . $_SERVER[ 'REQUEST_URI' ] );
				exit();

				//Без www
			} elseif( !substr_count( $_SERVER[ 'HTTP_HOST' ], 'www.' ) and ( model::$settings[ 'no_www' ] == 'www' ) and ( substr_count( $_SERVER[ 'HTTP_HOST' ], '.' )<2 ) ) {
				$new_host = 'www.' . $_SERVER[ 'HTTP_HOST' ];
				header( 'HTTP/1.1 301 Moved Permanently' );
				header( 'Location: http://' . $new_host . $_SERVER[ 'REQUEST_URI' ] );
				exit();
			}
		}
	}

	//Режим демонстрации
	public function check_demo()
	{
		if( self::$config[ 'settings' ][ 'demo_mode' ] ) {
			print( 'В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"' );
			exit();
		}
	}

	//Уточнить текущий домен, если необходимо
	public static function pointDomain()
	{
		if( IsSet( self::$extensions[ 'domains' ] ) )
			return self::$extensions[ 'domains' ]->getWhere();
		else
			return '1';
	}

	//Уточнить текущий домен, если необходимо
	public static function pointDomainID()
	{
		if( IsSet( self::$extensions[ 'domains' ] ) )
			return self::$extensions[ 'domains' ]->domain[ 'id' ];
		else
			return '1';
	}

	//Уточнить текущий домен, если необходимо
	public static function getDomain()
	{
		if( IsSet( self::$extensions[ 'domains' ] ) )
			return self::$extensions[ 'domains' ]->domain;
		else
			return array(
				'title'        => self::$settings[ 'domain_title' ],
				'host'         => self::$ask->host,
				'current_host' => self::$ask->host,
			);
	}

	//Тесты модулей - запускаются после окончания всех инициплизаций
	public function unittest_modules()
	{
		foreach( self::$modules as $module )
			$module->unitTests();

	}

	//Класс работы с Excel
	public function initExcel()
	{
		include_once( self::$config[ 'path' ][ 'core' ] . '/../libs/excel/excel.php' );

		return new excel( self::$config[ 'path' ] );
	}

	//Класс работы с Zip
	public function initZip()
	{
		include_once( self::$config[ 'path' ][ 'core' ] . '/../libs/zipper.php' );

		return new zipper( self::$config[ 'path' ] );
	}

	//Класс работы с Email
	public function initEmail()
	{
		include_once( self::$config[ 'path' ][ 'core' ] . '/../libs/email.php' );

		return new email( $this );
	}

	//Класс работы с фильтрами HTML
	public function initFilters()
	{
		include_once( self::$config[ 'path' ][ 'core' ] . '/../libs/filters.php' );

		return new filters( $this );
	}

	//Класс работы с RSS/XML
	public function initRss()
	{
		include_once( self::$config[ 'path' ][ 'core' ] . '/../libs/rss.php' );

		return new rss( self::$config[ 'path' ] );
	}

	//Класс определения IP-адреса
	public function initGeoip()
	{
		include_once( self::$config[ 'path' ][ 'core' ] . '/../libs/geoip.php' );

		return new geoip( self::$config[ 'path' ] );
	}

	// Вызывается в конце работы
	public function stop()
	{
		exit();
	}

}
