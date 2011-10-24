<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Фотоизображение						*/
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

class field_type_image extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Картинка', 'value' => 0, 'width' => '100%', /*
	inner - по минимальному размеру (изображение не более указанных размеров)
	outer - по максимальному разделу  (изобажение не меньше указанных размеров)
	width - по ширине (изображение по ширине соответствует указанному размеру)
	height - по высоте (изображение по высоте соответствует указанному размеру)
	exec - по указанным размерам (изображение соответствует указанным размерам, без сохранения пропорций)
	*/ 'resize_type' => 'inner', 'resize_width' => 250, 'resize_height' => 250, 'resize_proportions' => true);
	
	//Разрешённые форматы файлов для загрузки
	private $allowed_extensions = array('image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/png' => 'png');
	
	public $template_file = 'types/image.tpl';
	
	//Поле участввует в поиске
	public $searchable = false;
	
	public function creatingString($name)
	{
		return '`' . $name . '` text not null';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{
	
		//Настройки поля, переданные из модуля
		if ($settings)
			foreach ($settings as $var => $val)
				$this->$var = $val;
		
		//Удаление фотки
		if ($values[$value_sid . '_delete']) {
			//Старые данные
			list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname) = explode('|', $values[$value_sid . '_old_id']);
			
			//Удаляем старые данные
			UnLink($this->model->config['path']['www'] . $old_path);
			
			//Обнуляем картинку
			$image_id = 0;
			
		//Файл передан
		} elseif (strlen($values[$value_sid]['tmp_name'])) {
			$image_id = 0;
			
			//Указан ID уже имеющейся картинки - будем обновлять
			$old_id = @$values[$value_sid . '_old_id'];
			
			$values[$value_sid]['name'] = mb_strtolower($values[$value_sid]['name'], 'utf-8');
			$values[$value_sid]['name'] = str_replace('.jpeg', '.jpg', $values[$value_sid]['name']);
			$values[$value_sid]['name'] = str_replace(' ', '_', $values[$value_sid]['name']);
			
			//Обновление картинки
			if ($old_id > 0) {
				//Старые данные
				list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname) = explode('|', $old_id);
				
				//Удаляем старые данные
				UnLink($this->model->config['path']['www'] . $old_path);
			}
			
			//Принимаем только файлы транслитом
			$values[$value_sid]['name'] = str_replace(' ', '_', $values[$value_sid]['name']);
			$values[$value_sid]['name'] = str_replace('__', '_', $values[$value_sid]['name']);
			$values[$value_sid]['name'] = preg_replace( "/[^\da-zа-яё_\-.]/iu", '', $values[$value_sid]['name']);				// так красивее!
			$values[$value_sid]['name'] = translitIt($values[$value_sid]['name']);
				
			//Если файл не уникален - делаем ему уникальное имя
			if (file_exists($this->model->config['path']['www'] . $this->model->config['path']['public_images'] . '/' . $values[$value_sid]['name'])) {
				//Новое имя
				$name = substr($values[$value_sid]['name'], 0, strrpos($values[$value_sid]['name'], '.'));
				$ext  = substr($values[$value_sid]['name'], strrpos($values[$value_sid]['name'], '.') + 1);
				
				//Подставляем окончание
				$i        = 1;
				$new_name = $name . '.' . $ext;
				while (file_exists($this->model->config['path']['www'] . $this->model->config['path']['public_images'] . '/' . $new_name) && ($i < 1000)) {
					$i++;
					$new_name = $name . '_' . $i . '.' . $ext;
				}
				
				//Новое имя готово
				$values[$value_sid]['name'] = $new_name;
				
			}
			
			//Новое имя
			$name = substr($values[$value_sid]['name'], 0, strrpos($values[$value_sid]['name'], '.'));
			$ext  = substr($values[$value_sid]['name'], strrpos($values[$value_sid]['name'], '.') + 1);
			
			//Создаём картинку
			$image_data = $this->makeImage($values[$value_sid]['tmp_name'], $values[$value_sid]['type'], $name);
			
			//Определяем превалирующие цвета
			$colors = $this->getMainColors($this->model->config['path']['www'] . $image_data['path']);
			
			//Данные о картинке
			$image_id = $image_data['path'] . '|' . $values[$value_sid]['type'] . '|' . $values[$value_sid]['size'] . '|' . $image_data['size']['w'] . '|' . $image_data['size']['h'] . '|' . $values[$value_sid . '_title'] . '|' . $values[$value_sid]['name'] . '|' . implode(',', $colors);
			
		//Файл не передан, просто обновление Alt
		} elseif (strlen($values[$value_sid . '_old_id'])) {
			//Указан ID уже имеющейся картинки - будем обновлять
			$old_id = $values[$value_sid . '_old_id'];
			
			//Старые данные
			list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname, $old_colors) = explode('|', $old_id);
			
			//Если указанный файл всёже существует
			if (file_exists($this->model->config['path']['www'] . $old_path)) {
				//Данные о картинке
				$image_id = $old_path . '|' . $old_type . '|' . $old_size . '|' . $old_width . '|' . $old_height . '|' . $values[$value_sid . '_title'] . '|' . $old_realname . '|' . $old_colors;
			}
			
			//Картинки нет и небыло
		} else {
			$image_id = 0;
		}

		//Готово
		return $image_id;
	}
	
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		if ($value) {
			$rec = array();
			
			//Данные
			if(!is_array($value)){
				list($rec['path'], $rec['type'], $rec['size'], $rec['width'], $rec['height'], $rec['title'], $rec['realname'], $rec['colors']) = explode('|', $value);
				$rec['colors'] = explode(',', $rec['colors']);
			}
			
			//ID
			$rec['id'] = $value;
			
			//Превьюшки
			if (IsSet($settings['pre'])) {
				//Новое имя
				$name = substr($rec['path'], 0, strrpos($rec['path'], '.'));
				$ext  = substr($rec['path'], strrpos($rec['path'], '.') + 1);
				
				//Создаём данные о превьюшках
				foreach ($settings['pre'] as $sid => $pre) {
					$rec[$sid] = $name . '_' . $sid . '.' . $ext;
				}
			}
		} else {
			$rec = false;
		}
		
		//Готово
		return $rec;
	}
	
	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}
	
	
	
	
	//Создаём картинку из пришедших данных
	private function makeImage($tmp, $type, $new_name)
	{
		//Загружаем изображение
		$path = $this->uploadFile($tmp, $type, $new_name);
		if (!$path)
			return false;
		
		//Публичный адрес
		$public_path = $this->model->config['path']['public_images'] . '/' . $new_name . '.' . $this->allowed_extensions[$type];
		
		//Изменение размена
		$size = $this->resizeImage($path, $type);
		
		//Готово
		return array(
			'path' => $public_path,
			'size' => $size
		);
	}
	
	//Изменить размеры
	public function resizeImage($path, $type)
	{
		//Читаем размер рисунка
		$size           = GetImageSize($path);
		$current_width  = $size[0];
		$current_height = $size[1];
		
		$res = false;
		
		//Меняем размеры основного изображения
		list($new_width, $new_height) = $this->getNewSize($current_width, $current_height, @$this->resize_type, @$this->resize_width, @$this->resize_height);
		$res = $this->saveImage($path, $path, $type, $new_width, $new_height, $current_width, $current_height);
		
		//Превьюшки
		if (is_array($this->pre))
			foreach ($this->pre as $sid => $pre) {
				//Меняем размеры основного изображения
				list($pre_new_width, $pre_new_height) = $this->getNewSize($new_width, $new_height, @$pre['resize_type'], @$pre['resize_width'], @$pre['resize_height']);
				$to = str_replace('.' . $this->allowed_extensions[$type], '_' . $sid . '.' . $this->allowed_extensions[$type], $path);
				$this->saveImage($path, $to, $type, $pre_new_width, $pre_new_height, $new_width, $new_height);
			}
		
		//Готово
		return array(
			'w' => @$new_width,
			'h' => @$new_height
		);
	}
	
	//Загрузка файла
	private function uploadFile($tmp, $type, $new_name)
	{
		//Файл загружен
		if (is_uploaded_file($tmp)) {
			//Если формат файла размещёт
			if (IsSet($this->allowed_extensions[$type])) {
				//Сохраняем его во временное хранилище
				if (move_uploaded_file($tmp, $this->model->config['path']['images'] . '/' . $new_name . '.' . $this->allowed_extensions[$type])) {
					//Ставим доступ
					chmod($this->model->config['path']['images'] . '/' . $new_name . '.' . $this->allowed_extensions[$type], 0775);
					
					//Готово
					return $this->model->config['path']['images'] . '/' . $new_name . '.' . $this->allowed_extensions[$type];
				}
			}
			
			//Указан уже имеющийся файл, который является картинкой
		} else {
			//Получаем описание файла
			$mime = @getimagesize($tmp);
			//Проверяем формат
			if (IsSet($this->allowed_extensions[$mime['mime']])) {
				copy($tmp, $this->model->config['path']['images'] . '/' . $new_name . '.' . $this->allowed_extensions[$type]);
				return $this->model->config['path']['images'] . '/' . $new_name . '.' . $this->allowed_extensions[$type];
			}
		}
		
		//some shit happens
		return false;
	}
	
	//Поределяем финальные размеры изображения по заданным настройкам
	private function getNewSize($current_width, $current_height, $resize_type, $resize_width, $resize_height)
	{
		//inner
		if ($resize_type == 'inner') {
			//Нужно менять
			if ($current_width > $resize_width || $current_height > $resize_height) {
				$ratio_x = $current_width / $resize_width;
				$ratio_y = $current_height / $resize_height;
				if ($ratio_x > $ratio_y) {
					$new_width  = $current_width / $ratio_x;
					$new_height = $current_height / $ratio_x;
				} else {
					$new_width  = $current_width / $ratio_y;
					$new_height = $current_height / $ratio_y;
				}
				
				//Оставляем как есть
			} else {
				$new_width  = $current_width;
				$new_height = $current_height;
			}
			
			//outer
		} elseif ($resize_type == 'outer') {
			//Нужно менять
			if ($current_width > $resize_width && $current_height > $resize_height) {
				$ratio_x = $current_width / $resize_width;
				$ratio_y = $current_height / $resize_height;
				if ($ratio_x < $ratio_y) {
					$new_width  = $current_width / $ratio_x;
					$new_height = $current_height / $ratio_x;
				} else {
					$new_width  = $current_width / $ratio_y;
					$new_height = $current_height / $ratio_y;
				}
				
				//Оставляем как есть
			} else {
				$new_width  = $current_width;
				$new_height = $current_height;
			}
			
			//width
		} elseif ($resize_type == 'width') {
			//Нужно менять
			if ($current_width > $resize_width) {
				$new_width  = $resize_width;
				$ratio      = $current_width / $resize_width;
				$new_height = $current_height / $ratio;
				
				//Оставляем как есть
			} else {
				$new_width  = $current_width;
				$new_height = $current_height;
			}
			
			//height
		} elseif ($resize_type == 'height') {
			//Нужно менять
			if ($current_height > $resize_height) {
				$new_height = $resize_height;
				$ratio      = $current_height / $resize_height;
				$new_width  = $current_width / $ratio;
				
				//Оставляем как есть
			} else {
				$new_width  = $current_width;
				$new_height = $current_height;
			}
			
			//exec
		} elseif ($resize_type == 'exec') {
			$new_width  = $resize_width;
			$new_height = $resize_height;
		}
		
		//Готово
		return array(
			round($new_width),
			round($new_height)
		);
	}
	
	//Сохраняем изображение по готовым размерам
	private function saveImage($path, $to, $type, $new_width, $new_height, $current_width, $current_height)
	{
		//JPG
		if ($type == 'image/jpeg') {
			$src = ImageCreateFromJpeg($path);
			$dst = ImageCreateTrueColor($new_width, $new_height);
			ImageCopyResampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $current_width, $current_height);
			$res = ImageJpeg($dst, $to, 100);
			ImageDestroy($src);
			ImageDestroy($dst);
			
			//GIF
		} elseif ($type == 'image/gif') {
			$src = ImageCreateFromGif($path);
			$dst = ImageCreateTrueColor($new_width, $new_height);
/*
ImageAlphaBlending($src, true);

$transparentcolor = imagecolortransparent($src);
imagefill($dst,0,0,$transparentcolor);
imagecolortransparent($dst,$transparentcolor);

imagesavealpha($dst, true);
*/
			ImageCopyResampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $current_width, $current_height);
			$res = ImageGif($dst, $to);
			ImageDestroy($src);
			ImageDestroy($dst);
			
			//PNG
		} elseif ($type == 'image/png') {
			$src = ImageCreateFromPng($path);
			$dst = ImageCreateTrueColor($new_width, $new_height);

ImageAlphaBlending($src, true);

$transparentcolor = imagecolortransparent($src);
imagefill($dst,0,0,$transparentcolor);
imagecolortransparent($dst,$transparentcolor);

imagesavealpha($dst, true);

			ImageCopyResampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $current_width, $current_height);
			$res = ImagePng($dst, $to, 1);
			ImageDestroy($src);
			ImageDestroy($dst);
		}
	
		//Разрешения на файл
		chmod($to, 0775);
		
		//Готово
		return $res;
	}

	//Получить список превалирующих в фотографии цветов
	private function getMainColors($path, $num_or_colors = 5, $step = 5){
		
		require_once($this->model->config['path']['libraries'].'/getImageColor_tmp.php');
		$img = new GeneratorImageColorPalette();
		
		//Получаем цвета
		$colors = $img->getImageColor( $path, $num_or_colors, $step );
		return array_keys( $colors );

	}
	
}

?>
