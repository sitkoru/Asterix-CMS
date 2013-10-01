<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Текст + Визуальный редактор			*/
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

class field_type_text_editor extends field_type_default
{

	public $default_settings = array( 'sid' => false, 'title' => 'Визуальный редактор', 'value' => '', 'width' => '100%' );

	public $template_file = 'types/text_editor.tpl';

	public function creatingString( $name )
	{
		return '`' . $name . '` LONGTEXT NOT NULL';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue( $value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false )
	{
		//Врзвращаем текстовые поля к нормальному виду
		$values[$value_sid] = str_replace( 'text_area', 'textarea', $values[$value_sid] );

		//Если значение найдено
		if( IsSet($values[$value_sid]) ) {
			//Вернуть просто значение, без sql-обёртки
			if( $return_simple_value )
				return $values[$value_sid];

			//Готовая к SQL-запросу строка
			else
				return $values[$value_sid];

			//Значение не найдено
		} else
			return false;
	}

	public function getAdmValueExplode( $value, $settings = false, $record = array() )
	{
		//Врзвращаем текстовые поля к нормальному виду
		$value = str_replace( 'textarea', 'text_area', $value );

		return stripslashes( $value );
	}

}

?>