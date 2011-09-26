<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Библиотека работы с директориями					*/
/*															*/
/*	Версия ядра 2.12										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009-2011  Мишин Олег						*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 21 июля 2011 года								*/
/*	Модифицирован: 21 июля 2011 года						*/
/*															*/
/************************************************************/

class forders
{

	function __construct($path){
		$this->root_path = $path;
	}

	function getDirs($path = $this->root_path, $current_dir = $this->root_path, $level = false, $limit = 1){
		$files = array();

		$f = opendir($path);
		$i=0;
		while ( (($file = readdir($f)) !== false) and ($i<$limit) )
		if( !in_array($file, array('.','..')) ){

			//Тип записи
			$type = filetype($path . $file);

			//Если директория - рекурсивно читаем её
			if($type == 'dir'){
				//Добавляем
				$files[] = array(
					'file' => $file,
					'dir' => $current_dir,
					'path' => $path,
				);

				//Рекурсия
				$sub = get_dirs($path.$file.'/', $file, $level+1, $limit);
				//Добавляем
				$files = array_merge($files, $sub);

				$i++;
			}
		}

		return $files;
	}

	function getFiles($path = $this->root_path, $current_dir = $this->root_path, $level = false, $limit = 100){
		$files = array();

		$f = opendir($path);
		$i=0;
		while ( (($file = readdir($f)) !== false) and ($i<$limit) )
		if( !in_array($file, array('.','..')) ){

			//Тип записи
			$type = filetype($path . $file);

			$sub = false;
			$ext = false;

			//Если директория - рекурсивно читаем её
			if($type != 'dir'){

				$info = pathinfo($path.$file);
				$ext = $info['extension'];
				
				//Добавляем
				$files[] = array(
					'file' => $file,
					'dir' => $current_dir,
					'path' => $path,
					'ext' => $ext,
				);
			}

			$i++;
		}

		return $files;
	}

	//Выбрать файлы по расширению
	function selectExt($ext, $files){
	  $items = array();
	  foreach($files as $file)if( $file['ext'] == $ext ){
		$items[] = $file['path'].$file['file']; 
	  }
	  return $items;
	}

}
?>