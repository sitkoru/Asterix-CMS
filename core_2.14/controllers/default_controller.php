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
	public static $add;

	//Библиотеки, которые можно подключить по псевдонимам
	public static $known_js = array(
		'lightbox' => 'http://src.opendev.ru/3.0/j/lightbox.js',
		'carousel' => 'http://src.opendev.ru/3.0/bootstrap/bootstrap-carousel.js',
	);
	public static $known_css = array(
		'lightbox'  => 'http://src.opendev.ru/a/c/lightbox.css',
		'bootstrap' => 'http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css',
	);

	function __construct( $model, $vars, $cache )
	{
		$this->model = $model;
		$this->vars  = $vars;
		$this->cache = $cache;
	}

	//Добавить необходимую JS-библиотеку
	public function addJS( $path, $params = false )
	{
		self::$add['js_core'][] = array(
			'path'   => $path,
			'params' => $params,
		);

		return self::$add;
	}

	//Добавить необходимую JS-библиотеку
	public function addCSS( $path, $params = false )
	{
		self::$add['css_core'][] = array(
			'path'   => $path,
			'params' => $params,
		);

		return self::$add;
	}

	//Добавить необходимую JS-библиотеку
	public function addUserJS( $vals, $params = false )
	{
		if( substr_count( $vals, ',' ) ) {
			$vals = explode( ',', $vals );
			foreach( $vals as $i => $val )
				$vals[$i] = trim( $val );
		} else
			$vals = array( $vals );

		foreach( $vals as $i => $val ) {
			if( IsSet(self::$known_js[$val]) )
				$vals[$i] = self::$known_js[$val];
		}

		return $vals;
	}

	//Добавить необходимую JS-библиотеку
	public function addUserCSS( $vals, $params = false )
	{
		if( substr_count( $vals, ',' ) ) {
			$vals = explode( ',', $vals );
			foreach( $vals as $i => $val )
				$vals[$i] = trim( $val );
		} else
			$vals = array( $vals );

		foreach( $vals as $i => $val ) {
			if( IsSet(self::$known_css[$val]) )
				$vals[$i] = self::$known_css[$val];
		}

		return $vals;
	}


}

?>