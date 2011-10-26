<?php

class acmsFiles{

	//Загрузка изображения
	public function upload($tmp, $path, $chmod = 0775){
		if( is_uploaded_file($tmp) )
			if( move_uploaded_file($tmp, $path) ){
					chmod($path, $chmod);
					return $path;
				}
		return false;
	}
	
	//Удаление файла
	public function delete($path){
		if( is_writable( $path ) )
			return unlink( $path );
		else
			return false;
	}

	//Проверка уникальнойсти файла
	public function unique($filename, $path){

		if( file_exists( $path . '/' . $filename ) ) {
			$name = substr($filename, 0, strrpos($filename, '.') );
			$ext  = substr($filename, strrpos($filename, '.') + 1);
			
			//Подставляем окончание
			$i        = 1;
			$new_name = $name . '.' . $ext;
			while (file_exists($path . '/' . $new_name) && ($i < 1000)) {
				$i++;
				$new_name = $name . '_' . $i . '.' . $ext;
			}
			
			//Новое имя готово
			return $new_name;
			
		//Файл и так уникальный
		}else
			return $filename;
	}
	
	//Проверка валидности символов в имени файла
	public function filename_filter($name){
		$name = mb_strtolower($name, 'utf-8');
		
		$name = str_replace(' ', '_', $name);
		$name = str_replace('__', '_', $name);
		$name = preg_replace( "/[^\da-zа-яё_\-.]/iu", '', $name);
		$name = translitIt($name);
		
		return $name;
	}

}

?>
