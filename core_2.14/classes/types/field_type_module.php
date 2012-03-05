<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Выбор из списка						*/
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

class field_type_module extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Ссылка на модуль', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/module.tpl';
	
	private $table = 'modules';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{
		//Настройки поля, переданные из модуля
		if ($settings)
			foreach ($settings as $var => $val)
				$this->$var = $val;
		
		//Готово
		return $values[$value_sid];
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$res      = array();

		//Варианты значений
		$variants = model::$modules;
		
		//Отмечаем в массиве выбранные элементы
		foreach (model::$modules as $sid => $module)
			$res[] = array(
				'value' => $sid,
				'title' => $module->info['title'],
				'selected' => ($sid == $value)
			);
	
		$res[] = array(
			'value' => '',
			'title' => '- ссылка на модуль отсутствует -',
			'selected' => (!$value)
		);
		
		//Готово
		return $res;
	}
}

?>