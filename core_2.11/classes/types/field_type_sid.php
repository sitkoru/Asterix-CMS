<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Текстовый системный идентификатор SID	*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 29 октября 2009 года						*/
/*															*/
/************************************************************/

class field_type_sid extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Системное имя', 'value' => '', 'width' => '100%');

	public $template_file = 'types/sid.tpl';

	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(64) NOT NULL';
	}

	//Проверка уникальности значения SID
	public function checkUnique($module, $structure_sid, $value, $id = false)
	{
		$res = $this->model->makeSql(array(
			'tables' => array(
				$this->model->modules[$module]->getCurrentTable($structure_sid)
			),
			'where' => array(
				'and' => array(
					'sid' => '`sid`="' . mysql_real_escape_string($value) . '"',
					'id' => ($id ? '(not(`id`="' . $id . '"))' : '1')
				)
			)
		), 'getrow');
		return !$res;
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		//Если поле SID пустое - берём его из заголовка
		if (!strlen($values[$value_sid]))
			$values[$value_sid] = $values['title'];

		//Проверим значение, уберём лишнее
		$values[$value_sid] = $this->correctValue($values[$value_sid]);

		return htmlspecialchars($values[$value_sid]);
	}

	//Перевести массив значений в само значение для системы управления
	public function impAdmValue($value, $settings = false, $record = array())
	{
		//Если поле SID пустое - берём его из заголовка
		if (!$value)
			$value = $record['title'];

		//Проверим значение, уберём лишнее
		$value = $this->correctValue($value);

		return htmlspecialchars($value);
	}

	//Проверим значение, уберём лишнее
	private function correctValue($value)
	{

		//Только латинские URL
		if( $this->model->config['settings']['latin_url_only'] ) {

			if (preg_match('/[^A-Za-z0-9_\-]/', $value)) {
				$value = translitIt($value);
				$value = preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
			}

		//Поддержка кириллических URL
		}else{
			$value = str_replace(' ', '_', $value);
			$value = str_replace('__', '_', $value);
			$value = preg_replace( "/[^\da-zа-яё_\-]/iu", '', $value );				// так красивее!
			//$value = preg_replace('/[^A-Za-zАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя0-9_\-]/', '', $value);
		}

		//Максимальная длина
		if (strlen($value) > 64)
			$value = mb_substr($value, 0, 64, 'utf-8');

		return $value;
	}

}

?>
