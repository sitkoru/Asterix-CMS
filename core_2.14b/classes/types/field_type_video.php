<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Текст									*/
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

class field_type_video extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Видео-ролик', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/video.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		if (is_array($values[$value_sid])) {
			return serialize($values[$value_sid]);
		} else
			return $values[$value_sid];
	}
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array()){
		$value = unserialize( htmlspecialchars_decode( $value ) );
		if( $value['type'] == 'youtube' ){
			$value['code'] = '
<object width="640" height="360">
<param name="movie" value="http://www.youtube.com/v/'.str_replace('http://youtu.be/','',$value['link']).'?version=3&amp;hl=ru_RU&amp;rel=0" />
<param name="allowFullScreen" value="true" />
<param name="allowscriptaccess" value="always" />
<param name="wmode" value="transparent" />
<embed 
	src="http://www.youtube.com/v/'.str_replace('http://youtu.be/','',$value['link']).'?version=3&amp;hl=ru_RU&amp;rel=0" 
	type="application/x-shockwave-flash" 
	width="640"
	height="360"
	allowscriptaccess="always"
	allowfullscreen="true"
	wmode="transparent"
></embed>
</object>';
//			$value['code'] = '<iframe width="480" height="360" src="http://www.youtube.com/embed/'.str_replace('http://youtu.be/','',$value['link']).'?rel=0" frameborder="0" allowfullscreen></iframe>';
		}elseif( $value['type'] == 'file' ){
			$value['code'] = '[формат видео не поддерживается]';
		}
		return $value;
	}

	public function getAdmValueExplode($value, $settings = false, $record = array()){
		$value = unserialize( htmlspecialchars_decode( $value ) );
		return $value;
	}
	
}

?>