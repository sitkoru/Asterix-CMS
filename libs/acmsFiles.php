<?php

class acmsFiles{

	//Загрузка изображения
	public function upload($tmp, $path, $chmod = 0775, $allowed_extensions = false){
		
		// Закаченный файл
		if( is_uploaded_file( $tmp ) ){

			if( is_array( $allowed_extensions ) ) {
				$name = basename( $path );
				$ext  = substr( $name, strrpos( $name, '.' )+1 );
				if( !in_array( $ext, $allowed_extensions ) )
					return false;
			}

			if( move_uploaded_file($tmp, $path) ){
				chmod($path, $chmod);
				return $path;
			}
		
		// Файл из интернета
		}elseif( substr_count( $tmp, 'http://' ) ){
			$content = file_get_contents( $tmp );
			$f = fopen( $path, 'w' );
			fwrite( $f, $content );
			fclose( $f );
			chmod($path, $chmod);
			return $path;
		
		// Файл с локального сервера
		}elseif( is_readable( $tmp ) ){
			copy($tmp, $path);
			chmod($path, $chmod);
			return $path;
		}
		
		return false;
	}
	
	//Удаление файла
	public function delete($path){
		if( is_writable( $path ) and is_file( $path ) )
			return @unlink( $path );
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
		$name = self::translitIt($name);
		
		return $name;
	}
	public static function translitIt($str){
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
			" "=> "_", "/"=> "_"
		);
		return strtr($str,$tr);
	}

}

?>
