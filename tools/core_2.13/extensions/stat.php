<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс учёта статистики показов и кликов				*/
/*															*/
/*	Версия ядра 2.06										*/
/*	Версия скрипта 0.1										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 12 декабря 2010	года							*/
/*	Модифицирован: 12 декабря 2010 года						*/
/*															*/
/************************************************************/

require_once 'default.php';

class extention_stat extends extention_default
{

	public function __construct($model)
	{
		$this->model = $model;
		
		//Регистрируем функцию подсчёта просмотров
		$this->model->tmpl->register_modifier('count_view', array(
			$this,
			'countView'
		));
		
		//Регистрируем функцию подсчёта переходов по ссылкам
		$this->model->tmpl->register_modifier('count_click', array(
			$this,
			'countClick'
		));
		

	}
	
	//Регистрируем функцию подсчёта просмотров
	//Отобразим JavaScript, показывающий картинку в один пиксель, которая будет запрашиваться со скрипта /stat.php
	public function countView($value, $top, $anons = false, $label = false){//Передаем любой элемент
		//Готовими код, который будет приклеен к подсчитываемому элементу
		$plus_code = '<img src="/stat.php?a=v&i='.$top.'|'.intval($anons).'&l='.$label.'" alt="stat" style="position:absolute; z-index:-1000; width:1px; height:1px;" />';
		//Приклеиваем код
		$value .= $plus_code;
		//Готово
		return $value;		
	}
	
	//Регистрируем функцию подсчёта переходов по ссылкам
	//Переделаем ссылку с записи, которая будет вести на /stat.php и передавать параметры что посчитать и куда направить после.
	public function countClick($value, $top, $label = false){//Передаем ссылку
		//Готовим сслыку, которая будет вести статистику
		$value = '/stat.php?a=c&i='.$top.'&l='.$label.'&h='.urlencode($value);
		//Готово
		return $value;		
	}

}

?>