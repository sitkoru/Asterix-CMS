<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Счётчик дочерних записей				*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 31 мая 2010	года								*/
/*	Модифицирован: 31 мая 2010 года							*/
/*															*/
/************************************************************/


class field_type_count extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Количество связанных дочерних записей', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/hidden.tpl';
	
	//Поле участвует в поиске
	public $searchable = true;
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}

	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		//Включены комментарии у записи
		$res=unserialize($value);
		
		//Готово
		return $res;
	}

	public function reindex(){
		
		//Все модули
		foreach($this->model->modules as $module_sid => $module){
			//Все структуры
			if($module->structure)
			foreach($module->structure as $structure_sid => $structure){
				//Проверяем все поля
				foreach($structure['fields'] as $field_sid => $field){
					//Ищем поля типа count
					if($field['type'] == 'count'){

						//Ищем все записи найденной структуры
						$recs=$this->model->execSql('select * from `'.$module->getCurrentTable($structure_sid).'` where '.$this->model->extensions['domains']->getWhere().'','getall');

						$count=array();
						
						//Проверяем у них зависимости
						foreach($recs as $i=>$rec){	
							//Все дети или ссылающиеся щаписиы
							$count = $this->getMyChildren($module_sid, $structure_sid, $rec);

							//Обновляем текущую запись
							$this->model->execSql('update `'.$module->getCurrentTable($structure_sid).'` set `'.$field_sid.'`="'.mysql_real_escape_string( serialize($count) ).'" where `id`="'.$rec['id'].'" limit 1','update');
						}	
					}
				}
			}
		}
	}

	//Получить всех детей и ссылающихся записей
	public function getMyChildren($rec_module_sid, $rec_structure_sid, $record){
		
		$types=array();
		
		//Все модули
		foreach($this->model->modules as $module_sid => $module){
			//Все структуры
			if($module->structure)
			foreach($module->structure as $structure_sid => $structure){
				
				$count=0;
			
				//Перебираем поля - ссылки
				foreach($structure['fields'] as $field_sid => $field){

					//Тип link
					if( ($field['type'] == 'link') && ($field['module'] == $rec_module_sid) && ($field['structure_sid'] == $rec_structure_sid) ){
						$n=$this->model->execSql('select count(`id`) as `counter` from `'.$module->getCurrentTable( $structure_sid ).'` where `'.$field_sid.'`="'.$record['id'].'"','getrow');
						$count+=$n['counter'];
					
					//Тип linkm
					}elseif( ($field['type'] == 'linkm') && ($field['module'] == $rec_module_sid) && ($field['structure_sid'] == $rec_structure_sid) ){
						$n=$this->model->execSql('select count(`id`) as `counter` from `'.$module->getCurrentTable( $structure_sid ).'` where `'.$field_sid.'` LIKE "%|'.$record['id'].'|%"','getrow');
						$count+=$n['counter'];
					}
					
				}
				
				//Есть записимая структура типа link или linkm
				if( ( $rec_module_sid == $module_sid ) && ( $structure['dep_path']['structure'] == $rec_structure_sid) ){
					$n=$this->model->execSql('select count(`id`) as `counter` from `'.$module->getCurrentTable( $structure_sid ).'` where `dep_path_'.$rec_structure_sid.'`="'.$record[ $this->model->types[ $structure['dep_path']['link_type'] ]->link_field ].'"','getrow');
					$count+=$n['counter'];
				}
		
				//Итого
				if($count)
					$types[$module->info['prototype'].'_'.$structure_sid]=array(
						'module'=>$module_sid,
						'structure_sid'=>$structure_sid,
						'count'=>$count
					);
				
			}
		}
		
		$total=0;
		foreach($types as $t)$total+=$t['count'];
		
		$result=array(
			'total'=>$total,
			'modules'=>$types,
		);
		
		//Готово
		return $result;
	}
}

?>