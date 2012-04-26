<?php

class acmsImageMask{

	//Загрузить фильтр для обрезания картинки
	public function readMask( $mask_file_path ){

		//Читаем картинку
		$img = file_get_contents( $mask_file_path );
		$img = imagecreatefromstring( $img );
/*
		//Выключаем палитру
		imagealphablending($img, false);
		//Включаем альфа-канал
		imagesavealpha($img, true);
*/
		//Получаем ячейку палитры с прозрачным цветом
		$transparent_index = imagecolortransparent( $img , 0x000000 );
		
		//Читаем маску
		$size = @getimagesize( $mask_file_path );
		$min_i = $size[0];
		$max_i = 0;
		$min_j = $size[1];
		$max_j = 0;
		$mask = array();
		for($i=0; $i<$size[0]; $i++)
			for($j=0; $j<$size[1]; $j++){
				
				//Ячейка цвета в палитре
				$index = imagecolorat($img, $i, $j);
				
				//Если в пикселе прозрачный цвет - запоминаем его в маске
				if( $index == $transparent_index ){
					//Запоминаем
					$this -> mask[$i][$j] = true;
					
					//Определяем размеры финального изображения
					$min_i = min($i, $min_i);
					$max_i = max($i, $max_i);
					$min_j = min($j, $min_j);
					$max_j = max($j, $max_j);
				}
			}
		
		//Размеры и смещение начала маски
		$this->mask_width = $max_i - $min_i;
		$this->mask_height = $max_j - $min_j;
		$this->delta_x = $min_i;
		$this->delta_y = $min_j;
	}
	
	public function cutImage($file_path){
		if( !$this -> mask )
			return false;
		
		//Читаем картинку
		$src = file_get_contents( $file_path );
		$src = imagecreatefromstring( $src );

		$new = imagecreatetruecolor($this->mask_width, $this->mask_height);
		imagealphablending($new, false);
		imagesavealpha($new, true);

		$back = imagecolorallocatealpha($new, 0, 0, 0, 127);
		imagefilledrectangle($new, 0, 0, $this->mask_width, $this->mask_height, $back);

		for( $i=0; $i<$this->mask_width; $i++ )
			for( $j=0; $j<$this->mask_height; $j++ )
				if( $this->mask[$i + $this->delta_x][$j + $this->delta_y] ){
					
					//Ячейка цвета в палитре
					$index = imagecolorat($src, $i + $this->delta_x, $j + $this->delta_y);
					$rgb = imagecolorsforindex($src, $index);

					$color = imagecolorallocatealpha($new, $rgb['red'], $rgb['green'], $rgb['blue'], 0 ); 
					imagesetpixel($new, $i, $j, $color);
				}
		
		return $new;
	}
	
}

/*
$filter = new imageMaskFilter();

//Получаем маску
$filter->readMask('mask.png');

//Обрезаем файт полученной маской
$new = $filter->cutImage('src.jpg');

imagepng($new, 'tmp.png');
chmod('tmp.png', 0777);
print('<div style="background:url(src.jpg) left top; padding:100px; text-align: center;"><img src="data:image/png;base64,'. base64_encode( file_get_contents('tmp.png') ).'" alt="" /></div>');
*/

?>