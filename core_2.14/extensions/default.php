<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Прототип расширений									*/
/*															*/
/*	Версия ядра 2.0.b5										*/
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

class extention_default
{
	var $title = 'Расширение, которое ничего не делает, а лишь показывает что может делать расширение';

	//Инициализация расширения
	public function __construct( $db, $log )
	{
		$this->log = $log;
	}

	//Запуск модуля
	public function onModuleStart()
	{
	}

	//Запуск контроллера
	public function onControllerStart()
	{
	}

	//Перед выполнением запроса
	public function onSql( $fields, $tables, $where = false, $group = false, $order = false, $limit = false, $query_type = 'getall' )
	{
		return array(
			$fields,
			$tables,
			$where,
			$group,
			$order,
			$limit
		);
	}

	//Добавляем системное поле в модуль
	public function addFields()
	{
	}

	//Перед записью данных в шаблонизатор
	public function onAssign()
	{
	}

	//Перед обработкой шаблона
	public function onFetch()
	{
	}
}

?>