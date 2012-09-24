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

class field_type_link extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Пользователь системы', 'value' => 0, 'width' => '100%', 'module' => false, 'scructure_sid' => 'rec');
	
	public $template_file = 'types/link.tpl';
	
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
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		return $values[$value_sid];
	}
	
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{

		if( !IsSet( model::$modules[ $settings['module'] ] ) )
			return false;
		
		//Варианты значений
		if( IsSet(model::$modules[$settings['module']]) ){
			if($value)
				$rec = model::execSql('select * from `'.model::$modules[$settings['module']]->getCurrentTable($settings['structure_sid']).'` where `'.$this->link_field.'`=' . intval($value) . ' limit 1', 'getrow');
			if ($rec){
				$value = $rec;
				$value = model::$modules[$settings['module']]->insertRecordUrlType($value);
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
		
		$fields = array($this->link_field, 'title');
		$order = 'order by `title`';
		if( model::$modules[ $settings['module'] ]->structure[ $settings['structure_sid'] ]['type'] == 'tree' ){
			$fields[] = 'tree_level';
			$order = 'order by `left_key`';
		}
		
		if( !IsSet( model::$modules[ $settings['module'] ] ) )
			return false;
		
		//Варианты значений
		$variants = model::makeSql(array(
			'tables' => array(model::$modules[ $settings['module'] ]->getCurrentTable($settings['structure_sid'])),
			'where' => (IsSet($settings['where']) ? array('and' => array($s)) : false),
			'fields' => $fields,
			'order' => $order
		), 'getall');
		
		//Отмечаем в массиве выбранные элементы
		foreach ($variants as $i => $variant)
			if ( strlen( $variant['title'] ) ){
				$res[] = array(
					'value' => $variant[$this->link_field],
					'title' => $variant['title'],
					'tree_level' => $variant['tree_level'],
					'selected' => ( (string)$variant[$this->link_field] === (string)$value)
				);
			}
		
		//Готово
		return $res;
	}
	
}

?>