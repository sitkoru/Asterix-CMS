<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс тестирования ошибок в работе сайта			*/
/*															*/
/*	Версия ядра 2.13										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2011  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 5 сентября 2011	года							*/
/*	Модифицирован: 5 сентября 2011 года						*/
/*															*/
/************************************************************/

class acms_selftest{

	var $acms_path_server = 'http://src.opendev.ru';
	var $acms_path_last = 'http://src.opendev.ru/ver/last.txt';

/*
	Тесты бывают трёх видов:
		1. Стандартные, зашитые в системе.
		2. Дополнительные, находящиеся на сервере OpenDev.ru
		3. Тесты на совместимость ядер
*/
	
	//Зашитые в системе тесты
	var $tests = array(

		//Переход 2.12 -> 2.13
		'folder_tmp' => array(
			'title' => 'Тест на наличие прав записи в папку для временных файлов',
			'autofix' => true,
		),
		'tmpl_smarty3' => array(
			'title' => 'Тест для шаблонов на совместимость с шаблонизатором Smarty 3',
			'autofix' => true,
		),
		
	);
	
	public function __construct( $model ){
		$this->model = $model;
	}
	
//////////////////////////// TESTS ///////////////////////////////
	
	private function check_folder_tmp(){return is_writable( $this->model->config['path']['tmp'] );}
	private function check_folder_tmp_fix(){
		if ( !mkdir( $this->model->config['path']['tmp'] )) 
			return 'Не могу создать директорию '.$this->model->config['path']['tmp'];
		if ( !chmod( $this->model->config['path']['tmp'], 0775 ) )
			return 'Не могу выставить права на запись для директории '.$this->model->config['path']['tmp'];
		return 'ok';
	}

	private function check_tmpl_smarty3(){
		$files = $this->get_files( $this->model->config['path']['templates'], false, 0, 10000, true, 'tpl');
		foreach($files as $file){
			$path = $file['path'].'/'.$file['file'];
			$f = file_get_contents( $path );
			
			if( substr_count($f, '`') )
				print('fuck =) ');
		
		}
	}
	private function check_tmpl_smarty3_fix(){
		return 'ok';
	}
	

?>