<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Расширение работы с несколькими доменами			*/
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

require_once 'default.php';

class extention_domains extends extention_default
{
	var $title = 'Домен';
	var $sid = 'domains';
	var $table_name = 'domains';
	
	//Инициализация расширения
	public function __construct($model)
	{
		$this->model = $model;

		//Определяем текущий домен
		$this->defineCurrentDomain();
	}
	
	//Инициализация расширения
	public function execute()
	{
		//Вставим дополнительные поля в модули
		$this->insertFields();
		
		//Исправление путей в шаблонизаторе
		$this->correctTemplater();
	}
	
	
	//Вставим дополнительные поля в модули
	private function insertFields()
	{
//		if( model::$config['settings']['domain_switch'])
		if(model::$modules)
		foreach (model::$modules as $module_sid => $module)
			if ($module->structure) {
				foreach ($module->structure as $structure_sid => $structure) {
					model::$modules[$module_sid]->structure[$structure_sid]['fields']['domain'] = array(
						'sid' => 'domain',
						'group' => 'system',
						'type' => 'domain',
						'title' => 'Домен'
					);
				}
			}
	}
	
	//Определяем текущий домен
	private function defineCurrentDomain()
	{
/*
		if( !model::$config['settings']['domain_switch']){
			$this->domain = array(
				'title' => model::$settings['domain_title'],
				'host' => $_SERVER['HTTP_HOST'],
			);
		}
*/		
		//Хост
		$host = $_SERVER['HTTP_HOST'];
		$host = str_replace('www.', '', $host);
		
		//Смотрим текущий хост среди заведённых в системе
		$this->domain = $this->model->execSql('select * from `domains` where (`host`="' . mysql_real_escape_string($host) . '" or `host2`="' . mysql_real_escape_string($host) . '" or `host3`="' . mysql_real_escape_string($host) . '" or `host4`="' . mysql_real_escape_string($host) . '") and `active`=1', 'getrow');
		
		//Если домен не найден - должен быть домен по умолчанию "_default_"
		if (!$this->domain) {
			$this->domain = $this->model->makeSql(array(
				'tables' => array(
					$this->table_name
				),
				'where' => array(
					'and' => array(
						'`host`="_default_"',
						'access' => '1'
					)
				)
			), 'getrow');
		}
		
		//Домен не опознан - выходим.
		if (!$this->domain) {
			header('Content-Type: text/html; charset=utf-8');
			header("HTTP/1.0 404 Not Found");
			print('Домен не опознан.');
			exit();
		}
		
		//Домен опознан, но отключен - выходим.
		if (!$this->domain['active']) {
			header('Content-Type: text/html; charset=utf-8');
			header("HTTP/1.0 404 Not Found");
			print('Домен отключен.');
			exit();
		}

		//Проверка на основной домен
		if($this->domain['host'] == $host)
			$this->domain['main']=true;
		else
			$this->domain['main']=false;
		
		//Дата
		$this->domain['date_public'] = model::$types['datetime']->getValueExplode($this->domain['date_start']);
		
		//Текущий хост
		$this->domain['current_host'] = $host;
	}
	
	//Исправление путей в шаблонизаторе
	private function correctTemplater()
	{
		model::$config['path']['templates'] .= '/' . $this->domain['templates'];
	}
	
	
	//Перед выполнением запроса
	public function onSql($fields, $tables, $where = false, $group = false, $order = false, $limit = false, $query_type = 'getall')
	{
//		if( model::$config['settings']['domain_switch']){
			if ($query_type == 'insert') {
				if (!$fields)
					$fields = array();
				if (!IsSet($fields['domain']))
					$fields['domain'] = '`domain`="|' . $this->domain['id'] . '|"';
			} else {
				if (!$where)
					$where = array();
				if ($structure_sid != 'domain')
					if (!IsSet($where['and']['domain']))
						$where['and']['domain'] = $this->getWhere();
			}
//		}
		return array(
			$fields,
			$tables,
			$where,
			$group,
			$order,
			$limit
		);
	}
	
	//Составить подстроку для запроса
	public function getWhere()
	{
//		if( model::$config['settings']['domain_switch'])
			return '( (`domain`="all") || (`domain` LIKE "%|' . $this->domain['id'] . '|%") )';
/*
		else
			return '1';
*/
	}
	
	
	//Добавляем системное поле в модуль
	public function addFields()
	{
		return array(
			'domain' => array(
				'sid' => 'domain',
				'group' => 'system',
				'type' => 'domain',
				'title' => 'Домен',
				'value' => 'all'
			)
		);
	}
}

?>