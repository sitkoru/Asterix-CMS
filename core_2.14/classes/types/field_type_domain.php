<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Домен									*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 28 октября 2009 года						*/
/*															*/
/************************************************************/

class field_type_domain extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Домен', 'value' => 'all', 'width' => '100%');
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public $template_file = 'types/domain.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
	}
	
	public function getDefaultValue()
	{
		if( model::$config['settings']['domain_switch'] )
			return 'all';
		else
			return $this->model->extensions['domains']->domain['id'];
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{
		//Настройки поля, переданные из модуля
		if ($settings)
			foreach ($settings as $var => $val)
				$this->$var = $val;
		
		//Если нет значения - ставим значение по умолчанию
		if (!IsSet($values[$value_sid]))
			$values[$value_sid] = array(
				$this->model->extensions['domains']->domain['id']
			);
		
		//Готово
		if ( $values[$value_sid] == 'all'){
			$res_value = 'all';
		}elseif (@in_array('all', $values[$value_sid])) {
			$res_value = 'all';
		} else {
			$res_value = '|' . (is_array($values[$value_sid]) ? implode('|', $values[$value_sid]) : $values[$value_sid]) . '|';
		}
		
		//Убираем двоынйе разделители, возникающие после воссатновления резервных копий
		$res_value = str_replace('||', '|', $res_value);
		
		return $res_value;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		if( is_array($value) )
			return $value;

		//Оптимизация выдачи, исключение повторного запроса
		if ($value == '|' . model::pointDomainID() . '|')
			return array( model::getDomain() );
		
		//Все домены
		if($value == 'all'){
			//Варианты значений
			if(!$this->recs){
				$recs = $this->model->execSql('select `id`,`title`,`host` from `domains` where `active`=1 order by `pos`');
				$this->recs = $recs;
			}else
				$recs = $this->recs;
		
		//Указан домен
		}else{
			$arr  = explode('|', $value);
			$res  = array();
			//Варианты значений
			$recs = $this->model->execSql('select `id`,`title`,`host` from `domains` where `id` in ("' . implode('", "', $arr) . '") and `active`=1 order by `pos`');
		}
		
		//Готово
		return $recs;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		//Переключение домена записей в рамках сайта
		$domain_switch = model::$config['settings']['domain_switch'];
		
		$res = array();
		
		if ($domain_switch) {
			//Варианты значений
			$variants = $this->model->execSql('select `id`,`title` from `domains` where `active`=1 order by `title`');
			//Вариант универсальной записи
			$variants = array_merge(array(
				array(
					'id' => 'all',
					'title' => '- все домены -'
				)
			), $variants);
		} else {
			//Варианты значений
			$variants = array(
				model::getDomain()
			);
		}
		
		//Если значение ещё не развёрнуто - разворачиваем
		$arr = explode('|', $value);
		
		//Отмечаем в массиве выбранные элементы
		foreach ($variants as $i => $variant)
			if (strlen($variant['id']))
				$res[] = array(
					'value' => $variant['id'],
					'title' => $variant['title'],
					'selected' => in_array($variant['id'], $arr)
				);
		
		//Готово
		return $res;
	}
	
	//Перевести массив значений в само значение для системы управления
	public function impAdmValue($value, $settings = false, $record = array())
	{
		return '|' . implode('|', $value) . '|';
	}
	
}

?>