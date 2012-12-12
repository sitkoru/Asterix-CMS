<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Выбор из списка (мультивыбор)			*/
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

class field_type_menum extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Пользователь системы', 'value' => 0, 'width' => '100%');
	
	public $template_file = 'types/menum.tpl';
	
	//Поле, используемое для связки
	public $link_field='sid';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` LONGTEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		$vals = array();
		foreach( $values[$value_sid] as $val )
			if( $val )
				$vals[] = $val;
		if( !count( $vals ) )
			return '';
		
		return '|'.implode('|', $vals).'|';
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{

		$vals = explode('|', $value);
		UnSet($vals[0]);
		UnSet($vals[count($vals)]);
		
		$new_vals = array();
		foreach( $vals as $val )
			if( IsSet( $settings['variants'][$val] ) )
				$new_vals[$val] = $settings['variants'][$val];
			else
				$new_vals[] = $val;
		
		return $new_vals;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$res      = array();

		//Варианты значений
		$variants = $settings['variants'];
		
		//Если значение ещё не развёрнуто - разворачиваем
		if( !is_array($value) )
			$value      = explode('|', $value);
		
		//Отмечаем в массиве выбранные элементы
		if(is_array($variants)){
			foreach ($variants as $sid => $title)if($title){
				if( is_int($sid) )
					$res[] = array(
						'value' => $title,
						'title' => $title,
						'selected' => in_array($title, $value)
					);
				else
					$res[] = array(
						'value' => $sid,
						'title' => $title,
						'selected' => in_array($sid, $value)
					);
			}
		
		//Если почему-то нет variants - сохраняем текущие значения
		}else{
			foreach($value as $sid => $title)if($title){
				if( is_array($title) ){
					$res[] = array(
						'value' => $title['value'],
						'title' => '+'.$title['title'],
						'selected' => $title['selected']
					);
				}elseif( is_int($sid) )
					$res[] = array(
						'value' => $title,
						'title' => $title,
						'selected' => in_array($title, $value)
					);
				else
					$res[] = array(
						'value' => $sid,
						'title' => $title,
						'selected' => in_array($sid, $value)
					);
			}
		}
		
		//Готово
		return $res;
	}
	
	//Перевести массив значений в само значение для системы управления
	public function impAdmValue($value, $settings = false, $record = array())
	{
		return '|' . implode('|', $value) . '|';
	}
	
}

?>