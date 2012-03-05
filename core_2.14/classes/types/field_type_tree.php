<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Дерево сайта							*/
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

class field_type_tree extends field_type_default
{
	public $default_settings = array(
		'sid' => false, 
		'title' => 'Меню (один к одному)', 
		'value' => 'index', 
		'width' => '100%'
	);
	
	public $template_file = 'types/tree.tpl';
	
	public $link_field = 'sid';
	
	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(64) NOT NULL';
	}
	
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$res      = array();
		
		//Варианты значений предоставлены заранее
		$recs=$this->model->makeSql(
			array(
				'tables'=>array( model::$modules[ $settings['module'] ]->getCurrentTable( $settings['structure_sid'] ) ),
				'fields' => array('id','sid','title','tree_level','left_key','right_key'),
				'order' => 'order by `left_key`',
			),
			'getall'
		);
		
		if($recs)
		foreach($recs as $i=>$rec){
			$recs[$i]=model::$modules[ $settings['module'] ]->insertRecordUrlType($rec);
			
			$recs[$i]['selected']=($rec['sid'] == $value);
			
			//Невозможно выбрать себя из дерева, а также своих потомков
			if( ($rec['id'] == $record['id']) || ( ($rec['left_key'] > $record['left_key']) && ($rec['right_key'] < $record['right_key']) ) )
				$recs[$i]['disabled']=true;
		}
		
		//Готово
		return $recs;
	}
}

?>