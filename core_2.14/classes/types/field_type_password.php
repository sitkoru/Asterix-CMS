<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Пароль									*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 10 июля 2012 года						*/
/*															*/
/************************************************************/

class field_type_password extends field_type_default
{

	private $algorithm = 'whirlpool';
	private $users_table = 'users';
	
	public $default_settings = array('sid' => false, 'title' => 'Пароль', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/password.tpl';
	
	public function creatingString($name){
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	// Свернуть значение до хранимого вида
	public function implodeValue($field_sid, $record, $old_record = array(), $settings = false, $module_sid = 'users', $structure_sid = false){

		// Коррекция типа данных
		$this->correctFieldType($module_sid, $structure_sid, $field_sid);
		
		// Если значение найдено
		if (strlen(@$record[ $field_sid ]) > 0) {
			
			// Соль не задана
			if( !strlen( $record['salt'] ) ){
				$record['salt'] = $this->salt();
				
				// Указан ID пользователя - сразу устанавливаем ему Соль в таблице `users`
				if( IsSet( $record['id'] ) )
					model::execSql('update `'.$this->users_table.'` set `salt`="'.mysql_real_escape_string( $record['salt'] ).'" where `id`='.intval( $record['id'] ).' limit 1','update');
				
				// Пользователь пока не существует - 
				else{
					$record['id'] = model::$modules[ $module_sid ]->getNextId();
					model::execSql('replace into `'.$this->users_table.'` set `id`='.$record['id'].', `salt`="'.mysql_real_escape_string( $record['salt'] ).'"', 'insert');
				}
			}
			
			// Готово
			$record[ $field_sid ] = $this->encrypt( $record[ $field_sid ], $record['salt'] );
			return $record;
		}
		
		$record[ $field_sid ] = false;	
		return $record;
	}
	

	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		return '';
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return '';
	}
	
	//Шифруем пароль
	public function encrypt($password, $salt = false){
		if( $salt )
			return hash( $this->algorithm, $password.$salt, false );
		else
			return md5( $password );
	}

	//Проверяем, что поле имеет тим TEXT
	private function correctFieldType($module_sid, $structure_sid, $field_sid){
		if( $module_sid ){
			$sql = 'select DATA_TYPE from information_schema.COLUMNS where TABLE_SCHEMA="'.model::$config['db']['system']['name'].'" and TABLE_NAME="'.$this->users_table.'" and COLUMN_NAME="'.$field_sid.'"';
			$res = model::execSql($sql, 'getrow');
			if( $res['DATA_TYPE'] != 'text' ){
				
				// Поле для пароля теперь должно быть типа TEXT
				$sql = 'alter table `'.$this->users_table.'` modify '.$this->creatingString( $field_sid );
				model::execSql($sql, 'update');
				
				// Соль для паролей
				$sql = 'alter table `'.$this->users_table.'` add `salt` VARCHAR( 255 ) NOT NULL AFTER  `'.$field_sid.'`';
				model::execSql($sql, 'update');
				
				// Таблица хешей
				model::execSql('CREATE TABLE IF NOT EXISTS `pass` (`hash` text NOT NULL)', 'update');
			}
			
			$sql = 'select * from information_schema.COLUMNS where TABLE_SCHEMA="'.model::$config['db']['system']['name'].'" and TABLE_NAME="'.$this->users_table.'" and COLUMN_NAME="salt"';
			$res = model::execSql($sql, 'getrow');
			if( $res['CHARACTER_MAXIMUM_LENGTH'] != 255 ){
			
				// Поле для пароля теперь должно быть типа VARCHAR(255)
				$sql = 'alter table `'.$this->users_table.'` modify `salt` VARCHAR(255) NOT NULL';
				model::execSql($sql, 'update');
			
			/*
				Если мы делаем такое преобразование, то значит находимся на старом сайте
				Тогда за одно проверим существование SID и URL у пользователей
			*/
				$recs = model::execSql('select * from `users` where `sid`="" or `url`=""', 'getall');
				foreach( $recs as $rec ){
					if( strlen( $rec['sid'] ) )
						$sid = $rec['sid'];
					else
						$sid = $rec['login'];
					$sid = model::$types['sid']->correctValue( $sid );
					$url = '/users/'.$sid;
					model::execSql('update `users` set `sid`="'.mysql_real_escape_string( $sid ).'", `url`="'.mysql_real_escape_string( $url ).'" where `id`='.intval($rec['id']).' limit 1', 'update');
				}
					
			}
/*			
			$sql = 'select DATA_TYPE, CHARACTER_MAXIMUM_LENGTH from information_schema.COLUMNS where TABLE_SCHEMA="'.model::$config['db']['system']['name'].'" and TABLE_NAME="'.model::$modules[ $module_sid ]->getCurrentTable($structure_sid).'" and COLUMN_NAME="salt"';
			$res = model::execSql($sql, 'getrow');
			if( ($res['DATA_TYPE'] != 'varchar') || ($res['CHARACTER_MAXIMUM_LENGTH'] != 255) ){
				
				// Поле для пароля теперь должно быть типа TEXT
				$sql = 'alter table `'.model::$modules[ $module_sid ]->getCurrentTable($structure_sid).'` modify `salt` VARCHAR(255) NOT NULL';
				model::execSql($sql, 'update');
				
				// Таблица хешей
				model::execSql('CREATE TABLE IF NOT EXISTS `pass` (`hash` text NOT NULL)', 'update');
			}
*/
		}
	}
	
	private function salt(){
		
		// Используемые 
		$chars = array(
			array("0","1","2","3","4","5","6","7","8","9"),
			array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"),
			array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
			array('!','@','#','$','%','^','&','*','(',')','-','~','+','=','|','/','{','}',':',';',',','.','?','<','>','['),
		);
		
		// Количество кругов обхода массива
		$rounds = 2;
		
		// Собираем символы
		$fake_salt = '';
		foreach( $chars as $arr )
			for( $i = 0; $i < $rounds; $i++){
				$k = rand(5, min(15, count($arr) ) );
				$keys = array_rand( $arr, $k );
				if( is_array( $keys ) )
					foreach( $keys as $key )
						$fake_salt .= $arr[ $key ];
			}
		
		// Перемешиваем
		$salt = str_shuffle($fake_salt);
		
		// Готово
		return $salt;
	}

	// Найти пользователя по авторизационным данным
	public function tryAuth( $type, $value ){
		$user = false;

		// Новая авторизация на сайте
		if( $type == 'login' ){

			$this->correctFieldType('users', 'rec', 'password');

			$recs = model::execSql('select * from `'.$this->users_table.'` where `login`="'.mysql_real_escape_string( $value['login'] ).'" and `active`=1', 'getall');
			
			foreach( $recs as $rec ){
				$hash = $this->encrypt( $value['password'], $rec['salt'] );
				
				if( $rec['password'] == $hash ){
					$user = $rec;

					// Если авторизация прошла по старой системе без соли - генерируем соль и обновляем пароль
					if( $user && !$rec['salt']  )
						$this->updateAccountWithSalt($user, $value);				

				}
			}
		
		// Авторизация для приложений
		}elseif( $type == 'auth' ){

		// Авторизация по уже начатой сессии
		}elseif( $type == 'session' ){
			$user = model::execSql('select * from `'.$this->users_table.'` where `session_id`="'.mysql_real_escape_string( $value ).'" and `active`=1 limit 1', 'getrow');
		}
		
		return $user;
	}
	
	// Если авторизация прошла по старой системе без соли - генерируем соль и обновляем пароль
	private function updateAccountWithSalt( $record, $value ){
	
		$salt = $this->salt();
		$password = $value['password'];
		$hash = $this->encrypt( $password, $salt );
		
		model::execSql('update `'.$this->users_table.'` set `password`="'.mysql_real_escape_string( $hash ).'", `salt`="'.mysql_real_escape_string( $salt ).'" where `id`='.intval( $record['id'] ).' limit 1','update');
	
	}
	
}

/*

11 июля 2012, core 2.14, dekmabot
- добавлен генератор соли
- добавлено шифрование с солью
- тип поля password изменён на TEXT
- алгоритм шифрования изменён на whirlpool


*/

?>