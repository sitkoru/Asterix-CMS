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
	public $default_settings = array('sid' => false, 'title' => 'Характеристики', 'value' => 0, 'width' => '100%');
	
	public $template_file = 'types/params.tpl';
	
	//Поле участввует в поиске
	public $searchable = true;
	
	public function creatingString($name)
	{
		return '`' . $name . '` LONGTEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false){
	
		$val = array();
		foreach($values[$value_sid]['title'] as $i=>$title)if($title){
			$val[] = array(
				'title' => $title,
				'value' => $values[$value_sid]['value'][ $i ],
				'header' => intval( $values[$value_sid]['header'][ $i ] ),
			);
		}
		
		return serialize( $val );
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array()){
		//Готово
		$value = unserialize( $value );
		return $value;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array()){
		return $this->getValueExplode($value, $settings, $record);
	}
	
	
}

?>