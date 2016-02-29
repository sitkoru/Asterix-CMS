<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс обновления ядра								*/
/*															*/
/*	Версия ядра 2.12										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2011  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 16 августа 2011	года							*/
/*	Модифицирован: 16 августа 2011 года						*/
/*															*/
/************************************************************/

class acms_core_update{

	var $acms_path_server = 'http://src.sitko.ru';
	var $acms_path_last = 'http://src.sitko.ru/ver/last.txt';

	var $errors = array(
		'acmsServer' => 'Не доступен сервер обновлений',
		'acmsCoreVersion' => 'Запрашиваемая версия ядра не обнаружена на сервере',
		'acmsSqlFile' => 'Отсутствует файл сверки таблиц баз данных',

		'fileWriteWww' => 'Отсутствует доступ на запись в публичную папку вебсервера',
		'fileWriteRoot' => 'Отсутствует доступ на запись в родительскую папку публичной папки вебсервера',
		'fileWriteTools' => 'Отсутствует доступ на запись в папку, в которой лежит ядро (обычно tools)',

		'dbCreateTable' => 'Отсутствует доступ на создание таблиц в базе данных',
		'dbModifyTable' => 'Отсутствует доступ на модификацию структуры таблиц в базе данных',
		'dbLockTable' => 'Отсутствует доступ на блокирование таблиц в базе данных',

		'libZip' => 'Отсутствует необходимая библиотека для работы с архивами ZIP',
		'configWrite' => 'Отсутствует доступ на изменение файла конфигурации (обычно config.php)',
	);
	
	public function __construct( $model ){
		$this->model = $model;
	}
	
	//Проверяем, нужно ли обновление текущему ядру
	public function checkUpdate(){
		
		//Режимы обновления ядра
		$modes = array(
			'auto' => 'в автоматическом режиме',
			'manual' => 'в ручном режиме',
			'none' => ', обновление не требуется',
		);

		//Значения по умолчанию
		$next = false;
		$mode = false;
		$comment = 'Обновление не поддерживается вашей версией Asterix CMS';

		//Проверяем возможные обновления для версии
		$vers = file( $this->acms_path_last );
		
		//Проверяем все варианты обновления
		foreach ( $vers as $i => $ver )
			if( $i ){
				
				list($c_version, $c_next, $c_mode, $c_comment) = explode(':', $ver);
				
				if( $this->model->config['settings']['version'] == $c_version ){
					$next = $c_next;
					$mode = $c_mode;
					$comment = $c_comment;
				}
			}
			
		//Если доступно обновление		
		if( $next && $next!='false' ){
			//Подробная проверка всех доступов для обновления
			$errors = $this->checkAllBeforeUpdate( $next );
		}
		
		//Готово
		$result = array(
			'mode' => $mode,
			'current' => $this->model->config['settings']['version'],
			'next' => @$next,
			'text' => $comment,
			'errors' => $errors,
		);		
		
		return $result;

	}

	//Запустить обновление
	public function doUpdate($vars){
		print_r($vars);
	}
	
	//Проверки перед автоматическим обновлением
	private function checkAllBeforeUpdate($next){
		
		$errors = false;
		foreach ($this->errors as $name => $error){
			$f_name = 'check_'.$name;
			if( method_exists($this, $f_name) ){
				if( !$this->$f_name($next) )
					$errors[ $name ] = $error;
			}else{
				print('Отсутствует провека: '.$f_name.'<br />');
			}
		}
		return $errors;

	}
	

	//Далее идут все проверки

	private function check_acmsServer(){return file_get_contents( $this->acms_path_last , NULL, NULL, 0, 100 );}
	private function check_acmsCoreVersion($next){return file_get_contents( $this->acms_path_server.'/distr/core_'.$next.'.zip' , NULL, NULL, 0, 100 );}
	private function check_acmsSqlFile($next){return file_get_contents( $this->acms_path_server.'/distr/core_'.$next.'.sql' , NULL, NULL, 0, 100 );}
	
	private function check_fileWriteWww(){return is_writable( $this->model->config['path']['www'] );}
	private function check_fileWriteRoot(){return is_writable( $this->model->config['path']['www'].'/..' );}
	private function check_fileWriteTools(){return is_writable( $this->model->config['path']['core'].'/..' );}
	
	private function check_dbCreateTable(){return true;}
	private function check_dbModifyTable(){return true;}
	private function check_dbLockTable(){return true;}
	
	private function check_libZip(){return true;}
	private function check_configWrite(){return is_writable( $this->model->config['path']['www'].'/../config.php' );}

}

?>