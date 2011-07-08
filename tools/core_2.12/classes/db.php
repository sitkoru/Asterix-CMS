<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Менеджер библиотек СУБД								*/
/*															*/
/*	Версия ядра 2.0											*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 25 сентября 2009 года					*/
/*															*/
/************************************************************/

global $config;

//Класс управления базами данных
class database
{
	public function __construct($model)
	{
		$this->model = $model;
		$this->connection = false;
		$this->active_database = false;
	}
	
	public function activate()
	{
		mysql_select_db($this->name, $this->connection);
		$this->active_database = $this->name;
	}
	
}

//Инициализация баз данных
$db = array();
foreach ($config['db'] as $name => $one) {
	if (IsSet($supported_databases[$one['lib_pack']][$one['type']])) {
		$config['db'][$name]['supported'] = true;
		
		require_once($config['path']['libraries'] . '/mysql.php');
		
		$db[$name] = new mysql($model);
		$db[$name]->PConnect($one['host'], $one['user'], $one['password'], $one['name']);
		$db[$name]->Execute('set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"');
		
	} else {
		$config['db'][$name]['supported'] = false;
	}
}

return $db;

?>