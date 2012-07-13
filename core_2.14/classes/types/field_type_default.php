<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Прототип типа данных								*/
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

class field_type_default
{
	//Значения по умолчанию
	public $default_settings = array('sid' => false, 'title' => 'Текстовое поле', 'value' => false, 'width' => '100%');
	
	//Поле подлежит изменению
	public $editable = true;
	
	//Поле участввует в поиске
	public $searchable = true;
	
	public $template_file = 'types/default.tpl';
	
	public function creatingString($name){
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}
	
	// Свернуть значение до хранимого вида
	public function implodeValue($field_sid, $record, $old_record = array(), $settings = false, $module_sid = false, $structure_sid = false){
		$record[ $field_sid ] = $this->toValue($field_sid, $record, $old_record, $settings, $module_sid, $structure_sid);
		return $record;
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		//Если значение найдено
		if (IsSet($values[$value_sid])) {
			//Вернуть просто значение, без sql-обёртки
			if ($return_simple_value)
				return $values[$value_sid];
			
			//Готовая к SQL-запросу строка
			else
				return $values[$value_sid];
			
			//Значение не найдено
		} else
			return false;
	}
	
	
	
	
	
	//Получить простое значение по умолчанию из настроек поля
	public function getDefaultValue($settings = false){
		
		if (IsSet($settings['default']))
			return $settings['default'];
		else
			return $this->default_settings['value'];
	}
	/*	
	//Получить простое значение из простого значения
	public function getValue($value, $settings=false, $record=array()){
	return htmlspecialchars_decode($value);
	}
	*/
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		return stripslashes($value);
	}
	/*
	//Получить простое значение для системы управления из простого значения
	public function getAdmValue($value, $settings=false, $record=array()){
	return htmlspecialchars_decode($value);
	}
	*/
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return stripslashes($value);
	}
	
	//Перевести массив значений в само значение для системы управления
	public function impAdmValue($value, $settings = false, $record = array())
	{
		return $value;
	}
	
	
	//Проверить значение, и вернуть тип ошибки если значение не подходит
	public function error($value)
	{
		return false;
	}
}

?>