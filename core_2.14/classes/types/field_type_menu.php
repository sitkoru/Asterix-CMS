<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Выбор из списка						*/
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

class field_type_menu extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Пользователь системы', 'value' => 0, 'width' => '100%');
	
	public $template_file = 'types/menu.tpl';
	
	//Поле, используемое для связки
	public $link_field='sid';
	
	private $table = 'users';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{
		//Настройки поля, переданные из модуля
		if ($settings)
			foreach ($settings as $var => $val)
				$this->$var = $val;
		
		//Готово
		return $values[$value_sid];
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
/*
		$variants = $settings['variants'];
		if( IsSet( $settings['variants'][$value] ) )
			$value = $settings['variants'][$value];
*/	
		return $value;
	}
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array()){
		$variants = $settings['variants'];
		
		//Значения из описания поля
		if($variants){
			if (is_array($variants)){
				foreach ($variants as $sid => $title)
					if( is_int($sid) )
						$res[] = array(
							'value' => $title,
							'title' => $title,
							'selected' => $title == $value,
						);
					else
						$res[] = array(
							'value' => $sid,
							'title' => $title,
							'selected' => $sid == $value,
						);
				$variants = $res;
			}
			
		//Значения из структуры
		}else{
			//Тип menu, используемый в dep_path
			$fields = array( $this->link_field, 'title' );
			if( model::$modules[ $settings['module'] ]->structure[ $settings['structure_sid'] ]['type'] == 'tree' )
				$fields[] = 'tree_level';
			if( IsSet( $settings['module'] ) && IsSet( $settings['structure_sid'] ) ){
				$variants=$this->model->makeSql(
					array(
						'tables'=>array( model::$modules[ $settings['module'] ]->getCurrentTable( $settings['structure_sid'] ) ),
						'fields'=>array( $this->link_field, 'title' ),
						'order'=>'order by `'.$this->link_field.'`'
					),
					'getall'
				);
				foreach($variants as $i=>$variant){
					$variants[$i]['value'] = $variant['sid'];
					$variants[$i]['selected'] = ($variant['sid'] == $value);
				}
			}
		}
		
		//Готово
		return $variants;
	}
}

?>