<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс работы с Архивами ZIP							*/
/*															*/
/*	Версия ядра 2.01										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 17 ноября 2009	года							*/
/*	Модифицирован: 17 ноября 2009 года						*/
/*															*/
/************************************************************/

class zipper{
	
	public function __construct($config){
		$this->config=$config;
	}
	
	//Распаковываем архив в папку
	public function unzip( 
			$archive_path, 
			$to_folder=false, 
			$extensions=array('jpg','jpeg','gif','png') 
		){
		
		//Если папка не указана - размещаем во временной папке
		if(!$to_folder)$to_folder=$this->config['tmp'];

		//Коллекционируем файлы, извлечённые из архива
		$files=array();
	
		//Проверка существования папки
		if(!is_dir($to_folder)){pr('Папка для распаковки архива не найдена. ['.$to_folder.']');exit();}
		//Выставляем досутп на папку
		chmod($to_folder,0775);
		
		//Открываем архив
		$f=zip_open($archive_path);
		//Каждый файл
		while($entry=zip_read($f)){
			//Открываеи
			if(zip_entry_open($f,$entry,'r')){
				//Читаем
				$buf=zip_entry_read($entry,zip_entry_filesize($entry));
				//Имя файла
				$filename=basename(zip_entry_name($entry));
				//Другая инфа о файле
				$finfo=pathinfo($filename);
				//Расширение
				$finfo['extension']=strtolower($finfo['extension']);
				//Проверяем расширение
				if( in_array($finfo['extension'],$extensions) ){
					//Сочиняем новое имя
					$fname=substr($filename, 0, strrpos($filename,'.') );
					//Полный путь
					$filepath=$to_folder.'/'.$fname.'.'.$finfo['extension'];
					//Копируем
					$of=fopen($filepath,'w');
						fwrite($of,$buf);
						fclose($of);
					
					//Выставляем доступ
					chmod($filepath,0775);
						
					//Учитываем результат распаковки
					$files[]=array(
						'inzip_name'=>$filename,
						'filename'=>$fname.'.'.$finfo['extension'],
						'name'=>$fname,
						'extension'=>$finfo['extension'],
						'folder'=>$to_folder,
						'size'=>filesize($to_folder.'/'.$fname.'.'.$finfo['extension']),
					);
				}
				//Закрываем файл
				zip_entry_close($entry);
			}
		}
		//Закрываем архив
		zip_close($f);
		
		//Готово
		return $files;
	}
	
}

?>