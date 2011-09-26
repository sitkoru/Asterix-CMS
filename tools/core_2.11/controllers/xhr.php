<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Контроллер XHR-системы								*/
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

require('default_controller.php');

class controller_xhr extends default_controller
{
	public function start()
	{
		/*
		//Формат вывода данных
		$output=$this->model->ask->output;
		
		//json
		if($output=='json'){
		
		//Текущий пользователь
		if($this->model->ask->full_url[0]=='user'){
		//Запись
		$record=$this->model->user;
		
		//Что-то ещё
		}else{
		//Запись
		$record=$this->model->prepareMainRecord();
		}
		
		//JSON
		$result=json_encode($record);
		
		//Готово
		header('Content-Type: text/html; charset=utf-8');
		header("HTTP/1.0 200 Ok");
		print($result);
		exit();
		
		//html
		}elseif($output=='html'){
		
		//Подключаем шаблонизатор
		require($this->model->config['path']['core'].'/classes/templates.php');
		$tmpl=new templater($this->model);
		
		//Текущий пользователь
		if($this->model->ask->full_url[0]=='user'){
		
		//Данные
		$tmpl->assign('user',$this->model->user->info);
		
		//Текущий шаблон
		$current_template_file='xhr/'.$this->model->ask->full_url[0].'.tpl';
		
		//Что-то ещё
		}else{
		}
		
		//Готовим вывод
		$ready_html=$tmpl->fetch($current_template_file);
		
		header('Content-Type: text/html; charset=utf-8');
		header("HTTP/1.0 200 Ok");
		
		print($ready_html);
		exit();
		
		}
		*/
	}
}
?>