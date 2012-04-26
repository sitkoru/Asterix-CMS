<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Файл									*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 18 марта 2010 года						*/
/*															*/
/************************************************************/

class field_type_file extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Файл', 'value' => 0, 'width' => '100%', /*
	inner - по минимальному размеру (изображение не более указанных размеров)
	outer - по максимальному разделу  (изобажение не меньше указанных размеров)
	width - по ширине (изображение по ширине соответствует указанному размеру)
	height - по высоте (изображение по высоте соответствует указанному размеру)
	exec - по указанным размерам (изображение соответствует указанным размерам, без сохранения пропорций)
	*/ 'resize_type' => 'inner', 'resize_width' => 250, 'resize_height' => 250, 'resize_proportions' => true);

	//Разрешённые форматы файлов для загрузки
	private $allowed_extensions = array(
		'image/jpeg' => 'jpg',
		'image/gif' => 'gif',
		'image/png' => 'png',
		'image/x-icon' => 'ico',
		'application/msword' => 'doc',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
		'application/octet-stream' => 'docx',
		'xls' => 'xls',
		'application/vnd.ms-excel' => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
		'application/vnd.ms-powerpoint' => 'ppt',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/pdf' => 'pdf',
		'application/vnd.oasis.opendocument.text' => 'odt',
		'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
		'text/plain' => 'txt',
		'text/xml' => 'xml',
		'application/x-zip-compressed' => 'zip',
		'7z' => '7z',
		'application/x-shockwave-flash'=>'swf'
	);

	public $template_file = 'types/file.tpl';

	private $table = 'images';

	//Поле участвует в поиске
	public $searchable = false;

	public function creatingString($name)
	{
		return '`' . $name . '` VARCHAR(255) NOT NULL';
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
			list($old_path, $old_type, $old_size) = explode('|', $values[$value_sid . '_old_id']);

			//Удаляем старые данные
			UnLink($this->model->config['path']['www'] . $old_path);

			//Обнуляем картинку
			$image_id = 0;

		//Файл передан
		} elseif (strlen($values[$value_sid]['tmp_name'])) {
			$image_id = 0;

			//Указан ID уже имеющейся картинки - будем обновлять
			$old_id = @$values[$value_sid . '_old_id'];

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
			if (file_exists($this->model->config['path']['www'] . $this->model->config['path']['public_files'] . $values[$value_sid]['name'])) {
				//Новое имя
				$name = substr($values[$value_sid]['name'], 0, strrpos($values[$value_sid]['name'], '.'));
				$ext  = substr($values[$value_sid]['name'], strrpos($values[$value_sid]['name'], '.') + 1);

				//Подставляем окончание
				$i        = 1;
				$new_name = $name . '.' . $ext;
				while (!file_exists($this->model->config['path']['www'] . $this->model->config['path']['public_files'] . $new_name) && ($i < 1000)) {
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
			$image_data = $this->makeFile($values[$value_sid]['tmp_name'], $values[$value_sid]['type'], $name);

			//Данные о картинке
			$image_id = $image_data['path'] . '|' . $values[$value_sid]['type'] . '|' . $values[$value_sid]['size'] . '|' . $image_data['size']['w'] . '|' . $image_data['size']['h'] . '|' . $values[$value_sid . '_title'] . '|' . $values[$value_sid]['name'];


			//Файл не передан, просто обновление Alt
		} elseif (strlen($values[$value_sid . '_old_id'])) {
			//Указан ID уже имеющейся картинки - будем обновлять
			$old_id = $values[$value_sid . '_old_id'];

			//Старые данные
			list($old_path, $old_type, $old_size, $old_width, $old_height, $old_alt, $old_realname) = explode('|', $old_id);

			//Если указанный файл всёже существует
			if (file_exists($this->model->config['path']['www'] . $old_path)) {
				//Данные о картинке
				$image_id = $old_path . '|' . $old_type . '|' . $old_size;
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
		if (strlen($value)) {
			$rec = array();

			//Данные
			list($rec['path'], $rec['type'], $rec['size']) = explode('|', $value);

			//ID
			$rec['id'] = $value;
		}

		//Готово
		return $rec;
	}

	//Получить развёрнутое значение для системы управления из простого значения
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		$res = $this->getValueExplode($value, $settings, $record);
		return $res;
	}






	//Создаём картинку из пришедших данных
	private function makeFile($tmp, $type, $new_name)
	{
		//Загружаем изображение
		$path = $this->uploadFile($tmp, $type, $new_name);
		if (!$path)
			return false;

		//Публичный адрес//application/octet-stream
		$public_path = $this->model->config['path']['public_files'] . '/' . $new_name . '.' . $this->allowed_extensions[$type];

		//Готово
		return array(
			'path' => $public_path,
			'size' => $size
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
				if (move_uploaded_file($tmp, $this->model->config['path']['www'] . $this->model->config['path']['public_files'] . '/' . $new_name . '.' . $this->allowed_extensions[$type])) {
					//Ставим доступ
					chmod($this->model->config['path']['www'] . $this->model->config['path']['public_files'] . '/' . $new_name . '.' . $this->allowed_extensions[$type], 0775);

					//Готово
					return $this->model->config['path']['www'] . $this->model->config['path']['public_files'] . '/' . $new_name . '.' . $this->allowed_extensions[$type];
				} else {
					pr('upload error 3');
					exit();
				}
			} else {
				pr('upload error: wrong extension [' . $type . ']');
				exit();
			}
		} else {
			pr('upload error 1');
			exit();
		}
		//some shit happens
		return false;
	}
}

?>
