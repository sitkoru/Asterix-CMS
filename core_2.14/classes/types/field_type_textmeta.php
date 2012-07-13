<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Мета-данные к текстовому полю 			*/
/*															*/
/*	Версия ядра 2.14										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2012  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 13 июля 2012 года								*/
/*	Модифицирован: 13 июля 2012 года						*/
/*															*/
/************************************************************/

class field_type_textmeta extends field_type_default{
	
	public $default_settings = array
	(
		'sid' 	=> false, 
		'title' => 'Мета-данные к текстовому полю вида "Визуальный редактор"', 
		'value' => '', 
		'width' => '100%'
	);
	
	public $template_file = 'types/hidden.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){

		$meta = false;
		if( !IsSet( $settings['field'] ) )
			$settings['field'] = 'text';
	
		// Поле, которое будем анализировать
		$field_sid = $settings['field'];
		if( IsSet( model::$modules[ $module_sid ]->structure[ $structure_sid ]['fields'][ $field_sid ] ) )
		{
			$field = model::$modules[ $module_sid ]->structure[ $structure_sid ]['fields'][ $field_sid ];
			
			// Есть чо анализировать-то ваще?
			if( IsSet( $values[ $field_sid ] ) )
			{
				
				// Данные о файлах
				$meta['files'] = $this->getFiles( $values[ $field_sid ] );
				
				// Данные о ссылках
				$meta['links'] = $this->getLinks( $values[ $field_sid ] );
				
				$meta = serialize( $meta );
			}
			
		}
		
		// Готово
		return $meta;
	}
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		$result = false;
		if( $value )
			if( !is_array($value) )
			{
				$result = unserialize( htmlspecialchars_decode( $value ) );
				$result['old'] = $value;
			}
			
		return $result;
	}
	
	// олучить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}





	// Данные о файлах
	private function getFiles( $value )
	{
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $value, $result);
		$result=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$result[0]);
		return $result;
	}
	
	// Данные о ссылках
	private function getLinks( $value )
	{
		preg_match_all("/<[Aa][ \r\n\t]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\n\r\t]*([^ \"'>\r\n\t#]+)[^>]*>/", $value, $result);
		return @$result[1];
	}
	
}

?>