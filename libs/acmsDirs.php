<?php

class acmsDirs{

	function get_dirs($path, $current_dir = false, $level = 0, $limit = 1000){
		$files = array();
		if($path[strlen($path)-1]!='/')
			$path.='/';

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
			$sub = $this->get_dirs($path.$file.'/', $file, $level+1, $limit);
			//Добавляем
			$files = array_merge($files, $sub);

			$i++;
			}
		}

		return $files;
	}

	function get_files($path, $current_dir = false, $level = 0, $limit = 1000){
		$files = array();
		
		if($path[ strlen($path)-1 ] != '/')
			$path.='/';

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

	function makeFolder($path, $chmod = 0775){
		$dirs = explode('/', $path);
		$t = false;
		foreach($dirs as $dir)
			if( strlen($dir) ){
				$t .= '/'.$dir;
				if( !@file_exists($t) ){
					@mkdir( $t, $chmod );
					@chmod( $t, $chmod );
				}
			}
		return file_exists($path);
	}
	
	//Выбрать файлы по расширению
	function select_ext($ext, $files){
		$items = array();
		foreach($files as $file)if( $file['ext'] == $ext ){
			$items[] = $file['path'].$file['file']; 
		}
		return $items;
	}

	//Рекурсивное копирование
	function copy($dir_from, $dir_to, $level = 100, $limit = 1000000){
		@mkdir($dir_to, 0775, true);
		$dirs = $this->get_dirs($dir_from, basename($dir_from), $level, $limit);
		pr_r($dirs);
		exit();
		foreach($dirs as $dir){
			@mkdir($dir_to.$dir['file'], 0775, true);
			$files = $this->get_files($dir['path'].$dir['file'], $dir['file'], $level, $limit);
		}
	}
}

?>
