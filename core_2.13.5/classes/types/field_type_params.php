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
		//Готово
		return serialize( $values[ $value_sid ] );
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array()){
		//Готово
		return unserialize( $values[ $value_sid ] );
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array()){
		$value = $this->getValueExplode($value, $settings, $record);
		$keys = @array_keys($value);
		
		if( $value )
			foreach($value as $key => $val)
				$res[] = array(
					'title' => $key,
					'value' => $val,
					'variants' => $keys,
				);
			
		$variants = array('Операционная система', 'Процессор', 'Ширина экрана', 'Количество цветов', 'Стандарт WiFi');	
		$res = array(
			array(
					'title' => 'Операционная система',
					'value' => 'Google Android 2.3.3',
					'variants' => $variants,
			),
			array(
					'title' => 'Процессор',
					'value' => 'Intel Core i5',
					'variants' => $variants,
			),
			array(
					'title' => 'Диагональ экрана',
					'value' => '14"',
					'variants' => $variants,
			),
			array(
					'title' => 'Количество цветов',
					'value' => '12 млн',
					'variants' => $variants,
			),
			array(
					'title' => 'Стандарт WiFi',
					'value' => '1.0',
					'variants' => $variants,
			),
		);
		
		return $res;
	}
	
	
}

?>