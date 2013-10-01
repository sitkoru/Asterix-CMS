<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Дата									*/
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

class field_type_date extends field_type_default
{
	public $default_settings = array( 'sid' => false, 'title' => 'Дата и время', 'value' => '2000-01-01 12:00:00', 'width' => '100%' );

	public $months = array( '', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря' );
	public $months_in = array( '', 'в январе', 'в феврале', 'в марте', 'в апреле', 'в мае', 'в июне', 'в июле', 'в августе', 'в сентябре', 'в октябре', 'в ноябре', 'декабря' );
	public $Months = array( '', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' );
	public $weekdays = array( 'воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота' );
	public $weekdays_in = array( 'в воскресенье', 'в понедельник', 'во вторник', 'в среду', 'в четверг', 'в пятницу', 'в субботу' );
	public $Weekdays = array( 'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота' );

	public $months_eng = array( '', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'desember' );
	public $Months_eng = array( '', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'Desember' );
	public $weekdays_eng = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' );
	public $Weekdays_eng = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

	//Поле участввует в поиске
	public $searchable = false;

	public $template_file = 'types/date.tpl';

	public function creatingString( $name )
	{
		return '`' . $name . '`  DATE NOT NULL';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue( $value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false )
	{
		return date( "Y-m-d", strtotime( $values[$value_sid]['date'] ) );
	}


	//Получить простое значение по умолчанию из настроек поля
	public function getDefaultValue( $settings = false )
	{
		return date( "Y-m-d" );
	}

	//Получить развёрнутое значение из простого значения
	public function getValueExplode( $value, $settings = false, $record = array() )
	{
		$res         = array();
		$res['date'] = date( "Y-m-d", strtotime( $value ) );

		$res['year']   = date( "Y", strtotime( $value ) );
		$res['month']  = date( "m", strtotime( $value ) );
		$res['day']    = date( "j", strtotime( $value ) );
		$res['hour']   = date( "H", strtotime( $value ) );
		$res['minute'] = date( "i", strtotime( $value ) );
		$res['second'] = date( "s", strtotime( $value ) );
		$res['r']      = date( "r", strtotime( $value ) );

		$res['month_title']    = $this->months[date( "n", strtotime( $value ) )];
		$res['month_title_in'] = $this->months_in[date( "n", strtotime( $value ) )];
		$res['month_Title']    = $this->Months[date( "n", strtotime( $value ) )];
		$res['weekday']        = $this->weekdays[date( "w", strtotime( $value ) )];
		$res['weekday_in']     = $this->weekdays_in[date( "w", strtotime( $value ) )];
		$res['Weekday']        = $this->Weekdays[date( "w", strtotime( $value ) )];

		$res['month_title_eng'] = $this->months_eng[date( "n", strtotime( $value ) )];
		$res['month_Title_eng'] = $this->Months_eng[date( "n", strtotime( $value ) )];
		$res['weekday_eng']     = $this->weekdays_eng[date( "w", strtotime( $value ) )];
		$res['Weekday_eng']     = $this->Weekdays_eng[date( "w", strtotime( $value ) )];

		$res['days_from']     = abs( floor( ( date( "U" ) - date( "U", strtotime( $value ) ) ) / 86400 ) );
		$res['date_in_past']     = date( "U", strtotime( $value ) ) < date( "U" );

//		pr_r($res);

		return $res;
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode( $value, $settings = false, $record = array() )
	{
		$result = $this->getValueExplode( $value, $settings, $record );

		return $result;
	}
}

?>