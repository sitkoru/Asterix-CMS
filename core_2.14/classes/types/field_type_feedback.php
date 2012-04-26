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
	
	public function creatingString($name){
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false){
		$fields = array();
		$value = $values[$value_sid];

		foreach($value['fields'] as $var=>$vals){
			foreach($vals as $i=>$val){
				if( $var == 'required' )
					$val = intval($val);
				$fields[$i][$var] = $val;
			}
		}
		$value['shw'] = intval($value['shw']);
		$value['captcha'] = intval($value['captcha']);
		$value['fields'] = $fields;
		
		//Отметаем пустые поля
		if (is_array($value['fields']))
			foreach ($value['fields'] as $i => $f)
				if (!strlen($f['title']))
					UnSet($value['fields'][$i]);
					
		//Готово
		return serialize( $value );
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array()){
		//Расшифровываем
		$value = unserialize($value);

		//Разделяем варианты для списковых полей
		if($value['fields'])
		foreach($value['fields'] as $i => $field)
			if( in_array($field['type'], array('radiolist','checklist')) )
				$value['fields'][$i]['default_vals'] = explode(',', $field['default']);
		
		//Captcha
		if ($value['protection'] == 'captcha') {
			include_once(model::$config['path']['core'] . '/../libs/captcha.php');
			
			//Готовим Captcha
			$captcha = new captcha(model::$config);
			list($image, $code) = $captcha->generate();
			
			//Запоминаем код
			$_SESSION['form_captcha_code'] = $code;
			
			//Чиатем исходник файла
			$path = tempnam(model::$config['path']['tmp'], 'FOO');
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
	public function getAdmValueExplode($value, $settings = false, $record = array()){
		$value = unserialize( htmlspecialchars_decode( $value ) );
		
		if( IsSet($value['protection']) )
			if( $value['protection'] == 'captcha' )
				$value['captcha'] = 1;
		
		return $value;
	}
}

?>