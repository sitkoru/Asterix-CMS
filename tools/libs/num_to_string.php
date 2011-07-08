<?php

class asSummToString{
	var $title = 'ACMS Summ to string converting library';
	var $version = '1.1';
	var $date = '2007-09-21';

	//Set the notation system type
	var $notation_system='eu';//'eu','us';

	//Use 'and'
	var $and=true;

	//Set current language
	var $ln='ru';//'ru','en';

	//Set currency
	var $cur='rub';//'rub','usd';

	//Set divider (point)
	var $div=true;

	//Converting
	function conv($number,$append_currency,$currency){

		//Дробная часть
		$subnumber=false;
		if(substr_count($number,'.')>0){
			$t=explode('.',$number);
			$number=$t[0];
			$subnumber=$t[1];
		}elseif(substr_count($number,',')>0){
			$t=explode(',',$number);
			$number=$t[0];
			$subnumber=$t[1];
		}

		$digit=$this->defineDigits($ln,$currency);
		$level=$this->defineLevels($ln);

		//Целая часть
		$number=$this->conv_part($number,$digit,$level,$this->currency,true);

		//Дробная часть
		if($subnumber){
			//При необходимости обрезаем до сотых
			if(strlen($subnumber)>2){
				$subnumber=$subnumber/pow(10,(strlen($subnumber)-2));
				$subnumber=round($subnumber);
			}
			$subnumber=$this->conv_part($subnumber,$digit,$level,$this->subcurrency,$append_currency);
			$number.=($append_currency?' ':$this->divider).$subnumber;
		}
		//Возвращаем результат
		return $number;
	}

	//Define digits according to settings
	function defineDigits($ln,$currency){

		if($this->ln=='ru'){
			//Currency
			if($currency){
				$this->currency=$currency['currency'];
				$this->subcurrency=$currency['subcurrency'];
			}else{
				if($this->cur=='rub'){
					$this->currency=array('title'=>array('рубль','рубля','рублей'),'gender'=>'m');
					$this->subcurrency=array('title'=>array('копейка','копейки','копеек'),'gender'=>'w');
				}elseif($this->cur=='usd'){
					$this->currency=array('title'=>array('доллар','доллара','долларов'),'gender'=>'m');
					$this->subcurrency=array('title'=>array('цент','цента','центов'),'gender'=>'m');
				}
			}

			$this->divider=' точка ';
			$digit=array();
			$digit['zero']=array('title'=>'ноль');
			$digit[0]=false;
			$digit[1]=array('title'=>array('m'=>'один','w'=>'одна'));
			$digit[2]=array('title'=>array('m'=>'два','w'=>'две'));
			$digit[3]=array('title'=>'три');
			$digit[4]=array('title'=>'четыре');
			$digit[5]=array('title'=>'пять');
			$digit[6]=array('title'=>'шесть');
			$digit[7]=array('title'=>'семь');
			$digit[8]=array('title'=>'восемь');
			$digit[9]=array('title'=>'девять');
			$digit[10]=array('title'=>'десять');
			$digit[11]=array('title'=>'одинадцать');
			$digit[12]=array('title'=>'двенадцать');
			$digit[13]=array('title'=>'тринадцать');
			$digit[14]=array('title'=>'четырнадцать');
			$digit[15]=array('title'=>'пятнадцать');
			$digit[16]=array('title'=>'шестнадцать');
			$digit[17]=array('title'=>'семнадцать');
			$digit[18]=array('title'=>'восемнадцать');
			$digit[19]=array('title'=>'девятнадцать');
			$digit[20]=array('title'=>'двадцать');
			$digit[30]=array('title'=>'тридцать');
			$digit[40]=array('title'=>'сорок');
			$digit[50]=array('title'=>'пятьдесят');
			$digit[60]=array('title'=>'шестьдесят');
			$digit[70]=array('title'=>'семьдесят');
			$digit[80]=array('title'=>'восемьдесят');
			$digit[90]=array('title'=>'девяносто');
			$digit[100]=array('title'=>'сто');
			$digit[200]=array('title'=>'двести');
			$digit[300]=array('title'=>'триста');
			$digit[400]=array('title'=>'четыреста');
			$digit[500]=array('title'=>'пятьсот');
			$digit[600]=array('title'=>'шестьсот');
			$digit[700]=array('title'=>'семьсот');
			$digit[800]=array('title'=>'восемьсот');
			$digit[900]=array('title'=>'девятьсот');
			return $digit;
		}elseif($this->ln=='en'){
			//Currency
			if($this->cur=='rub'){
				$this->currency=array('title'=>array('ruble','ruble','rubles'),'gender'=>'m');
				$this->subcurrency=array('title'=>array('kopeck','kopeck','kopecks'),'gender'=>'w');
			}elseif($this->cur=='usd'){
				$this->currency=array('title'=>array('dollar','dollar','dollars'),'gender'=>'m');
				$this->subcurrency=array('title'=>array('cent','cent','cents'),'gender'=>'m');
			}

			$this->divider=' point ';
			$digit=array();
			$digit['zero']=array('title'=>'zero');
			$digit[0]=false;
			$digit[1]=array('title'=>'one');
			$digit[2]=array('title'=>'two');
			$digit[3]=array('title'=>'three');
			$digit[4]=array('title'=>'four');
			$digit[5]=array('title'=>'five');
			$digit[6]=array('title'=>'six');
			$digit[7]=array('title'=>'seven');
			$digit[8]=array('title'=>'eight');
			$digit[9]=array('title'=>'nine');
			$digit[10]=array('title'=>'ten');
			$digit[11]=array('title'=>'eleven');
			$digit[12]=array('title'=>'twelve');
			$digit[13]=array('title'=>'thirteen');
			$digit[14]=array('title'=>'fourteen');
			$digit[15]=array('title'=>'fifteen');
			$digit[16]=array('title'=>'sixteen');
			$digit[17]=array('title'=>'seventeen');
			$digit[18]=array('title'=>'eighteen');
			$digit[19]=array('title'=>'nineteen');
			$digit[20]=array('title'=>'twenty');
			$digit[30]=array('title'=>'thirty');
			$digit[40]=array('title'=>'fourty');
			$digit[50]=array('title'=>'fifty');
			$digit[60]=array('title'=>'sixty');
			$digit[70]=array('title'=>'seventy');
			$digit[80]=array('title'=>'eighty');
			$digit[90]=array('title'=>'ninety');
			$digit[100]=array('title'=>'one hundred'.($this->and?' and':''));
			$digit[200]=array('title'=>'two hundred'.($this->and?' and':''));
			$digit[300]=array('title'=>'three hundred'.($this->and?' and':''));
			$digit[400]=array('title'=>'four hundred'.($this->and?' and':''));
			$digit[500]=array('title'=>'five hundred'.($this->and?' and':''));
			$digit[600]=array('title'=>'six hundred'.($this->and?' and':''));
			$digit[700]=array('title'=>'seven hundred'.($this->and?' and':''));
			$digit[800]=array('title'=>'eight hundred'.($this->and?' and':''));
			$digit[900]=array('title'=>'nine hundred'.($this->and?' and':''));
			return $digit;
		}
	}

	//Определяем степени тысячи
	function defineLevels($ln){
		if($this->notation_system=='eu'){
			if($this->ln=='ru'){
				$level=array();
				$level[0]=array('title'=>array('',						'',						''),					'gender'=>false,	'power'=>0);
				$level[1]=array('title'=>array('тясяча',				'тысячи',				'тысяч'),				'gender'=>'w',	'power'=>3);
				$level[2]=array('title'=>array('миллион',				'миллиона',				'миллионов'),			'gender'=>'m',	'power'=>6);
				$level[3]=array('title'=>array('миллиард',				'миллиарда',			'миллиардов'),			'gender'=>'m',	'power'=>9);
				$level[4]=array('title'=>array('биллион',				'биллиона',				'биллионов'),			'gender'=>'m',	'power'=>12);
				$level[5]=array('title'=>array('биллиард',				'биллиарда',			'биллиардов'),			'gender'=>'m',	'power'=>15);
				$level[6]=array('title'=>array('триллион',				'триллиона',			'триллионов'),			'gender'=>'m',	'power'=>18);
				$level[7]=array('title'=>array('триллиард',				'триллиарда',			'триллиардов'),			'gender'=>'m',	'power'=>21);
				$level[8]=array('title'=>array('квадриллион',			'квадриллиона',			'квадриллионов'),		'gender'=>'m',	'power'=>24);
				$level[9]=array('title'=>array('квадриллиард',			'квадриллиарда',		'квадриллиардов'),		'gender'=>'m',	'power'=>27);
				$level[10]=array('title'=>array('квинтиллион',			'квинтиллиона',			'квинтиллионов'),		'gender'=>'m',	'power'=>30);
				$level[11]=array('title'=>array('квинтиллиард',			'квинтиллиарда',		'квинтиллиардов'),		'gender'=>'m',	'power'=>33);
				$level[12]=array('title'=>array('секстиллион',			'секстиллиона',			'секстиллионов'),		'gender'=>'m',	'power'=>36);
				$level[13]=array('title'=>array('секстиллиард',			'секстиллиарда',		'секстиллиардов'),		'gender'=>'m',	'power'=>39);
				$level[14]=array('title'=>array('септиллион',			'септиллиона',			'септиллионов'),		'gender'=>'m',	'power'=>42);
				$level[15]=array('title'=>array('септиллиард',			'септиллиарда',			'септиллиардов'),		'gender'=>'m',	'power'=>45);
				$level[16]=array('title'=>array('октиллион',			'октиллиона',			'октиллионов'),			'gender'=>'m',	'power'=>48);
				$level[17]=array('title'=>array('октиллиард',			'октиллиарда',			'октиллиардов'),		'gender'=>'m',	'power'=>51);
				$level[18]=array('title'=>array('нониллион',			'нониллиона',			'нониллионов'),			'gender'=>'m',	'power'=>54);
				$level[19]=array('title'=>array('нониллиард',			'нониллиарда',			'нониллиардов'),		'gender'=>'m',	'power'=>57);
				$level[20]=array('title'=>array('дециллион',			'дециллиона',			'дециллионов'),			'gender'=>'m',	'power'=>60);
				$level[21]=array('title'=>array('дециллиард',			'дециллиарда',			'дециллиардов'),		'gender'=>'m',	'power'=>63);
				$level[22]=array('title'=>array('ундециллион',			'ундециллиона',			'ундециллионов'),		'gender'=>'m',	'power'=>66);
				$level[23]=array('title'=>array('ундециллиард',			'ундециллиарда',		'ундециллиардов'),		'gender'=>'m',	'power'=>69);
				$level[24]=array('title'=>array('додециллион',			'додециллиона',			'додециллионов'),		'gender'=>'m',	'power'=>72);
				$level[25]=array('title'=>array('додециллиард',			'додециллиард',			'додециллиардов'),		'gender'=>'m',	'power'=>75);
				$level[26]=array('title'=>array('тредециллион',			'тредециллиона',		'тредециллионов'),		'gender'=>'m',	'power'=>78);
				$level[27]=array('title'=>array('тредециллиард',		'тредециллиарда',		'тредециллиардов'),		'gender'=>'m',	'power'=>81);
				$level[28]=array('title'=>array('кваттуордециллион',	'кваттуордециллиона',	'кваттуордециллионов'),	'gender'=>'m',	'power'=>84);
				$level[29]=array('title'=>array('кваттуордециллиард',	'кваттуордециллиарда',	'кваттуордециллиардов'),'gender'=>'m',	'power'=>87);
				$level[30]=array('title'=>array('квиндециллионон',		'квиндециллионона',		'квиндециллиононов'),	'gender'=>'m',	'power'=>90);
				$level[31]=array('title'=>array('квиндециллионард',		'квиндециллионарда',	'квиндециллионардов'),	'gender'=>'m',	'power'=>93);
				$level[32]=array('title'=>array('седециллион',			'седециллиона',			'седециллионов'),		'gender'=>'m',	'power'=>96);
				$level[33]=array('title'=>array('седециллиард',			'седециллиарда',		'седециллиардов'),		'gender'=>'m',	'power'=>99);
				$level[34]=array('title'=>array('септдециллион',		'септдециллиона',		'септдециллионов'),		'gender'=>'m',	'power'=>102);
				$level[35]=array('title'=>array('септдециллиард',		'септдециллиарда',		'септдециллиардов'),	'gender'=>'m',	'power'=>105);
				$level[36]=array('title'=>array('дуодевигинтиллион',	'дуодевигинтиллиона',	'дуодевигинтиллионов'),	'gender'=>'m',	'power'=>108);
				$level[37]=array('title'=>array('дуодевигинтиллиард',	'дуодевигинтиллиарда',	'дуодевигинтиллиардов'),'gender'=>'m',	'power'=>111);
				$level[38]=array('title'=>array('ундевигинтиллион',		'ундевигинтиллиона',	'ундевигинтиллионов'),	'gender'=>'m',	'power'=>114);
				$level[39]=array('title'=>array('ундевигинтиллиард',	'ундевигинтиллиарда',	'ундевигинтиллиардов'),	'gender'=>'m',	'power'=>117);
				$level[40]=array('title'=>array('вигинтиллион',			'вигинтиллиона',		'вигинтиллионов'),		'gender'=>'m',	'power'=>120);
				$level[41]=array('title'=>array('вигинтиллиард',		'вигинтиллиарда',		'вигинтиллиардов'),		'gender'=>'m',	'power'=>123);
			}elseif($this->ln=='en'){
				$level=array();
				$level[0]=array('title'=>array('',						'',						''),					'gender'=>false,	'power'=>0);
				$level[1]=array('title'=>array('thousand',				'thousand',				'thousand'),			'gender'=>'w',	'power'=>3);
				$level[2]=array('title'=>array('million',				'million',				'million'),				'gender'=>'m',	'power'=>6);
				$level[3]=array('title'=>array('milliard',				'milliard',				'milliard'),			'gender'=>'m',	'power'=>9);
				$level[4]=array('title'=>array('billion',				'billion',				'billion'),				'gender'=>'m',	'power'=>12);
				$level[5]=array('title'=>array('billiard',				'billiard',				'billiard'),			'gender'=>'m',	'power'=>15);
				$level[6]=array('title'=>array('trillion',				'trillion',				'trillion'),			'gender'=>'m',	'power'=>18);
				$level[7]=array('title'=>array('trilliard',				'trilliard',			'trilliard'),			'gender'=>'m',	'power'=>21);
				$level[8]=array('title'=>array('quadrillion',			'quadrillion',			'quadrillion'),			'gender'=>'m',	'power'=>24);
				$level[9]=array('title'=>array('quadrilliard',			'quadrilliard',			'quadrilliard'),		'gender'=>'m',	'power'=>27);
				$level[10]=array('title'=>array('quintillion',			'quintillion',			'quintillion'),			'gender'=>'m',	'power'=>30);
				$level[11]=array('title'=>array('quintilliard',			'quintilliard',			'quintilliard'),		'gender'=>'m',	'power'=>33);
				$level[12]=array('title'=>array('sextillion',			'sextillion',			'sextillion'),			'gender'=>'m',	'power'=>36);
				$level[13]=array('title'=>array('sextilliard',			'sextilliard',			'sextilliard'),			'gender'=>'m',	'power'=>39);
				$level[14]=array('title'=>array('septillion',			'septillion',			'septillion'),			'gender'=>'m',	'power'=>42);
				$level[15]=array('title'=>array('septilliard',			'septilliard',			'septilliard'),			'gender'=>'m',	'power'=>45);
				$level[16]=array('title'=>array('octillion',			'octillion',			'octillion'),			'gender'=>'m',	'power'=>48);
				$level[17]=array('title'=>array('octilliard',			'octilliard',			'octilliard'),			'gender'=>'m',	'power'=>51);
				$level[18]=array('title'=>array('nonillion',			'nonillion',			'nonillion'),			'gender'=>'m',	'power'=>54);
				$level[19]=array('title'=>array('nonilliard',			'nonilliard',			'nonilliard'),			'gender'=>'m',	'power'=>57);
				$level[20]=array('title'=>array('decillion',			'decillion',			'decillion'),			'gender'=>'m',	'power'=>60);
				$level[21]=array('title'=>array('decilliard',			'decilliard',			'decilliard'),			'gender'=>'m',	'power'=>63);
				$level[22]=array('title'=>array('undecillion',			'undecillion',			'undecillion'),			'gender'=>'m',	'power'=>66);
				$level[23]=array('title'=>array('undecilliard',			'undecilliard',			'undecilliard'),		'gender'=>'m',	'power'=>69);
				$level[24]=array('title'=>array('duodecillion',			'duodecillion',			'duodecillion'),		'gender'=>'m',	'power'=>72);
				$level[25]=array('title'=>array('duodecilliard',		'duodecilliard',		'duodecilliard'),		'gender'=>'m',	'power'=>75);
				$level[26]=array('title'=>array('tredecillion',			'tredecillion',			'tredecillion'),		'gender'=>'m',	'power'=>78);
				$level[27]=array('title'=>array('tredecilliard',		'tredecilliard',		'tredecilliard'),		'gender'=>'m',	'power'=>81);
				$level[28]=array('title'=>array('quattuordecillion',	'quattuordecillion',	'quattuordecillion'),	'gender'=>'m',	'power'=>84);
				$level[29]=array('title'=>array('quattuordecilliard',	'quattuordecilliard',	'quattuordecilliard'),	'gender'=>'m',	'power'=>87);
				$level[30]=array('title'=>array('quindecillion',		'quindecillion',		'quindecillion'),		'gender'=>'m',	'power'=>90);
				$level[31]=array('title'=>array('quindecilliard',		'quindecilliard',		'quindecilliard'),		'gender'=>'m',	'power'=>93);
				$level[32]=array('title'=>array('sexdecillion',			'sexdecillion',			'sexdecillion'),		'gender'=>'m',	'power'=>96);
				$level[33]=array('title'=>array('sexdecilliard',		'sexdecilliard',		'sexdecilliard'),		'gender'=>'m',	'power'=>99);
				$level[34]=array('title'=>array('septendecillion',		'septendecillion',		'septendecillion'),		'gender'=>'m',	'power'=>102);
				$level[35]=array('title'=>array('septendecilliard',		'septendecilliard',		'septendecilliard'),	'gender'=>'m',	'power'=>105);
				$level[36]=array('title'=>array('octodecillion',		'octodecillion',		'octodecillion'),		'gender'=>'m',	'power'=>108);
				$level[37]=array('title'=>array('octodecilliard',		'octodecilliard',		'octodecilliard'),		'gender'=>'m',	'power'=>111);
				$level[38]=array('title'=>array('novemdecillion',		'novemdecillion',		'novemdecillion'),		'gender'=>'m',	'power'=>114);
				$level[39]=array('title'=>array('novemdecilliard',		'novemdecilliard',		'novemdecilliard'),		'gender'=>'m',	'power'=>117);
				$level[40]=array('title'=>array('vigintillion',			'vigintillion',			'vigintillion'),		'gender'=>'m',	'power'=>120);
				$level[41]=array('title'=>array('vigintilliard',		'vigintilliard',		'vigintilliard'),		'gender'=>'m',	'power'=>123);
			}
		}elseif($this->notation_system=='us'){
			if($this->ln=='ru'){
				$level=array();
				$level[0]=array('title'=>array('',						'',						''),					'gender'=>false,	'power'=>0);
				$level[1]=array('title'=>array('тясяча',				'тысячи',				'тысяч'),				'gender'=>'w',	'power'=>3);
				$level[2]=array('title'=>array('миллион',				'миллиона',				'миллионов'),			'gender'=>'m',	'power'=>6);
				$level[3]=array('title'=>array('биллион',				'биллиона',				'биллионов'),			'gender'=>'m',	'power'=>9);
				$level[4]=array('title'=>array('триллион',				'триллиона',			'триллионов'),			'gender'=>'m',	'power'=>12);
				$level[5]=array('title'=>array('квадриллион',			'квадриллиона',			'квадриллионов'),		'gender'=>'m',	'power'=>15);
				$level[6]=array('title'=>array('квинтиллион',			'квинтиллиона',			'квинтиллионов'),		'gender'=>'m',	'power'=>18);
				$level[7]=array('title'=>array('секстиллион',			'секстиллиона',			'секстиллионов'),		'gender'=>'m',	'power'=>21);
				$level[8]=array('title'=>array('септиллион',			'септиллиона',			'септиллионов'),		'gender'=>'m',	'power'=>24);
				$level[9]=array('title'=>array('октиллион',			'октиллиона',			'октиллионов'),			'gender'=>'m',	'power'=>27);
				$level[10]=array('title'=>array('нониллион',			'нониллиона',			'нониллионов'),			'gender'=>'m',	'power'=>30);
				$level[11]=array('title'=>array('дециллион',			'дециллиона',			'дециллионов'),			'gender'=>'m',	'power'=>33);
				$level[12]=array('title'=>array('ундециллион',			'ундециллиона',			'ундециллионов'),		'gender'=>'m',	'power'=>36);
				$level[13]=array('title'=>array('додециллион',			'додециллиона',			'додециллионов'),		'gender'=>'m',	'power'=>39);
				$level[14]=array('title'=>array('тредециллион',			'тредециллиона',		'тредециллионов'),		'gender'=>'m',	'power'=>42);
				$level[15]=array('title'=>array('кваттуордециллион',	'кваттуордециллиона',	'кваттуордециллионов'),	'gender'=>'m',	'power'=>45);
				$level[16]=array('title'=>array('квиндециллионон',		'квиндециллионона',		'квиндециллиононов'),	'gender'=>'m',	'power'=>48);
				$level[17]=array('title'=>array('седециллион',			'седециллиона',			'седециллионов'),		'gender'=>'m',	'power'=>51);
				$level[18]=array('title'=>array('септдециллион',		'септдециллиона',		'септдециллионов'),		'gender'=>'m',	'power'=>54);
				$level[19]=array('title'=>array('дуодевигинтиллион',	'дуодевигинтиллиона',	'дуодевигинтиллионов'),	'gender'=>'m',	'power'=>57);
				$level[20]=array('title'=>array('ундевигинтиллион',		'ундевигинтиллиона',	'ундевигинтиллионов'),	'gender'=>'m',	'power'=>60);
				$level[21]=array('title'=>array('вигинтиллион',			'вигинтиллиона',		'вигинтиллионов'),		'gender'=>'m',	'power'=>63);
			}elseif($this->ln=='en'){
				$level=array();
				$level[0]=array('title'=>array('',						'',						''),					'gender'=>false,	'power'=>0);
				$level[1]=array('title'=>array('thousand',				'thousand',				'thousand'),				'gender'=>'m',	'power'=>3);
				$level[2]=array('title'=>array('million',				'million',				'million'),				'gender'=>'m',	'power'=>6);
				$level[3]=array('title'=>array('billion',				'billion',				'billion'),				'gender'=>'m',	'power'=>9);
				$level[4]=array('title'=>array('trillion',				'trillion',				'trillion'),			'gender'=>'m',	'power'=>12);
				$level[5]=array('title'=>array('quadrillion',			'quadrillion',			'quadrillion'),			'gender'=>'m',	'power'=>15);
				$level[6]=array('title'=>array('quintillion',			'quintillion',			'quintillion'),			'gender'=>'m',	'power'=>18);
				$level[7]=array('title'=>array('sextillion',			'sextillion',			'sextillion'),			'gender'=>'m',	'power'=>21);
				$level[8]=array('title'=>array('septillion',			'septillion',			'septillion'),			'gender'=>'m',	'power'=>24);
				$level[9]=array('title'=>array('octillion',				'octillion',			'octillion'),			'gender'=>'m',	'power'=>27);
				$level[10]=array('title'=>array('nonillion',			'nonillion',			'nonillion'),			'gender'=>'m',	'power'=>30);
				$level[11]=array('title'=>array('decillion',			'decillion',			'decillion'),			'gender'=>'m',	'power'=>33);
				$level[12]=array('title'=>array('undecillion',			'undecillion',			'undecillion'),			'gender'=>'m',	'power'=>36);
				$level[13]=array('title'=>array('duodecillion',			'duodecillion',			'duodecillion'),		'gender'=>'m',	'power'=>39);
				$level[14]=array('title'=>array('tredecillion',			'tredecillion',			'tredecillion'),		'gender'=>'m',	'power'=>42);
				$level[15]=array('title'=>array('quattuordecillion',	'quattuordecillion',	'quattuordecillion'),	'gender'=>'m',	'power'=>45);
				$level[16]=array('title'=>array('quindecillion',		'quindecillion',		'quindecillion'),		'gender'=>'m',	'power'=>48);
				$level[17]=array('title'=>array('sexdecillion',			'sexdecillion',			'sexdecillion'),		'gender'=>'m',	'power'=>51);
				$level[18]=array('title'=>array('septendecillion',		'septendecillion',		'septendecillion'),		'gender'=>'m',	'power'=>54);
				$level[19]=array('title'=>array('octodecillion',		'octodecillion',		'octodecillion'),		'gender'=>'m',	'power'=>57);
				$level[20]=array('title'=>array('novemdecillion',		'novemdecillion',		'novemdecillion'),		'gender'=>'m',	'power'=>60);
				$level[21]=array('title'=>array('vigintillion',			'vigintillion',			'vigintillion'),		'gender'=>'m',	'power'=>63);
			}
		}
		return $level;
	}

	//Переводим только часть числа
	function conv_part($number,$digit,$level,$currency,$append_currency){

		//Если передан знак - запоминаем и потом вернем
		$mark=false;
		$marks=array('-'=>'минус');
		if(IsSet($marks[$number[0]])){
			$mark=$marks[$number[0]];
			$number=substr($number,1);
		}

		//Если введено не число
		if(!is_numeric($number)){
			return 'ошибка';
		}

		//Если введен ноль
		if($number==0){
			$str=$digit['zero']['title'].($append_currency?' '.$currency['title'][2]:'');
			return $str;
		}

		//Разбиваем число на степерни тысячи
		$l=0;
		$k=0;
		$dec=array();
		$dec[$l]='';
		$number=str_pad($number,3,'0',STR_PAD_LEFT);
		$number=strrev($number);
		for($i=0;$i<strlen($number);$i++){
			if($k>2){
				$l++;
				$k=1;
				$dec[$l]='';
			}else $k++;
			$dec[$l].=$number[$i];
		}
		foreach($dec as $i=>$d)$dec[$i]=strrev($d);

		//Формируем массив вывода
		$string=array();
		$global_title_type=false;
		foreach($dec as $i=>$d){

			//Если название разряда известно
			if(IsSet($level[$i])){

				//Дополняем до полных трех знаков
				$d=str_pad($d,3,'0',STR_PAD_LEFT);

				//Определяем род степени тысячи
				$gender=$level[$i]['gender'];
				if(!$gender)$gender=$currency['gender'];

				//Все число
				if($d!='000'){
					if(IsSet($digit[$d])){
						$l=$d;
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Только единицы
					}elseif( ($d[0]==0) && ($d[1]==0) && IsSet($digit[$d[2]]) ){
						$l=$d[2];
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Десятки
					}elseif( ($d[0]==0) && IsSet($digit[$d[1].'0']) && ($d[2]==0) ){
						$l=$d[1].'0';
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Десятки и единицы вместе (10-19)
					}elseif( ($d[0]==0) && IsSet($digit[$d[1].$d[2]]) ){
						$l=$d[1].$d[2];
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Десятки и единицы отдельно (>=20)
					}elseif( ($d[0]==0) && IsSet($digit[$d[1].'0']) && IsSet($digit[$d[2]]) ){
						$l=$d[1].'0';
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
						$l=$d[2];
						$string[$i].=($this->ln=='en'?'-':' ').(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Сотни и единицы
					}elseif( IsSet($digit[$d[0]]) && ($d[1]==0) && IsSet($digit[$d[2]]) ){
						$l=$d[0].'00';
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
						$l=$d[2];
						$string[$i].=' '.(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Сотни и десятки единицы вместе
					}elseif( IsSet($digit[$d[0].'00']) && IsSet($digit[$d[1].$d[2]]) ){
						$l=$d[0].'00';
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
						$l=$d[1].$d[2];
						$string[$i].=' '.(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					//Сотни и десятки единицы отдельно
					}elseif( IsSet($digit[$d[0].'00']) && IsSet($digit[$d[1].'0']) && IsSet($digit[$d[2]]) ){
						$l=$d[0].'00';
						$string[$i]=(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
						$l=$d[1].'0';
						$string[$i].=' '.(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
						$l=$d[2];
						$string[$i].=($this->ln=='en'?'-':' ').(is_array($digit[$l]['title'])?$digit[$l]['title'][$gender]:$digit[$l]['title']);
					}

					//Определяем род текущей цифры
					if($this->ln=='ru'){
						$title_type=0;
						if(IsSet($digit[$d[1].$d[2]]))$title_type=2;
						elseif(in_array($d[2],array(1)))$title_type=0;
						elseif(in_array($d[2],array(2,3,4)))$title_type=1;
						elseif(in_array($d[2],array(0,5,6,7,8,9)))$title_type=2;
					}elseif($this->ln=='en'){
						if( ($d[0]=='0') && ($d[1]=='0') && ($d[2]=='1') )$title_type=0;
						else $title_type=2;
					}
					$string[$i].=' '.$level[$i]['title'][$title_type];

					//Определяем род последней значащей цифры
					if($i==0)$global_title_type=$title_type;
					if( ($i>0) && ($global_title_type===false) )$global_title_type=2;
				}

			//Если не определено название степени тысячи - выводим просто цифры
			}else{
				$string[$i]=' '.$d;
			}
		}

		//Переворачиваем строку обратно
		$string=array_reverse($string);

		//Формируем финальную строку
		$str=($mark?$mark.' ':'').implode(' ',$string).($append_currency?' '.$currency['title'][$global_title_type]:'');
		return $str;
	}
}

/*###########################
Пример использования:
$summ2string=new asSummToString;
$s='567890123456789012345678901234567890123456789012345678901234567890.41';
$append_currency=true;
###########################*/

/*
Разработка: Мишин Олег.
Email: mishinoleg@mail.ru
Web: http://www.mishinoleg.ru/
*/

?>