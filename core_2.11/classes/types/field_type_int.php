<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Целое число							*/
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

class field_type_int extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Целое число', 'value' => 0, 'width' => '100%');
	
	public $template_file = 'types/int.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` INT NOT NULL DEFAULT 0';
	}
	
}

?>