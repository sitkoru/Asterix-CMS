<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Фотогалерея							*/
/*															*/
/*	Версия ядра 2.04										*/
/*	Версия скрипта 1.1										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 18 марта 2010 года						*/
/*															*/
/************************************************************/

class field_type_gallery extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Галерея картинок', 'value' => 0, 'width' => '100%', /*
	inner - по минимальному размеру (изображение не более указанных размеров)
	outer - по максимальному разделу  (изобажение не меньше указанных размеров)
	width - по ширине (изображение по ширине соответствует указанному размеру)
	height - по высоте (изображение по высоте соответствует указанному размеру)
	exec - по указанным размерам (изображение соответствует указанным размерам, без сохранения пропорций)
	*/ 'resize_type' => 'inner', 'resize_width' => 250, 'resize_height' => 250, 'resize_proportions' => true);

	//Разрешённые форматы файлов для загрузки
	private $allowed_extensions = array('image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/png' => 'png');

	public $template_file = 'types/gallery.tpl';

	//Поле участввует в поиске
	public $searchable = false;

	public function creatingString($name)	{
		return '`' . $name . '` text NOT NULL';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){
		$images = array();
		$data = false;
	
		//Коррекция типа данных
		$this->correctFieldType($module_sid, $structure_sid, $value_sid);
		
	/*
		Название папки будет строиться на основании прототипа модуля, 
		так как модуль может называться кириллицей что плохо для файловой системы Linux
	*/
		$module_prototype = model::$modules[ $module_sid ]->info['prototype'];
		
		require_once model::$config['path']['core'] . '/../libs/acmsDirs.php';
		require_once model::$config['path']['core'] . '/../libs/acmsFiles.php';
		require_once model::$config['path']['core'] . '/../libs/acmsImages.php';

		//Старые значения
		$old_values[$value_sid] = $this->getValueExplode($old_values[$value_sid], $settings, $values);
		if( IsSet($_POST[$value_sid . '_old_id']) )
		foreach( $_POST[$value_sid . '_old_id'] as $i => $value)
			if( !$values[$value_sid . '_delete'][$i] )
				if( strlen($old_values[$value_sid][$i]['path']) ){
					//Обновляем заголовок
					$old_values[$value_sid][$i]['title'] = strip_tags( $values[$value_sid . '_title'][$i] );
					if( IsSet( $_POST[$value_sid . '_title'][$i] ) )
						$old_values[$value_sid][$i]['title'] = strip_tags( $_POST[$value_sid . '_title'][$i] );
					$images[] = $old_values[$value_sid][$i];
				}
				
		//Новые
		foreach( $values[$value_sid]['name'] as $i => $value)
			if( strlen($values[$value_sid]['tmp_name'][$i]) > 0 ){

			//Создаём папку, если ещё нет
			$dir_path = model::$config['path']['public_images'] . '/' . $module_prototype . '/' . $structure_sid. str_pad($values['id'], 6, '0', STR_PAD_LEFT).'/'.$value_sid;
			$created = acmsDirs::makeFolder( model::$config['path']['www'] . $dir_path );
			if( !$created )
				log::stop('500 Internal Server Error', 'Нет доступа для создания папки', model::$config['path']['www'] . $dir_path );
			
			//Удаление фотки
			if ($values[$value_sid . '_delete'][$i]) {
				$old_path = substr( $values[ $value_sid.'_old_id' ][$i], 0, strpos( $values[ $value_sid.'_old_id' ][$i], '|' ) );
				acmsFiles::delete(model::$config['path']['www'] . $dir_path . '/' . $old_path);
				
			//Файл передан
			} elseif (strlen( $values[$value_sid]['tmp_name'][$i] ) ) {
				
				//Обновление картинки
				if( @$values[$value_sid . '_old_id'] ){
					$old_path = substr( $values[ $value_sid.'_old_id' ][$i], 0, strpos( $values[ $value_sid.'_old_id' ][$i], '|' ) );
					acmsFiles::delete(model::$config['path']['www'] . $dir_path . '' . $old_path);
					$image_id = 0;
				}
				
				//Проверка уникальности имени файла
				$name = acmsFiles::unique( $values[$value_sid]['name'][$i], model::$config['path']['www'] . $dir_path );
				
				//Проверка корректности имени файла
				$name = acmsFiles::filename_filter( $name );
				
				//Расширение файла
				$ext = substr($name, strrpos($name, '.') + 1);
				
				//Загружаем файл
				$filename = acmsFiles::upload( $values[$value_sid]['tmp_name'][$i], model::$config['path']['www'] . $dir_path . '/' . $name );
				
				//Ужимаем до нужного размера и перезаписываем
				$acmsImages = new acmsImages;
				$data = $acmsImages->resize( $filename, false, $settings['resize_type'], @$settings['resize_width'], @$settings['resize_height'] );
				
				//Доп.характеристики
				$data['path'] = $dir_path . '/'. $name;
				$data['title'] = strip_tags( $values[$value_sid . '_title'][$i] );
				
				//Определяем основные цвета картинки
				$data['colors'] = $acmsImages->colors( $filename );
			
				//Делаем превьюшки
				if( IsSet( $settings['pre'] ) )
					foreach( $settings['pre'] as $sid => $pre){
						$pre_filename = str_replace( '.'.$ext, '_'.$sid.'.'.$ext, $filename );
						$data[ $sid ] = $dir_path . '/' . str_replace( '.'.$ext, '_'.$sid.'.'.$ext, basename($data['path']) );
						
						// Размер картинки
						$acmsImages->resize( $filename, $pre_filename, $pre['resize_type'], @$pre['resize_width'], @$pre['resize_height'] );
						
						//Фильтры - чёлно-белый
						if( is_array($values[$value_sid.'_filter']['bw']) )
						if( in_array($sid, $values[$value_sid.'_filter']['bw']) )
							$acmsImages->filter_bw($pre_filename);
						
					}
			//Файл не передан, просто обновление Alt
			} elseif ( strlen( $_POST[ $value_sid . '_old_id' ][ $i ] ) ) {
				$data = $this->getValueExplode( $_POST[ $value_sid . '_old_id' ][ $i ] );
				$data['title'] = strip_tags( $_POST[$value_sid . '_title'][$i] );
			} elseif ( strlen( $values[ $value_sid . '_old_id' ][ $i ] ) ) {
				$data = $this->getValueExplode( $values[$value_sid . '_old_id'][$i] );
				$data['title'] = strip_tags( $values[$value_sid . '_title'][$i] );
			}
			
			$images[] = $data;
		}
		
		$dirs = new acmsDirs();
		$dirs -> clearFolder( 
			model::$config['path']['images'].'/users/rec'.str_pad(user::$info['id'], 6, '0', STR_PAD_LEFT).'/tmp',
			array( 'thumb' )
		);
		
		//Готово
		if( count($images) )
			return serialize( $images );
		else
			return 0;
	}


	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		if( is_string($value) and $value)
			$result = unserialize( stripslashes( $value ) );
		
		if(is_array($result))
			$keys = array_keys($result);
		
		if ( is_array($result) and ( !is_array( $result[ $keys[0] ] ) ) ) {
			
			foreach ($result as $i => $val) {
				$result[$i] = array();
			
				//Данные
				list($result[$i]['path'], $result[$i]['type'], $result[$i]['size'], $result[$i]['width'], $result[$i]['height'], $result[$i]['title'], $result[$i]['realname']) = explode('|', $val);

				//ID
				$result[$i]['id'] = $val;


				//Превьюшки
				if (IsSet($settings['pre'])) {
					//Новое имя
					$name = substr($result[$i]['path'], 0, strrpos($result[$i]['path'], '.'));
					$ext  = substr($result[$i]['path'], strrpos($result[$i]['path'], '.') + 1);

					//Создаём данные о превьюшках
					foreach ($settings['pre'] as $sid => $pre) {
						$result[$i][$sid] = $name . '_' . $pre['sid'] . '.' . $ext;
					}
				}
			}
		}
		
		//Готово
		return $result;
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}

	//Проверяем, что поле имеет тим TEXT
	private function correctFieldType($module_sid, $structure_sid, $field_sid){
		$sql = 'select DATA_TYPE from information_schema.COLUMNS where TABLE_SCHEMA="'.model::$config['db']['system']['name'].'" and TABLE_NAME="'.model::$modules[ $module_sid ]->getCurrentTable($structure_sid).'" and COLUMN_NAME="'.$field_sid.'"';
		$res = model::execSql($sql, 'getrow');
		if( $res['DATA_TYPE'] != 'text' ){
			$sql = 'alter table `'.model::$modules[ $module_sid ]->getCurrentTable($structure_sid).'` modify '.$this->creatingString( $field_sid );
			$res = model::execSql($sql, 'update');
		}
	}
	
}

?>
