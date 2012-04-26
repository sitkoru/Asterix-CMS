<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Расширение работы с языковыми версиями				*/
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

require_once 'default.php';

class extention_languages extends extention_default
{
	var $title = 'Языковая версия';
	var $sid = 'ln';
	
	//Инициализация расширения
	public function __construct($model)
	{
		$this->model = $model;
		/*		
		//Вставим дополнительные поля в модули
		$this->insertFields();
		*/
	}
	
	//Инициализация расширения
	public function execute()
	{
		//Вставим дополнительные поля в модули
		$this->insertFields();
	}
	
	//Вставим дополнительные поля в модули
	private function insertFields()
	{
		foreach (model::$modules as $module_sid => $module)
			if ($module->structure) {
				foreach ($module->structure as $structure_sid => $structure) {
					model::$modules[$module_sid]->structure[$structure_sid]['fields']['ln'] = array(
						'sid' => 'ln',
						'group' => 'system',
						'type' => 'ln',
						'title' => 'Языковая версия'
					);
				}
			}
	}
	
	//Перед выполнением запроса
	public function onSql($fields, $tables, $where = false, $group = false, $order = false, $limit = false, $query_type = 'getall')
	{
		if ($query_type == 'insert') {
			if (!$fields)
				$fields = array();
			if (!IsSet($fields['ln']))
				$fields['ln'] = '`ln`=1';
		} else {
			if (!$where)
				$where = array();
			if (!IsSet($where['and']['ln']))
				$where['and']['ln'] = '`ln`=1';
		}
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
		return array(
			'ln' => array(
				'sid' => 'ln',
				'group' => 'system',
				'type' => 'ln',
				'title' => 'Языковая версия',
				'value' => '1'
			)
		);
	}
}

?>