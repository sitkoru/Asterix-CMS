<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Рейтинг Доверия/Известности/Мобильности*/
/*															*/
/*	Версия ядра 2.06										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2010  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 29 сентября 2010 года							*/
/*	Модифицирован: 29 сентября 2010 года					*/
/*															*/
/************************************************************/

class field_type_rating extends field_type_default
{
	public $default_settings = array(
		'sid' => false, 
		'title' => 'Рейтинг', 
		'value' => '', 
		'width' => '100%'
	);
	
	public $template_file = 'types/rating.tpl';
	private $table = 'votes';
	
	//Поле участвует в поиске
	public $searchable = true;
	
	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR (64) NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		foreach($values[$value_sid] as &$val)
			$val = str_pad($val, 6, '0', STR_PAD_LEFT);
		
		if ($values[$value_sid])
			$value=$values[$value_sid]['thanks'].'|'.$values[$value_sid]['popular'].'|'.$values[$value_sid]['mobile'];
			
		else
			$value = false;
		
		//Готово
		return $value;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		//Включены комментарии у записи
		list($res['thanks'], $res['popular'], $res['mobile']) = explode('|', $value);
		$res['thanks']=intval($res['thanks']);
		$res['popular']=intval($res['popular']);
		$res['mobile']=intval($res['mobile']);
		
		//Готово
		return $res;
	}

	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}

	//Добавляем комментарий
	public function addThanks($user_id, $record_graph_top = false){	
	
		//Ищем нужную запись
		$module_sid = $this->model->getModuleSidByPrototype('users');
		$rec = model::$modules[ $module_sid ]->getRecordById('rec', $user_id);
		$rec = model::$modules[ $module_sid ]->explodeRecord($rec, 'rec');
		
		//Добавляем голос в запись
		foreach($rec as $field_sid => &$field){
			if( model::$modules[ $module_sid ]->structure['rec']['fields'][ $field_sid ]['type'] == 'rating' ){
				@$field['thanks']++;
				$rating = $this->toValue($field_sid, $rec);
				$this->model->execSql('update `'.model::$modules[ $module_sid ]->getCurrentTable('rec').'` set `'.$field_sid.'`="'.$rating.'" where `id`="'.$rec['id'].'" limit 1', 'update');
			}
		}
		
		//Возвращаем URL обновлённой записи
		return model::$modules[ $module_sid ]->insertRecordUrlType( $rec['url'] );
	}
	
	public function votesForRecord($module, $structure_sid, $record_id){
		$votes=$this->model->execSql('select * from `votes` where `module`="'.mysql_real_escape_string($module).'" and `structure_sid`="'.mysql_real_escape_string($structure_sid).'" and `record_id`="'.mysql_real_escape_string($record_id).'"', 'getall');
		$authors=array();
		$yes=0;
		$no=0;
		$total=0;
		$mark=0;
		foreach($votes as $val)
			if(!in_array($val['author'],$authors)){
				if($val['value']>0)$yes+=$val['value'];
				if($val['value']<0)$no+=$val['value'];
				$total++;
				$mark+=$val['value'];
				$authors[]=$val['author'];
			}
		if($total>0)$mark=round($mark/$total);
		
		return array(
			'total' => str_pad($total, 4, '0', STR_PAD_LEFT),
			'mark' => str_pad($mark, 4, '0', STR_PAD_LEFT),
			'yes' => $yes,
			'no' => $no,
		);
	}
	
	public function reindex(){
		foreach(model::$modules as $module_sid=>$module){
			if($module->structure)
			foreach($module->structure as $structure_sid=>$structure){
				foreach($structure['fields'] as $field_sid=>$field){
					if($field['type'] == 'votes'){
						//К каким записям есть голоса
						$t=$this->model->execSql('select distinct `record_id` from `votes` where `module`="'.mysql_real_escape_string($module_sid).'" and `structure_sid`="'.mysql_real_escape_string($structure_sid).'"','getall');
						$ids=array();
						foreach($t as $ti)$ids[]=$ti['record_id'];
						//Сами записи
						$recs=$this->model->execSql('select * from `'.$module->getCurrentTable($structure_sid).'` where `id` IN ('.implode(', ', $ids).') order by `id`','getall');
						if($recs)
						foreach($recs as $rec){
							$votes = $this->votesForRecord($module_sid, $structure_sid, $rec['id']);
							if($votes){
								$this->model->execSql('update `'.$module->getCurrentTable($structure_sid).'` set `'.$field_sid.'`="'.implode('|', $votes).'" where `id`="'.$rec['id'].'" limit 1', 'update');
							}
						}
						$this->model->execSql('update `'.$module->getCurrentTable($structure_sid).'` set `votes`="0000|0000|0|0" where `id` NOT IN ('.implode(', ', $ids).')','update');
					}
				}
			}
		}
	}
}

?>