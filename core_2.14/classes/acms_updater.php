<?php

class acms_updater{

	// Необходимо проверить наличие обновлений
	public static function checkUpdate( $params ){
		$result = false;
		
		// Получаем версии пакетов
		$current_version = file( model::$config['path']['core'].'/version.txt' );
		$max_version = file( 'http://src.opendev.ru/version.txt' );
		$max_version_dev = file( 'http://src.opendev.ru/version_dev.txt' );
		
		// Требуется обновление
		if( floatval( $current_version[0] ) < floatval( $max_version[0] ) ){

			$result = array(
				'version' => $max_version[0],
				'version_dev' => $max_version_dev[0],
			);
		
		}
		
		// Готово
		return $result;
	}

}

?>