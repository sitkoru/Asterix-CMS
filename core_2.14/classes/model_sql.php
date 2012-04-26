<?php

class ModelSql{

	//Выполнить готовый запрос к базе данных
	public function execSql($sql, //готовый sql-запрос
		$query_type = 'getall', //варианты: getraw, getall, insert, update, delete
		$database = 'system', //нужная база данных.
		$no_cache = false		//Не использовать кеш запроса
		){
		//Засекаем время выполнения запроса
		$t         = explode(' ', microtime());
		$sql_start = $t[1] + $t[0];
/*
		if( $no_cache )
			pr('NO_CACHE: '.$sql.'|'.$query_type);
*/			
		//Используется кеширование - запрашиваем кеш
        $result = false;

        if( model::$config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
            $result = $this->cache->load( $sql.'|'.$query_type, model::$config['cache']['cache_timeout'] );
        }

        //Если кеша не найдено - собираем все данные заново
        if($result === false){

//			pr('sql:exec ['.$sql.'|'.$query_type.']');
		
            //Получение одной записи
            if ($query_type == 'getrow') {
                $result = model::$db[$database]->GetRow($sql);

                //Получение списка данных
            } elseif ($query_type == 'getall') {
                $result = model::$db[$database]->GetAll($sql);

                //Обновление данных
            } elseif ($query_type == 'update') {
                //			pr($sql);
                if (model::$config['settings']['demo_mode']) {
                    print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
                    exit();
                } else {
                    $result = model::$db[$database]->Execute($sql);
                }

                //Вставка данных
            } elseif ($query_type == 'insert') {
                //			pr($sql);
                if ($this->config['settings']['demo_mode']) {
                    print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
                    exit();
                } else {
                    $result = model::$db[$database]->Execute($sql);
                }

                //Удаление данных
            } elseif ($query_type == 'delete') {
                if ($this->config['settings']['demo_mode']) {
                    print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
                    exit();
                } else {
                    $result = model::$db[$database]->Execute($sql);
                }
            }

            //Используется кеширование - записываем результат
            if( model::$config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
                $this->cache->save( $result, $sql.'|'.$query_type );
            }
        }else{
//			pr('sql:cache');
		}

		//Сколько прошло
		$t        = explode(' ', microtime());
		$sql_stop = $t[1] + $t[0];
		$time     = $sql_stop - $sql_start;

		//Статистика
		log::sql($sql, $time, $result, $query_type, $database);

		//Запоминаем последний запрос
		model::$last_sql = $sql;

		//Готово
		return $result;
	}

	//Подготовить запрос к базе данных на основе предоставленных характеристик
	public function makeSql(
		$sql_conditions = array('fields' => array(), 'tables' => array(), 'where' => array(), 'group' => array(), 'order' => '', 'limit' => false), //массив условий для sql-запроса
		$query_type = 'getall', //варианты: getrow, getall, insert, update, delete
		$database = 'system', 	//нужная база данных.
		$no_cache = false		//Не использовать кеш запроса
		){
		
		if (model::$extensions)
			foreach (model::$extensions as $ext)
				if( method_exists ( $ext , 'onSql' ) )
					list($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit']) = $ext->onSql($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit'], $query_type);

		//Что запрашиваем
		if ($query_type == 'getrow' or $query_type == 'getall') {
			
			//Не указано что запрашивать - забираем всё
			if( !is_array( $sql_conditions['fields'] ) ){
				$fields = '*';
			
			//Указано что конкретно забирать			
			}else{
				//Склеиваем с ковычками
				$fields = '';
				foreach($sql_conditions['fields'] as $field){
					if($fields != '')$fields .= ', ';
					
					if(substr_count($field, ' as '))
						$fields .= $field;
					else
						$fields .= '`'.$field.'`';
						
				}
			}

		} elseif ($query_type == 'insert' or $query_type == 'update') {
			//Склеиваем без ковычек
			$fields = implode(', ', $sql_conditions['fields']);
		}

		//Условия
		if ($sql_conditions['where']) {
			if (is_array($sql_conditions['where'])) {
				$res = '';
				foreach ($sql_conditions['where'] as $logic => $vars) {
					$res_logic = '';
					if (is_array($vars))
						foreach ($vars as $i => $val)
							if (strlen($val)) {
								if (strlen($res_logic))
									$res_logic .= ' ' . $logic . ' ';
								$res_logic .= $val;
							}
					if (strlen($res))
						$res .= ' ' . $logic . ' ';
					$res .= '(' . $res_logic . ')';
				}
				$where = $res;
			}
		}

		//Таблицы
		$tables = '`'.implode('`, `', $sql_conditions['tables']).'`';

		//Таблицы
		$order = $sql_conditions['order'];

		//Ограничения
		$limit = $sql_conditions['limit'];

		//Получение одной записи
		if ($query_type == 'getrow') {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' limit 1';

			//Получение списка данных
		} elseif ($query_type == 'getall') {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' ' . $limit . '';

			//Обновление данных
		} elseif ($query_type == 'update') {
			$sql = 'update ' . $tables . ' set ' . $fields . ' where ' . $where . ' ' . $limit . '';

			//Вставка данных
		} elseif ($query_type == 'insert') {
			$sql = 'insert into ' . $tables . ' set ' . $fields . '';

			//Удаление данных
		} elseif ($query_type == 'delete') {
			$sql = 'delete from ' . $tables . ' where ' . $where . '';
		}

		//Режим демонстрации
		if (model::$config['settings']['demo_mode'] and (in_array($query_type, array(
			'update',
			'insert',
			'delete'
		)))) {
			print('В режиме демонстрации вы не можете вносить изменения в базу данных. Нажмите "Назад"');
			exit();
		} else {
			
			//Выполняем запрос
			$result = model::execSql($sql, //готовый sql-запрос
				$query_type, 	//варианты: getraw, getall, insert, update, delete
				$database, 		//нужная база данных.
				$no_cache		//Не использовать кеш запроса
			);
		}

		return $result;
	}


}

?>