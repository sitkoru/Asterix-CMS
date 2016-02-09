<?php

class acms_updater
{

	static $current_version;
	static $max_version;
	static $max_version_dev;

	// Необходимо проверить наличие обновлений
	public static function checkUpdate( $params )
	{
		$result = false;

		return false;

		// Получаем версии пакетов
		self::$current_version = file( model::$config['path']['core'] . '/version.txt' );
		self::$max_version     = file( 'http://src.sitko.ru/version.txt' );
		self::$max_version_dev = file( 'http://src.sitko.ru/version_dev.txt' );

		// Требуется обновление
//		if( floatval( $current_version[0] ) < floatval( $max_version[0] ) ){
		if( floatval( self::$current_version[0] ) == floatval( self::$max_version_dev[0] ) ) {

			$result = array(
				'version'     => self::$max_version[0],
				'version_dev' => self::$max_version_dev[0],
			);

		}

		// Готово
		return $result;
	}

	public static function welcomeScreen( $params )
	{


	}

}

?>