<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Ссылка на структуру (с мультивыбором)	*/
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

class field_type_linkm extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Пользователь системы', 'value' => 0, 'width' => '100%', 'module' => false, 'scructure_sid' => 'rec');
	
	public $template_file = 'types/linkm.tpl';
	
	//Поле, используемое для связки
	public $link_field='id';
	
	private $table = 'users';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		if (IsSet($values[$value_sid]))
			return htmlspecialchars('|' . implode('|', $values[$value_sid]) . '|');
		else
			return false;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		$arr  = explode('|', $value);
		$res  = array();
		//Варианты значений
		$recs = $this->model->makeSql(array(
			'tables' => array(
				$this->model->modules[$settings['module']]->getCurrentTable($settings['structure_sid'])
			),
			'fields' => array(
				$this->link_field,
				'title'
			),
			'where' => array(
				'and' => array(
					'``'.$this->link_field.'` in ("' . implode('", "', $arr) . '")',
					(IsSet($settings['sql']) ? $settings['sql'] : '1')
				)
			),
			'order' => 'order by `title`'
		), 'getall');
		
		//Готово
		return $recs;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$res = array();
		
		//Варианты значений
		$variants = $this->model->makeSql(array(
			'tables' => array(
				$this->model->modules[$settings['module']]->getCurrentTable($settings['structure_sid'])
			),
			'where' => (IsSet($settings['where']) ? array(
				'and' => $settings['where']
			) : false),
			'fields' => array(
				$this->link_field,
				'title'
			),
			'order' => 'order by `title`'
		), 'getall');
		
		//Если значение ещё не развёрнуто - разворачиваем
		$arr      = explode('|', $value);
		
		//Отмечаем в массиве выбранные элементы
		foreach ($variants as $i => $variant)
			if (strlen($variant[$this->link_field]))
				$res[] = array(
					'value' => $variant[$this->link_field],
					'title' => $variant['title'],
					'selected' => in_array($variant[$this->link_field], $arr)
				);
		
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