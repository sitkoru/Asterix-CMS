<?php

trait finder
{

	//Забрать запись по ID
	public function getRecordById( $structure_sid, $id )
	{
		//Получаем записи
		$rec = model::makeSql(
			array(
				'fields' => false,
				'tables' => array( $this->getCurrentTable( $structure_sid ) ),
				'where'  => array( 'and' => array( '`id`="' . mysql_real_escape_string( $id ) . '"' ) ),
				'order'  => $order
			),
			'getrow'
		);

		//Чистим данные
		$rec = self::clearAfterDB( $rec );

		//Вставляем окончание .html
		$rec = $this->insertRecordUrlType( $rec );

		return $rec;
	}

	//Забрать запись по SID
	public function getRecordBySid( $structure_sid, $sid )
	{
		//Получаем записи
		$rec = model::makeSql(
			array(
				'fields' => false,
				'tables' => array( $this->getCurrentTable( $structure_sid ) ),
				'where'  => array( 'and' => array( '`sid`="' . mysql_real_escape_string( $sid ) . '"', '`shw`=1' ) )
			),
			'getrow'
		);

		//Чистим данные
		$rec = self::clearAfterDB( $rec );

		//Вставляем окончание .html
		$rec = $this->insertRecordUrlType( $rec );

		return $rec;
	}

	//Забрать запись по WHERE
	public function getRecordsByWhere( $structure_sid, $where )
	{
		//Сортировка
		$order = 'order by `date_public` desc';
		if( IsSet($this->structure[$structure_sid]['fields']['pos']) ) $order = 'order by `pos`';
		if( $this->structure[$structure_sid]['type'] == 'tree' ) $order = 'order by `left_key`';

		//Получаем записи
		$recs = model::makeSql(
			array(
				'fields' => false,
				'tables' => array( $this->getCurrentTable( $structure_sid ) ),
				'where'  => $where,
				'order'  => $order
			),
			'getall'
		);

		//Чистим данные
		$recs = self::clearAfterDB( $recs );

		//Вставляем окончание .html
		$recs = $this->insertRecordUrlType( $recs );

		return $recs;
	}

	//Форматирование полученных данных после запроса из базы
	public function clearAfterDB( $recs )
	{

		//Список записей
		if( IsSet($recs[0]['id']) ) {
			foreach( $recs as $i => $rec ) {
				foreach( $rec as $var => $val ) {
					if( !is_array( $val ) ) {
						$recs[$i][$var] = htmlspecialchars_decode( stripslashes( $val ) );
					} else {
						$recs[$i][$var] = self::clearAfterDB( $val );
					}
				}
			}

			//Одна запись
		} elseif( IsSet($recs['id']) ) {
			foreach( $recs as $var => $val ) {
				if( !is_array( $val ) ) {
					$recs[$var] = htmlspecialchars_decode( stripslashes( $val ) );
				} else {
					$recs[$var] = self::clearAfterDB( $val );
				}
			}
		}

		return $recs;
	}

}
