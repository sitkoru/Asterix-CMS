<?php

class captcha{
	
	public function __construct($config){
		$this->config=$config;
	}
	
	public function generate($color=array('red'=>255,'green'=>0,'blue'=>0),$bg_color=array('red'=>255,'green'=>255,'blue'=>255) ){
		
		
		$width=150;
		$height=60;
		
		$image=$this->getBlank($width,$height,$bg_color);
		
		$angle=0;
		$x=30;
		$y=45;
		$font_color=imagecolorallocate($image, $color['red'], $color['green'], $color['blue']);
		$back_color=imagecolorallocate($image, $bg_color['red'], $bg_color['green'], $bg_color['blue']);
		$font=$this->config['path']['libraries'].'/arial.ttf';

		$l=$width/5;

		for($i=0;$i<4;$i++){
			for($j=0;$j<20;$j++){
				$letter=rand(0,9);
				$angle=rand(0,360);
				imagettftext($image, 30, $angle, ($width/10*($i*2+1)), $y-15, $font_color, $font, $letter);		
			}
		}

		$code='';
		if(!IsSet($_SESSION['form_captcha_code'])){
			$code='';
			for($i=0;$i<4;$i++)$code.=rand(0,9);
		}else{
			$code=$_SESSION['form_captcha_code'];
		}
		
		for($i=0;$i<strlen($code);$i++){
			$letter=$code[$i];
			$angle=rand(-20,20);
			imagettftext($image, 30, $angle, ($width/10*($i*2+1))+5, $y, $back_color, $font, $letter);		
		}

		//Noize
		for($i=0;$i<3;$i++){
			$this->imagelinethick($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $font_color, 3);
		}

		imagerectangle($image, ($width/10*1), ($height/4*3), ($width/10*3), ($height/4*1), $back_color);
		imagerectangle($image, ($width/10*3), ($height/4*3), ($width/10*5), ($height/4*1), $back_color);
		imagerectangle($image, ($width/10*5), ($height/4*3), ($width/10*7), ($height/4*1), $back_color);
		imagerectangle($image, ($width/10*7), ($height/4*3), ($width/10*9), ($height/4*1), $back_color);

		return array($image,$code);
	}
	
	private function getBlank($width=150, $height=150, $bg_color=array('red'=>240,'green'=>240,'blue'=>240)){
		//Создаём
		$blank = ImageCreateTrueColor($width,$height);
		//Альфаканал
		imagealphablending($blank, true);
		//Фон, если нужно
		imagefilledrectangle($blank, 0, 0, $width, $height, imagecolorallocate($blank, $bg_color['red'], $bg_color['green'], $bg_color['blue']));
		//Готово	
		return $blank;
	}
	
	private function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		if ($thick == 1) {
			return imageline($image, $x1, $y1, $x2, $y2, $color);
		}
		$t = $thick / 2 - 0.5;
		if ($x1 == $x2 || $y1 == $y2) {
			return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
		}
		$k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
		$a = $t / sqrt(1 + pow($k, 2));
		$points = array(
			round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
			round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
			round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
			round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
		);
		imagefilledpolygon($image, $points, 4, $color);
		return imagepolygon($image, $points, 4, $color);
	}


}

?>