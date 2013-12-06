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
/*															*/
/************************************************************/

require_once( 'structures.php' );
require_once( 'components.php' );
require_once( 'interfaces.php' );
require_once( 'acms_trees.php' );
require_once( 'model_finder.php' );

//Модуль по умолчанию
class default_module // extends Dynamic
{
	use structures, interfaces, components, acms_trees, finder;

	//Приставка перед таблицей в дазе данных - пока не используется
	public $database_table_preface = false;

	// Идентификатор базы данных, у основного модуля всегда system,
	public $db_sid = 'system';

	//Шаблоны в модуле по умолчанию
	public $templates = array(
		'index'   => array( 'sid' => 'index', 'title' => 'Главная страница модуля' ),
		'content' => array( 'sid' => 'content', 'title' => 'Страница модуля одной записи' ),
	);

	//Шаблоны в модуле по умолчанию
	public $prepares = array();
	public $interfaces = array();

////////////////////////////
/// ИНИЦИАЛИЗАЦИЯ МОДУЛЯ ///
////////////////////////////

	//Инициализация модуля
	public function __construct( $model, $info )
	{
		$this->model = $model;
		$this->info  = $info;

		model::$modules[ $info[ 'sid' ] ] = $this;

		$this->getStructure_load();
	}

	// Инициализация компонентов и интерфейсов
	public function init()
	{
		$this->prepares   = $this->getComponent_load( $this );
		$this->interfaces = $this->getInterface_load( $this );
	}

	//Инициализация компонентов
	public function prepareComponent( $prepare, $params )
	{
		return $this->getComponent_init( $this, $prepare, $params );
	}

	//Эти функции используются в модулях для донастройки структуры и интерфейсов
	public function setStructure()
	{
	}

	public function setInterfaces()
	{
	}


///////////////////////////////////////////
/// ВНУТРЕННИЕ СЛУЖЕБНЫЕ ФУНКЦИИ МОДУЛЯ ///
///////////////////////////////////////////

	//Дополнительная обработка записи при типе вывода "content"
	public function contentPrepare( $rec, $structure_sid = 'rec' )
	{
		return $rec;
	}

	//Возвращаем название таблицы текущей структуры
	public function getCurrentTable( $part = 'rec' )
	{
		return $this->database_table_preface . $this->info[ 'prototype' ] . '_' . $part;
	}

	//Следующий свободный ID в структуре
	public function genNextId( $structure_sid = 'rec' )
	{
		$last = model::execSql( 'select `id` from `' . $this->getCurrentTable( $structure_sid ) . '` order by `id` desc', 'getrow', 'system', true );
		if( !IsSet( $last[ 'id' ] ) ) $last[ 'id' ] = 1;

		return $last[ 'id' ]+1;
	}

	public function getNextId( $structure_sid = 'rec' )
	{
		return $this->genNextId( $structure_sid );
	}

	//Представить запись в виде вершины социального графа
	public function getGraphTop( $record_id, $structure_sid = 'rec' )
	{
		return array( 'module' => $this->info[ 'sid' ], 'structure_sid' => $structure_sid, 'id' => $record_id );
	}

	//Проверка наличия доступа к записи
	public function checkAccess( $record, $interface_sid )
	{

		//Авторы
		if( $record[ 'author' ] == user::$info[ 'id' ] )
			return true;

		//Ответ
		return false;
	}

	public function unitTests()
	{
		require_once model::$config[ 'path' ][ 'core' ] . '/tests/units.php';
		unitTests::forModule();
	}

	public function getOrderBy( $structure_sid = 'rec' )
	{
		return $this->getStructure_defaultOrderBy( $structure_sid );
	}

}
