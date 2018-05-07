<?php

class ModelLoader
{

	public static $config;

	// Устаревшие версии браузеров, которые возможно заблокировать
	public static $block_ie6 = array(
		'ie6' => 'Internet Explorer 6',
		'ie7' => 'Internet Explorer 7',
		'ie8' => 'Internet Explorer 8'
	);

	//Загрузка конфига
	public static function loadConfig( $config )
	{

		if( !$config )
			log::stop( '500 Internal Server Error', 'Не загружен файл конфигурации' );

		// Версия ядра
		$t                                 = file( $config[ 'path' ][ 'core' ] . '/version.txt' );
		$config[ 'settings' ][ 'version' ] = trim( $t[ 0 ] );

		//Совместимость с версиями до 2.14
		$config[ 'path' ][ 'libraries' ]       = $config[ 'path' ][ 'core' ] . '/../libs';
		$config[ 'path' ][ 'admin_templates' ] = $config[ 'path' ][ 'core' ] . '/templates';

		if( !IsSet( $config[ 'path' ][ 'templates' ] ) )
			$config[ 'path' ][ 'templates' ] = $config[ 'path' ][ 'www' ] . '/../templates';

		$config[ 'path' ][ 'modules' ] = $config[ 'path' ][ 'www' ] . '/../modules';
		$config[ 'path' ][ 'backup' ]  = $config[ 'path' ][ 'www' ] . '/../backup';
		$config[ 'path' ][ 'temp' ]    = $config[ 'path' ][ 'www' ] . '/../tmp';
		$config[ 'path' ][ 'tmp' ]     = $config[ 'path' ][ 'www' ] . '/../tmp';
		$config[ 'path' ][ 'cache' ]   = $config[ 'path' ][ 'www' ] . '/../cache';
		$config[ 'path' ][ 'admin' ]   = $config[ 'path' ][ 'www' ] . '/admin';

		if( !IsSet( $config[ 'path' ][ 'public_javascript' ] ) ) {
			$config[ 'path' ][ 'public_images' ]     = $config[ 'path' ][ 'images' ];
			$config[ 'path' ][ 'public_files' ]      = $config[ 'path' ][ 'files' ];
			$config[ 'path' ][ 'public_styles' ]     = $config[ 'path' ][ 'styles' ];
			$config[ 'path' ][ 'public_javascript' ] = $config[ 'path' ][ 'javascript' ];

			$config[ 'path' ][ 'images' ]     = $config[ 'path' ][ 'www' ] . $config[ 'path' ][ 'public_images' ];
			$config[ 'path' ][ 'files' ]      = $config[ 'path' ][ 'www' ] . $config[ 'path' ][ 'public_files' ];
			$config[ 'path' ][ 'styles' ]     = $config[ 'path' ][ 'www' ] . $config[ 'path' ][ 'public_styles' ];
			$config[ 'path' ][ 'javascript' ] = $config[ 'path' ][ 'www' ] . $config[ 'path' ][ 'public_javascript' ];
		}

		$config[ 'settings' ][ 'phpversion' ] = phpversion();

		ini_set( 'include_path', implode( ';', $config[ 'path' ] ) );

		return $config;
	}

	//Подключение базы данных
	public static function loadDatabase( $db )
	{

		if( !$db ) {

			//Поддерживаемые форматы баз данных
			$supported_databases = array(
				false => array(
					'mysql' => 'mysql.php'
				),
				'ado' => array(
					'mysql' => 'adodb5/adodb.inc.php'
				)
			);

			//Инициализация баз данных
			$db = array();
			foreach( model::$config[ 'db' ] as $name => $one ) {
				if( IsSet( $supported_databases[ $one[ 'lib_pack' ] ][ $one[ 'type' ] ] ) ) {

					model::$config[ 'db' ][ $name ][ 'supported' ] = true;
					require_once( model::$config[ 'path' ][ 'libraries' ] . '/' . $supported_databases[ $one[ 'lib_pack' ] ][ $one[ 'type' ] ] );
					$n = $one[ 'type' ];

					//ADO
					if( $one[ 'lib_pack' ] == 'ado' ) {
						$db[ $name ] = ADONewConnection( $one[ 'type' ] );
						$db[ $name ]->SetFetchMode( ADODB_FETCH_ASSOC );
						$db[ $name ]->debug = false;

						//не ADO
					} else {
						$db[ $name ] = new $n();
					}

					$db[ $name ]->Connect( $one[ 'host' ], $one[ 'user' ], $one[ 'password' ], $one[ 'name' ] );
					if( !$db[ $name ]->connection ) {
						print( 'Ошибка соединения с базой данных.' );
						exit();
					}

					$db[ $name ]->Execute( 'set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"' );

				} else {
					model::$config[ 'db' ][ $name ][ 'supported' ] = false;
				}
			}
		}

		return $db;
	}

	//Подгружаем типы данных
	public static function loadTypes()
	{

		//Загружаем библиотеку типов данных
		$supported_types = array(
			'default'     => 'field_type_default.php',
			'id'          => 'field_type_id.php',

			'sid'         => 'field_type_sid.php',
			'ln'          => 'field_type_ln.php',
			'int'         => 'field_type_int.php',
			'float'       => 'field_type_float.php',

			'label'       => 'field_type_label.php',
			'hidden'      => 'field_type_hidden.php',

			'text'        => 'field_type_text.php',
			'textarea'    => 'field_type_textarea.php',
			'text_editor' => 'field_type_texteditor.php',
			'textwiki'    => 'field_type_textwiki.php',
			'textmeta'    => 'field_type_textmeta.php',

			'password'    => 'field_type_password.php',
			'tags'        => 'field_type_tags.php',
			'count'       => 'field_type_count.php',
			'map'         => 'field_type_map.php',
			'tree'        => 'field_type_tree.php',
			'comments'    => 'field_type_comments.php',
			'votes'       => 'field_type_votes.php',
			'rating'      => 'field_type_rating.php',

			'date'        => 'field_type_date.php',
			'domain'      => 'field_type_domain.php',
			'graph'       => 'field_type_graph.php',
			'datetime'    => 'field_type_datetime.php',

			'image'       => 'field_type_image.php',
			'gallery'     => 'field_type_gallery.php',
			'video'       => 'field_type_video.php',
			'file'        => 'field_type_file.php',
			'user'        => 'field_type_user.php',
			'access'      => 'field_type_access.php',
			'html'        => 'field_type_html.php',
			'feedback'    => 'field_type_feedback.php',
			'robots'      => 'field_type_robots.php',

			'menu'        => 'field_type_menu.php',
			'menum'       => 'field_type_menum.php',
			'module'      => 'field_type_module.php',

			'params'      => 'field_type_params.php',
			'array'       => 'field_type_array.php',

			'link'        => 'field_type_link.php',
			'linkm'       => 'field_type_linkm.php',

			'check'       => 'field_type_check.php',

			'tree'        => 'field_type_tree.php'
		);

		//Инициализируем типы данных
		$types = array();
		foreach( $supported_types as $type_sid => $path ) {
			$type_path = model::$config[ 'path' ][ 'core' ] . '/classes/types/' . $path;
			if( file_exists( $type_path ) ) {
				require_once( $type_path );
				$type_name          = 'field_type_' . $type_sid;
				$types[ $type_sid ] = new $type_name();
			}
		}

		return $types;
	}

	//Подгружаем пользовательский тип данных
	public static function loadUserType( $field )
	{
		if( ( $field[ 'type' ][ 0 ] == '_' ) && IsSet( $field[ 'type_path' ] ) ) {
			$type_path = model::$config[ 'path' ][ 'www' ] . '/' . $field[ 'type_path' ];

			if( file_exists( $type_path ) ) {
				require_once( $type_path );
				$type_sid                  = $field[ 'type' ];
				$type_name                 = 'field_usertype' . $type_sid;
				model::$types[ $type_sid ] = new $type_name();

				return true;
			}
		}

		return false;
	}

	//Подключение модулей
	public static function loadModules()
	{
		$modules = array();

		//Подгружаем модули
		$mods = model::execSql( 'select * from `modules` where `active`=1 order by `id`', 'getall', 'system', true );

		// Обратная совместимость с ядрами 2.13 и меньше
		foreach( $mods as $i => $mod )
			if( $mod[ 'prototype' ] == 'start' )
				$mods[ $i ][ 'sid' ] = 'start';

		foreach( $mods as $module ) {

//			self::initModule( $module );

			$module_path = model::$config[ 'path' ][ 'modules' ] . '/' . $module[ 'prototype' ] . '.php';
			//echo $module_path;
			if( file_exists( $module_path ) ) {

				/*
					Определение необходимости включения режима совместимости для старых классов
					созданных для не статичного окружения ядра
				*/
				if( !compatibility::$non_static ) {
					$s = file_get_contents( $module_path );
					if( substr_count( $s, '$this->model->' ) )
						compatibility::on( 'non_static', $module[ 'prototype' ] . '.php' );
				}

				require_once( $module_path );

				$module[ 'url' ]  = '/' . $module[ 'sid' ];
				$module[ 'path' ] = $module_path;

				$name                               = $module[ 'prototype' ] . '_module';
				$modules[ $module[ 'sid' ] ]        = new $name( NULL, $module );
				model::$modules[ $module[ 'sid' ] ] = $modules[ $module[ 'sid' ] ];
			}
		}

	}

	private static function initModule( $record )
	{

		if( !$record[ 'active' ] )
			return false;

		// Физический модуль
		if( $record[ 'prototype' ] ) {

			$module_file_path = model::$config[ 'path' ][ 'modules' ] . '/' . basename( $record[ 'prototype' ] ) . '.php';
			if( is_readable( $module_file_path ) ) {
				require_once( $module_file_path );

				$record[ 'url' ]  = '/' . $record[ 'sid' ];
				$record[ 'path' ] = $module_file_path;

				$name = $record[ 'prototype' ] . '_module';

				model::$modules[ $record[ 'sid' ] ] = new $name( NULL, $record );
			}

			// Виртуальный модуль
		} else {
			/*
						$sid = $record[ 'sid' ];

						$record[ 'url' ]  = '/' . $sid;
						$record[ 'path' ] = false;

						$module = unserialize( $record[ 'object' ] );
						if( !is_object( $module ) ) {
							$settings = unserialize( $record[ 'settings' ] );
							$module   = self::createModule( $settings );
						}

						if( !is_object( $module ) )
							return false;

						model::$modules[ $record[ 'sid' ] ] = $module;
			*/
		}

		return true;
	}

	private static function createModule( $settings )
	{


	}

	//Подгрузка расширений к модулю
	public static function loadExtensions()
	{

		$extensions = array();
		//Подгрузка библиотеки расширений к модулям, инициализация
		if( is_array( model::$config[ 'extensions' ] ) )
			foreach( model::$config[ 'extensions' ] as $extension_sid => $filename ) {
				//Подключаем файл библиотеки
				require_once( model::$config[ 'path' ][ 'core' ] . '/extensions/' . $filename );
				//Создаём
				$name                         = 'extention_' . $extension_sid;
				$extensions[ $extension_sid ] = new $name();
				$extensions[ $extension_sid ]->execute();
			}

		return $extensions;
	}

	//Загрузка настроек домена
	public static function loadSettings()
	{
		$settings = array();

		$res = model::execSql( 'select * from `settings` where ' . model::pointDomain(), 'getall' );

		// Добавление поля field для старых версий ядра
		if( !IsSet( $res[ 0 ][ 'field' ] ) )
			model::execSql( 'alter table `settings` add `field` TEXT NOT NULL', 'insert' );

		// Разворачиваем поля настроек
		foreach( $res as $r )
			if( is_object( model::$types[ trim( $r[ 'type' ] ) ] ) )
				$settings[ trim( $r[ 'var' ] ) ] = model::$types[ trim( $r[ 'type' ] ) ]->getValueExplode( trim( $r[ 'value' ] ), false, $res );

		$settings = self::checkNeededSettings( $settings );

		self::setErrorReporting( $settings[ 'errors' ] );

		return $settings;
	}

	//Разбор параметров запроса
	public static function loadAsk()
	{
		$ask               = new StdClass;
		$ask->original_url = self::getCurrentUrl();

		$ask->method        = $_SERVER[ 'REQUEST_METHOD' ];
		$ask->protocol      = substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, strpos( $_SERVER[ 'SERVER_PROTOCOL' ], '/' ) );
		$ask->host          = $_SERVER[ 'HTTP_HOST' ];
		$ask->output_type   = '404';
		$ask->output_format = false;

		if( $ask->original_url != '/' ) {
			$ask->url = array_values( explode( '/', substr( $ask->original_url, 1 ) ) );
			foreach( $ask->url as $i => $url ) {
				if( substr_count( $url, '.' ) ) {
					$mode            = explode( '.', $url );
					$url             = array_shift( $mode );
					$ask->tree[ $i ] = array( 'url' => $url, 'mode' => $mode );
					$ask->url[ $i ]  = $url;

					if( class_exists( 'controller_manager' ) )
						$formats = controller_manager::$output_formats;
					else
						$formats = array( 'html', 'json', 'xml', 'tpl', 'txt', 'php' );

					if( in_array( end( $ask->tree[ $i ][ 'mode' ] ), $formats ) )
						$ask->output_format = array_pop( $ask->tree[ $i ][ 'mode' ] );

					//Модификаторы
					$ask->mode = $ask->tree[ $i ][ 'mode' ];

					//Номер страницы
					if( is_numeric( end( $ask->mode ) ) ) {
						$ask->current_page = intval( array_pop( $ask->mode ) );
					}

				} else {
					$ask->tree[ $i ] = array(
						'url' => $url,
					);
				}
			}
		} else {
			$ask->output_format = 'html';
		}

		// TODO: убрать этот костыль и сделать перенаправление на верный URL, что закомментирован ниже
		if( !$ask->output_format )
			$ask->output_format = 'html';
		/*
				// Формат не указан, скорее всего ошибка, перенаправляем на адрес с .html
				if( !$ask->output_format ){
					header('Location: '.$ask->original_url.'.html');
					exit();
				}
		*/

		//Панель управления - адреса типа "/admin/..."
		if( $ask->url[ 0 ] == 'admin' ) {
			$ask->controller = array_shift( $ask->url );
//			$ask->tree = $ask->url;
		}

		return $ask;
	}

	//Определить текущий URL
	public static function getCurrentUrl()
	{
		if( $_SERVER[ 'REDIRECT_URL' ][ 0 ] == '/' )
			$_SERVER[ 'REDIRECT_URL' ] = $_SERVER[ 'REQUEST_URI' ];

		// Фикс для некоторых серверов nginx
		if( !IsSet( $_SERVER[ 'REDIRECT_URL' ] ) )
			$_SERVER[ 'REDIRECT_URL' ] = $_SERVER[ 'REQUEST_URI' ];

		$original_url = urldecode( $_SERVER[ 'REDIRECT_URL' ] );
		if( substr_count( $original_url, '?' ) )
			$original_url = substr( $original_url, 0, strpos( $original_url, '?' ) );

		return $original_url;
	}

	//Проверяем наличие необходимых настроек
	private static function checkNeededSettings( $settings = array() )
	{
		if( IsSet( $settings[ 'test_mode' ] ) )
			if( !$settings[ 'test_mode' ] )
				return $settings;

		$needed_settings = array(

			// Общие
			'domain_title'    => array(
				'group'         => 'main',
				'title'         => 'Название сайта',
				'type'          => 'text',
				'default_value' => 'ООО "Наша Компания"',
			),
			'copyright'       => array(
				'group'         => 'main',
				'title'         => 'Копирайты',
				'type'          => 'text',
				'default_value' => 'ООО "Наша Компания"',
			),
			'test_mode'       => array(
				'group'         => 'main',
				'title'         => 'Режим разработки - закрыть сайт для всех, кроме разрешённых IP-адресов',
				'type'          => 'check',
				'default_value' => 1,
			),
			'test_mode_text'  => array(
				'group'         => 'main',
				'title'         => 'Текст для пользователя в режиме Разработки',
				'type'          => 'textarea',
				'default_value' => 'Сайт находится в разработке, ориентировочная дата открытия: ' . date( "d.m.Y", strtotime( "+1 month" ) ),
			),
			'date_start'      => array(
				'group' => 'main',
				'title' => 'Дата создания сайта',
				'type'  => 'datetime',
			),
			'acms_position'   => array(
				'group'    => 'main',
				'title'    => 'Позиция панели управления сайтом',
				'type'     => 'menu',
				'variants' => array(
					'top'    => 'Вверху',
					'bottom' => 'Внизу',
					/*
										'right'  => 'Справа',
										'left'   => 'Слева',
					*/
				),
			),

			// SEO
			'counter'         => array(
				'group'         => 'seo',
				'title'         => 'Код счётчика',
				'type'          => 'html',
				'default_value' => '[Код счётчика]',
			),
			'seo_keywords'    => array(
				'group'         => 'seo',
				'title'         => 'Ключевые слова (до 255 символов)',
				'type'          => 'textarea',
				'default_value' => '',
			),
			'seo_description' => array(
				'group'         => 'seo',
				'title'         => 'Краткое описание сайта (до 255 символов)',
				'type'          => 'textarea',
				'default_value' => '',
			),
			'robots'          => array(
				'group'         => 'seo',
				'title'         => 'Файл robots.txt для домена',
				'type'          => 'robots',
				'default_value' => '',
			),
			'meta_add'        => array(
				'group'         => 'seo',
				'title'         => 'Дополнительные теги в head',
				'type'          => 'html',
				'default_value' => '',
			),

			// Оформление
			'logo'            => array(
				'group'         => 'media',
				'title'         => 'Логотип сайта',
				'type'          => 'image',
				'default_value' => 0,
			),
			'favicon'         => array(
				'group'         => 'media',
				'title'         => 'Иконка в адресной строке браузера',
				'type'          => 'file',
				'default_value' => 0,
			),

			// Конфигурация
			'js_libraries'    => array(
				'group'         => 'config',
				'title'         => 'Использовать JS-библиотеки',
				'type'          => 'menum',
				'default_value' => '|lightbox|',
				'variants'      => array(
					'jquery1'        => 'заменить jQuery 2 на jQuery 1',
					'jquery-ui'      => 'jQuery-UI',
					'jquery-migrate' => 'jQuery Migrate for 2.0',
					'bootstrap'      => 'Bootstrap 2',
					'bootstrap3'     => 'Bootstrap 3',
					'lightbox'       => 'Lightbox',
					'less'           => 'LESS - Leaner CSS',
					'combosex'       => 'Combosex',
				),
			),
			'css_main'        => array(
				'group' => 'config',
				'title' => 'Путь к основному файлу CSS-стилей',
				'type'  => 'text',
			),
			'latin_url_only'  => array(
				'group'         => 'config',
				'title'         => 'Ограничить URL`ы только латинскими буквами, цифрами, и знаком подчёркивания',
				'type'          => 'check',
				'default_value' => true,
			),
			'show_stat'       => array(
				'group'         => 'config',
				'title'         => 'Показывать статистику генерации страницы',
				'type'          => 'menu',
				'default_value' => 'shirt',
				'variants'      => array(
					0       => 'не показывать',
					'shirt' => 'Показывать краткую статистику',
					'all'   => 'Показывать полную статистику' ),
			),
			'errors'          => array(
				'group'         => 'config',
				'title'         => 'Настройки отображения ошибок',
				'type'          => 'menum',
				'variants'      => array(
					'warning'  => 'PHP: warnings',
					'strict'   => 'PHP: strict',
					'notice'   => 'PHP: notices',
					'template' => 'PHP: ошибки при компиляции шаблонов',
					'sql'      => 'SQL: Ошибки в запросах',
				),
				'default_value' => '',
			),
			'mainmenu_levels' => array(
				'group'         => 'config',
				'title'         => 'Сколько уровней главного меню обсчитывать',
				'type'          => 'menu',
				'default_value' => 2,
				'variants'      => array(
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
				),
			),
			'no_www'          => array(
				'group'         => 'config',
				'title'         => 'Как относиться к приставке www в адресах',
				'type'          => 'menu',
				'default_value' => 0,
				'variants'      => array(
					0        => 'Не важно',
					'no_www' => 'Использовать адреса только без приставки www',
					'www'    => 'Использовать адреса только с приставкой www',
				),
			),
			'block_ie6'       => array(
				'group'         => 'config',
				'title'         => 'Перенаправлять владельцев следующих устаревших браузеров на http://browsehappy.com/',
				'type'          => 'menum',
				'default_value' => 0,
				'variants'      => self::$block_ie6,
			),
			'doctype'         => array(
				'group'         => 'config',
				'title'         => 'Используемый стандарт HTML',
				'type'          => 'menu',
				'default_value' => 'XHTML 1.0 Transitional',
				'variants'      => array(
					'HTML 4.01 Strict',
					'HTML 4.01 Transitional',
					'XHTML 1.0 Strict',
					'XHTML 1.0 Transitional',
					'XHTML 1.1',
					'HTML 5',
				),
			),
			'resort_fields'   => array(
				'group'         => 'config',
				'title'         => 'Пересортировать колонки в таблицах согласно структуре (работает в режиме разработки, сильно тормозит загрузку)',
				'type'          => 'check',
				'default_value' => false,
			),
			'viewport'        => array(
				'group'         => 'config',
				'title'         => 'Тег Meta Viewport',
				'type'          => 'text',
				'default_value' => 'width=device-width, initial-scale=1.0',
			),
			'oauth_openid'    => array(
				'group'         => 'OAuth',
				'title'         => 'Разрешённые Openid/OAuth провайдеры',
				'type'          => 'menum',
				'default_value' => '|google.com|',
				'variants'      => array(
					'yandex.ru'    => 'Яндекс',
					'google.com'   => 'Google',
					'facebook.com' => 'Facebook',
					'vk.com'       => 'Вконтакте',
					'twitter.com'  => 'Twitter',
				),
			),
			'exitpage'        => array(
				'group'         => 'config',
				'title'         => 'Переходная страница для внешних ссылок',
				'type'          => 'text',
				'default_value' => '',
			),

		);

		// Дополняем настройками модуля watermark
		$watermark_library_path = model::$config[ 'path' ][ 'libraries' ] . '/acmsWatermark.php';
		if( file_exists( $watermark_library_path ) ) {
			require_once( $watermark_library_path );
			$needed_settings = array_merge( $needed_settings, acmsWatermark::getSettings() );
		}

		//Добиваем системные настройки настряоками из модулей
		foreach( model::$modules as $module_sid => $module )
			if( IsSet( $module->settings ) )
				$needed_settings = array_merge( $module->settings, $needed_settings );

		$i = 1;
		foreach( $needed_settings as $key => $set )
			if( $key ) {
				$i++;

				//Добавляем поле
				if( !IsSet( $settings[ $key ] ) ) {
					model::execSql( 'insert into `settings` set
					`pos`="' . ( $i*10 ) . '",
					`group`="' . mysql_real_escape_string( $set[ 'group' ] ) . '",
					`domain`="|' . model::pointDomainID() . '|",
					`var`="' . mysql_real_escape_string( $key ) . '",
					`title`="' . mysql_real_escape_string( $set[ 'title' ] ) . '",
					`type`="' . mysql_real_escape_string( $set[ 'type' ] ) . '",
					`value`="' . mysql_real_escape_string( $set[ 'default_value' ] ) . '",
					`field`="' . mysql_real_escape_string( serialize( $set ) ) . '"
				', 'insert' );
					$settings[ $key ] = $set[ 'default_value' ];

				} else {
					model::execSql( 'update `settings` set
					`pos`="' . ( $i*10 ) . '",
					`group`="' . mysql_real_escape_string( $set[ 'group' ] ) . '",
					`title`="' . mysql_real_escape_string( $set[ 'title' ] ) . '",
					`type`="' . mysql_real_escape_string( $set[ 'type' ] ) . '",
					`field`="' . mysql_real_escape_string( serialize( $set ) ) . '"
					where
					`var`="' . mysql_real_escape_string( $key ) . '" and
					' . model::pointDomain() . '
				', 'update' );
				}
			}

		return $settings;
	}

	// Режим отображения ошикок
	public static function setErrorReporting( $mode )
	{

		if( $mode ) {

			$warning = in_array( 'warning', $mode );
			$notice  = in_array( 'notice', $mode );
			$strict  = in_array( 'strict', $mode );

			if( $warning && $notice && $strict )
				error_reporting( E_ALL^E_DEPRECATED );
			elseif( $warning && $notice )
				error_reporting( E_ALL^E_DEPRECATED^E_STRICT );
			elseif( $warning && $strict )
				error_reporting( E_ALL^E_DEPRECATED^E_NOTICE );
			elseif( $notice && $strict )
				error_reporting( E_ALL^E_DEPRECATED^E_WARNING );
			elseif( $warning )
				error_reporting( E_ALL^E_DEPRECATED^E_STRICT^E_NOTICE );
			elseif( $strict )
				error_reporting( E_ALL^E_DEPRECATED^E_WARNING^E_NOTICE );
			elseif( $notice )
				error_reporting( E_ALL^E_DEPRECATED^E_WARNING^E_STRICT );
			else
				error_reporting( 0 );

			ini_set( "display_errors", "on" );

//			ini_set( "display_errors", "off" );

		} else {

			error_reporting( 0 );
			ini_set( "display_errors", "off" );

		}
	}

}

