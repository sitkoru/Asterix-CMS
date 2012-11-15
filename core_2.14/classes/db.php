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
//		mysql_select_db($this->name, $this->connection);
		$this->active_database = $this->name;
	}
	
}


?>