<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Техи									*/
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

class field_type_tags extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Однострочный текст', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/tags.tpl';
	private $table = 'tags';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		$arr  = explode(',', $values[$value_sid]);
		$recs = array();
		foreach ($arr as $a)
			if (strlen(trim($a)))
				$recs[] = trim($a);
		
		//Переиндексация облака тегов
		$this->model->types['tags']->reindexTegs();
		
		return '|' . implode('|', $recs) . '|';
	}
	
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		$vals = explode('|', $value);
		$recs = array();
		
		$search_module_sid=$this->model->getModuleSidByPrototype('search');
		
		foreach ($vals as $i => $val)
			if (strlen($val)) {
				$recs[] = array(
					'title' => trim($val),
					'url' => $this->model->modules[$search_module_sid]->info['url'].'.html?t=' . $val . ''
				);
			}
		//Представляем в виде массива
		return $recs;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}
	
	
	
	
	//Получить облако тегов
	public function getTagsCloud($counter_limit = 1, $font_max = 28, $font_min = 12)
	{
		//Получаем теги
		$tags = $this->getTagsList($counter_limit);
		
		//Ищем самый популярный тег - от него будем счетать размеры шрифтов
		$counter_max = false;
		foreach ($tags as $tag)
			if ($tag['counter'] > $counter_max)
				$counter_max = $tag['counter'];
		
		//Расставляем размеры шрифтов
		$delta_font = $font_max - $font_min;
		$delta_tags = $counter_max - $counter_limit;
		if (!$delta_tags)
			$delta_tags = 1;
		foreach ($tags as $i => $tag) {
			$tags[$i]['font_size']       = (($tag['counter'] - $counter_limit) / $delta_tags) * $delta_font + $font_min;
			$tags[$i]['font_size_round'] = round($tags[$i]['font_size']);
		}
		
		//Готово
		return $tags;
	}
	
	//Получить список тегов
	public function getTagsList($counter_limit = 2)
	{
		$recs = $this->model->makeSql(array(
			'tables' => array(
				$this->table
			),
			'where' => array(
				'and' => array(
					'`counter`>=' . intval($counter_limit) . '',
					'access'=>false
				)
			),
			'order' => 'order by `title`'
		), 'getall');
		
		$recs = $recs ? $recs : array();
		
		$search_module_sid=$this->model->getModuleSidByPrototype('search');

		//Вставляем ссылки
		foreach ($recs as $i => $rec) {
			$recs[$i]['url'] = $this->model->modules[$search_module_sid]->info['url'].'.html?t=' . $rec['title'] . '';
		}
		
		return $recs;
	}
	
	//Проверить существование тегов в системе
	public function checkTags($tags, $rec)
	{
	}
	
	//Проиндексировать все теги в системе
	public function reindexTegs()
	{
		$all_tags = array();
		
		//Перебираем все модули - ищем теги
		foreach ($this->model->modules as $module_sid => $module) {
			//Перебираем все структуры
			if (is_array($module->structure))
				foreach ($module->structure as $structure_sid => $structure) {
					//Перебираем все поля
					foreach ($structure['fields'] as $field_sid => $field) {
						//Ищем все поля с Тегами
						if ($field['type'] == 'tags') {
							$recs = $this->model->makeSql(array(
								'tables' => array(
									$module->getCurrentTable($structure_sid)
								),
								'fields' => array(
									'id',
									'date_public',
									'title',
									'url',
									$field_sid
								),
								'where' => array(
									'and' => array(
										'`shw`=1'
									)
								)
							), 'getall');
							
							//Собираем все теги из записей
							if(is_array($recs))
							foreach ($recs as $rec) {
								//Вынимаем теги
								$tags = explode('|', $rec[$field_sid]);
								$tags = array_values($tags);
								
								//Заносим их в результирующий массив
								foreach ($tags as $tag)
									if (strlen($tag)) {
										//Учитываем тег в результирующем массиве
										$all_tags[$tag]['where'][$module_sid][$structure_sid][] = array(
											'id' => $rec['id'],
											'date_public' => $rec['date_public'],
											'title' => $rec['title'],
											'url' => $rec['url']
										);
										//Увеличиваем счётчик
										$all_tags[$tag]['counter']++;
									}
							}
						}
					}
				}
		}
		
		//Записываем теги в базу
		$this->model->execSql('delete from `' . $this->table . '` where '.$this->model->extensions['domains']->getWhere().'', 'insert');
		foreach ($all_tags as $tag => $tag_vals) {
			$this->model->makeSql(array(
				'tables' => array(
					$this->table
				),
				'fields' => array(
					'`title`="' . mysql_real_escape_string($tag) . '"',
					'`counter`="' . $tag_vals['counter'] . '"',
					'`where`="' . mysql_real_escape_string(serialize($tag_vals['where'])) . '"'
				)
			), 'insert');
		}
	}
	
}

?>
