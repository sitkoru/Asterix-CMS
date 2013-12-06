<?php

class installer
{

	public static function __construct( $config )
	{

		require_once( $config[ 'path' ][ 'core' ] . '/classes/model_loader.php' );
		require_once( $config[ 'path' ][ 'core' ] . '/classes/model_sql.php' );

		// Подключаем необходимое окружение
		self::$config = ModelLoader::loadConfig( $config );
		self::$db     = ModelLoader::loadDatabase( self::$db );
		self::$types  = ModelLoader::loadTypes();

		// Проверяем все необходимые пути
		self::checkPaths( self::$config[ 'path' ] );

		// Проверяем соединение с базой данных
		self::checkDatabaseConnection();

		// Устанавливаем системные таблицы для данных
		self::checkDatabaseStructure();

	}

	private static function checkPaths( $path )
	{

		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );

		$paths = model::$config[ 'path' ];
		foreach( $paths as $sid => $path ) if( !substr_count( $sid, 'public_' ) ) {

			// Создаём несуществующие директории
			if( !file_exists( $path ) ) {
				$dirs = new acmsDirs();
				$dirs->makeFolder( $path, 0775 );
				print( 'Создана директория: ' . $path . '<br />' );

				// Проверяем доступ на запись
			} elseif( !is_writable( $path ) ) {
				print( 'Ошибка: директория защищена от записи [' . $path . ']<br />' );

			}

		}

		return true;
	}

	private static function checkDatabaseConnection()
	{
		if( !self::$db->connection ) {
			print( 'Ошибка: отсутствует соединение с базой данных.<br />' );
		}

		return true;
	}

	private static function checkDatabaseStructure()
	{

		if( !self::$db->connection )
			return false;

		$tables = array(
			'modules'   => array( 'title' => 'Установленные модули', 'installer' => 'tableInstall_modules' ),
			'users'     => array( 'title' => 'Пользователи системы', 'installer' => 'tableInstall_users' ),
			'settings'  => array( 'title' => 'Настройки сайта', 'installer' => 'tableInstall_settings' ),
			'start_rec' => array( 'title' => 'Основная таблица модуля статей', 'installer' => 'tableInstall_start' ),
		);

		// Устанавливаем все системные таблицы
		foreach( $tables as $table_name => $table ) {
			$search = model::execSql( 'show tables like "' . $table_name . '"' );

			if( !count( $search ) ) {
				$table_installer = $table[ 'installer' ];
				forward_static_call( array( 'installer', $table_installer ) );
				print( 'Создана таблица: ' . $table[ 'title' ] . '<br />' );
			}

		}

		// Устанавливаем режим тестирования
		model::execSql( 'update `settings` set `value`=1 where `var`="test_mode" limit 1', 'update' );

		return true;
	}

	private static function tableInstall_modules()
	{

		$sql = "CREATE TABLE IF NOT EXISTS `modules` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `sid` varchar(64) NOT NULL DEFAULT '',
		  `prototype` varchar(64) NOT NULL DEFAULT '',
		  `pos` int(11) NOT NULL DEFAULT '0',
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `ln` int(11) NOT NULL DEFAULT '0',
		  `active` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;
		";
		model::execSql( $sql, 'insert' );

		// Ищем все модули для сайта
		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		$acmsDirs = new acmsDirs();
		$files    = $acmsDirs->get_files( model::$config[ 'path' ][ 'modules' ] );

		// Выставляем модули start и users на первое место
		$sids = array( 'start', 'users' );
		foreach( $files as $i => $file ) {
			$module_sid = basename( $file );
			$module_sid = str_replace( '', '', $module_sid );
			if( !in_array( $module_sid, $sids ) )
				$sids[ ] = $module_sid;
		}

		// Устанавливаем все модули, найденные на сайте
		foreach( $sids as $i => $sid ) {

			require_once( model::$config[ 'path' ][ 'modules' ] . '/' . $sid . '.php' );
			$class_name = $sid . '_module';
			if( class_exists( $class_name ) ) {
				$module = new $class_name;
				model::execSql( 'insert into `modules` set `id`=' . intval( $i+1 ) . ', `sid`="' . $sid . '", `prototype`="' . $sid . '", `pos`=' . intval( ( $i+1 )*10 ) . ', `title`="' . $module->title . '", `ln`=1, `active`=1', 'insert' );

				print( 'Добавлен модуль "' . $module->title . '"<br />' );
			}
		}
	}

	private static function tableInstall_users()
	{

		$sql = "CREATE TABLE IF NOT EXISTS `modules` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `sid` varchar(64) NOT NULL DEFAULT '',
		  `prototype` varchar(64) NOT NULL DEFAULT '',
		  `pos` int(11) NOT NULL DEFAULT '0',
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `ln` int(11) NOT NULL DEFAULT '0',
		  `active` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;
		";
		model::execSql( $sql, 'insert' );


	}

	private static function tableInstall_settings()
	{

		// Создаём таблицу
		$sql = "CREATE TABLE IF NOT EXISTS `modules` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `sid` varchar(64) NOT NULL DEFAULT '',
		  `prototype` varchar(64) NOT NULL DEFAULT '',
		  `pos` int(11) NOT NULL DEFAULT '0',
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `ln` int(11) NOT NULL DEFAULT '0',
		  `active` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;
		";
		model::execSql( $sql, 'insert' );

		// Заполняем её настройками по умолчанию
		ModelLoader::loadSettings();
	}

}