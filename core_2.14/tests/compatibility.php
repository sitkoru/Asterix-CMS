<?php

class compatibility{

	static $non_static = false;

	// Включаем различные режимы совместимости
	public static function on( $value, $reason ){
		
		if( $value == 'non_static' )
			self::$non_static = $reason;
	
	}
	
	// Инициализируем режимы совместимости
	public static function init(){
	
		//Псевдо-модель, для инициализации молулей в режиме совместимости с 2.13
		if( self::$non_static ){
			$fake_model = new fake_model();
			foreach( model::$modules as $i=>$module )
				model::$modules[ $i ]->model = $fake_model;
		}
		
	}
	
	public function display(){
		
	}


}


/*
	Класс, реулизующий обратную совместимость для модулей, созданных для нестатичного окружения ядра
*/
class fake_model {

	public function __construct(){
		$this->config = 		model::$config;
		$this->db = 			model::$db;
		$this->types = 			model::$types;
		$this->modules =     	model::$modules;
		$this->extensions = 	model::$extensions;
		$this->settings = 		model::$settings;
		$this->ask = 			model::$ask;

		$user = new user();
		$user->info = user::$info;
		$this->user = $user;
	}

	public function execSql($sql, $query_type = 'getall', $database = 'system', $no_cache = false){
		return model::execSql($sql, $query_type, $database, $no_cache);
	}
	
	public function makeSql($sql_conditions, $query_type = 'getall', $database = 'system', $no_cache = false){
		return model::makeSql($sql_conditions, $query_type, $database, $no_cache);
	}
	
	public function getModuleSidByPrototype($prototype){
		return model::getModuleSidByPrototype($prototype);	
	}

}


?>