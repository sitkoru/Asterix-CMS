<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Менеджер управления таблицами базы данных			*/
/*															*/
/*	Версия ядра 2.0											*/
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

class table_manage
{
	//Поддерживаемые типы полей данных
	private $supported_types = array('id' => 'field_type_id.php', 'sid' => 'field_type_sid.php', 'pos' => 'field_type_pos.php', 'ln' => 'field_type_ln.php', 'int' => 'field_type_int.php', 'float' => 'field_type_float.php', 'label' => 'field_type_label.php', 'text' => 'field_type_text.php', 'textarea' => 'field_type_textarea.php', 'text_editor' => 'field_type_texteditor.php', 'password' => 'field_type_password.php', 'object' => 'field_type_object.php', 'tags' => 'field_type_tags.php', 'map_point' => 'field_type_map_point.php', 'date' => 'field_type_date.php', 'domain' => 'field_type_domain.php', 'datetime' => 'field_type_datetime.php', 'image' => 'field_type_image.php', 'file' => 'field_type_file.php', 'menu' => 'field_type_menu.php', 'menu_m' => 'field_type_menum.php', 'menu_open' => 'field_type_menuopen.php', 'check' => 'field_type_check.php');
	
	public function __construct($db, $table_name, $structure, $core_path)
	{
		$this->db              = $db;
		$this->table_name      = $table_name;
		$this->structure       = $structure;
		$this->classes_path    = $core_path . '/classes/types';
		$this->supported_types = include($this->classes_path . '/load.php');
		
		require_once($this->classes_path . '/' . 'field_type_default.php');
		
		foreach ($this->supported_types as $sid => $path) {
			if (file_exists($this->classes_path . '/' . $path)) {
				require_once($this->classes_path . '/' . $path);
				$n                           = 'field_type_' . $sid;
				$this->supported_types[$sid] = new $n(array());
			} else {
				$this->supported_types[$sid] = new field_type_default(array());
			}
		}
		
	}
	
	public function delete()
	{
		$this->db->Execute('drop table `' . $this->table_name . '`');
	}
	
	public function create()
	{
		if ($this->structure['type'] == 'simple')
			$this->create_simple();
		elseif ($this->structure['type'] == 'tree')
			$this->create_tree();
	}
	
	private function create_simple()
	{
		$sql = 'create table `' . $this->table_name . '` (' . "\n\r";
		//		foreach($this->structure['dep_param'] as $name=>$field)$sql.=$this->supported_types[$field['type']]->creatingString($name).', '."\n\r";
		
		//Основные поля
		foreach ($this->structure['fields'] as $name => $field)
			$sql .= $this->supported_types[$field['type']]->creatingString($name) . ', ' . "\n\r";
		
		//Поля связок
		if ($this->structure['type'] == 'tree') {
			$name      = 'dep_path_parent';
			$link_type = 'menu_open';
			$sql .= $this->supported_types[$link_type]->creatingString($name) . ', ' . "\n\r";
		} elseif ($this->structure['dep_path']) {
			$name      = 'dep_path_' . $this->structure['dep_path']['structure'];
			$link_type = $this->structure['dep_path']['link_type'];
			$sql .= $this->supported_types[$link_type]->creatingString($name) . ', ' . "\n\r";
		}
		
		//		$sql.='`pos` INT NOT NULL DEFAULT 0, '."\n\r";
		$sql .= '`url` VARCHAR(255) NOT NULL, ' . "\n\r";
		$sql .= 'UNIQUE KEY `id` (`id`)' . "\n\r";
		$sql .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;' . "\n\r";
		//pr($sql);
		$this->db->Execute($sql);
	}
	private function create_tree()
	{
		$sql = 'create table `' . $this->table_name . '` (' . "\n\r";
		//		foreach($this->structure['dep_param'] as $name=>$field)$sql.=$this->supported_types[$field['type']]->creatingString($name).', '."\n\r";
		
		//Основные поля
		foreach ($this->structure['fields'] as $name => $field) {
			$sql .= $this->supported_types[$field['type']]->creatingString($name) . ', ' . "\n\r";
		}
		
		//Системные поля для деревьев
		$sql .= '`is_link_to_module` VARCHAR(255) NOT NULL, ' . "\n\r";
		$sql .= '`url` VARCHAR(255) NOT NULL, ' . "\n\r";
		
		//Поля связок
		if ($this->structure['type'] == 'tree') {
			$name      = 'dep_path_parent';
			$link_type = 'menu';
			$sql .= $this->supported_types[$link_type]->creatingString($name) . ', ' . "\n\r";
		} elseif ($this->structure['dep_path']) {
			$name      = 'dep_path_' . $this->structure['dep_path']['structure'];
			$link_type = $this->structure['dep_path']['link_type'];
			$sql .= $this->supported_types[$link_type]->creatingString($name) . ', ' . "\n\r";
		}
		
		//Структура DB-TREE
		$sql .= '`left_key` INT NOT NULL DEFAULT 0, ' . "\n\r";
		$sql .= '`right_key` INT NOT NULL DEFAULT 0, ' . "\n\r";
		$sql .= '`tree_level` INT NOT NULL DEFAULT 0, ' . "\n\r";
		
		$sql .= 'UNIQUE KEY `id` (`id`)' . "\n\r";
		$sql .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;' . "\n\r";
		//pr($sql);
		$this->db->Execute($sql);
	}
	
}

?>