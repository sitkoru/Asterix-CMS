<?php

class structures{

	//Достройка структуры, связей, параметров, типов данных
	public static function load( $module ){

		//Системные поля, необходимые каждому модулю
		$system_fields=array(
			'id'=>					array('sid'=>'id',				'group'=>'system',		'public'=>true, 	'type'=>'id', 			'title'=>'ID'),
			'sid'=>					array('sid'=>'sid',				'group'=>'system',		'public'=>false, 	'type'=>'sid', 			'title'=>'Системное имя'),
			'date_public'=>			array('sid'=>'date_public',		'group'=>'main',		'public'=>false, 	'type'=>'datetime', 	'title'=>'Публичная дата'),
			'date_added'=>			array('sid'=>'date_added',		'group'=>'system',		'public'=>false, 	'type'=>'datetime', 	'title'=>'Дата создания записи'),
			'date_modify'=>			array('sid'=>'date_modify',		'group'=>'system',		'public'=>false, 	'type'=>'datetime', 	'title'=>'Дата последнего изменения записи'),
			'title'=>				array('sid'=>'title',			'group'=>'main',		'public'=>true, 	'type'=>'text', 		'title'=>'Название'),
			'shw'=>					array('sid'=>'shw',				'group'=>'show',		'public'=>true, 	'type'=>'check', 		'title'=>'Показывать на сайте', 				'default'=>true),
			'url'=>					array('sid'=>'url',				'group'=>'system',		'public'=>false, 	'type'=>'text', 		'title'=>'Адрес записи', 						'default'=>''),
			'author'=>				array('sid'=>'author',			'group'=>'system',		'public'=>false, 	'type'=>'user', 		'title'=>'Автор записи',						'default'=>false),
		);
	
		//Подгружаем заданную структуру модуля
		$module->setStructure();
	
		if($module->structure)
		foreach($module->structure as $structure_sid=>$part)
			if( is_array( $part['fields'] ) )
				if( count( $part['fields'] ) )
					foreach( $part['fields'] as $field_sid=>$field)
						if( $field['type'][0] == '_' )
							if( !ModelLoader::loadUserType( $field ) )
								UnSet( $module->structure[ $structure_sid ]['fields'][ $field_sid ] );



		// Заносим системные поля
		if($module->structure)
		foreach($module->structure as $structure_sid=>$part){
			$module->structure[$structure_sid]['fields']=$system_fields;
			if($part['fields']){
				foreach($part['fields'] as $field_sid=>$field){
					$module->structure[$structure_sid]['fields'][$field_sid]=array_merge(
						array('sid'=>$field_sid),
						$field
					);
				}
			}
			
			//Деревьям приклеиваем поле наследования
			if( $part['type'] == 'tree' )
				$module->structure[$structure_sid]['fields']['dep_path_parent'] = array('sid'=>'dep_path_parent',	'group'=>'main', 'type'=>'tree', 'title'=>'Расположение в дереве сайта', 'module'=>$module->info['sid'], 'structure_sid'=>$structure_sid);

			//Указатель на родительскую структуру
			if( $module->structure[$structure_sid]['dep_path'] )
				$module->structure[$structure_sid]['fields']['dep_path_dir'] = 
					array(
						'sid' =>			'dep_path_dir',	
						'group' =>			'main', 
						'type' =>			$module->structure[ $structure_sid ]['dep_path']['link_type'], 
						'title' =>			$module->structure[ $module->structure[$structure_sid]['dep_path']['structure'] ]['title'], 
						'module' =>			$module->info['sid'], 
						'structure_sid' =>	$module->structure[$structure_sid]['dep_path']['structure'],
					);
		}

		//Если модуль Start - будем клеить поле-ссылку
		if($module->info['sid'] == 'start'){
			$module->structure['rec']['fields']['is_link_to_module']=	array('sid'=>'is_link_to_module','type'=>'module','group'=>'system','title'=>'Раздел является ссылкой на модуль','variants'=>$all_modules);
			$module->structure['rec']['fields']['url_alias']=			array('sid'=>'url_alias','group'=>'system','public'=>false,'type'=>'text','title'=>'Виртуальный адрес записи','default'=>'');
		}
		//Если модуль Start - будем клеить поле-ссылку
		if($module->info['sid'] == 'users'){
			$module->structure['rec']['fields']['is_link_to']=		array('sid'=>'is_link_to','type'=>'user','group'=>'system','title'=>'Этот модуль является ссылкой на модуль');
			$module->structure['rec']['fields']['salt']=			array('sid'=>'salt','type'=>'hidden','group'=>'system','title'=>'Соль для пароля');
		}
		
		return $module->structure;
	}

	//Вернуть массив основных полей структуры
	public function getMainFields($structure_sid = 'rec'){
		$fields = array('id','sid','title','url');
		$main=array('id','sid','date_public','title','url','shw','dep_path_darent','dep_path_dir','left_key','right_key','is_link_to_module','seo_title','seo_keywords','seo_description','seo_changefreq','seo_priority');
		if(is_array($this->structure[$structure_sid]['fields']))
		foreach($this->structure[$structure_sid]['fields'] as $sid=>$f)
			if( (in_array($sid, $main) || @$f['main']) and (!IsSet($fields[$sid])) )
				$fields[$sid]=$sid;
				
		return $fields;
	}

	//Разворачиваем значения полей перед выводом в браузер
	public function explodeRecord($rec,$structure_sid='rec', $use_admin_explode = false){

		$second_level_explodable_fields=array('image','gallery');

		if(is_array($rec))
		foreach($rec as $sid=>$value){
			//Настройки поля в структуре модуля
			$field_settings=$this->structure[$structure_sid]['fields'][$sid];

			//Разворачиваем значение
			if($field_settings['type']){
				if(IsSet(model::$types[ $field_settings['type'] ]))

				//Разворачиваем ненулевые значения
				if($value){
				
					if( $use_admin_explode )
						$rec[$sid]=model::$types[ $field_settings['type'] ]->getAdmValueExplode($value, $this->structure[$structure_sid]['fields'][$sid], $rec);
					else
						$rec[$sid]=model::$types[ $field_settings['type'] ]->getValueExplode($value, $this->structure[$structure_sid]['fields'][$sid], $rec);

					//Разварачиваем картинки у связанных записей
					if( $field_settings['type'] == 'link' ){

						if(IsSet(model::$modules[ $field_settings['module'] ]->structure[$field_settings['structure_sid']]))
						foreach(model::$modules[ $field_settings['module'] ]->structure[ $field_settings['structure_sid'] ]['fields'] as $sub_field_sid => $sub_field)
							if($sub_field['type'] == 'image'){

								//Разворачиваем занчение
								$new_val = model::$types[ 'image' ] -> getValueExplode(
									$rec[ $sid ][ $sub_field_sid ],
									model::$modules[ $field_settings['module'] ]->structure[ $field_settings['structure_sid'] ]['fields'][ $sub_field_sid ],
									$field_settings
								);

								//Если значение развернулось
								if( $new_val )
								if( is_array($new_val) )
									$rec[ $sid ][ $sub_field_sid ] = $new_val;
							}

					//Разварачиваем картинки у связанных записей
					}elseif( $field_settings['type'] == 'user' ){

						$module_sid = model::getModuleSidByPrototype('users');
						foreach(model::$modules[ $module_sid ]->structure[ 'rec' ]['fields'] as $sub_field_sid => $sub_field)
							if($sub_field['type'] == 'image'){

								//Разворачиваем занчение
								$new_val = model::$types[ 'image' ] -> getValueExplode(
									$rec[ $sid ][ $sub_field_sid ],
									model::$modules[ $module_sid ]->structure[ 'rec' ]['fields'][ $sub_field_sid ],
									$field_settings
								);

								//Если значение развернулось
								if( $new_val )
								if( is_array($new_val) )
									$rec[ $sid ][ $sub_field_sid ] = $new_val;
							}

					}
				}
			}
		}
		
		//BETA
		$rec['module'] = $this->info['sid'];
		$rec['structure_sid'] = $structure_sid;

		//Если установлено расширение социального графа - дополняем записи значением вершины графа
		if(IsSet(model::$extensions['graph'])){
			$rec['graph_top']=$this->getGraphTop($rec['id'],$structure_sid);
			$rec['graph_top_text']=implode('|', $this->getGraphTop($rec['id'],$structure_sid));
		}

		return $rec;
	}

	//Вставка html или других окончаний для URL-ов записей
	public function insertRecordUrlType($rec, $type='html', $insert_host = false){

		//Передана одна запись
		if( IsSet($rec['url']) && !IsSet( $rec['url_clear'] ) ){
			if( strlen($rec['url']) ){
				if( !substr_count($rec['url'], '.'.$type) ){
					$rec['url_print']=$rec['url'].'.print.'.$type;
					$rec['url_clear']=$rec['url'];
					$rec['url']=$rec['url'].'.'.$type;
				}
			}else{
				$rec['url']='/';
				$rec['url_print']='/start.print.'.$type;
				$rec['url_clear']='/start';
			}
			
			if( IsSet($rec['sub']) )
				$rec['sub'] = self::insertRecordUrlType($rec['sub'], $type, $insert_host);
			
			//Делать полный путь, а не относительный
			if($insert_host)
				$rec = self::insertHostToUrl($rec);

		//Несколько записей
		}elseif( IsSet($rec[0]['url']) && !IsSet( $rec[0]['url_clear'] ) ){
			foreach($rec as $i=>$record)
				$rec[$i] = self::insertRecordUrlType($record, $type, $insert_host);
		}
		return $rec;
	}

	//Указать путь, включая хост
	public static function insertHostToUrl($rec){
		$rec['url'] = 'http://'.$rec['domain'][0]['host'].$rec['url'];
		return $rec;
	}


}

?>