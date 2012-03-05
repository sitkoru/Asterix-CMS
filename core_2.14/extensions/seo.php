<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Расширение для SEO-оптимизации						*/
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

class extention_seo extends extention_default
{
	var $title = 'Оптимизация сайта';
	var $sid = 'seo';
	var $table_name = 'seo';
	
	private $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
	private $priority = array('0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1.0');
	
	//Инициализация расширения
	public function __construct($model)
	{
		$this->model = $model;
		/*
		//Вставим дополнительные поля в модули
		$this->insertFields();
		*/
	}
	
	//Инициализация расширения
	public function execute()
	{
		//Вставим дополнительные поля в модули
		$this->insertFields();
	}
	
	//Вставим дополнительные поля в модули
	private function insertFields()
	{
		$fields = array(
			'seo_title' => array(
				'sid' => 'seo_title',
				'type' => 'text',
				'group' => 'seo',
				'title' => 'Заголовок страницы (title)'
			),
			'seo_keywords' => array(
				'sid' => 'seo_keywords',
				'type' => 'textarea',
				'group' => 'seo',
				'title' => 'Ключевые слова (meta keywords)'
			),
			'seo_description' => array(
				'sid' => 'seo_description',
				'type' => 'textarea',
				'group' => 'seo',
				'title' => 'Описание (meta description)'
			),
			'seo_changefreq' => array(
				'sid' => 'seo_changefreq',
				'type' => 'menu',
				'group' => 'seo',
				'title' => 'Частота изменения содержания (sitemap changefreq)',
				'variants' => $this->changefreq,
				'default' => 'weekly'
			),
			'seo_priority' => array(
				'sid' => 'seo_priority',
				'type' => 'menu',
				'group' => 'seo',
				'title' => 'Важность страницы с точки зрения бизнеса (sitemap priority)',
				'variants' => $this->priority,
				'default' => '0.7'
			),
/*
			'seo_reindex_yandex' => array(
				'sid' => 'seo_reindex_yandex',
				'type' => 'label',
				'group' => 'seo',
				'title' => 'Дата индексации Яндексом'
			),
			'seo_reindex_google' => array(
				'sid' => 'seo_reindex_google',
				'type' => 'label',
				'group' => 'seo',
				'title' => 'Дата индексации Google`ом'
			),
			'seo_reindex_rambler' => array(
				'sid' => 'seo_reindex_rambler',
				'type' => 'label',
				'group' => 'seo',
				'title' => 'Дата индексации Рамблером'
			)
*/
		);
		
		foreach (model::$modules as $module_sid => $module)
			if ($module->structure) {
				foreach ($module->structure as $structure_sid => $structure) {
					foreach ($fields as $field)
						model::$modules[$module_sid]->structure[$structure_sid]['fields'][$field['sid']] = $field;
				}
			}
	}
	
	
	//Учёт приходов поисковиков
	public function crawlerCount()
	{
		//Учёт поисковиков отключен
		if (!model::$config['settings']['count_crawlers'])
			return false;
		
		$browser = @get_browser();
		if ($browser->crawler) {
			$field = false;
			$sql   = false;
			
			//Опознаём поисковик
			if ($browser->crawler) {
				if (substr_count($browser->browser, 'Yandex')) {
					$field = 'yandex';
				} elseif (substr_count($browser->browser, 'Google')) {
					$field = 'google';
				} elseif (substr_count($browser->browser, 'Rambler')) {
					$field = 'rambler';
				}
				
				//Если поисковик опознан - обновляем страничку
				if ($field) {
					$this->model->makeSql(array(
						'tables' => array(
							model::$modules[model::$ask->module]->getCurrentTable(model::$ask->structure_sid)
						),
						'fields' => array(
							'`seo_reindex_' . $field . '`=NOW()'
						),
						'where' => array(
							'and' => array(
								'`id`="' . model::$ask->rec['id'] . '"'
							)
						)
					), 'update');
					$sql = $this->model->last_sql;
				}
				
				//Пишем лог для выявления ошибок
				$f = fopen(model::$config['path']['www'] . '/crawlers.log', 'a+');
				fwrite($f, date("Y-m-d H:i:s") . '|' . $this->model->extensions['domains']->domain['host'] . '|' . $browser->browser . '|' . model::$ask->original_url . '|' . @$sql . "\r\n");
				fclose($f);
				
				chmod(model::$config['path']['www'] . '/crawlers.log', 0775);
			}
		}
	}
	
	//Учёт приходов поисковиков
	public function siteMap()
	{
		$tree = $this->model->prepareShirtTree('start', 'rec', false, 10, $conditions = array(
			'and' => array(
				'`shw`=1'
			)
		));
		
		function toLine($tree)
		{
			$recs = array();
			$i    = 0;
			
			//Перебираем дерево
			foreach ($tree as $i => $t) {
				$i = count($recs) + 1;
				
				//Сама запись
				$recs[$i] = $t;
				
				//Если есть подразделы
				if (@$recs[$i]['sub'])
					if (count($recs[$i]['sub'])) {
						//Делаем из них список
						$subs = toLine($recs[$i]['sub']);
						//Убираем список подразделов у текущей записи
						UnSet($recs[$i]['sub']);
						//Вставляем список подразделов следом
						$recs = array_merge($recs, $subs);
					}
			}
			
			return $recs;
		}
		
		//Получаем данные
		$recs = toLine($tree);
		
		//Форматируем данные
		foreach ($recs as $i => $rec) {
			//Дописываем Url
			$recs[$i]['url']         = 'http://' . $_SERVER['HTTP_HOST'] . $recs[$i]['url'];
			//Форматируем дату
			$recs[$i]['date_public'] = @date("c", strtotime($recs[$i]['date_public']));
		}
		
		return $recs;
	}
	
}

?>