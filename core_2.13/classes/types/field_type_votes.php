<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Голоса ЗА и ПРОТИВ						*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 28 мая 2010	года								*/
/*	Модифицирован: 28 мая 2010 года							*/
/*															*/
/************************************************************/

class field_type_votes extends field_type_default
{
	public $default_settings = array(
		'sid' => false, 
		'title' => 'Голоса ЗА и ПРОТИВ', 
		'value' => '', 
		'width' => '100%'
	);
	
	var $marks=array(
		'-1'=>-1,
		'1'=>1,
		'2'=>2,
		'3'=>3,
		'4'=>4,
		'5'=>5,
		'like'=>1,
		'dontlike'=>-1,
	);
	
	public $template_file = 'types/votes.tpl';
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
		if ($values[$value_sid])
			$value=implode('|',$values[$value_sid]);
			
		else
			$value = false;
		
		//Готово
		return $value;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		//Включены комментарии у записи
		list($res['total'], $res['mark'], $res['yes'], $res['no']) = explode('|', $value);
		$res['mark']=intval($res['mark']);
		$res['total']=intval($res['total']);
		
		//Голосовал ли текущий пользователь
//		$this->model->execSql('select * from `votes` where `rec`','getrow');
		
		//Готово
		return $res;
	}

	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}
	
	//Добавляем комментарий
	public function addVote($module_sid, $structure_sid, $record_id, $vote){	
		
		//Валидация оценки
		if(IsSet($this->marks[$vote]))
			$mark=$this->marks[$vote];
		else
			return false;
		
		//Дозаносим новую
		$this->model->execSql('insert into `'.$this->table.'` set 
			`date`=NOW(),
			`value`="'.mysql_real_escape_string($mark).'",
			`author`="'.$this->model->user->info['id'].'",
			`domain`="all",
			`ln`="1",
			`module`="'.mysql_real_escape_string($module_sid).'",
			`structure_sid`="'.mysql_real_escape_string($structure_sid).'",
			`record_id`="'.mysql_real_escape_string($record_id).'"
		','insert');
		
		
		//Все значения к записи
		$vals=$this->model->makeSql(
			array(
				'tables'=>array($this->table),
				'where'=>array('and'=>array(
					'`module`="'.mysql_real_escape_string($module_sid).'"',
					'`structure_sid`="'.mysql_real_escape_string($structure_sid).'"',
					'`record_id`="'.mysql_real_escape_string($record_id).'"',
					'access'=>false,
				))
			),
			'getall'
		);
		
		//Считаем баллы
		$yes=0;
		$no=0;
		$total=0;
		$mark=0;
		if(is_array($vals))
		foreach($vals as $val){
			if($val['value']>0)$yes+=$val['value'];
			if($val['value']<0)$no+=$val['value'];
			$total++;
			$mark+=$val['value'];
		}
		
		if($total>0)
			$mark=round($mark/$total);
		
		$res=str_pad($total, 4, '0', STR_PAD_LEFT).'|'.$mark.'|'.$yes.'|'.$no;
		
		//Обновляем значение голосов в записи
		if( $module_sid == 'comments'){
			$this->model->execSql('update `comments` set `votes`="'.mysql_real_escape_string($res).'" where `id`="'.$record_id.'" and '.$this->model->extensions['domains']->getWhere().' limit 1','update');
		}else
			$this->model->execSql('update `'.$this->model->modules[ $module_sid ]->getCurrentTable( $structure_sid ).'` set `votes`="'.mysql_real_escape_string($res).'" where `id`="'.$record_id.'" and '.$this->model->extensions['domains']->getWhere().' limit 1','update');
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
		foreach($this->model->modules as $module_sid=>$module){
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