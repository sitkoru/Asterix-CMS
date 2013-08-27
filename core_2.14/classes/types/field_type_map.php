<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Местоположение на карте				*/
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

class field_type_map extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'Точка на карте', 'value' => '55.160632|61.398726|14', 'width' => '100%' );

	public $template_file = 'types/map.tpl';

	public $types = array(
		'yandex#map',
		'yandex#satellite',
		'yandex#hybrid',
		'yandex#publicMap'
	);


	//Поле участввует в поиске
	public $searchable = false;

	public function creatingString( $name )
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue( $value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false )
	{
		return @$values[$value_sid]['x'] . '|' . @$values[$value_sid]['y'] . '|' . @$values[$value_sid]['z'];
	}


	//Получить развёрнутое значение из простого значения
	public function getValueExplode( $value, $settings = false, $record = array() )
	{
		list($val['x'], $val['y'], $val['z'], $val['type']) = explode( '|', $value );
		if( !$val['type'] )
			$val['type'] = 'yandex#map';

		return $val;
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode( $value, $settings = false, $record = array() )
	{
		return $this->getValueExplode( $value, $settings, $record );
	}
}

?>