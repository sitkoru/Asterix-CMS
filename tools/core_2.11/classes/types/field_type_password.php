<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Пароль									*/
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

class field_type_password extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Пароль', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/password.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{
		//Если значение найдено
		if (strlen(@$values[$value_sid]) > 0) {
			//Вернуть просто значение, без sql-обёртки
			return $this->encrypt($values[$value_sid], @$valies['salt']);
			
			//Значение не найдено
		} else
			return false;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		return '';
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return '';
	}
	
	//Шифруем пароль
	public function encrypt($password, $salt = false)
	{
		return md5($password);
		/*
		//С Salt
		if($salt) return md5($password.$record['salt']).$record['salt'];
		//Без Salt
		else return md5($password);
		*/
	}
	
}

?>