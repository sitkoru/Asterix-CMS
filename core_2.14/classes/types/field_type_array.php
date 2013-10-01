<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Массив данных							*/
/*															*/
/*	Версия ядра 2.14										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2012  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 20 декабря 2012 года							*/
/*	Модифицирован: 20 декабря 2012 года						*/
/*															*/
/************************************************************/

class field_type_array extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'JSON-массив', 'value' => '', 'width' => '100%' );

	public $template_file = 'types/html.tpl';

	public function creatingString( $name )
	{
		return '`' . $name . '` LONGTEXT NOT NULL';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue( $value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false )
	{
		if( is_array( $values[$value_sid] ) ) {
			return json_encode( $values[$value_sid] );
		} else
			return $values[$value_sid];
	}

	//Получить развёрнутое значение из простого значения
	public function getValueExplode( $value, $settings = false, $record = array() )
	{
		return json_decode( htmlspecialchars_decode( $value ) );
	}

}

?>