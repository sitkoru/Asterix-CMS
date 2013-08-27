<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Социальный граф						*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 28 октября 2009 года						*/
/*															*/
/************************************************************/

class field_type_graph extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'Социальный граф', 'value' => 'all', 'width' => '100%' );

	//Поле участввует в поиске
	public $searchable = false;

	public $template_file = 'types/graph.tpl';

	public function creatingString( $name )
	{
		return '`' . $name . '` LONGTEXT NOT NULL';
	}

}

?>