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
		return '`' . $name . '` LONGTEXT NOT NULL';
	}
	
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		if (IsSet($values[$value_sid]))
			return htmlspecialchars('|' . implode('|', $values[$value_sid]) . '|');
		else
			return false;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		if( strlen( $value ) ){
		
			// Разбираем значения
			$arr  = explode('|', $value);
			$res  = array();
			UnSet( $arr[ count($arr)-1 ] );
			UnSet( $arr[0] );
			$arr = array_values( $arr );
			
			$fields = array(
				$this->link_field,
				'title',
				'url'
			);
			if( IsSet( $record['img'] ) )
				$fields[] = 'img';
				
			//Варианты значений
			$this->structure = model::$modules[$settings['module']]->structure[$settings['structure_sid']];
			if( $this->structure['type'] == 'simple' )
				$order = 'order by `title`';
			else{
				$order = 'order by `left_key`';
				$fields[] = 'tree_level';
			}
			if( isset($this->structure['fields']['pos']) )
				$order = 'order by `pos` asc';
			
			
			/*
				Тестовый вариант запроса записей поштучно а не единым запросом.
				
				На больших сайтах, когда одни и те же записи достаются из базы в различных комбинациях
				такой вид единичных запросов лучше кешируется, что позвоняет
				не тянуть одни и те же записи несколько раз
			*/
			$test = false;
			if( $test ){
			
				if( !count($arr) )
					return false;
				
				$recs = array();
				foreach( $arr as $id )
					if( $id ){
						$sql = 'select * from `'.model::$modules[$settings['module']]->getCurrentTable($settings['structure_sid']).'` where `'.$this->link_field.'`='.intval( $id ).''.(IsSet( $settings['where'] )?' and '.$settings['where']:'').' limit 1';
						$rec = model::execSql($sql, 'getrow');
						if( $rec )
							$recs[] = $rec;
					}
			}else{
			
				if( count( $arr )>1 ){
					$recs = model::execSql('select * from `'.model::$modules[$settings['module']]->getCurrentTable($settings['structure_sid']).'` where `'.$this->link_field.'` in ('.implode(',', $arr).')'.(IsSet( $settings['where'] )?' and '.$settings['where']:'').' '.$order, 'getall');
				}else{
					if( !intval( $arr[0] ) )
						return false;
					$rec = model::execSql('select * from `'.model::$modules[$settings['module']]->getCurrentTable($settings['structure_sid']).'` where `'.$this->link_field.'`='.intval( $arr[0] ).''.(IsSet( $settings['where'] )?' and '.$settings['where']:'').' '.$order.' limit 1', 'getrow');
					$recs = array( $rec );
				}
				
			}
			
			foreach($recs as $i=>$rec){
				if( IsSet( $rec['img']) )
					$rec['img'] = model::$types['image']->getValueExplode($rec['img'], $modules[ $settings['module'] ]->structure[ $settings['structure_sid'] ]['fields']['img'], $record );
				$recs[$i]=model::$modules['start']->insertRecordUrlType($rec);
			}
			
		}else{
		
			$recs = array();
		
		}
			
		pr_r( $recs );
		
		//Готово
		return $recs;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
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
				
		$fields = array(
			$this->link_field,
			'title',
			'url'
		);
		$this->structure = model::$modules[$settings['module']]->structure[$settings['structure_sid']];
		//Варианты значений
		if( $this->structure['type'] == 'simple' )
			$order = 'order by `title`';
		else{
			$order = 'order by `left_key`';
			$fields[] = 'tree_level';
		}
		
			
		//Варианты значений
		$variants = model::makeSql(array(
			'tables' => array(
				model::$modules[$settings['module']]->getCurrentTable($settings['structure_sid'])
			),
			'where' => (IsSet($settings['where']) ? array('and' => array($s)) : false),
			'fields' => $fields,
			'order' => $order
		), 'getall');
		
		//Если значение ещё не развёрнуто - разворачиваем
		$arr      = explode('|', $value);
		
		//Отмечаем в массиве выбранные элементы
		foreach ($variants as $i => $variant)
			if (strlen($variant[$this->link_field]))
				$res[] = array(
					'value' => $variant[$this->link_field],
					'title' => $variant['title'],
					'selected' => in_array($variant[$this->link_field], $arr),
					'tree_level' => $variant['tree_level'],
				);
		
		//Готово
		return $res;
	}
	
	//Перевести массив значений в само значение для системы управления
	public function impAdmValue($value, $settings = false, $record = array())
	{
		return '|' . implode('|', $value) . '|';
	}
	
	public function toggleInLinkm($value, $id = false){
		if(!$value)$value='|';
		if(!$id)$id = user::$info['id'];
		if( substr_count($value, '|'.$id.'|') ){
			$value = str_replace('|'.$id.'|','|',$value);
			if( $value == '|' )$value='';
		}else{
			$value .= $id.'|';
		}
		return $value;
	}

}

?>