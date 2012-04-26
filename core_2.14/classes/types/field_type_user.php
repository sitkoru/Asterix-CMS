<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Ссылка на структуру					*/
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

class field_type_user extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Пользователь системы', 'value' => 0, 'width' => '100%', 'module' => false, 'scructure_sid' => 'rec');
	
	public $template_file = 'types/user.tpl';
	
	//Поле, используемое для связки
	public $link_field='id';
	
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
		//Варианты значений
		if( IsSet(model::$modules['users']) ){
			if($value)
				$rec = model::makeSql(array(
					'tables' => array(
						model::$modules['users']->getCurrentTable($settings['structure_sid'])
					),
					'where' => array(
						'and' => array(
							'`'.$this->link_field.'`="' . mysql_real_escape_string($value) . '"'
						)
					),
				), 'getrow');
			if ($rec){
				$value = $rec;
				$value = model::$modules['users']->insertRecordUrlType($value);
			}else
				$value = false;
		}else{
			$value = false;
		}
		
		//Готово
		return $value;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array()){
	
		$res = array();

		if(isset($settings['where'])){
			if( substr_count($settings['where'], 'echo')){
				ob_start();
				@eval($settings['where']);
				$s = ob_get_contents();
				ob_end_clean();
			}else{
				$s = $settings['where'];
			}
		}

		$s['is_link_to'] = '`is_link_to`=0';
		$fields = array($this->link_field, 'title', 'login');
		$order = 'order by `admin` desc, `title`';

		
		//Варианты значений
		$variants = $this->model->makeSql(array(
			'tables' => array(model::$modules['users']->getCurrentTable($settings['structure_sid'])),
			'where' => array('and' => $s),
			'fields' => $fields,
			'order' => $order
		), 'getall');

		//Отмечаем в массиве выбранные элементы
		foreach ($variants as $i => $variant)
			if (strlen($variant['title']))
				$res[] = array(
					'value' => $variant[$this->link_field],
					'title' => $variant['title'],
					'login' => $variant['login'],
					'selected' => ($variant[$this->link_field] === $value)
				);

		//Готово
		return $res;
	}
	
}

?>