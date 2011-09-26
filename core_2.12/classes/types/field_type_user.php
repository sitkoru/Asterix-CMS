<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Пользователь сайта						*/
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
	public $default_settings = array('sid' => false, 'title' => 'Пользователь системы', 'value' => 0, 'width' => '100%');
	
	public $template_file = 'types/user.tpl';
	
	private $table = 'users';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
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
		if ($value) {
			$user_id = $value;
			
			//Достаём пользователя из базы данных
			$value = $this->model->makeSql(array(
				'tables' => array(
					$this->table
				),
				'where' => array(
					'and' => array(
						'id' => '`id`="' . mysql_real_escape_string($user_id) . '"',
						'domain' => $this->model->extensions['domains']->getWhere()
					)
				)
			), 'getrow');
			
			if($value){
				$value['url'].='.html';
			}
			
/*			
			//Если подключен модуль профилей
			if (IsSet($this->model->modules['пользователи'])) {
				//Запрашиваем профиль пользователя
				$value['profile'] = $this->model->modules['пользователи']->loadProfile($user_id);
			}
*/
		} else {
			$value = array(
				'sid' => 'гость',
				'url' => '#',
				'title' => 'Гость',
				'photo' => '0'
			);
		}
		
		//Готово
		return $value;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$recs = $this->model->makeSql(array(
			'tables' => array(
				'users'
			),
			'where' => array(
				'and' => array(
					'domain' => $this->model->extensions['domains']->getWhere()
				)
			),
			'order' => 'order by `admin` desc, `title`'
		), 'getall');
		
		$empty = array(
			array(
				'sid' => 0,
				'title' => '- никто -',
				'selected' => !$value
			)
		);
		$recs  = array_merge($empty, $recs);
		
		//Выставляем выбранное значение
		foreach ($recs as $i => $rec)
			$recs[$i]['selected'] = ($rec['id'] == $value);
		
		return $recs;
	}
}

?>