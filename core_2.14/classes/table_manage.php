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
	public static $db;
	
	//Поддерживаемые типы полей данных
	private $supported_types = array('id' => 'field_type_id.php', 'sid' => 'field_type_sid.php', 'pos' => 'field_type_pos.php', 'ln' => 'field_type_ln.php', 'int' => 'field_type_int.php', 'float' => 'field_type_float.php', 'label' => 'field_type_label.php', 'text' => 'field_type_text.php', 'textarea' => 'field_type_textarea.php', 'text_editor' => 'field_type_texteditor.php', 'password' => 'field_type_password.php', 'object' => 'field_type_object.php', 'tags' => 'field_type_tags.php', 'map_point' => 'field_type_map_point.php', 'date' => 'field_type_date.php', 'domain' => 'field_type_domain.php', 'datetime' => 'field_type_datetime.php', 'image' => 'field_type_image.php', 'file' => 'field_type_file.php', 'menu' => 'field_type_menu.php', 'menu_m' => 'field_type_menum.php', 'menu_open' => 'field_type_menuopen.php', 'check' => 'field_type_check.php');
	
	public function __construct($db, $table_name, $structure, $core_path)
	{
		self::$db              = $db;
		$this->table_name      = $table_name;
		$this->structure       = $structure;
	}
	
	public function delete()
	{
		self::$db->Execute('drop table `' . $this->table_name . '`');
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
			if( IsSet(model::$types[ $field['type'] ]) )
				$sql .= model::$types[ $field['type'] ]->creatingString($name) . ', ' . "\n\r";
		
		$sql .= 'UNIQUE KEY `id` (`id`)' . "\n\r";
		$sql .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;' . "\n\r";
		//pr($sql);
		model::execSql($sql, 'insert');
	}
	private function create_tree()
	{
		$sql = 'create table `' . $this->table_name . '` (' . "\n\r";
		//		foreach($this->structure['dep_param'] as $name=>$field)$sql.=$this->supported_types[$field['type']]->creatingString($name).', '."\n\r";
		
		//Основные поля
		foreach ($this->structure['fields'] as $name => $field) {
			$sql .= model::$types[ $field['type'] ]->creatingString($name) . ', ' . "\n\r";
		}
		
		//Структура DB-TREE
		$sql .= '`left_key` INT NOT NULL DEFAULT 0, ' . "\n\r";
		$sql .= '`right_key` INT NOT NULL DEFAULT 0, ' . "\n\r";
		$sql .= '`tree_level` INT NOT NULL DEFAULT 0, ' . "\n\r";
		
		$sql .= 'UNIQUE KEY `id` (`id`)' . "\n\r";
		$sql .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;' . "\n\r";
		
		model::execSql($sql, 'insert');
		
		$sql = 'insert into `' . $this->table_name . '` set `sid`="'.$this->info['sid'].'", `title`="Главная страница модуля", `shw`=1, `left_key`=1, `right_key`=2, `tree_level`=1, `url_alias`="/'.$this->info['sid'].'"';
		model::execSql($sql, 'insert');
		
	}
	
}

?>