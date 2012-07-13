<?php

class unitTests{

	//Тесты для модели
	public function forModel(){
		if( !model::$settings['test_mode'] )return false;
		
	}

	//Тесты для модуля
	public function forModule(){
		if( !model::$settings['test_mode'] )return false;

		unitTests::module_checkStructure();
		unitTests::module_checkTree();
		unitTests::module_checkInstallation();
	}

	//Проверка корректности индексов таблиц деревьев текущего модуля
	public function module_checkTree(){

		if($this->structure)
		foreach($this->structure as $structure_sid=>$structure){
			if($structure['type']=='tree'){
				$message=false;

				//Забираем все записи дерева
				$recs=model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` order by `left_key`','getall');

				//Проверяем общую контрольную сумму дерева
				if($recs[0]['right_key']!=count($recs)*2)
					$message.'['.$this->info['sid'].'] > ['.$structure_sid.'] checksum error'."\n\n";

				//Проверяем все записи на совпадение разницы индексов и числа подразделов
				if($recs)
				foreach($recs as $i=>$rec){
					$subrecs=model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `left_key`>'.$rec['left_key'].' and `right_key`<'.$rec['right_key'].' and `tree_level`>'.$rec['tree_level'].' order by `left_key`','getall');

					//Проверка числа подразделов
					if($rec['left_key']+1+($subrecs?count($subrecs)*2:0)!=$rec['right_key']){
						$message.='['.$this->info['sid'].'] > ['.$structure_sid.'] -> ['.$rec['id'].'] subs checksum error'."\n";
						$message.=model::$last_sql."\n";
						$message.=$rec['left_key'].'+1+'.($subrecs?count($subrecs)*2:0).' != '.$rec['right_key']."\n\n";
					}
				}
				
				//Если были ошибки - высылаем сообщение
				if($message){
//					print('<h1 style="color:#f00">Редактировать что-либо временно не рекомендуется.</h1>');
//					mail('dekmabot@gmail.com',model::$extensions['domains']->domain['host'].' tree checksum error',$message);
//					pr_r($message);

					error_reporting(E_ERROR | E_WARNING | E_PARSE);	
					
					//Строим нормальное дерево
					$counter = 0;
					$recs = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where (`sid`="start" or `sid`="index")','getall');
					foreach($recs as $i=>$rec){
						$counter ++;
						$rec['left_key'] = $counter;
						$recs2 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec['sid'].'" order by `left_key`, `id`','getall');
						foreach($recs2 as $i2=>$rec2){
							$counter ++;
							$rec2['left_key'] = $counter;
							$recs3 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec2['sid'].'" order by `left_key`, `id`','getall');
							foreach($recs3 as $i3=>$rec3){
								$counter ++;
								$rec3['left_key'] = $counter;
								$recs4 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec3['sid'].'" order by `left_key`, `id`','getall');
								foreach($recs4 as $i4=>$rec4){
									$counter ++;
									$rec4['left_key'] = $counter;
									$recs5 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec4['sid'].'" order by `left_key`, `id`','getall');
									foreach($recs5 as $i5=>$rec5){
										$counter ++;
										$rec5['left_key'] = $counter;
										$recs6 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec5['sid'].'" order by `left_key`, `id`','getall');
										foreach($recs6 as $i6=>$rec6){
											$counter ++;
											$rec6['left_key'] = $counter;
											$recs7 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec6['sid'].'" order by `left_key`, `id`','getall');
											foreach($recs7 as $i7=>$rec7){
												$counter ++;
												$rec7['left_key'] = $counter;
												$recs8 = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec7['sid'].'" order by `left_key`, `id`','getall');
												foreach($recs8 as $i8=>$rec8){
													$counter ++;
													$rec8['left_key'] = $counter;
													$counter ++;
													$rec8['right_key'] = $counter;
													$tree_level=8;
													model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec8['left_key'] ).', `right_key`='.intval( $rec8['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'/'.$rec6['sid'].'/'.$rec7['sid'].'/'.$rec8['sid'].'" where `id`='.intval($rec8['id']).'','update');
												}
												$counter ++;
												$rec7['right_key'] = $counter;
												$tree_level=7;
												model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec7['left_key'] ).', `right_key`='.intval( $rec7['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'/'.$rec6['sid'].'/'.$rec7['sid'].'" where `id`='.intval($rec7['id']).'','update');
											}
											$counter ++;
											$rec6['right_key'] = $counter;
											$tree_level=6;
											model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec6['left_key'] ).', `right_key`='.intval( $rec6['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'/'.$rec6['sid'].'" where `id`='.intval($rec6['id']).'','update');
										}
										$counter ++;
										$rec5['right_key'] = $counter;
										$tree_level=5;
										model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec5['left_key'] ).', `right_key`='.intval( $rec5['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'" where `id`='.intval($rec5['id']).'','update');
									}
									$counter ++;
									$rec4['right_key'] = $counter;
									$tree_level=4;
									model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec4['left_key'] ).', `right_key`='.intval( $rec4['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'" where `id`='.intval($rec4['id']).'','update');
								}
								$counter ++;
								$rec3['right_key'] = $counter;
								$tree_level=3;
								model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec3['left_key'] ).', `right_key`='.intval( $rec3['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'" where `id`='.intval($rec3['id']).'','update');
							}
							$counter ++;
							$rec2['right_key'] = $counter;
							$tree_level=2;
							model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec2['left_key'] ).', `right_key`='.intval( $rec2['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'" where `id`='.intval($rec2['id']).'','update');
						}
						$counter ++;
						$rec['right_key'] = $counter;
						$tree_level=1;
						model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec['left_key'] ).', `right_key`='.intval( $rec['right_key'] ).', `tree_level`='.intval($tree_level).' where `id`='.intval($rec['id']).'','update');
					}
				}
			}
		}
	}

	//Проверка корректности установки модуля
	public function module_checkStructure(){

		//Все структуры
		if($this->structure)
		foreach($this->structure as $structure_sid=>$structure){

			//Проверяем есть ли таблицы, пр необходимости переустанавливаем
			$res=model::execSql('show tables like "'.$this->getCurrentTable($structure_sid).'"');

			if(!count($res)){
				//Переустановка
				unitTests::reinstall($structure_sid);

			}else{

				//Все имеющиеся поля
				$table_fields=model::execSql('show columns from `'.$this->getCurrentTable($structure_sid).'`','getall');
				
				//Все поля
				foreach($structure['fields'] as $sid=>$field){
					$flag=false;
					foreach($table_fields as $f)
						if($f['Field']==$sid)$flag=true;
					if(!$flag and IsSet( model::$types[$field['type']] ) ){
						$sql='alter table `'.$this->getCurrentTable($structure_sid).'` add '.model::$types[$field['type']]->creatingString($sid);
						model::execSql($sql,'update');
					}
				}
			}
		}
		
		//Обновляем алиасные ссылки для модулей в дереве
		model::execSql('update `start_rec` set `sid`=`is_link_to_module`, `url_alias`=CONCAT("/",`is_link_to_module`) where `is_link_to_module`!=""','update');
		model::execSql('update `start_rec` set `sid`="start", `url_alias`="/start" where `left_key`=1','update');
		model::execSql('update `start_rec` set `dep_path_parent`="start" where `dep_path_parent`="index"','update');
	}

	//Проверка корректности установки модуля
	public function module_checkInstallation(){
		if( $this->info['sid'] == 'start' )
			return false;
		$res = model::execSql('select * from `start_rec` where `is_link_to_module`="'.mysql_real_escape_string( $this->info['sid'] ).'"','getrow');
		if( !$res ){
			$this->info['dep_path_parent'] == 'start';
			$this->info['shw'] == true;
			$record = $this->info;
			$record['shw'] = true;
			$record['domain'] = model::pointDomainID();
			$record['is_link_to_module'] = $this->info['sid'];
			$record['url_alias'] = '/'.$this->info['sid'];
			$record['dep_path_parent'] = 'start';
			
			model::addRecord('start', 'rec', $record);
		}
	}
	
	//Переустановка таблиц модуля
	public function reinstall($part_sid){
		require_once(model::$config['path']['core'].'/classes/table_manage.php');
		$table=new table_manage(model::$db['system'],$this->getCurrentTable($part_sid),$this->structure[$part_sid],model::$config['path']['core']);
		$table->delete();
		$table->create();
		
		//Вставляем первую корневую запись в дерево
		if( $this->structure[$part_sid]['type'] == 'tree' )
			model::execSql('insert into `' . $this->getCurrentTable($part_sid) . '` set `sid`="index", `title`="'.$this->info['title'].'", `left_key`=1, `right_key`=2, `tree_level`=1, `url`="/'.$this->info['sid'].'", `domain`="all", `shw`=1, `ln`=1','insert');
	}
	
}

?>