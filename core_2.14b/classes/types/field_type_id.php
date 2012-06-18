<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - ID записи								*/
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

class field_type_id extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Системный идентификатор', 'value' => 1, 'width' => '100%');
	
	//Поле участввует в поиске
	public $searchable = false;
	
	//Поле подлежит изменению
	public $editable = false;
	
	public $template_file = 'types/id.tpl';
	
	public function creatingString($name){
		return '`id` INT NOT NULL AUTO_INCREMENT';
	}
	
}

?>