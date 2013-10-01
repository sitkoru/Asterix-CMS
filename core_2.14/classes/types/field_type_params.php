<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Набор характеристик					*/
/*															*/
/*	Версия ядра 2.13										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 ноября 2011 года								*/
/*	Модифицирован: 10 ноября 2011 года						*/
/*															*/
/************************************************************/

class field_type_params extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'Характеристики', 'value' => 0, 'width' => '100%' );

	public $template_file = 'types/params.tpl';

	//Поле участввует в поиске
	public $searchable = true;

	public function creatingString( $name )
	{
		return '`' . $name . '` LONGTEXT NOT NULL';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue( $value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false )
	{

		$val = array();

		// Нормальный формат
		if( IsSet($values[$value_sid]['title']) ) {
			foreach( $values[$value_sid]['title'] as $i => $title ) if( $title ) {
				$val[] = array(
					'title'  => $title,
					'value'  => $values[$value_sid]['value'][$i],
					'ed'     => $values[$value_sid]['ed'][$i],
					'type'   => $values[$value_sid]['type'][$i],
					'group'  => $values[$value_sid]['group'][$i],
					'header' => intval( $values[$value_sid]['header'][$i] ),
				);
			}

			// Обратный формат, значения переданы as is, не из формы
		} elseif( IsSet($values[$value_sid][0]['title']) ) {
			$val = $values[$value_sid];
		}

		return serialize( $val );
	}

	//Получить развёрнутое значение из простого значения
	public function getValueExplode( $value, $settings = false, $record = array() )
	{
		//Готово
		$value = unserialize( $value );

		// Приводим значения типов данных
		foreach( $value as $i => $param ) {

			if( $param['value'] === '-' ) {
				$value[$i]['value'] = 'н/д';

			} elseif( $param['type'] == 'int' ) {
				$value[$i]['value'] = (int)$param['value'];

			} elseif( $param['type'] == 'float' ) {
				$value[$i]['value'] = (float)$param['value'];

			} elseif( $param['type'] == 'boolean' ) {
				if( $param['value'] )
					$value[$i]['value'] = 'да';
				else
					$value[$i]['value'] = 'нет';

			}
		}

		return $value;
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode( $value, $settings = false, $record = array() )
	{
		//Готово
		return unserialize( $value );
	}

	public function getByTitle( $value )
	{
	}

	public function getByValue( $value )
	{
	}


}

?>