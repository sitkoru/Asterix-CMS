<?php

trait db
{

	//Выполнить готовый запрос к базе данных
	static public function execSql( $sql, //готовый sql-запрос
									$query_type = 'getall', //варианты: getraw, getall, insert, update, delete
									$database = 'system', //нужная база данных.
									$no_cache = false //Не использовать кеш запроса
	)
	{

		if( !strlen( $sql ) )
			return false;

		// Запрос из кеша движка
		if( !$no_cache ) {
			$result = cache::readSqlCache( $sql );
			if( $result )
				return $result;
		}

		//Засекаем время выполнения запроса
		$t         = explode( ' ', microtime() );
		$sql_start = $t[1]+$t[0];

		//Используется кеширование - запрашиваем кеш
		$result = false;

		if( model::$config['cache'] and (!$no_cache) and in_array( $query_type, array( 'getrow', 'getall' ) ) ) {
			$result = model::$cache->load( $sql . '|' . $query_type, model::$config['cache']['cache_timeout'] );
		}

		$result_count = 0;

		//Если кеша не найдено - собираем все данные заново
		if( $result === false ) {

			// Получение одной записи
			if( $query_type == 'getrow' ) {
				$result       = model::$db[$database]->GetRow( $sql );
				$result_count = 1;

				// Получение списка данных
			} elseif( $query_type == 'getall' ) {
				$result       = model::$db[$database]->GetAll( $sql );
				$result_count = count( $result );

				// Вставка данных
			} elseif( $query_type == 'insert' ) {
				model::check_demo();
				$result       = model::$db[$database]->Insert( $sql );
				$result_count = 0;

				// Изменение
			} elseif( in_array( $query_type, array( 'replace', 'update', 'delete' ) ) ) {
				model::check_demo();
				$result       = model::$db[$database]->Execute( $sql );
				$result_count = 0;
			}

			//Используется кеширование - записываем результат
			if( model::$config['cache'] and (!$no_cache) and in_array( $query_type, array( 'getrow', 'getall' ) ) ) {
				model::$cache->save( $result, $sql . '|' . $query_type );
			}
		}

		//Сколько прошло
		$t        = explode( ' ', microtime() );
		$sql_stop = $t[1]+$t[0];
		$time     = $sql_stop-$sql_start;

		//Статистика
		log::sql( $sql, $time, $result_count, $query_type, $database );

		//Запоминаем последний запрос
		model::$last_sql = $sql;

		// Запоминаем результат в кеше движка
		if( !$no_cache )
			cache::makeSqlCache( $sql, $result );

		//Готово
		return $result;
	}

	//Подготовить запрос к базе данных на основе предоставленных характеристик
	static public function makeSql(
		$sql_conditions = array( 'fields' => array(), 'tables' => array(), 'where' => array(), 'group' => array(), 'order' => '', 'limit' => false ), //массив условий для sql-запроса
		$query_type = 'getall', //варианты: getrow, getall, insert, update, delete
		$database = 'system', //нужная база данных.
		$no_cache = false //Не использовать кеш запроса
	)
	{
		/*
				if (model::$extensions)
					foreach (model::$extensions as $ext)
						if( method_exists ( $ext , 'onSql' ) )
							list($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit']) = $ext->onSql($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit'], $query_type);
		*/
		//Что запрашиваем
		if( $query_type == 'getrow' or $query_type == 'getall' ) {

			//Не указано что запрашивать - забираем всё
			if( !is_array( $sql_conditions['fields'] ) ) {
				$fields = '*';

				//Указано что конкретно забирать
			} else {
				//Склеиваем с ковычками
				$fields = '';
				foreach( $sql_conditions['fields'] as $field ) {
					if( $fields != '' ) $fields .= ', ';

					if( substr_count( $field, ' as ' ) )
						$fields .= $field;
					else
						$fields .= '`' . $field . '`';

				}
			}

		} elseif( in_array( $query_type, array( 'insert', 'update', 'replace' ) ) ) {
			//Склеиваем без ковычек
			$fields = implode( ', ', $sql_conditions['fields'] );
		}

		//Условия
		if( $sql_conditions['where'] ) {
			if( is_array( $sql_conditions['where'] ) ) {
				$res = '';
				foreach( $sql_conditions['where'] as $logic => $vars ) {
					$res_logic = '';
					if( is_array( $vars ) )
						foreach( $vars as $i => $val )
							if( strlen( $val ) ) {
								if( strlen( $res_logic ) )
									$res_logic .= ' ' . $logic . ' ';
								$res_logic .= $val;
							}
					if( strlen( $res ) )
						$res .= ' ' . $logic . ' ';
					$res .= '(' . $res_logic . ')';
				}
				$where = $res;
			}
		}

		//Таблицы
		$tables = '`' . implode( '`, `', $sql_conditions['tables'] ) . '`';

		//Таблицы
		$order = $sql_conditions['order'];

		//Ограничения
		$limit = $sql_conditions['limit'];

		//Получение одной записи
		if( $query_type == 'getrow' ) {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' limit 1';

			//Получение списка данных
		} elseif( $query_type == 'getall' ) {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' ' . $limit . '';

			//Обновление данных
		} elseif( $query_type == 'update' ) {
			$sql = 'update ' . $tables . ' set ' . $fields . ' where ' . $where . ' ' . $limit . '';

			//Вставка данных
		} elseif( $query_type == 'insert' ) {
			$sql = 'insert into ' . $tables . ' set ' . $fields . '';

			//Вставка данных
		} elseif( $query_type == 'replace' ) {
			$sql = 'replace into ' . $tables . ' set ' . $fields . '';

			//Удаление данных
		} elseif( $query_type == 'delete' ) {
			$sql = 'delete from ' . $tables . ' where ' . $where . '';
		}

		/*
				//Режим демонстрации
				if (model::$config['settings']['demo_mode'] and (in_array($query_type, array(
					'update',
					'insert',
					'replace',
					'delete'
				)))) {
					print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
					exit();
				} else {
		*/
		//Выполняем запрос
		$result = model::execSql( $sql, //готовый sql-запрос
			$query_type, //варианты: getraw, getall, insert, update, delete
			$database, //нужная база данных.
			$no_cache //Не использовать кеш запроса
		);

//		}

		return $result;
	}


}
