<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Прототип модуля										*/
/*															*/
/*	Версия ядра 2.14										*/
/*	Версия скрипта 2.0										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 27 декабря 2011 года						*/
/*															*/
/************************************************************/

class Dynamic{

	function __call($func, $args){
		$func = strtolower($func);
		$assoc = $this -> funcs[$func];
		if (is_object($assoc))
			return call_user_func_array(array($assoc,$func), $args);
		if (!isset($assoc)) 
			$assoc = get_class($this);
		$argarr = array();
		$keys = array_keys($args);
		foreach ($keys as $id => $key)
			$argarr[] = '$args[$keys['.$id.']]';
		$argstr = implode($argarr, ",");
		return 
			eval("return $assoc::$func($argstr);");
	}

	public function class_import($arg1, $arg2=null){
		assert (is_object($arg1) || class_exists($arg1));
		if (isset($arg2))
			$this -> funcs[strtolower($arg2)] = $arg1;
		else
			foreach (get_class_methods($arg1) as $method)
				$this -> funcs[strtolower($method)] = $arg1;
	}
}

//Модуль по умолчанию
class default_module extends Dynamic{

	//Приставка перед таблицей в дазе данных - пока не используется
	public $database_table_preface=false;

	// Идентификатор базы данных, у основного модуля всегда system,
	public $db_sid='system';

	//Шаблоны в модуле по умолчанию
	public $templates=array(
		'index'=>				array('sid'=>'index',			'title'=>'Главная страница модуля'),
		'content'=>				array('sid'=>'content',			'title'=>'Страница модуля одной записи'),
	);

	//Шаблоны в модуле по умолчанию
	public $prepares=array();
	public $interfaces=array();

////////////////////////////
/// ИНИЦИАЛИЗАЦИЯ МОДУЛЯ ///
////////////////////////////

	//Инициализация модуля
	public function __construct($model, $info){
		$this->model = $model;
		$this->info = $info;

		require_once model::$config['path']['core'].'/classes/structures.php';
		require_once model::$config['path']['core'].'/classes/components.php';
		require_once model::$config['path']['core'].'/classes/interfaces.php';
		require_once model::$config['path']['core'].'/classes/acms_trees.php';

		model::$modules[ $info['sid'] ] = $this;
		
		$this->structure = 	structures::load( $this );
	}
	
	// Инициализация компонентов и интерфейсов
	public function init(){
		$this->prepares = 	components::load( $this );
		$this->interfaces = interfaces::load( $this );
	}

	//Инициализация структуры модуля
	public function initStructure(){
		structures::initStructure();
	}

	//Инициализация компонентов
	public function prepareComponent($prepare,$params){
		return components::init($this, $prepare, $params);
	}

	//Эти функции используются в модулях для донастройки структуры и интерфейсов
	public function setStructure(){}
	public function setInterfaces(){}


//////////////////
/// ИНТЕРФЕЙСЫ ///
//////////////////

	//Получить содержимое интерфейса
	public function prepareInterface($prepare,$params, $public = false){
		return interfaces::prepareInterface($prepare,$params, $public);
	}
	
	//Запустить обработчик интерфейса
	public function controlInterface($interface,$params, $public = false){
		return interfaces::controlInterface($interface,$params, $public);
	}
	
	//Ответ на запрос интерфейса
	public function answerInterface($interface,$result){
		return interfaces::answerInterface($interface,$result);
	}

////////////////////////////////////////////
/// РАБОТА С ДЕРЕВЬЯМИ МОДУЛЯ И СТРУКТУР ///
////////////////////////////////////////////

	//Показать краткое дерево модуля
	public function getModuleShirtTree($root_record_id = false,$structure_sid = 'rec',$levels_to_show=0,$conditions=array()){
		return acms_trees::getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
	}

	//Показать краткое дерево сруктуры
	public function getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions){
		return acms_trees::getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
	}

//////////////////////
/// ПОИСКИ ЗАПИСЕЙ ///
//////////////////////

	//Забрать запись по ID
	public function getRecordById($structure_sid,$id){
		return ModelFinder::getRecordById($structure_sid,$id);
	}

	//Забрать запись по SID
	public function getRecordBySid($structure_sid,$sid){
		return ModelFinder::getRecordBySid($structure_sid,$sid);
	}

	//Забрать запись по WHERE
	public function getRecordsByWhere($structure_sid,$where){
		return ModelFinder::getRecordsByWhere($structure_sid,$where);
	}


//////////////////////////////////////////////////////////////////////
/// ДЕЙСТВИЯ МОДУЛЯ ПО ОТНОШЕНИЮ К СВОИМ ОБЪЕКТАМ ///
/////////////////////////////////////////////////////////////////////

	//Добавление записи в структуру модуля
	public function addRecord($record, $structure_sid = 'rec', $conditions=false){
		//Для версий до 2.14 параметры шли наоборот, сохраняем обратную совместимость
		if( !is_array($record) ){$k = $record; $record = $structure_sid; $structure_sid = $k; }
		return interfaces::addRecord($record, $structure_sid, $conditions);
	}

	//Добавление записи в структуру модуля
	public function editRecord($record, $structure_sid = 'rec', $conditions=false){
		//Для версий до 2.14 параметры шли наоборот, сохраняем обратную совместимость
		if( !is_array($record) ){$k = $record; $record = $structure_sid; $structure_sid = $k; }
		return interfaces::editRecord($record, $structure_sid, $conditions);
	}

	//Удаление записи
	public function deleteRecord($record, $structure_sid = 'rec', $conditions=false){
		//Для версий до 2.14 параметры шли наоборот, сохраняем обратную совместимость
		if( !is_array($record) ){$k = $record; $record = $structure_sid; $structure_sid = $k; }
		return interfaces::deleteRecord($record, $structure_sid, $conditions);
	}

	//Переместить на одну позицию выше
	public function moveUp($record, $structure_sid = 'rec', $conditions=false){
		//Для версий до 2.14 параметры шли наоборот, сохраняем обратную совместимость
		if( !is_array($record) ){$k = $record; $record = $structure_sid; $structure_sid = $k; }
		return interfaces::moveUp($record, $structure_sid, $conditions);
	}

	//Переместить на одну позицию ниже
	public function moveDown($record, $structure_sid = 'rec', $conditions=false){
		//Для версий до 2.14 параметры шли наоборот, сохраняем обратную совместимость
		if( !is_array($record) ){$k = $record; $record = $structure_sid; $structure_sid = $k; }
		return interfaces::moveDown($record, $structure_sid, $conditions);
	}

	//Переместить на одну позицию ниже
	public function moveTo($record, $structure_sid = 'rec', $conditions=false){
		//Для версий до 2.14 параметры шли наоборот, сохраняем обратную совместимость
		if( !is_array($record) ){$k = $record; $record = $structure_sid; $structure_sid = $k; }
		return interfaces::moveTo($record, $structure_sid, $conditions);
	}

	//Переместить на одну позицию ниже
	public function updateChildren($structure_sid, $old_data, $new_data, $new_url, $condition = false, $domain = false){
		return interfaces::moveDownupdateChildren($structure_sid, $old_data, $new_data, $new_url, $condition, $domain);
	}

////////////////////////////
////	Компоненты 		////
////////////////////////////

	public function prepareRec($params){
		return components::prepareRec($params);
	}

	public function prepareAnons($params){
		return components::prepareAnons($params);
	}

	public function prepareAnonsList($params){
		return components::prepareAnonsList($params);
	}

	public function prepareRecs($params){
		return components::prepareRecs($params);
	}

	public function prepareCount($params){
		return components::prepareCount($params);
	}

	public function prepareRandom($params){
		return components::prepareRandom($params);
	}

	public function prepareRandomList($params){
		return components::prepareRandomList($params);
	}

	public function prepareParent($params){
		return components::prepareParent($params);
	}

	public function prepareMap($params){
		return components::prepareMap($params);
	}

	public function preparePages($params){
		return components::preparePages($params);
	}

	public function prepareTags($params){
		return components::prepareTags($params);
	}


///////////////////////////////////////////
/// ВНУТРЕННИЕ СЛУЖЕБНЫЕ ФУНКЦИИ МОДУЛЯ ///
///////////////////////////////////////////

	//Дополнительная обработка записи при типе вывода "content"
	public function contentPrepare($rec,$structure_sid='rec'){
		return $rec;
	}

	//Вернуть массив основных полей структуры
	public function getMainFields($structure_sid = 'rec'){
		return structures::getMainFields($structure_sid);
	}

	//Возвращаем название таблицы текущей структуры
	public function getCurrentTable($part = 'rec'){
		return $this->database_table_preface.$this->info['prototype'].'_'.$part;
	}

	//Разворачиваем значения полей перед выводом в браузер
	public function explodeRecord($rec,$structure_sid='rec'){
		return structures::explodeRecord($rec,$structure_sid);
	}

	//Вставка html или других окончаний для URL-ов записей
	public function insertRecordUrlType($recs, $type='html', $insert_host = false){
		require_once model::$config['path']['core'].'/classes/structures.php';
		return structures::insertRecordUrlType($recs, $type='html', $insert_host = false);
	}
	
	//Получить иерархию структур модуля
	public function getLevels($structure, $level_tree = false){
		
		if( IsSet( $this->structure[ 'rec' ] ) )
			$level_tree[] = 'rec';
		
		if( $this->structure['rec']['dep_path'] )
			if( !in_array($this->structure['rec']['dep_path']['structure'], $level_tree) )
				$level_tree[] = $this->structure['rec']['dep_path']['structure'];
			
		if( is_array( $this->structure ) )
		foreach( $this->structure as $sid=>$structure ){
			if( !in_array($sid, $level_tree) )
				$level_tree[] = $sid;
			if( $structure['dep_path'] )
				if( !in_array($structure['dep_path']['structure'], $level_tree) )
					$level_tree[] = $sid;
		}
		
		return $level_tree;
	}

	//Следующий свободный ID в структуре
	public function genNextId($structure_sid='rec'){
		$last=model::execSql('select `id` from `'.$this->getCurrentTable($structure_sid).'` order by `id` desc','getrow', 'system', true);
		if(!IsSet($last['id']))$last['id']=1;
		return $last['id']+1;
	}
	public function getNextId($structure_sid='rec'){
		return $this->genNextId($structure_sid);	
	}

	//Представить запись в виде вершины социального графа
	public function getGraphTop($record_id, $structure_sid='rec'){
		return array( 'module' => $this->info['sid'], 'structure_sid' => $structure_sid, 'id' => $record_id );
	}

	//Проверка наличия доступа к записи
	public function checkAccess($record, $interface_sid){
		
		//Авторы
		if( $record['author'] == user::$info['id'] )
			return true;
		//Ответ
		return false;
	}
	
	public function unitTests(){
		require_once model::$config['path']['core'].'/tests/units.php';
		unitTests::forModule();
	}
	
	public function convertParamsToWhere($params){
		return components::convertParamsToWhere($params);
	}
	public function getOrderBy($params){
		return components::getOrderBy($params);
	}
	
////////////////////////////
/// СОВМЕСТИМОСТЬ С 2.13 ///
////////////////////////////
/*
	public function execSql($sql, $query_type = 'getall', $database = 'system', $no_cache = false){
		return model::execSql($sql, $query_type, $database, $no_cache);
	}
	
	public function makeSql($sql_conditions, $query_type = 'getall', $database = 'system', $no_cache = false){
		return model::makeSql($sql_conditions, $query_type, $database, $no_cache);
	}
*/
	public function updateRecord($record, $structure_sid = 'rec', $conditions=false){
		return $this->editRecord($record, $structure_sid, $conditions);
	}
	
}


?>
