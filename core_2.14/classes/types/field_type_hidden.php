<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Строка									*/
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

class field_type_hidden extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'Однострочный текст', 'value' => '', 'width' => '100%' );

	public $template_file = 'types/hidden.tpl';

	//Поле участввует в поиске
	public $searchable = false;

	public function creatingString( $name )
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}

	//Получить простое значение по умолчанию из настроек поля
	public function getDefaultValue( $settings = false )
	{
		if( $settings['sid'] == 'session_id' )
			return md5( date( "Y-m-d H:i:s" ) . rand() );
		elseif( IsSet($settings['default']) )
			return $settings['default']; else
			return $this->default_settings['value'];
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode( $value, $settings = false, $record = array() )
	{
		return htmlspecialchars( stripslashes( $value ) );
	}

}

?>