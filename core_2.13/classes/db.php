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

//Поддерживаемые форматы баз данных
$supported_databases = array(
	false => array(
		'mysql' => 'mysql.php'
	),
	'ado' => array(
		'mysql' => 'adodb5/adodb.inc.php'
	)
);

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
		
		require_once($config['path']['libraries'] . '/' . $supported_databases[$one['lib_pack']][$one['type']]);
		
		$n = $one['type'];
		
		//ADO
		if ($one['lib_pack'] == 'ado') {
			$db[$name] = ADONewConnection($one['type']);
			$db[$name]->SetFetchMode(ADODB_FETCH_ASSOC);
			$db[$name]->debug = false;
			
		//не ADO
		} else {
			$db[$name] = new $n($model);
		}
		
		$db[$name]->PConnect($one['host'], $one['user'], $one['password'], $one['name']);
		if( !$db[$name]->connection ){
			print('Ошибка соединения с базой данных.');
			exit();
		}
		
		$db[$name]->Execute('set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"');
		
	} else {
		$config['db'][$name]['supported'] = false;
	}
}

return $db;

?>