<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Интерфейс работы с деревьями Nested Sets			*/
/*															*/
/*	Версия ядра 2.0											*/
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

class nested_sets
{
	public function __construct($model, $table)
	{
		//Донастройка управляющей библиотеки (себя)
		$this->model  = $model;
		$this->table  = $table;
	}
	
	
	//Получить все дерево
	public function getFull($fields, $condition = false)
	{
		return $this->getSub(false, $fields, $condition);
	}
	
	//Получить все поддерево
	public function getSub($record_id = false, $fields, $condition = false, $cache = false)
	{
		$fields[] = 'tree_level';
		
        //Поля, которые запрашиваем
		if (is_array($fields)) {
            $fields = '`'.implode('`, `', $fields).'`';
        } else {
            $fields = '*';
        }
		
		//Условия выборки
		if( !$condition )
			$where = '1';
		else
			$where = '('.implode(') and (', $condition['and']).')';
		
		//Указан корень
		if($record_id){
			$root = $this->model->execSql('select `left_key`, `right_key` from `'.$this->table.'` where `id`='.intval($record_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
			$where .= ' and `left_key`>='.$root['left_key'].' and `right_key`<='.$root['right_key'];
		}

		//Выполняем
		$recs = $this->model->execSql('select '.$fields.' from `'.$this->table.'` where '.$where.' order by `left_key`', 'getall');
//		pr($this->model->last_sql);
		
		//Готово
		return $recs;
	}
	
	
	//Добавление записи в конец таблицы
	public function addChild($parent_id, $record, $conditions = false)
	{
		//Ищем Родителя, в которого вставлять
		$root = $this->model->execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//Обновляем ключи записей, идущие после текущей записи (раздвигаем ветки)
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+2) where `left_key`>'.$root['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');
		$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`+2) where `right_key`>='.$root['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');

		//Ключи новой записи
		$record['left_key'] = $root['right_key'];
		$record['right_key'] = $root['right_key']+1;
		$record['tree_level'] = $root['tree_level']+1;
		
		//Вставляем в пустое место запись
		$what = array();
		foreach($record as $var=>$val)
			$what[] = '`'.mysql_real_escape_string($var).'`="'.mysql_real_escape_string($val).'"';
		$this->model->execSql('insert into `'.$this->table.'` set '.implode(', ', $what).'','insert');
	}
	
	//Перемещение внутрь определенной записи
	public function moveChild($parent_id, $record_id, $condition = false)
	{
		//Забираем запись, которую перемещаем
		$record = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//Забираем запись родителя, в которую перемещаем запись
		$root = $this->model->execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		$right_key_near = $root['right_key']-1;
		
		//Смещения
		$skew_level = $root['tree_level'] - $record['tree_level'] + 1;
		$skew_tree = $record['right_key'] - $record['left_key'] + 1;
		$skew_edit = $right_key_near - $record['left_key'] + 1 - $skew_tree;
			
		//ID перемещаемых записей
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','getall');
		$ids = array();
		foreach($t as $ti)$ids[]=$ti['id'];
		
		//Перемещаем вверх по дереву
		if( $root['right_key']<$record['right_key']){
			$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.$skew_tree.') where `left_key`<'.$record['right_key'].' and `left_key`>'.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');
			$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`+'.$skew_tree.') where `right_key`<='.$record['right_key'].' and `right_key`>'.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');

		//Перемещаем вниз по дереву
		}else{
			$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$skew_tree.') where `left_key`>'.$record['right_key'].' and `left_key`<='.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');
			$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$skew_tree.') where `right_key`>'.$record['right_key'].' and `right_key`<='.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');
		}
		
		//Перемещаем ветку
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($skew_edit).'), `right_key`=(`right_key`+'.$skew_edit.'), `tree_level`=(`tree_level`+'.$skew_level.') where `id` IN ('.implode(',', $ids).') and '.$this->model->extensions['domains']->getWhere().'', 'update');
	}
	
	
	//Поменять местами две записи
	public function move($first_id, $second_id, $conditions = false)
	{
		//Забираем запись, которую перемещаем
		$first = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($first_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//Забираем запись родителя, в которую перемещаем запись
		$second = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($second_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		$right_key_near = $first['right_key'];
		$left_key_near = $first['left_key'];
		
		//Смещения
		$first_volume = $first['right_key'] - $first['left_key'] + 1;
		$second_volume = $second['right_key'] - $second['left_key'] + 1;
			
		//ID перемещаемых записей
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$first['left_key'].' and `right_key`<='.$first['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','getall');
		$first_ids = array();
		foreach($t as $ti)$first_ids[]=$ti['id'];
		
		//ID перемещаемых записей
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$second['left_key'].' and `right_key`<='.$second['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','getall');
		$second_ids = array();
		foreach($t as $ti)$second_ids[]=$ti['id'];
		
		//Перемещаем ветку
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).') and '.$this->model->extensions['domains']->getWhere().'', 'update');
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).') and '.$this->model->extensions['domains']->getWhere().'', 'update');
	}
	
	//Удаление
	public function delete($record_id, $conditions = false)
	{
		//Забираем запись, которую перемещаем
		$record = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//Смещения
		$volume = $record['right_key'] - $record['left_key'] + 1;

		//Удаляем
		$this->model->execSql('delete from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'', 'delete');
		
		//Подтягиваем остальные
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$volume.') where `left_key`>'.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');
		$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$volume.') where `right_key`>='.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');
	}
	
}

?>