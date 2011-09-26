<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Форма обратной связи					*/
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

class field_type_feedback extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Форма обратной связи', 'value' => 0, 'width' => '100%');
	
	public $template_file = 'types/feedback.tpl';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{
		//Отметаем пустые поля
		if (is_array($values[$value_sid]['fields']))
			foreach ($values[$value_sid]['fields'] as $i => $f)
				if (!strlen($f['title']))
					UnSet($values[$value_sid]['fields'][$i]);
		//Готово
		
		return serialize($values[$value_sid]);
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		//Расшифровываем
		$value = unserialize($value);

		//Разделяем варианты для списковых полей
		foreach($value['fields'] as $i => $field)
			if( in_array($field['type'], array('radiolist','checklist')) )
				$value['fields'][$i]['default_vals'] = explode(',', $field['default']);
		
		//Captcha
		if ($value['protection'] == 'captcha') {
			include_once($this->model->config['path']['libraries'] . '/captcha.php');
			
			//Готовим Captcha
			$captcha = new captcha($this->model->config);
			list($image, $code) = $captcha->generate();
			
			//Запоминаем код
			$_SESSION['form_captcha_code'] = $code;
			
			//Чиатем исходник файла
			$path = tempnam($this->model->config['path']['tmp'], 'FOO');
			imagepng($image, $path);
			
			//Записываем
			$value['captcha'] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
		}
		
		//Расставляем доп.поля
		if ($value['fields'])
			if (is_array($value['fields']))
				foreach ($value['fields'] as $i => $f)
					$value['fields'][$i]['sid'] = 'f' . $i;
		//Готово
		return $value;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$min_num_of_fields = 8;
		
		$value = unserialize($value);
		if(!is_array($value))
			$value=array();

		for ($i = 0; $i < 3; $i++)
			$value['fields'][] = array(
				'title' => false,
				'type' => 'text',
				'required' => false
			);
		
		if (count($value['fields']) < $min_num_of_fields)
			for ($i = count($value['fields']); $i < $min_num_of_fields; $i++) {
				$value['fields'][] = array(
					'title' => false,
					'type' => 'text',
					'required' => false
				);
			}
		
		//		$value['counter']=intval($value['counter']);
		
		//Готово
		return $value;
	}
}

?>