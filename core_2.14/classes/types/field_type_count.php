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
	
	public function creatingString($name){
		return '`' . $name . '` INT NOT NULL';
	}

	// Переиндексация всех полей типа COUNT в модели
	public function reindex(){
		
		foreach(model::$modules as $module_sid => $module){
			if($module->structure)
			foreach($module->structure as $structure_sid => $structure){
				foreach($structure['fields'] as $field_sid => $field){
					
					// Ищем поля типа count
					if($field['type'] == 'count'){

						// Ищем какие поля будем считать ссылкой на наш COUNT
						$target_module_sid = $field['module'];
						$target_structure_sid = $field['structure_sid'];
						$target_field_sid = false;
						
						$fields = model::$modules[ $target_module_sid ]->structure[ $target_structure_sid ]['fields'];
						foreach( $fields as $i_field_sid => $i_field ){
						
							// Поле типа Link
							if( ($i_field['type'] == 'link') && ($i_field['module'] == $module_sid) && ($i_field['structure_sid'] == $structure_sid) ){
								$target_field_sid = $i_field_sid;
								$link = 'link';
								
							// Поле типа LinkM
							}elseif( ($i_field['type'] == 'linkm') && ($i_field['module'] == $module_sid) && ($i_field['structure_sid'] == $structure_sid) ){
								$target_field_sid = $i_field_sid;
								$link = 'linkm';
							
							// Поля типа Module/Structure_sid/Record_id
							}elseif( IsSet($i_field['module_sid']) && IsSet($i_field['structure_sid']) && IsSet($i_field['record_id']) ){
								$target_field_sid = true;
								$link = 'msr';

							}
							
						}
						
						// Если связка найдена - считаем связанные записи
						if( $target_field_sid ){
							
							// Ищем все записи текущей структуры структуры
							$recs = model::execSql('select * from `'.model::$modules[ $module_sid ]->getCurrentTable( $structure_sid ).'`','getall');
							foreach($recs as $rec){	
								
								if( $link == 'link' )
									$where = '`'.$target_field_sid.'`='.intval( $rec['id'] );
								elseif( $link == 'linkm' )
									$where = '`'.$target_field_sid.'` LIKE "%|'.intval( $rec['id'] ).'|%"';
								elseif( $link == 'msr' )
									$where = '(`module_sid`="'.$module_sid.'" && `structure_sid`="'.$structure_sid.'" && `record_id`="'.intval( $rec['id'] ).'")';
								
								// Считаем всех детей или ссылающиеся записи
								$count = $this->model->execSql('select COUNT(`id`) as `counter` from `'.model::$modules[ $target_module_sid ]->getCurrentTable( $target_structure_sid ).'` where '.$where.'','getrow');
								$count = $count['counter'];
								
								// Обновляем текущую запись
								model::execSql('update `'.model::$modules[ $module_sid ]->getCurrentTable( $structure_sid ).'` set `'.$field_sid.'`='.intval( $count ).' where `id`='.intval($rec['id']),'getall');
							}
						}
					}
				}
			}
		}
	}

}

?>