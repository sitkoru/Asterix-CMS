<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс обработки запроса								*/
/*															*/
/*	Версия ядра 2.0											*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 3 ноября 2009 года						*/
/*															*/
/************************************************************/

class ask
{
	public $rec;
	
	var $output_formats = array('html', 'xml', 'json');
	
	public function __construct($model)
	{
		//Запос
		$this->original_url = urldecode($_SERVER['REDIRECT_URL']);
		
		//Разбираем URL по частям
		$this->readParts($model, urldecode($_SERVER['REDIRECT_URL']));
		
	}
	
	//Разбираем параметры
	public function readParts($model, $url_string, $prefered_module = false)
	{
	
		//Фикс против неправильной настройки сервера
		if(substr_count($url_string, '?'))
			$url_string = substr($url_string, 0, strpos($url_string, '?'));
	
		//Разбираем URL по частям
		$t = explode('/', $url_string);
		foreach ($t as $i => $ti)
			if (!strlen($ti))
				UnSet($t[$i]);
		$this->url = array_values($t);
		
		//Вытаскиваем и чистим модификаторы
		$mode = explode('.', $this->url[count($this->url) - 1]);
		UnSet($mode[count($mode) - 1]);
		UnSet($mode[0]);
		$mode = array_values($mode);
		
		//Обрезаем все части до SID`ов
		foreach ($this->url as $i => $u)
			if (substr_count($u, '.'))
				$this->url[$i] = substr($u, 0, strpos($u, '.'));
		
		//Запоминаем корневой URL, без учёта модуля
		if (!IsSet($this->full_url)) {
			$this->full_url = array_values($t);
			foreach ($this->full_url as $i => $u)
				if (substr_count($u, '.'))
					$this->full_url[$i] = substr($u, 0, strpos($u, '.'));
		}
		
		//Разбираем URL на части, определяем модификаторы
		$this->tree = false;
		
		//		$this->rec=false;
		$this->dep_path      = false;
		$this->module        = $prefered_module;
		$this->structure_sid = 'rec';
		
		//Формат вывода данных
		$output_format = substr($url_string, strrpos($url_string, '.') + 1);
		if (!in_array($output_format, $this->output_formats))
			$output_format = 'html';
		//Разбираем части URL на параметры
		foreach ($this->url as $i => $u) {
			$this->tree[$i] = $this->explodeUrlPart($u, $output_format);
			
			//Склеиваем URL - с ними удобнее потом искать запись в других модулях
			if ($i)
				$this->tree[$i]['url'] = $this->tree[$i - 1]['url'] . '/' . $this->tree[$i]['sid'];
			else
				$this->tree[$i]['url'] = '/' . $this->tree[$i]['sid'];
			
			//Объявляем структуру поумолчанию
			$this->tree[$i]['structure'] = 'rec';
		}
		
		//Определяем структуры модуля, их иерархию
		if ($model->modules[$prefered_module]->structure['rec']['type'] == 'simple') {
			$levels   = $model->modules[$prefered_module]->getLevels($model->modules[$prefered_module]->structure['rec']['dep_path']['structure'], array());
			$levels   = array_reverse($levels);
			$levels[] = 'rec';
			if ($levels[(count($this->url) > 0 ? count($this->url) - 1 : 0)])
				$this->structure_sid = $levels[(count($this->url) > 0 ? count($this->url) - 1 : 0)];
			$this->rec['structure'] = $this->structure_sid;
			if (!count($this->url))
				$this->output_type = 'index';
		}
		
		//Если есть какие-то элементы дерева в запросе - это внутренняя страница сайта
		if ($this->tree) {
			//Если тип последней структуры - Rec, то это запись.
			if ($this->tree[count($this->tree) - 1]['structure'] == 'rec') {
				$this->rec         = array_pop($this->tree);
				$this->output_type = 'content';
				
				//Иначе это список записей.
			} else {
				$this->output_type = 'list';
			}
			
			//Страницы
			$this->rec['mode'] = $mode;
			foreach ($mode as $val)
				if ( is_numeric($val) )
					$this->rec['page'] = intval($val);
			
			//Отдельно пишем родителя
			if ($this->tree)
				$this->dep_path = array_pop($this->tree);
			
			//А если его нет - что это главная страница сайта
		} else {
			if (!$this->rec['is_link_to_module'])
				$this->rec = array(
					'sid' => false,
					'mode' => false,
					'structure' => 'rec'
				);
			if (!$this->module)
				$this->rec['sid'] = 'index';
			$this->output_type = 'index';
		}
		
		//Если дерево опустело - накой оно нужно
		if (!count($this->tree))
			$this->tree = false;
		
		//Тип выводимых данных
		$this->output           = $output_format;
		$this->rec['structure'] = $this->structure_sid;
		
		//Сохраняем модифиакторы
		$this->rec['mode'] = $mode;
		if (count($mode))
			$this->mode = $mode;
			
		//Если указана текущая страница - убираем её из модификаторов
		if( $this->mode[count($this->mode)-1] == intval($this->rec['page']) && (strlen($this->mode[count($this->mode)-1]) == strlen($this->rec['page'])) ){
			UnSet($this->mode[count($this->mode)-1]);
		}
		
	}
	
	//Вытащить из части URL все данные
	private function explodeUrlPart($url_part, $type)
	{
		$res = array(
			'sid' => false,
			'mode' => false,
			'page' => false,
			'structure' => false
		);
		
		//Удаляем расширение файла - он же тип вывода
		$url_part = str_replace('.' . $type, '', $url_part);
		
		if (substr_count($url_part, '.')) {
			$t          = explode('.', $url_part);
			$res['sid'] = $t[0];
			UnSet($t[0]);
			
			//Вытаскиваем номера страниц
			foreach ($t as $i => $ti)
				if (intval($ti) == $ti) {
					$res['page'] = $ti;
					//				UnSet($t[$i]);
				}
			
			if (count($t)) {
				$res['mode'] = array_values($t);
			}
		} else {
			$res['sid'] = $url_part;
		}
		
		return $res;
	}
	
}

?>