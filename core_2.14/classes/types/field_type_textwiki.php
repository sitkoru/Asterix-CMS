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

class field_type_textwiki extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Визуальный редактор', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/textwiki.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	public function getValueExplode($value, $settings = false, $record = array()){
		include_once( model::$config['path']['libraries'].'/markdown.php' );
		return nl2br( Markdown($value) );
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false){
		$values[$value_sid] = strip_tags( $values[$value_sid] );
		return $values[$value_sid];
	}
	
	public function getAdmValueExplode($value, $settings = false, $record = array()){
		return stripslashes($value);
	}
	
}

?>