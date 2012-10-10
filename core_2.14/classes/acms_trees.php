<?php

class acms_trees{

	//Показать краткое дерево модуля
	public function getModuleShirtTree(
			$root_record_id,		//id записи, с которой начинать счетать дерево
			$structure_sid,			//интересующая нас структура
			$levels_to_show,		//количество уровней, которые необходимо найти
			$conditions=array()	//уcловия выборки веток
		){

		return acms_trees::getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
	}

	//Показать краткое дерево сруктуры
	public function getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions){
		//Некоторые структуры скрываются из деревьев
		if(!$this->structure[$structure_sid]['hide_in_tree']){
			//Древовидные структуры
			if($this->structure[$structure_sid]['type']=='tree'){
				$recs = acms_trees::getStructureShirtTree_typeTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
			//Линейные структуры
			}else{
				$recs = acms_trees::getStructureShirtTree_typeSimple($root_record_id,$structure_sid,$levels_to_show,false,$conditions);
			}
		}

		return $recs;
	}

	//Поиск краткого дерева в древовидной структуре
	public function getStructureShirtTree_typeTree($root_record_id,$structure_sid,$levels_to_show,$conditions){

		//Если не установлен обработчик таблицы деревьев - устанавливаем
		if( !IsSet($this->structure[$structure_sid]['db_manager']) ){
			require_once(model::$config['path']['core'].'/classes/nestedsets.php');
			$this->structure[$structure_sid]['db_manager']=new nested_sets(model,$this->getCurrentTable($structure_sid));
		}

		//Обработка расширениями - получаем в Where подстановки от расширений
		if(model::$extensions)foreach(model::$extensions as $ext){
			if( method_exists ( $ext , 'onSql' ) )
				list($a,$a,$where,$a,$a,$a)=$ext->onSql(false,false,$where,false,false,false);
		}

		//Учитываем переданные в функцию условия
		if(is_array($conditions['and'])){
			if($where)
				$where['and']=array_merge($where['and'],$conditions['and']);
			else
				$where['and']=$conditions['and'];
		}

		//Учитываем уровень
		if($levels_to_show > 0){
			//Если указан корень откуда брать дерево - будем брать количество уровней относительно указанного
			if($root_record_id){
				$rec=$this->getRecordById($structure_sid,$root_record_id);
				if($rec['tree_level']==1){
					$where['and']['tree_level']='( (`tree_level`>='.intval($rec['tree_level']).') and (`tree_level`<'.($rec['tree_level']+$levels_to_show+1).') )';
				}else{
					$where['and']['tree_level']='( (`tree_level`>'.intval($rec['tree_level']).') and (`tree_level`<='.($rec['tree_level']+$levels_to_show+1).') )';
				}
			}else{
				$where['and']['tree_level']='`tree_level`<='.($levels_to_show+1).'';
			}
		}
		
		//Если указано откуда считать дерево - счетаем поддерево
		if($root_record_id){

			//Засекаем время
			$t=explode(' ',microtime());
			$sql_start=$t[1]+$t[0];

			//Поля для вывода
			$what = $this->getMainFields($structure_sid);

			//Забираем записи полного дерева
			$recs=$this->structure[$structure_sid]['db_manager']->getSub($root_record_id, $what, $where);
			
			//Сколько прошло
			$t=explode(' ',microtime());
			$sql_stop=$t[1]+$t[0];
			$time=$sql_stop-$sql_start;

			//Статистика
			log::sql('nested_sets -> getSub',$time,$recs,$this->info['sid'],'getStructureShirtTree_typeTree');

		//Иначе получаем полное дерево структуры
		}else{
			//Засекаем время
			$t=explode(' ',microtime());
			$sql_start=$t[1]+$t[0];

			//Поля для вывода
			$what = $this->getMainFields($structure_sid);

			//ЗДЕСЬ ДОДУМАТЬ ОТКУДА БЕРУТСЯ ОТРИЦАТЕЛЬНЫЕ ЗНАЧЕНИЯ И ВОССТАНОВИТЬ ИХ АККУРАТНО
			//Переход между модулями тратит 2 уровня "tree_level", восстанавливаем их
			if(count($this->structure)>1)
				if(IsSet($where['and']['tree_level']))
					$where['and']['tree_level']='`tree_level`>1';

			//Забираем записи полного дерева
			$recs=$this->structure[$structure_sid]['db_manager']->getFull($what,$where);

			//Сколько прошло
			$t=explode(' ',microtime());
			$sql_stop=$t[1]+$t[0];
			$time=$sql_stop-$sql_start;

			//Статистика
			log::sql('nested_sets -> getFull',$time,$recs,$this->info['sid'],'getStructureShirtTree_typeTree');
		}

		if(!count($recs)){
			if( (model::$ask->structure_sid != 'rec') ){
				// Сначала смотрим зависимые структуры
				// потом к ним будем вызывать рекурсии
				$search_children=false;
				foreach($this->structure as $s_sid=>$s)
					if($s['dep_path']['structure']==model::$ask->structure_sid){
						$search_children=$s_sid;
						$link_type = $s['dep_path']['link_type'];
					}

				if($search_children){
					//Связь производится по полю
					$dep_field_sid = model::$types[$link_type]->link_field;
					
					//Ищем только значимых родителей
					if( $rec[$dep_field_sid] ){
						$where = array( 
							'and' => array(
								'dep_path_'.model::$ask->structure_sid.''=>'`dep_path_'.model::$ask->structure_sid.'`="'.mysql_real_escape_string($rec[ $dep_field_sid ]).'"'
							)
						);
						$recs = acms_trees::getStructureShirtTree_typeSimple(false,$search_children,$levels_to_show,$where);
					}
					
				}
			}
		}
		
		//Ищем ссылки на модули и считаем их деревья
		if($recs)
		foreach($recs as $i=>$rec){

			//Вложенные модули
			if($levels_to_show>1)
			if(strlen($rec['is_link_to_module'])){

				$recs[$i]['module']=$this->info['sid'];
				$recs[$i]['structure_sid']=$structure_sid;

				if( IsSet( model::$modules[ $rec['is_link_to_module'] ] ) ){
					if( is_object( model::$modules[ $rec['is_link_to_module'] ] ) ){

						//Корневая структура зависимого модуля
						$tree = model::$modules[$rec['is_link_to_module']]->getLevels('rec');
						$dep_structure_sid = $tree[count($tree)-1];
					
						//Ищем записи вложеного модуля
						$tmp=model::$modules[ $rec['is_link_to_module'] ]->getModuleShirtTree(false,$dep_structure_sid,$levels_to_show-1,$conditions);
						
						//Нашли вложенные модули
						if(count($tmp)){

							//Если вложенные записи есть на ряду с вложенными модулями - суммируем
							if(IsSet($recs[$i]['sub'])){
								$recs[$i]['sub']=array_merge($recs[$i]['sub'],$tmp);
							//Отсутствуют вложенные записи, только вложенные модули
							}else{
								$recs[$i]['sub']=$tmp;
							}
						}
					}
				}
			}

			//Вложенные структуры в пределах этого модуля
			if(count($this->structure)>1){
				//Ищем следующий уровень
				$levels=$this->getLevels('rec', array());
				$levels=array_reverse($levels);
				$next_structure_sid=false;
				foreach($levels as $j=>$level)if($level==$structure_sid)$next_structure_sid=@$levels[$j+1];
				//Нашли вложенную структуру в данном модуле
				if($next_structure_sid){
					//Название поля-связки с текущей структурой
					$field_name='dep_path_'.$structure_sid;
					//Добавялем условие поиска
					$where=$conditions;
					$where['and'][$field_name]='`'.mysql_real_escape_string($field_name).'`="'.mysql_real_escape_string($rec['sid']).'"';
				}
				//Забираем вложенные записи структуры
				$subs=acms_trees::getStructureShirtTree(false,$next_structure_sid,$levels_to_show-1,$where);
				//Нашли вложенные модули
				if($subs){
					//Если вложенные записи есть на ряду с вложенными модулями - суммируем
					if(IsSet($recs[$i]['sub'])){
						$recs[$i]['sub']=array_merge($recs[$i]['sub'],$subs);
					//Отсутствуют вложенные записи, только вложенные модули
					}else{
						$recs[$i]['sub']=$subs;
					}
				}
			}

		}

		//Перекомпановка из линейного массива во вложенные списки
		$recs=self::reformRecords($recs,$recs[0]['tree_level'],0,count($recs));
		//Вставляем окончание .html
		$recs=$this->insertRecordUrlType($recs);

		//Помним какая запись из какого модуля
		foreach($recs as &$rec){
			if(!IsSet($rec['module'])){
				$rec['module']=$this->info['sid'];
				$rec['structure_sid']=$structure_sid;
			}
		}

		return $recs;
	}

	//Поиск краткого дерева в линейной структуре
	public function getStructureShirtTree_typeSimple($root_record_id,$structure_sid,$levels_to_show,$where=false,$conditions=false){

		if($root_record_id){
//			pr('-> '.$this->info['sid'].'_'.$structure_sid.' ['.$root_record_id.']');

			// Сначала смотрим зависимые структуры
			// потом к ним будем вызывать рекурсии
			$search_children=false;
			if($structure_sid!='rec')
				if($this->structure)
					foreach($this->structure as $s_sid=>$s)
						if($s['dep_path']['structure']==$structure_sid){
							$search_children=$s_sid;
						}

			//Найдена структура-потомок
			if($search_children){
				$parent=$this->getRecordById($structure_sid,$root_record_id);

				//В разных типах используются разные поля для связки
				//Берём нужное поле связки
				$link_field=model::$types[$this->structure[$search_children]['dep_path']['link_type']]->link_field;

				//Условие связи элементов
				$where['and']=array('`dep_path_'.$structure_sid.'`="'.$parent[$link_field].'"');

				//Учитываем переданные в функцию условия
				if(is_array($conditions['and'])){
					$where['and']=array_merge($where['and'],$conditions['and']);
				}

				//Ищем потомков
				if($search_children){
					$recs=acms_trees::getStructureShirtTree_typeSimple(false,$search_children,$levels_to_show,$where);
				}
			}

		//Смотрим всю структуру
		}else{

			//Учитываем переданные в функцию условия
			if(is_array($conditions['and']) && is_array($where) ){
				$where['and']=array_merge($where['and'],$conditions['and']);
			}elseif(is_array($conditions['and'])){
				$where=$conditions;
			}

			// Сначала смотрим зависимые структуры
			// потом к ним будем вызывать рекурсии
			$search_children=false;
			if($structure_sid!='rec')
				if($this->structure)
					foreach($this->structure as $s_sid=>$s)
						if($s['dep_path']['structure']==$structure_sid){
							$search_children=$s_sid;
						}

			//Сортировка:
			//если есть поле POS - сортируем по нему,
			//иначе сортируем по публичной дате, в обратном порядке
			$order=IsSet($this->structure[$structure_sid]['fields']['pos'])?'order by `pos`':'order by `date_public` desc';

			//Получаем записи
			if($levels_to_show > 0){
				$recs=model::makeSql(
					array(
						'tables'=>array($this->getCurrentTable($structure_sid)),
						'where'=>$where,
						'order'=>$order
					),
					'getall'
				);
			}//pr(model::$last_sql);
			
			//Вставляем завиcимые записи если нужно
			if(is_array($recs))
			if($search_children)
			if($structure_sid!='rec')
			if($levels_to_show > 1)
			foreach($recs as $i=>$rec){

				//В разных типах используются разные поля для связки
				//Берём нужное поле связки
				$link_field=model::$types[$this->structure[$search_children]['dep_path']['link_type']]->link_field;

				//Условие связи элементов
				$where['and']=array('`dep_path_'.$structure_sid.'`="'.$rec[$link_field].'"');

				//Учитываем переданные в функцию условия
				if(is_array($conditions['and'])){
					$where['and']=array_merge($where['and'],$conditions['and']);
				}
				
				//Ищем потомков
				if($search_children){
					$children=acms_trees::getStructureShirtTree_typeSimple($root_record_id,$search_children,$levels_to_show-1,$where);
					if($children)$recs[$i]['sub']=$children;
				}
			}
		}
		//Вставляем окончание .html
		$recs=$this->insertRecordUrlType($recs);
		
		//Помним какая запись из какого модуля
		if($recs)
		foreach($recs as $i=>$rec){
			if( !$recs[$i]['module'] )
				$recs[$i]['module'] = $this->info['sid'];
			if( !$recs[$i]['structure_sid'] )
				$recs[$i]['structure_sid'] = $structure_sid;
		}

		//Готово
		if(count($recs))
			return $recs;
		else 
			return false;
	}
	
	//Рекурсивная функция переформирования линейного списка записей в дерево
	public static function reformRecords($recs,$level,$from,$to){
		$found=array();
		for($i=$from;$i<$to;$i++){
			if($recs[$i]['tree_level']==$level){
				$found[]=array('id'=>$i,'from'=>$i+1);
			}
		}
		$res=array();
		foreach($found as $i=>$f){
			if($i+1==count($found)){
				$new_subs=self::reformRecords($recs,$level+1,$f['from'],$to);
			}elseif($f['from']<$found[$i+1]['from']){
				$new_subs=self::reformRecords($recs,$level+1,$f['from'],$found[$i+1]['from']);
			}
			if($new_subs){
				//Уже есть какие-то подразделы
				if(is_array($recs[$f['id']]['sub']))
					$recs[$f['id']]['sub']=array_merge($new_subs,$recs[$f['id']]['sub']);
				//Подразделов пока нет
				else
					$recs[$f['id']]['sub']=$new_subs;
			}
			$res[]=$recs[$f['id']];
		}
		return $res;
	}


}

?>