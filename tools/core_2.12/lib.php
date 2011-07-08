<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Вспомогательная библиотека							*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 25 сентября 2009 года					*/
/*															*/
/************************************************************/

global $white_ips;
$white_ips = file($config['path']['core'] . '/ip_good.txt');
foreach ($white_ips as $i => $p)
	$white_ips[$i] = trim($p);

function pr($s,$exit=false)
{
	global $user_ip, $white_ips;
	if (in_array($user_ip, $white_ips)) {
		if (!headers_sent())
			header('Content-Type: text/html; charset=utf-8');
		print('<div style="width:auto; height:auto; border:1px solid #999; background-color:#F7F0D2; margin:2px; padding:3px; color:#000; font-size:12px; font-weight:normal;"><b style="font-weight:bold;">Служебный вывод [символов: ' . @strlen($s) . '].</b><br>' . $s . '</div>');
		if($exit)exit();
	}
}

function pr_r($a,$exit=false)
{
	global $user_ip, $white_ips;
	if (in_array($user_ip, $white_ips)) {
		if (!headers_sent())
			header('Content-Type: text/html; charset=utf-8');
		print('<div style="width:auto; height:auto; border:1px solid #999; background-color:#F7F0D2; margin:2px; padding:3px; color:#000; font-size:12px; font-weight:normal; text-align:left;"><b style="font-weight:bold;">Служебный вывод [элементов: ' . count($a) . '].</b><pre>');
		print_r($a);
		print('</pre></div>');
		if($exit)exit();
	}
}

function GetUserIP()
{
	if (IsSet($_SERVER['HTTP_X_REAL_IP']))
		return $_SERVER['HTTP_X_REAL_IP'];
	if (IsSet($_SERVER['REMOTE_ADDR']))
		return $_SERVER['REMOTE_ADDR'];
	if (IsSet($_SERVER['HTTP_CLIENT_IP']))
		return $_SERVER['HTTP_CLIENT_IP'];
}

function translitIt($str){
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

global $user_ip;
$user_ip = GetUserIP();


/*
Разработка: Мишин Олег.
Email: mishinoleg@mail.ru
Web: http://www.mishinoleg.ru/
*/

?>