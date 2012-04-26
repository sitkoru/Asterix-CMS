<?php

class acmsImages{

	/*
		Типы изменения размера
		
		inner 		- вписываем в указанную область
		outer 		- описываем вокруг указанной области
		width 		- подгоняем по ширине
		height 		- подгоняем по высоте
		exec 		- подгоняем под указанные размеры, без соблюдения соотношения сторон
	*/
	public function resize( $src_path, $dest_path = false, $resize_type = 'inner', $resize_width = 400, $resize_heigth = 300, $chmod = 0775){
	
		//Читаем размер рисунка
		$size           = GetImageSize($src_path);
		$current_width  = $size[0];
		$current_height = $size[1];
		$type 			= $size['mime'];
		
		//Если не указано другого пути - перезаписываем изображение
		if( !$dest_path )
			$dest_path = $src_path;
			
		//Меняем размеры основного изображения
		list($new_width, $new_height) = $this->newSize($current_width, $current_height, $resize_type, $resize_width, $resize_heigth);
		
		//Нужно менять размер
		if( $new_width && $new_height ){
			//Читаем картинку
			$src = file_get_contents( $src_path );
			$src = ImageCreateFromString( $src );

			//Новая картинка
			$dst = ImageCreateTrueColor($new_width, $new_height);

			//Включаем прозрачность для GIF и PNG
			if( in_array( $size['mime'], array('image/gif', 'image/png') ) ){
				$transparentcolor = imagecolortransparent($src);
				imagefill($dst,0,0,$transparentcolor);
				imagecolortransparent($dst,$transparentcolor);
				imagesavealpha($dst, true);
			}
			
			//Копируем изображение
			ImageCopyResampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $current_width, $current_height);

			//Сохраняем
			if($type == 'image/jpeg')
				ImageJpeg($dst, $dest_path, 100);
			elseif ($type == 'image/gif')
				ImageGif($dst, $dest_path);
			elseif ($type == 'image/png')
				ImagePng($dst, $dest_path, 1);
		
			//Освобождаем память
			ImageDestroy($src);
			ImageDestroy($dst);

			//Разрешения на файл
			chmod($dest_path, $chmod);
		
		//Не нужно менять размер - просто копируем
		}else{
			copy( $src_path, $dest_path );
			chmod( $dest_path, $chmod );
		}
		
		//Готово
		return array(
			'width' 	=> $new_width,
			'height' 	=> $new_height,
			'size' 		=> filesize($dest_path),
		);
		
	}

	//Получить список основных цветов картинки
	public function colors( $path, $num_or_colors = 5, $step = 5 ){
		require_once( 'getImageColor.php' );
		$img = new GeneratorImageColorPalette();
		$colors = $img->getImageColor( $path, $num_or_colors, $step );
		return array_keys( $colors );
	}

	//Сделать изображение чёрно-белым
	public function filter_bw( $src_path, $dest_path = false ){
	
		//Читаем размер рисунка
		$size           = GetImageSize($src_path);
		$current_width  = $size[0];
		$current_height = $size[1];
		$type 			= $size['mime'];
		
		//Читаем картинку
		$src = file_get_contents( $src_path );
		$src = ImageCreateFromJpeg( $src_path );

		for ($i=0; $i<$current_width; $i++)
			for ($j=0; $j<$current_height; $j++){
                $rgb = ImageColorAt($src, $i, $j); 
                $rr = ($rgb >> 16) & 0xFF;
                $gg = ($rgb >> 8) & 0xFF;
                $bb = $rgb & 0xFF;
                $g = round(($rr + $gg + $bb) / 3);
                $val = imagecolorallocate($src, $g, $g, $g);
                imagesetpixel ($src, $i, $j, $val);
			}

		//Если не указано другого пути - перезаписываем изображение
		if( !$dest_path )
			$dest_path = $src_path;
		
		//Сохраняем
		if($type == 'image/jpeg')
			ImageJpeg($src, $dest_path, 100);
		elseif ($type == 'image/gif')
			ImageGif($src, $dest_path);
		elseif ($type == 'image/png')
			ImagePng($src, $dest_path, 1);
	
		//Освобождаем память
		ImageDestroy($src);

	}

	//Обрезать файл по маске
	public function cut_mask( $src_path, $dest_path = false, $mask ){
		
		//Обрезаем
		require_once 'acmsImageMask.php';
		$filter = new acmsImageMask();
		$filter->readMask( $mask['tmp_name'] );
		$src = $filter->cutImage( $src_path );
		
		//Если не указано другого пути - перезаписываем изображение
		if( !$dest_path )
			$dest_path = $src_path;
		
		//Сохраняем
		$dest_path = substr($dest_path, 0, strrpos($dest_path, '.')) . '.png';
		ImagePng($src, $dest_path, 1);
	
		//Освобождаем память
		ImageDestroy($src);

		//Сохраняем
		chmod($dest_path, 0775);

		return $dest_path;
	}
	
	//Добавить возный знак на картинку
	public function put_watermark( $src_path, $dest_path = false, $watermark, $side = 'rb', $chmod = 0775){

		//Читаем картинку
		$src = file_get_contents( $src_path );
		$src = imagecreatefromstring( $src );
		$size       = GetImageSize( $src_path );
		$src_width  = $size[0];
		$src_height = $size[1];
		$type = $size['mime'];
		
		//Результирующая картинка
		$dst = imagecreatetruecolor($src_width, $src_height);
		imagesavealpha($dst, true);
		imagecopy(
			$dst,
			$src,
			0,
			0,
			0,
			0,
			$src_width,
			$src_height
		);
		
		//Читаем Watermark
		$wmk = imagecreatefrompng( $watermark['tmp_name'] );
		$size       = GetImageSize( $watermark['tmp_name'] );
		$wmk_width  = $size[0];
		$wmk_height = $size[1];

		//Куда клеим
		if($side == 'rb'){
			$start_x = $src_width - $wmk_width;
			$start_y = $src_height - $wmk_height;
		}elseif($side == 'rt'){
			$start_x = $src_width - $wmk_width;
			$start_y = 0;
		}elseif($side == 'lb'){
			$start_x = 0;
			$start_y = $src_height - $wmk_height;
		}elseif($side == 'lt'){
			$start_x = 0;
			$start_y = 0;
		}elseif($side == 'cc'){
			$start_x = round( $src_width/2 - $wmk_width/2 );
			$start_y = round( $src_height/2 - $wmk_height/2 );
		}
		
		//Копируем
		imagecopy(
			$dst,
			$wmk,
			$start_x,
			$start_y,
			0,
			0,
			$wmk_width,
			$wmk_height
		);
		
		//Если не указано другого пути - перезаписываем изображение
		if( !$dest_path )
			$dest_path = $src_path;

		//Сохраняем
		if($type == 'image/jpeg')
			ImageJpeg($dst, $dest_path, 100);
		elseif ($type == 'image/gif')
			ImageGif($dst, $dest_path);
		elseif ($type == 'image/png')
			ImagePng($dst, $dest_path, 1);

		//Разрешения на файл
		chmod($dest_path, $chmod);
		
		//Освобождаем память
		ImageDestroy($src);
		ImageDestroy($dst);
		ImageDestroy($wmk);
	}

	//Поределяем финальные размеры изображения по заданным настройкам
	private function newSize($current_width, $current_height, $resize_type, $resize_width, $resize_height){
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
				$new_width  = false;
				$new_height = false;
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
				$new_width  = false;
				$new_height = false;
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
				$new_width  = false;
				$new_height = false;
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
				$new_width  = false;
				$new_height = false;
			}
			
		//exec
		} elseif ($resize_type == 'exec') {
			//Нужно менять
			if ($current_width != $resize_width || $current_height != $resize_height) {
				$new_width  = $resize_width;
				$new_height = $resize_height;
			//Оставляем как есть
			}else{
				$new_width  = false;
				$new_height = false;
			}
		}
		
		if( $new_width ) $new_width = round($new_width);
		if( $new_height ) $new_height = round($new_height);
		
		//Готово
		return array($new_width,$new_height);
	}
	
}
?>