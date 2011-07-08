<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Число с плавающей запятой				*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 0.01										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 25 сентября 2009 года					*/
/*															*/
/************************************************************/

class field_type_float extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Поле для дробного числа', 'value' => 0, 'width' => '100%');
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` FLOAT(5,2) NOT NULL';
	}
	
}

?>