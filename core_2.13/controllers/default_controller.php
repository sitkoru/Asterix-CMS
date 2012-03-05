<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Прототип контроллера								*/
/*															*/
/*	Версия ядра 2.0.b5										*/
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

class default_controller
{
	public $vars = array();
	
	function __construct($model, $vars, $cache)
	{
		$this->model = $model;
		$this->vars  = $vars;
		$this->cache = $cache;
	}
	
	//Добавить необходимую JS-библиотеку
	public function addJS($path, $params = false){
		$this->add['js'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}

	//Добавить необходимую JS-библиотеку
	public function addJSLib($path, $params = false){
		$this->add['js_lib'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}

	//Добавить необходимую JS-библиотеку
	public function addCSS($path, $params = false){
		$this->add['css'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}
	//Добавить необходимую JS-библиотеку
	public function addCSSLib($path, $params = false){
		$this->add['css_lib'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}

	
}

?>