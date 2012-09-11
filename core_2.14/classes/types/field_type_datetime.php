<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Дата и Время							*/
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

class field_type_datetime extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Дата и время', 'value' => '2000-01-01 12:00:00', 'width' => '100%');

	public $months = 					array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
	public $months_in = 				array('', 'в январе', 'в феврале', 'в марте', 'в апреле', 'в мае', 'в июне', 'в июле', 'в августе', 'в сентябре', 'в октябре', 'в ноябре', 'декабря');
	public $Months = 					array('', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
	public $weekdays = 					array('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота');
	public $weekdays_in = 				array('в воскресенье', 'в понедельник', 'во вторник', 'в среду', 'в четверг', 'в пятницу', 'в субботу');
	public $Weekdays = 					array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');

	public $months_eng = 				array('', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'desember');
	public $Months_eng = 				array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'Desember');
	public $weekdays_eng = 				array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
	public $Weekdays_eng = 				array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

	//Поле участввует в поиске
	public $searchable = false;

	public $template_file = 'types/datetime.tpl';

	public function creatingString($name){
		return '`' . $name . '` DATETIME NOT NULL';
	}

	//Какой строкой обновлять тип данных
	public function getUpdateSQLString($value){
		//Компануем значение
		$s = $value['date'] . ' ' . $value['time'];

		//Получаем дату
		$val = date("Y-m-d H:i:s", strtotime($s));
		return '`' . $this->sid . '`="' . mysql_real_escape_string($val) . '"';
	}

	//Получить простое значение по умолчанию из настроек поля
	public function getDefaultValue($settings = false){
		if( IsSet($settings['default']) )
			return $settings['default'];
		else
			return date("Y-m-d H:i:s");
	}
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array(), $adm = false){
		
		if( is_array($value) )
			return $value;
		
		$res           			= array();
		$res['date']   			= date("Y-m-d", strtotime($value));
		$res['time']   			= date("H:i:s", strtotime($value));

		$res['year']   			= date("Y", strtotime($value));
		$res['month']  			= date("m", strtotime($value));
		$res['day']    			= date("j", strtotime($value));
		$res['hour']   			= date("H", strtotime($value));
		$res['minute'] 			= date("i", strtotime($value));
		$res['second'] 			= date("s", strtotime($value));
		$res['r']      			= date("r", strtotime($value));

		$res['month_title']    	= $this->months[date("n", strtotime($value))];
		$res['month_title_in'] 	= $this->months_in[date("n", strtotime($value))];
		$res['month_Title']    	= $this->Months[date("n", strtotime($value))];
		$res['weekday']        	= $this->weekdays[date("w", strtotime($value))];
		$res['weekday_in']     	= $this->weekdays_in[date("w", strtotime($value))];
		$res['Weekday']        	= $this->Weekdays[date("w", strtotime($value))];

		$res['month_title_eng'] = $this->months_eng[date("n", strtotime($value))];
		$res['month_Title_eng'] = $this->Months_eng[date("n", strtotime($value))];
		$res['weekday_eng']     = $this->weekdays_eng[date("w", strtotime($value))];
		$res['Weekday_eng']     = $this->Weekdays_eng[date("w", strtotime($value))];

		//Примерный текст
		$res['text'] = false;
		if (date("Y", strtotime($value)) == date("Y")) {
			if (date("m", strtotime($value)) == date("m")) {
				if (date("d", strtotime($value)) == date("d")) {
					if (date("H", strtotime($value)) == date("H")) {
						if (date("i", strtotime($value)) == date("i")) {
							$res['text'] = 'только что';
						} elseif (date("i", strtotime($value)) == date("i") - 1) {
							$res['text'] = 'минуту назад';
						} elseif (date("i", strtotime($value)) == date("i") - 2) {
							$res['text'] = 'пару минут назад';
						} elseif (date("i", strtotime($value)) == date("i") - 3) {
							$res['text'] = 'три минут назад';
						} elseif (date("i", strtotime($value)) == date("i") - 4) {
							$res['text'] = 'четыре минуты назад';
						} else {
							$res['text'] = (date("i") - date("i", strtotime($value))) . ' минут назад';
						}
					} elseif (date("H", strtotime($value)) == date("H") - 1) {
						$dif         = (date("i") + 60 - date("i", strtotime($value)));
						$res['text'] = ($dif >= 60 ? 'час назад' : $dif . ' минут назад');
					} elseif (date("H", strtotime($value)) == date("H") - 2) {
						$res['text'] = 'час назад';
					} else {
						$res['text'] = 'в ' . $res['hour'] . ':' . $res['minute'];
					}
				} elseif (date("d", strtotime($value)) == date("d") - 1) {
					$res['text'] = 'вчера в ' . $res['hour'] . ':' . $res['minute'];
				} elseif (date("d", strtotime($value)) == date("d") - 2) {
					$res['text'] = $res['weekday_in'];
				} elseif (date("d", strtotime($value)) == date("d") - 3) {
					$res['text'] = $res['weekday_in'];
				} elseif (date("d", strtotime($value)) == date("d") - 4) {
					$res['text'] = $res['weekday_in'];
				} else {
					$res['text'] = $res['day'] . ' ' . $res['month_title'];
				}
			} elseif (date("m", strtotime($value)) == date("m") - 1) {
				$res['text'] = 'в прошлом месяце';
			} else {
				$res['text'] = $res['month_title_in'];
			}
		} elseif (date("Y", strtotime($value)) == date("Y") - 1) {
			$res['text'] = 'в прошлом году';
		} else {
			$res['text'] = 'в '.date("Y", strtotime($value)).' году';
		}

		return $res;
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array()){
		return $this->getValueExplode($value, $settings, $record, true);
	}











	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		if (is_array($values[$value_sid])) {
			//Фильтруем несуществующие даты
			if (!checkdate( date("m",$values[$value_sid]['date']), date("d",$values[$value_sid]['date']), date("Y",$values[$value_sid]['date']) ))
				$values[$value_sid]['date'] = '2000-01-01';

			return $values[$value_sid]['date'] . ' ' . $values[$value_sid]['time'];
		} else {
			return $values[$value_sid];
		}
	}
}

?>
