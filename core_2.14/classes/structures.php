<?php

trait structures
{

	//Достройка структуры, связей, параметров, типов данных
	public function getStructure_load()
	{

		//Системные поля, необходимые каждому модулю
		$system_fields = array(
			'id'           => array( 'sid' => 'id', 'group' => 'system', 'public' => true, 'type' => 'id', 'title' => 'ID' ),
			'sid'          => array( 'sid' => 'sid', 'group' => 'system', 'public' => false, 'type' => 'sid', 'title' => 'Системное имя' ),
			'date_public'  => array( 'sid' => 'date_public', 'group' => 'main', 'public' => false, 'type' => 'datetime', 'title' => 'Публичная дата' ),
			'date_added'   => array( 'sid' => 'date_added', 'group' => 'system', 'public' => false, 'type' => 'datetime', 'title' => 'Дата создания записи (публиковать не ранее)' ),
			'date_modify'  => array( 'sid' => 'date_modify', 'group' => 'system', 'public' => false, 'type' => 'datetime', 'title' => 'Дата последнего изменения записи' ),
			'title'        => array( 'sid' => 'title', 'group' => 'main', 'public' => true, 'type' => 'text', 'title' => 'Название' ),
			'shw'          => array( 'sid' => 'shw', 'group' => 'show', 'public' => true, 'type' => 'check', 'title' => 'Показывать на сайте', 'default' => true ),
			'url'          => array( 'sid' => 'url', 'group' => 'system', 'public' => false, 'type' => 'text', 'title' => 'Адрес записи', 'default' => '' ),
			'author'       => array( 'sid' => 'author', 'group' => 'system', 'public' => false, 'type' => 'user', 'title' => 'Автор записи', 'default' => false ),
			'edit_history' => array( 'sid' => 'edit_history', 'group' => 'system', 'public' => false, 'type' => 'hidden', 'title' => 'Авторы, изменяющие запись' ),
		);

		//Подгружаем заданную структуру модуля
		$this->setStructure();

		// Пользовательские типы данных
		$structure = $this->getStructures();
		if( $structure ) {
			$keys = array_keys( $structure );
			foreach( $keys as $key ) {
				$fields = $this->getFields( $key );
				foreach( $fields as $field_sid => $field )
					if( $field[ 'type' ][ 0 ] == '_' )
						if( !ModelLoader::loadUserType( $field ) )
							$this->setStructure_removeField( $field_sid, $key );
			}
		}

		// Заносим системные поля
		$structures = $this->getStructures();
		if( $structures )
			if( is_array( $structures ) )
				foreach( $structures as $structure_sid => $part ) {

					// Дополняем поля системными полями
					$fields                                        = $this->getFields( $structure_sid );
					$fields                                        = array_merge( (array)$system_fields, (array)$fields );
					$this->structure[ $structure_sid ][ 'fields' ] = $fields;

					// Деревьям приклеиваем поле наследования
					if( $this->isStructure_tree( $structure_sid ) ) {
						$field = array( 'sid' => 'dep_path_parent', 'group' => 'main', 'type' => 'tree', 'title' => 'Расположение в дереве сайта', 'module' => $this->getModuleSid(), 'structure_sid' => $structure_sid );
						$this->setStructure_addField( 'dep_path_parent', $field, $structure_sid );
					}

					// Указатель на родительскую структуру
					$parent_structure_sid = $this->getStructure_parent( $structure_sid );
					if( $parent_structure_sid ) {
						$field = array(
							'sid'           => 'dep_path_' . $parent_structure_sid,
							'group'         => 'main',
							'type'          => $this->getStructure_parentLink_fieldType( $structure_sid ),
							'title'         => $this->structure[ $structure_sid ][ 'title' ],
							'module'        => $this->getModuleSid(),
							'structure_sid' => $this->getStructure_parentLink_structureSid( $structure_sid ),
						);
						$this->setStructure_addField( 'dep_path_dir', $field, $structure_sid );
					}

				}

		//Если модуль Start - будем клеить поле-ссылку
		if( $this->getModuleSid() == 'start' ) {
			$this->structure[ 'rec' ][ 'fields' ][ 'is_link_to_module' ] = array( 'sid' => 'is_link_to_module', 'type' => 'module', 'group' => 'system', 'title' => 'Раздел является ссылкой на модуль', 'variants' => array_keys( model::$modules ) );
			$this->structure[ 'rec' ][ 'fields' ][ 'url_alias' ]         = array( 'sid' => 'url_alias', 'group' => 'system', 'public' => false, 'type' => 'text', 'title' => 'Виртуальный адрес записи', 'default' => '' );
		}
		//Если модуль Start - будем клеить поле-ссылку
		if( $this->getModuleSid() == 'users' ) {
			$this->structure[ 'rec' ][ 'fields' ][ 'is_link_to' ] = array( 'sid' => 'is_link_to', 'type' => 'user', 'group' => 'system', 'title' => 'Этот модуль является ссылкой на модуль' );
			$this->structure[ 'rec' ][ 'fields' ][ 'salt' ]       = array( 'sid' => 'salt', 'type' => 'hidden', 'group' => 'system', 'title' => 'Соль для пароля' );
		}

//		return $structures;
	}

	//Вернуть массив основных полей структуры
	public function getMainFields( $structure_sid = 'rec' )
	{
		$fields = array( 'id', 'sid', 'title', 'url' );
		$main   = array( 'id', 'sid', 'date_public', 'title', 'url', 'shw', 'dep_path_darent', 'dep_path_dir', 'left_key', 'right_key', 'is_link_to_module', 'seo_title', 'seo_keywords', 'seo_description', 'seo_changefreq', 'seo_priority' );

		$my_fields = $this->getFields( $structure_sid );
		if( $my_fields )
			foreach( $my_fields as $sid => $f )
				if( ( in_array( $sid, $main ) || IsSet( $f[ 'main' ] ) ) and ( !IsSet( $fields[ $sid ] ) ) )
					$fields[ $sid ] = $sid;

		return $fields;
	}

	//Разворачиваем значения полей перед выводом в браузер
	public function explodeRecord( $rec, $structure_sid = 'rec', $explode_fields = true )
	{

		if( $explode_fields && ( $explode_fields !== true ) )
			$explode_fields = array_values( explode( ',', $explode_fields ) );

		$second_level_explodable_fields = array( 'image', 'gallery' );

		if( is_array( $rec ) )
			foreach( $rec as $sid => $value ) {

				// Настройки поля в структуре модуля
				$field_settings = $this->structure[ $structure_sid ][ 'fields' ][ $sid ];

				// Разворачиваем значение
				if( $field_settings[ 'type' ] ) {
					if( IsSet( model::$types[ $field_settings[ 'type' ] ] ) )

						/*
							Разворачиваем ненулевые значения
							только если разрешено разворачивать все
							либо разрешено разворачивать конкретно это поле
							либо это поле всегда разворачивается

						*/
						if( $value && ( ( $explode_fields === true ) || in_array( $sid, (array)$explode_fields ) || in_array( $field_settings[ 'type' ], $second_level_explodable_fields ) ) ) {

							$rec[ $sid ] = model::$types[ $field_settings[ 'type' ] ]->getValueExplode( $value, $this->structure[ $structure_sid ][ 'fields' ][ $sid ], $rec );

							//Разварачиваем картинки у связанных записей
							if( $field_settings[ 'type' ] == 'link' ) {

								if( IsSet( model::$modules[ $field_settings[ 'module' ] ]->structure[ $field_settings[ 'structure_sid' ] ] ) )
									foreach( model::$modules[ $field_settings[ 'module' ] ]->structure[ $field_settings[ 'structure_sid' ] ][ 'fields' ] as $sub_field_sid => $sub_field )
										if( $sub_field[ 'type' ] == 'image' ) {

											//Разворачиваем занчение
											$new_val = model::$types[ 'image' ]->getValueExplode(
												$rec[ $sid ][ $sub_field_sid ],
												model::$modules[ $field_settings[ 'module' ] ]->structure[ $field_settings[ 'structure_sid' ] ][ 'fields' ][ $sub_field_sid ],
												$field_settings
											);

											//Если значение развернулось
											if( $new_val )
												if( is_array( $new_val ) )
													$rec[ $sid ][ $sub_field_sid ] = $new_val;
										}

								//Разварачиваем картинки у связанных записей
							} elseif( $field_settings[ 'type' ] == 'user' ) {

								$module_sid = model::getModuleSidByPrototype( 'users' );
								foreach( model::$modules[ $module_sid ]->structure[ 'rec' ][ 'fields' ] as $sub_field_sid => $sub_field )
									if( $sub_field[ 'type' ] == 'image' ) {

										//Разворачиваем занчение
										$new_val = model::$types[ 'image' ]->getValueExplode(
											$rec[ $sid ][ $sub_field_sid ],
											model::$modules[ $module_sid ]->structure[ 'rec' ][ 'fields' ][ $sub_field_sid ],
											$field_settings
										);

										//Если значение развернулось
										if( $new_val )
											if( is_array( $new_val ) )
												$rec[ $sid ][ $sub_field_sid ] = $new_val;
									}

							}
						}
				}
			}

		//BETA
		$rec[ 'module' ]        = $this->info[ 'sid' ];
		$rec[ 'structure_sid' ] = $structure_sid;

		//Если установлено расширение социального графа - дополняем записи значением вершины графа
		if( IsSet( model::$extensions[ 'graph' ] ) ) {
			$rec[ 'graph_top' ]      = $this->getGraphTop( $rec[ 'id' ], $structure_sid );
			$rec[ 'graph_top_text' ] = implode( '|', $this->getGraphTop( $rec[ 'id' ], $structure_sid ) );
		}

		return $rec;
	}

	//Вставка html или других окончаний для URL-ов записей
	public function insertRecordUrlType( $rec, $type = 'html', $insert_host = false )
	{

		//Передана одна запись
		if( IsSet( $rec[ 'url' ] ) && !IsSet( $rec[ 'url_clear' ] ) ) {
			if( strlen( $rec[ 'url' ] ) ) {
				if( !substr_count( $rec[ 'url' ], '.' . $type ) ) {
					$rec[ 'url_print' ] = $rec[ 'url' ] . '.print.' . $type;
					$rec[ 'url_clear' ] = $rec[ 'url' ];
					$rec[ 'url' ]       = $rec[ 'url' ] . '.' . $type;
				}
			} else {
				$rec[ 'url' ]       = '/';
				$rec[ 'url_print' ] = '/start.print.' . $type;
				$rec[ 'url_clear' ] = '/start';
			}

			if( IsSet( $rec[ 'sub' ] ) )
				$rec[ 'sub' ] = $this->insertRecordUrlType( $rec[ 'sub' ], $type, $insert_host );

			//Делать полный путь, а не относительный
			if( $insert_host )
				$rec = $this->insertHostToUrl( $rec );

			//Несколько записей
		} elseif( IsSet( $rec[ 0 ][ 'url' ] ) && !IsSet( $rec[ 0 ][ 'url_clear' ] ) ) {
			foreach( $rec as $i => $record )
				$rec[ $i ] = $this->insertRecordUrlType( $record, $type, $insert_host );
		}

		return $rec;
	}

	//Указать путь, включая хост
	public function insertHostToUrl( $rec )
	{
		$rec[ 'url' ] = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $rec[ 'url' ];

		return $rec;
	}


	public function isStructure_set( $structure_sid = 'rec' )
	{
		$structures = $this->getStructures();

		if( !is_string( $structure_sid ) ) {
			pr( 'не строка' );
			pr_r( $structure_sid );
		}

		if( $structures )
			if( IsSet( $structures[ $structure_sid ] ) )
				return true;

		return false;
	}

	public function isStructure_hidden( $structure_sid = 'rec' )
	{
//		pr( '17 ' . get_called_class() );
//		pr( get_called_class().'  / '. __CLASS__.' / '.get_class($this) );

//		pr_r(get_parent_class(__CLASS__));

		$structure = $this->getStructure( $structure_sid );

		if( IsSet( $structure[ 'hide_in_tree' ] ) )
			if( $structure[ 'hide_in_tree' ] )
				return true;

		return false;
	}

	public function isStructure_simple( $structure_sid = 'rec' )
	{
		if( $this->getStructure_type( $structure_sid ) == 'simple' )
			return true;

		return false;
	}

	public function isStructure_tree( $structure_sid = 'rec' )
	{
		if( $this->getStructure_type( $structure_sid ) == 'tree' )
			return true;

		return false;
	}


	public function isField_set( $field_sid, $structure_sid = 'rec' )
	{
		$fields = $this->getFields( $structure_sid );

		if( IsSet( $fields[ $field_sid ] ) )
			if( is_array( $fields[ $field_sid ] ) )
				return true;

		return false;
	}


	public function countStructures()
	{
		$structures = $this->getStructures();

		if( $structures )
			if( is_array( $structures ) )
				return count( $structures );

		return false;
	}


	public function getStructures()
	{
		$module_sid = $this->getModuleSid();

		if( IsSet( model::$modules[ $module_sid ]->structure ) )
			return model::$modules[ $module_sid ]->structure;

		return false;
	}

	public function getStructure( $structure_sid = 'rec' )
	{
		if( $this->isStructure_set( $structure_sid ) ) {
			$structures = $this->getStructures();

			return $structures[ $structure_sid ];
		}

		return false;
	}

	public function getStructure_type( $structure_sid = 'rec' )
	{
		$structure = $this->getStructure( $structure_sid );
		$types     = array( 'simple', 'tree' );

		if( IsSet( $structure[ 'type' ] ) )
			if( in_array( $structure[ 'type' ], $types ) )
				return $structure[ 'type' ];

		return false;
	}


	public function getStructure_parent( $structure_sid = 'rec' )
	{
		$structure = $this->getStructure( $structure_sid );

		if( $this->isStructure_tree( $structure_sid ) )
			return false;

		else
			if( IsSet( $structure[ 'dep_path' ] ) )
				if( is_array( $structure[ 'dep_path' ] ) )
					if( IsSet( $structure[ 'dep_path' ][ 'structure' ] ) )
						return $structure[ 'dep_path' ][ 'structure' ];

		return false;
	}

	public function getStructure_parentLink_fieldType( $structure_sid = 'rec' )
	{
		$structure = $this->getStructure( $structure_sid );

		if( IsSet( $structure[ 'dep_path' ] ) )
			if( is_array( $structure[ 'dep_path' ] ) )
				if( IsSet( $structure[ 'dep_path' ][ 'link_type' ] ) )
					return $structure[ 'dep_path' ][ 'link_type' ];

		return false;
	}

	public function getStructure_parentLink_structureSid( $structure_sid = 'rec' )
	{
		$structure = $this->getStructure( $structure_sid );

		if( IsSet( $structure[ 'dep_path' ] ) )
			if( is_array( $structure[ 'dep_path' ] ) )
				if( IsSet( $structure[ 'dep_path' ][ 'structure' ] ) )
					return $structure[ 'dep_path' ][ 'structure' ];

		return false;
	}

	public function getStructure_parentLink_fieldSid( $structure_sid = 'rec' )
	{
		$field_type = $this->getStructure_parentLink_fieldType( $structure_sid );

		if( IsSet( model::$types[ $field_type ] ) )
			if( is_object( model::$types[ $field_type ] ) )
				if( IsSet( model::$types[ $field_type ]->link_field ) )
					return model::$types[ $field_type ]->link_field;

		return false;
	}


	public function getStructure_child( $structure_sid )
	{
		if( $this->isStructure_tree( $structure_sid ) )
			return $structure_sid;

		$structures = $this->getStructures();
		if( $structures )
			if( is_array( $structures ) ) {
				$keys = array_keys( $structures );
				foreach( $keys as $key )
					if( $this->getStructure_parent( $key ) == $structure_sid )
						return $key;
			}

		return false;
	}

	public function getStructure_childLink_fieldType( $structure_sid = 'rec' )
	{
		$child_structure_sid = $this->getStructure_child( $structure_sid );
		if( !$child_structure_sid ) return false;

		$structure = $this->getStructure( $child_structure_sid );
		if( IsSet( $structure[ 'dep_path' ] ) )
			if( is_array( $structure[ 'dep_path' ] ) )
				if( IsSet( $structure[ 'dep_path' ][ 'link_type' ] ) )
					return $structure[ 'dep_path' ][ 'link_type' ];

		return false;
	}

	public function getStructure_childLink_fieldSid( $structure_sid = 'rec' )
	{
		$field_type = $this->getStructure_childLink_fieldType( $structure_sid );

		if( IsSet( model::$types[ $field_type ] ) )
			if( is_object( model::$types[ $field_type ] ) )
				if( IsSet( model::$types[ $field_type ]->link_field ) )
					return model::$types[ $field_type ]->link_field;

		return false;
	}


	//Получить иерархию структур модуля
	public function getStructure_allLevels()
	{
		$i                    = 0;
		$tree_level           = array();
		$last_level_structure = 'rec';

		do {
			$tree_level[ ] = $last_level_structure;

			$parent = $this->getStructure_parent( $last_level_structure );

			// Дерево само для себя является родителем, прерываем цикл
			if( $parent == $last_level_structure )
				$parent = false;

			$last_level_structure = false;
			if( $parent )
				if( $this->isStructure_set( $parent ) )
					$last_level_structure = $parent;

			$i++;
		} while ( $last_level_structure && ( $i<100 ) );

		return $tree_level;
	}


	public function getFields( $structure_sid = 'rec' )
	{
		$structure = $this->getStructure( $structure_sid );

		if( IsSet( $structure[ 'fields' ] ) )
			if( is_array( $structure[ 'fields' ] ) )
				return $structure[ 'fields' ];

		return false;
	}

	public function getField( $field_sid, $structure_sid = 'rec' )
	{
		if( $this->isField_set( $field_sid, $structure_sid ) ) {
			$fields = $this->getFields( $structure_sid );

			return $fields[ $field_sid ];
		}

		return false;
	}


	public function getStructure_defaultOrderBy( $structure_sid = 'rec' )
	{
		if( $this->isStructure_tree( $structure_sid ) )
			return 'order by `left_key` asc';

		elseif( $this->isField_set( 'pos', $structure_sid ) )
			return 'order by `pos` asc, `title` asc';

		elseif( $this->isField_set( 'lock_up', $structure_sid ) )
			return 'order by `lock_up` desc, `date_public` desc';

		else
			return 'order by `date_public` desc';
	}

	public function setStructure_removeField( $field_sid, $structure_sid = 'rec' )
	{
		$fields = $this->getFields( $structure_sid );

		if( IsSet( $fields[ $field_sid ] ) ) {
			UnSet( $fields[ $field_sid ] );
			$this->structure[ $structure_sid ][ 'fields' ] = $fields;
		}

	}

	public function setStructure_addField( $field_sid, $field_array, $structure_sid = 'rec' )
	{

		$fields               = $this->getFields( $structure_sid );
		$fields[ $field_sid ] = $field_array;

		$this->structure[ $structure_sid ][ 'fields' ] = $fields;

	}

	// Имя модуля, вызвавшего метод
	public function getModuleSid()
	{
		if( !IsSet( $this->info[ 'sid' ] ) )
			log::stop( '500', 'Использование обращения к ' . __METHOD__ . ' не из модуля запрещено.', 'Родительский класс: ' . get_called_class() );

		return $this->info[ 'sid' ];
	}


}