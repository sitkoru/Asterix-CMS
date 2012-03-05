<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Группа доступа							*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 0.01										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 25 сентября 2009 года					*/
/*															*/
/************************************************************/

class field_type_access extends field_type_default{
	
	public $default_settings=array(
		'sid'=>false,
		'title'=>'Группа доступа',
		'value'=>'|admin=rwd|moder=rw-|all=r--|',
		'width'=>'100%'
	);
	
	public $template_file='types/access.tpl';

	private $table='groups';
	
	//Поле участввует в поиске
	public $searchable=false;

	public function creatingString($name){
		return '`'.$name.'` TEXT NOT NULL';
	}
	
	//Получить простое значение по умолчанию из настроек поля
	public function getDefaultValue($settings=false){
		return array('admin'=>'rwd','moder'=>'rw-','all'=>'r--');
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid,$values,$old_values=array(),$settings=false){
		
		//Доступы групп
		$groups=array();
		foreach($values[$value_sid] as $group=>$access)
			//Админам всегда и везьде доступ есть
			if($group=='admin')
				$groups[$group]=$group.'=rwd';
			//Другим по необходимости
			else
				$groups[$group]=$group.'='.$access;
		
		//Готово
		return '|'.implode('|',$groups).'|';
	}
		
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings=false, $record=array()){
		//Группы в системе
		$groups=$this->model->getUserGroups();
		
		//Разбираем имеющиеся значения
		if(is_array($value)){
			$gs=$value;
		}else{
			$gs=explode('|',$value);
		}
		
		foreach($gs as $g)if(strlen($g)){
			$t=explode('=',$g);
			$group_vals[$t[0]]=(substr_count($t[1],'r')?'r':'-').(substr_count($t[1],'w')?'w':'-').(substr_count($t[1],'d')?'d':'-');
		}

		//Указываем значения в группах
		foreach($groups as $i=>$group){
			//Есть значение
			if(IsSet($group_vals[$i])){
				$groups[$i]['access']=$group_vals[$i];
			//Есть настройка
			}elseif(IsSet(model::$settings['access_default'])){
				$groups[$i]['access']=model::$settings['access_default'];
			//Нет настройки - берём стандартное
			}else{
				if($i=='admin')$groups[$i]['access']='rwd';
				if($i=='moder')$groups[$i]['access']='rw-';
				if($i=='all')$groups[$i]['access']='r--';
			}
				
		}
		
		$result['groups']=$groups;
		
		return $result;
	}

}

?>