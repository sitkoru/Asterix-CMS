<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Расширение для ведения социального графа сайта		*/
/*															*/
/*	Версия ядра 2.0.5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 27 апреля 2010 года								*/
/*	Модифицирован: 27 апреля 2010 года						*/
/*															*/
/************************************************************/

require_once 'default.php';

class extention_graph extends extention_default
{
	var $title = 'Граф';
	var $sid = 'graph';
	var $table_name = 'graph';

	//Типы связей
	var $links = array(
		'friend'   => array( 'title' => 'Подружиться', 'link' => '[2] добавлен в друзья', 'unlink' => '[2] убран из друзей' ),
		'connect'  => array( 'title' => 'Подключиться к сети', 'link' => 'Вы подключены к сети [2]', 'unlink' => 'Вы отключены от сети [2]' ),
		'link'     => array( 'title' => 'Связать', 'link' => 'Добавлена связь между [2] и [1]', 'unlink' => 'Удалена связь между [2] и [1]' ),
		'want'     => array( 'title' => 'Хочу', 'link' => 'Вы бы хотели [2]', 'unlink' => 'Вы больше не хотите [2]' ),
		'have'     => array( 'title' => 'У меня есть!', 'link' => 'Вы пользуетесь [2]', 'unlink' => 'Вы больше не пользуетесь [2]' ),
		'use'      => array( 'title' => 'Я использую', 'link' => 'Вы используете [2]', 'unlink' => 'Вы польше не используете [2]' ),
		'favorite' => array( 'title' => 'Избранное', 'link' => 'Запись добавлена в избранное', 'unlink' => 'Запись убрана из избранного' ),
	);

	//Инициализация расширения
	public function __construct( $model )
	{
		$this->model = $model;
	}

	//Инициализация расширения
	public function execute()
	{

	}


	//Обращение к социальному графу из шаблона
	public function askFromTemplate( $function, $settings )
	{
		return $this->$function( @$settings );
	}

	//Получить полный граф для записи, либо взаимный граф со второй записью
	public function read( $params )
	{
		if( $params['top1'] )
			list($module, $structure_sid, $record_id) = explode( '|', $params['top1'] );
		elseif( $params['top2'] )
			list($module, $structure_sid, $record_id) = explode( '|', $params['top2'] ); elseif( $params['any_top'] )
			list($module, $structure_sid, $record_id) = explode( '|', $params['any_top'] );

		//Общий граф - нечёткий выбор
		if( $params['common'] ) {
			if( $params['top1'] ) $where['top1'] = '`top1` LIKE "' . mysql_real_escape_string( $params['top1'] ) . '%"';
			if( $params['top2'] ) $where['top2'] = '`top2` LIKE "' . mysql_real_escape_string( $params['top2'] ) . '%"';
			if( $params['any_top'] ) $where['top'] = '( (`top1` LIKE "' . mysql_real_escape_string( $params['any_top'] ) . '%") || (`top2` LIKE "' . mysql_real_escape_string( $params['any_top'] ) . '%") )';

			//Чёткий граф
		} else {
			if( $params['top1'] ) $where['top1'] = '`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"';
			if( $params['top2'] ) $where['top2'] = '`top2`="' . mysql_real_escape_string( $params['top2'] ) . '"';
			if( $params['any_top'] ) $where['top'] = '( (`top1`="' . mysql_real_escape_string( $params['any_top'] ) . '") || (`top2`="' . mysql_real_escape_string( $params['any_top'] ) . '") )';
		}

		//Тип связи
		if( $params['type'] ) $where['type'] = '`type`="' . mysql_real_escape_string( $params['type'] ) . '"';

		//Проверка
		$links = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'where'  => array( 'and' => $where ),
				'order'  => 'order by `date_added` desc',
			),
			'getall'
		);
		//pr(model::$last_sql);

		///Восстанавливаем записи из этих ссылок
		$recs = array();
		foreach( $links as $i => $link )
			if( strlen( $link['top1'] ) && strlen( $link['top2'] ) ) {
				if( $params['top1'] ) {
					list($module, $structure_sid, $record_id) = explode( '|', $link['top2'] );

				} elseif( $params['top2'] ) {
					list($module, $structure_sid, $record_id) = explode( '|', $link['top1'] );

				} elseif( $params['any_top'] ) {
					if( $link['top1'] == $params['any_top'] )
						list($module, $structure_sid, $record_id) = explode( '|', $link['top2'] );
					else
						list($module, $structure_sid, $record_id) = explode( '|', $link['top1'] );
				}

				if( IsSet(model::$modules[$module]) ) {
					$rec = model::$modules[$module]->getRecordById( $structure_sid, $record_id );

					if( $rec ) {
						$rec                      = model::$modules[$module]->explodeRecord( $rec, $structure_sid );
						$rec['graph_top']['type'] = $link['type'];

						$rec['module']       = $module;
						$rec['module_title'] = @model::$modules[$module]->title_one;
						$rec['structure']    = model::$modules[$module]->info['title'];

						//Кроме текущей записи
						if( $rec['graph_top_text'] != $params['any_top'] ) {

							//Рассортировать по модулям
							if( $params['by_module'] ) {
								$recs[$rec['structure']][] = $rec;

								//Рассортировать по типам связей
							} elseif( $params['by_type'] ) {
								$title          = model::$modules[$module]->graph_types[$link['type']]['title'];
								$recs[$title][] = $rec;

								//Рассортировать по типам связей
							} elseif( $params['by_type_back'] ) {
								list($p_module, $p_structure_sid, $p_record_id) = explode( '|', $link['top2'] );
								$title          = model::$modules[$p_module]->graph_types[$link['type']]['back'];
								$recs[$title][] = $rec;

								//Обычная выдача
							} else {
								$recs[] = $rec;
							}
						}
					}
				}
			}

		//Готово
		return $recs;
	}

	//Проверить наличие связи и её тип
	public function check( $params )
	{
		//Условия
		$where = array(
			'access' => false
		);
		if( $params['top1'] ) $where[] = '`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"';
		if( $params['top2'] ) $where[] = '`top2`="' . mysql_real_escape_string( $params['top2'] ) . '"';
		if( $params['type'] ) $where[] = '`type`="' . mysql_real_escape_string( $params['type'] ) . '"';
		if( $params['author'] ) $where[] = '`author`="' . mysql_real_escape_string( $params['author'] ) . '"';

		//Проверка
		$link = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'where'  => array( 'and' => $where ),
			),
			'getrow'
		);

		return $link;
	}

	//Добавить связь для двух вершин графа
	public function link( $params )
	{
		if( !IsSet($params['type']) )
			$params['type'] = 'link';

		//Проверяем, вдруг уже есть триггер
		$check = $this->check( $params );

		if( $check ) {
			$result = model::makeSql(
				array(
					'tables' => array( $this->table_name ),
					'fields' => array(
						'`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"',
						'`top2`="' . mysql_real_escape_string( $params['top2'] ) . '"',
						'`type`="' . mysql_real_escape_string( $params['type'] ) . '"',
						'`date_added`=NOW()',
						'`author`="' . mysql_real_escape_string( user::$info['id'] ) . '"',
						'`text`="' . mysql_real_escape_string( @$params['text'] ) . '"',
					),
					'where'  => array(
						'and' => array(
							'`id`="' . $check['id'] . '"',
							'access' => false,
						)
					),
				),
				'update'
			);

//			pr(model::$last_sql);
//			exit();
			return false;
		}

		$result = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'fields' => array(
					'`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"',
					'`top2`="' . mysql_real_escape_string( $params['top2'] ) . '"',
					'`type`="' . mysql_real_escape_string( $params['type'] ) . '"',
					'`date_added`=NOW()',
					'`author`="' . mysql_real_escape_string( user::$info['id'] ) . '"',
					'`text`="' . mysql_real_escape_string( @$params['text'] ) . '"',
				),
			),
			'insert'
		);

		return $result;
	}

	//Удалить указанную связь для двух вершин графа
	public function unlink( $params )
	{
		if( !IsSet($params['type']) )
			$params['type'] = 'link';

		//Проверяем, вдруг уже есть триггер
		$check = $this->check( $params );
		if( !$check ) return false;

		$result = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'where'  => array( 'and' => array(
					'`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"',
					'`top2`="' . mysql_real_escape_string( $params['top2'] ) . '"',
					'`type`="' . mysql_real_escape_string( $params['type'] ) . '"',
					'`author`="' . mysql_real_escape_string( user::$info['id'] ) . '"',
					'access' => false,
				) ),
			),
			'delete'
		);

		return true;
	}

	//Показать ожидающие свзяи
	public function waiting( $params )
	{
		//Ищем незавершённые ссылки
		$links = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'where'  => array( 'and' => array(
					'`top2`=""',
					'`author`="' . mysql_real_escape_string( user::$info['id'] ) . '"',
					'access' => false
				) ),
			),
			'getall'
		);
		//pr(model::$last_sql);

		///Восстанавливаем записи из этих ссылок
		$recs = array();
		foreach( $links as $link ) {
			list($module, $structure_sid, $record_id) = explode( '|', $link['top1'] );

			$rec = model::$modules[$module]->getRecordById( $structure_sid, $record_id );
			$rec = model::$modules[$module]->explodeRecord( $rec, $structure_sid );

			$recs[] = $rec;
		}

		//Готово
		return $recs;
	}

	//Начать процесс связывания записи
	public function start( $params )
	{
		if( !IsSet($params['type']) )
			$params['type'] = 'link';

		//Проверяем, вдруг уже есть триггер
		$check = $this->check(
			array(
				'top1' => $params['top1'],
				'top2' => false,
				'type' => $params['type']
			)
		);
		if( $check ) return 'Запись уже есть в очереди ожидания';

		$result = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'fields' => array(
					'`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"',
					'`type`="' . mysql_real_escape_string( $params['type'] ) . '"',
					'`date_added`=NOW()',
					'`author`="' . mysql_real_escape_string( user::$info['id'] ) . '"',
				),
			),
			'insert'
		);

		//Готово
		if( $result )
			return 'start';
		else
			return 'Что-то пошло не так';
	}

	//Начать процесс связывания записи
	public function finish( $params )
	{
		if( !IsSet($params['type']) )
			$params['type'] = 'link';

		$result = model::makeSql(
			array(
				'tables' => array( $this->table_name ),
				'fields' => array(
					'`top2`="' . mysql_real_escape_string( $params['top2'] ) . '"',
					'`type`="' . mysql_real_escape_string( $params['type'] ) . '"',
					'`date_added`=NOW()',
				),
				'where'  => array( 'and' => array(
					'`top1`="' . mysql_real_escape_string( $params['top1'] ) . '"',
					'`top2`=""',
					'`author`="' . mysql_real_escape_string( user::$info['id'] ) . '"',
					'access' => false
				) ),
			),
			'update'
		);
		//Готово
		if( $result )
			return 'ok';
		else
			return 'Что-то пошло не так';
	}


}

?>