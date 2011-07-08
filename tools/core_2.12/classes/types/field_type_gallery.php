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

	public function creatingString($name)
	{
		return '`' . $name . '` text';
	}

	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values, $old_values = array(), $settings = false)
	{

		//Настройки поля, переданные из модуля
		if ($settings)
			foreach ($settings as $var => $val)
				$this->$var = $val;

		//Сюда будем скадывать картинки
		$result_id = array();

		//Если что-то было передано
		if ($values[$value_sid]['name']){

			foreach ($values[$value_sid]['name'] as $i => $name) {

				if ( strlen($values[$value_sid]['tmp_name'][$i])>0 ) {
					$image_id = 0;

					//Указан ID уже имеющейся картинки - будем обновлять
					$old_id = @$values[$value_sid . '_old_id'][$i];

					$values[$value_sid]['name'][$i] = strtolower($values[$value_sid]['name'][$i]);
					$values[$value_sid]['name'][$i] = str_replace('.jpeg', '.jpg', $values[$value_sid]['name'][$i]);
					$values[$value_sid]['name'][$i] = str_replace(' ', '_', $values[$value_sid]['name'][$i]);

					//Обновление картинки
					if ($old_id > 0) {
						//Старые данные
						list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname) = explode('|', $old_id);

						//Удаляем старые данные
						UnLink($this->model->config['path']['www'] . $old_path);
					}

					//Принимаем только файлы транслитом
					$values[$value_sid]['name'][$i] = str_replace(' ', '_', $values[$value_sid]['name'][$i]);
					$values[$value_sid]['name'][$i] = str_replace('__', '_', $values[$value_sid]['name'][$i]);
					$values[$value_sid]['name'][$i] = preg_replace( "/[^\da-zа-яё_\-.]/iu", '', $values[$value_sid]['name'][$i]);				// так красивее!
					$values[$value_sid]['name'][$i] = translitIt($values[$value_sid]['name'][$i]);

					//Если файл не уникален - делаем ему уникальное имя
					if (file_exists($this->model->config['path']['www'] . $this->model->config['path']['public_images'] . '/' . $values[$value_sid]['name'][$i])) {

						//Новое имя
						$name = substr($values[$value_sid]['name'][$i], 0, strrpos($values[$value_sid]['name'][$i], '.'));
						$ext  = substr($values[$value_sid]['name'][$i], strrpos($values[$value_sid]['name'][$i], '.') + 1);

						//Подставляем окончание
						$j        = 1;
						$new_name = $name;

						while (file_exists($this->model->config['path']['www'] . $this->model->config['path']['public_images'] . '/' . $new_name . '.' . $ext) && ($j < 1000)) {
							$j++;
							$new_name = $name . '_' . $j;
						}

						//Новое имя готово
						$values[$value_sid]['name'][$i] = $new_name . '.' . $ext;
					}

					//Новое имя
					$name = substr($values[$value_sid]['name'][$i], 0, strrpos($values[$value_sid]['name'][$i], '.'));
					$ext  = substr($values[$value_sid]['name'][$i], strrpos($values[$value_sid]['name'][$i], '.') + 1);

					//Создаём картинку

					$image_data = $this->makeImage($values[$value_sid]['tmp_name'][$i], $values[$value_sid]['type'][$i], $name);

					//Данные о картинке
					$image_id = $image_data['path'] . '|' . $values[$value_sid]['type'][$i] . '|' . $values[$value_sid]['size'][$i] . '|' . $image_data['size']['w'] . '|' . $image_data['size']['h'] . '|' . $values[$value_sid . '_title'][$i] . '|' . $values[$value_sid]['name'][$i];


					//Файл не передан, просто обновление Alt
				} elseif ($values[$value_sid . '_old_id'][$i]) {
					//Указан ID уже имеющейся картинки - будем обновлять
					$old_id = $values[$value_sid . '_old_id'][$i];

					//Старые данные
					list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname) = explode('|', $old_id);

					//Если указанный файл всёже существует
					if (file_exists($this->model->config['path']['www'] . $old_path)) {
						//Данные о картинке
						$image_id = $old_path . '|' . $old_type . '|' . $old_size . '|' . $old_width . '|' . $old_height . '|' . $values[$value_sid . '_title'][$i] . '|' . $old_realname;
					}

					//Картинки нет и небыло
				} else {
					$image_id = 0;
				}

				//Чистим резервное значение
				UnSet($values[$value_sid . '_old_id'][$i]);

				//Итого
				if ($image_id)
					$result_id[$i] = $image_id;
			}

			//Сохраняем старые фотки
			$result_id=array_merge($values[$value_sid . '_old_id'],$result_id);

		}

		//Удаление фотки
		if ($values[$value_sid.'_title'])
			foreach ($values[$value_sid.'_title'] as $i => $value)
				if($result_id[$i]){
					//Старые данные
					list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname) = explode('|', $result_id[$i]);
					//Новые данные
					$result_id[$i]=$old_path . '|' . $old_type . '|' . $old_size . '|' . $old_width . '|' . $old_height . '|' . $values[$value_sid . '_title'][$i] . '|' . $old_realname;
				}

		//Удаление фотки
		if ($values[$value_sid.'_delete']){
			foreach ($values[$value_sid.'_delete'] as $i => $value) {
				UnSet($result_id[$i]);
			}
		}

		//Всё остаётся по старому
		if (!count($result_id))
			$result = $old_values[$value_sid];

		//Готово
		else
			$result = serialize($result_id);

		return $result;
	}


	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{
		if (strlen($value)) {
			//Отдельные картинки
			$values = unserialize($value);
			if (is_array($values))
				foreach ($values as $i => $val) {
					//Данные
					list($recs[$i]['path'], $recs[$i]['type'], $recs[$i]['size'], $recs[$i]['width'], $recs[$i]['height'], $recs[$i]['title'], $recs[$i]['realname']) = explode('|', $val);

					//ID
					$recs[$i]['id'] = $val;


					//Превьюшки
					if (IsSet($settings['pre'])) {
						//Новое имя
						$name = substr($recs[$i]['path'], 0, strrpos($recs[$i]['path'], '.'));
						$ext  = substr($recs[$i]['path'], strrpos($recs[$i]['path'], '.') + 1);

						//Создаём данные о превьюшках
						foreach ($settings['pre'] as $sid => $pre) {
							$recs[$i][$sid] = $name . '_' . $pre['sid'] . '.' . $ext;
						}
					}
				}
		}

		//Готово
		return $recs;
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
	private function resizeImage($path, $type)
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
				$to = $path;
				$to = str_replace('.jpg', '_' . $pre['sid'] . '.jpg', $to);
				$to = str_replace('.gif', '_' . $pre['sid'] . '.gif', $to);
				$to = str_replace('.png', '_' . $pre['sid'] . '.png', $to);
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

ImageAlphaBlending($src, true);

$transparentcolor = imagecolortransparent($src);
imagefill($dst,0,0,$transparentcolor);
imagecolortransparent($dst,$transparentcolor);

imagesavealpha($dst, true);

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

}

?>
