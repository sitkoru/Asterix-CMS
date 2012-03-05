<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Переключатель Checkbox					*/
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

class field_type_check extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Поле для галки (да/нет)', 'value' => true, 'width' => '100%');
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public $template_file = 'types/check.tpl';
	
	public function creatingString($name){
		return '`' . $name . '` BOOL NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values){
		return intval(@$values[$value_sid]);
	}
	
	
}

?>