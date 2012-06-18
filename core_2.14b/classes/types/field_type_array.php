<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - HTML-код								*/
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

class field_type_array extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Serialized-массив', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/html.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		if (is_array($values[$value_sid])) {
			return serialize($values[$value_sid]);
		} else
			return $values[$value_sid];
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array()){
		return unserialize( htmlspecialchars_decode( $value ) );
	}
	
}

?>