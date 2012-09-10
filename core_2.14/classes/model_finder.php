<?php

class ModelFinder{

	//Поиск записи в модели
	public function getRecordByAsk($url, $prefered_module = 'start'){

		// Сначала ищем в корневом модуле
		if( is_object( model::$modules[ $prefered_module ] ) )
		foreach( model::$modules[ $prefered_module ]->structure as $structure_sid=>$structure)
			if( !$record ){
				$last_structure_sid = $structure_sid;
				
				if( $url )
					$url_string =  '/'.implode('/', $url) ;
				else
					$url_string = '';
				
				$where = '(`url`="'.mysql_real_escape_string($url_string).'"';
				if( $prefered_module == 'start' )
					$where .= ' or `url_alias`="'.mysql_real_escape_string($url_string).'"';
				$where .= ')';
			
				$record = $this->makeSql(
					array(
						'tables'=>array(model::$modules[ $prefered_module ]->getCurrentTable( $last_structure_sid )),
						'where'=>array('and'=>array('url'=>$where))
					),
					'getrow'
				);
			}

		// Нашли запись в стандартном модуле
		if( $record ){
			if( $record['is_link_to_module'] ){
				model::$ask->module = $record['is_link_to_module'];
				model::$ask->structure_sid = end(array_keys( model::$modules[ $prefered_module ]->structure ));
				model::$ask->output_type = 'index';
			}else{
				model::$ask->module = $prefered_module;
				model::$ask->structure_sid = $last_structure_sid;
				
				if( $url_string == '' )
					model::$ask->output_type = 'index';
				elseif( $last_structure_sid != 'rec' )
					model::$ask->output_type = 'list';
				else
					model::$ask->output_type = 'content';
			}
			
		// Не нашли, ищем глубже
		}else{
			for($i=0; $i<count($url); $i++)
				if( !$record )
					if( IsSet( model::$modules[ $url[$i] ] ) )
						if( $url[$i] != $prefered_module )
							$record = ModelFinder::getRecordByAsk($url, $url[$i]);
		}
		
		// Вставляем окончание .html
//		$record = structures::insertRecordUrlType( $record );

		// Готово
		return $record;
	}

	//Забрать запись по ID
	public function getRecordById($structure_sid,$id){
		//Получаем записи
		$rec=model::makeSql(
			array(
				'fields'=>false,
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>array('and'=>array('`id`="'.mysql_real_escape_string($id).'"')),
				'order'=>$order
			),
			'getrow'
		);

		//Чистим данные
		$rec=self::clearAfterDB($rec);

		//Вставляем окончание .html
		$rec=$this->insertRecordUrlType($rec);
		return $rec;
	}

	//Забрать запись по SID
	public function getRecordBySid($structure_sid,$sid){
		//Получаем записи
		$rec=model::makeSql(
			array(
				'fields'=>false,
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>array('and'=>array('`sid`="'.mysql_real_escape_string($sid).'"','`shw`=1'))
			),
			'getrow'
		);

		//Чистим данные
		$rec=self::clearAfterDB($rec);

		//Вставляем окончание .html
		$rec=$this->insertRecordUrlType($rec);
		return $rec;
	}

	//Забрать запись по WHERE
	public function getRecordsByWhere($structure_sid,$where){
		//Сортировка
		$order='order by `date_public` desc';
		if(IsSet($this->structure[$structure_sid]['fields']['pos']))$order='order by `pos`';
		if($this->structure[$structure_sid]['type']=='tree')$order='order by `left_key`';

		//Получаем записи
		$recs=model::makeSql(
			array(
				'fields'=>false,
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>$where,
				'order'=>$order
			),
			'getall'
		);

		//Чистим данные
		$recs=self::clearAfterDB($recs);

		//Вставляем окончание .html
		$recs=$this->insertRecordUrlType($recs);
		return $recs;
	}

	//Форматирование полученных данных после запроса из базы
	public static function clearAfterDB($recs){

		//Список записей
		if(IsSet($recs[0]['id'])){
			foreach($recs as $i=>$rec){
				foreach($rec as $var=>$val){
					if(!is_array($val)){
						$recs[$i][$var]=htmlspecialchars_decode(stripslashes($val));
					}else{
						$recs[$i][$var]=self::clearAfterDB($val);
					}
				}
			}

		//Одна запись
		}elseif(IsSet($recs['id'])){
			foreach($recs as $var=>$val){
				if(!is_array($val)){
					$recs[$var]=htmlspecialchars_decode(stripslashes($val));
				}else{
					$recs[$var]=self::clearAfterDB($val);
				}
			}
		}

		return $recs;
	}

}

?>