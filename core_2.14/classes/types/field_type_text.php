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

class field_type_text extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'Однострочный текст', 'width' => '100%' );

	public $template_file = 'types/text.tpl';

	public function creatingString( $name )
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode( $value, $settings = false, $record = array() )
	{
		return htmlspecialchars( stripslashes( $value ) );
	}

}

?>