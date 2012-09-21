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

	//Зарезервированные значения - нельзя использовать
	public $reserver_sids = array('admin', '0', 'false');
	
	public $default_settings = array('sid' => false, 'title' => 'Системное имя', 'value' => '', 'width' => '100%');

	public $template_file = 'types/sid.tpl';

	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(64) NOT NULL';
	}

	//Проверка уникальности значения SID
	public function checkUnique($module, $structure_sid, $value, $id = false){
		if( !IsSet( model::$modules[$module] ) )
			return true;
		
		$repeats = model::makeSql(array(
			'tables' => array(
				model::$modules[$module]->getCurrentTable($structure_sid)
			),
			'where' => array(
				'and' => array(
					'sid' => '`sid`="' . mysql_real_escape_string($value) . '"',
					'id' => ($id ? '`id`!="' . intval($id) . '"' : '1')
				)
			)
		), 'getrow');
		
		//Проверка на зарезервированные зыначения
		if( !$repeats )
			$repeats = in_array($value, $this->reserver_sids);
		
		return !$repeats;
	}

	//Проверка уникальности значения SID
	public function makeUnique($module, $structure_sid, $value, $id = false){
		//Проверка уникальности присланного SID
		$sid_unique = $this->checkUnique($module, $structure_sid, $value, $id);
		
		//Если не уникально - будем придумывать уникальный SID
		if(!$sid_unique){
		
			//Подставляем окончание
			for($i=1;$i<=1000;$i++)
				if(!$sid_unique){
					$new_sid=$value.'_'.$i;
					$sid_unique=$this->checkUnique($module,$structure_sid,$new_sid,$id);
				}
				
			//Если уже все 1000 наименований заняты - ну всё, хватит издеваться над системой
			if(!$sid_unique){
				pr('Добавление невозможно, SID не уникален, существуют 1000 записей с таким же SID`ом');
				exit();
				
			//Всёже нашли уникальный SID
			}else
				return $new_sid;
		}
		return $value;
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		
		// Если в модуле указано - генерируем SID через специальную функцию
		if( method_exists( model::$modules[ $module_sid ], 'generateSid') ){
			$function_name = 'generateSid';
			$values[$value_sid] = model::$modules[$module_sid]->$function_name( $values, $structure_sid );
			
		// Если поле SID пустое - берём его из заголовка
		}elseif (!strlen($values[$value_sid])){
			$values[$value_sid] = $values['title'];
		}

		// Проверим значение, уберём лишнее
		$values[$value_sid] = $this->correctValue($values[$value_sid]);

		// Проверим на уникальность
		$values[$value_sid] = $this->makeUnique($module_sid, $structure_sid, $values[$value_sid], $values['id']);
		
		return htmlspecialchars( $values[$value_sid] );
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
	public function correctValue($value){

		//Только латинские URL
		if( model::$settings['latin_url_only'] ) {
		
			if (preg_match('/[^A-Za-z0-9_\-]/', $value)) {
				$value = $this->translitIt($value);
				$value = preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
			}

		//Поддержка кириллических URL
		}else{
			$value = str_replace(' ', '_', $value);
			$value = str_replace('__', '_', $value);
			$value = preg_replace( "/[^\da-zа-яё_\-]/iu", '', $value );
		}

		//Максимальная длина
		if (strlen($value) > 64)
			$value = mb_substr($value, 0, 64, 'utf-8');

		return $value;
	}
	
	public function translitIt($str){
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
			" "=> "_", "/"=> "_"
		);
		return strtr($str,$tr);
	}


}

?>
