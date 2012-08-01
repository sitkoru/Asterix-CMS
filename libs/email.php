<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Работа с почтой										*/
/*															*/
/*	Версия ядра 2.02										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 17 Февраля 2010 года						*/
/*															*/
/************************************************************/

class email{
	
	//Настройки по умолчанию
	var $from='cms@opendev.ru';
	private $encoding='koi8-r';
	
	//Поддерживаемые кодировки
	private $supported_encodings=array('koi8-r','utf8');
	
	//Отправка сообщения
	public function send(
			$to,
			$subject,
			$message,
			$type='plain',
			$files=array()
		){

		//Подготавливаем данные
		$address=$this->prepareAddress($to);
		$subject=$this->prepareSubject($subject,$type);
		$headers=$this->prepareHeaders($type,$files);
		$message=$this->prepareMessage($message,$subject,$type,$files);
		
		//Отправка
		foreach($address as $addr){
			mail($addr,$subject,$message,$headers);
		}
			
		//Готово
		return true;
	}
	
	
	public function __construct($model){
		$this->model = $model;
	}
	
	//Подготовить тему
	private function prepareSubject($subject,$type){
		
		//Plain
		if($type=='plain'){
			return '=?koi8-r?B?'.base64_encode( iconv( 'UTF-8', 'KOI8-R//IGNORE', stripslashes( $subject ) ) ).'?=';
		
		//HTML
		}elseif($type=='html'){
			return '=?koi8-r?B?'.base64_encode( iconv( 'UTF-8', 'KOI8-R//IGNORE', stripslashes( $subject ) ) ).'?=';
//			return iconv('utf-8', 'koi8-r//IGNORE', $subject);
		}
		
	}

	//Подготовить сообщение
	private function prepareMessage($message,$subject,$type,$files){
		
		//Plain
		if($type=='plain'){
			$message=iconv('utf-8', 'koi8-r//IGNORE', $message);
		
		//HTML
		}elseif($type=='html'){
			
			//Прикрепляем файлы
			if(is_array($files))
				foreach($files as $file)$attachment .= $this->addAttachment($file);
		
			$message=iconv('utf-8', 'koi8-r//IGNORE', $message);
			$message='--'.md5(1).'
Content-Type: multipart/alternative; boundary="'.md5(2).'"

--'.md5(2).'
Content-Type: text/plain; charset="koi8-r"
Content-Transfer-Encoding: base64

'.base64_encode(strip_tags($message)).'
--'.md5(2).'
Content-Type: text/html; charset="koi8-r"
Content-Transfer-Encoding: base64

'.base64_encode('<html><head><title>'.$subject.'</title></head><body><p>'.$message.'</p></body></html>').'
--'.md5(2).'--

'.$attachment.'
--'.md5(1).'--
';
		}
		
		//Готово
		return $message;
	}

	//Подготовить адреса для рассылки
	private function prepareAddress($to){
		
		//Кому рассылать
		$address=array();

		//Несколько ардресатов
		if(substr_count($to,' '))
			$address=explode(' ',$to);
		//Один адрес
		else
			$address[]=$to;
			
		//Готово
		return $address;
	}
	
	//Подготовить сообщение
	private function prepareHeaders($type,$files){
		
		//Plain
		if($type=='plain'){
			$headers = 'from:'.$this->from;
		
		//HTML
		}elseif($type=='html'){
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'from: '.$_SERVER['HTTP_HOST'].' <'.$this->from . ">\r\n";
			$headers .= 'Content-Type: multipart/mixed; boundary="'.md5(1).'"' . "\r\n";
		}

		//Готово
		return $headers;
	}
	
	//Прикрепить файл к сообщению
	function addAttachment($file){ 
		//Имя файла
		$fname = substr(strrchr($file['file'], "/"), 1); 		$type=$file['type'];
		//Содержимое		
		$content_type='Content-Type: '.$type;
		$data = file_get_contents($file['file']); 
		$content = '--'.md5('1').'
--'.md5(1).'
'.$content_type.'; charset="windows-1251"; name="'.$fname.'"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="'.$fname.'"

'.chunk_split( base64_encode($data), 68, "\n").'
';		
		return $content; 
	} 

	
	
	//Смена кодировки по умолчанию
	public function setEncoding($encoding='koi8-r'){
		//Приводим к единому формату
		$encoding=strtolower($encoding);
		//Проверяем поддержку данной кодировки
		if(in_array($encoding,$this->supported_encodings))
			//Устанавливаем
			$this->encoding=$encoding;
	}
	
}

?>