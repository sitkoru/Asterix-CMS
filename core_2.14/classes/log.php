<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс ведения системного лога						*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 0.01										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 25 сентября 2009 года					*/
/*															*/
/************************************************************/

class log
{
	public static $config;
	public static $sql = array();
	public static $time_start;
	public static $time_stop;
	public static $memory_total;
	
	var $errors = array('file_not_found' => array('title' => 'Файл не найден', 'action' => 'stop'));
	
	//Запуск
	public function __construct($config = array()){
		//Помним настройки
		self::$config = $config;
		
		//Старт работы
		self::setStart();
	}
	
	public function stop($error_code, $error_comment = false, $error_report = false){
		
		header('Content-Type: text/html; charset=utf-8');
		header("HTTP/1.0 ".$error_code);
		
		print('Страница не может быть показана.<br />Ошибка '.$error_code.'.');
		if( $error_comment ) print('<br />---<br />'.$error_comment);
		if( $error_report ) pr_r($error_report);
		exit();	
	}
	
	//Начало ведения статистики
	public static function setStart(){
		$t                	= explode(' ', microtime());
		self::$time_start 	= $t[1] + $t[0];
	}
	
	//Окончание работы
	public static function setStop(){
		$t               	= explode(' ', microtime());
		self::$time_stop 	= $t[1] + $t[0];
		self::$memory_total = memory_get_usage() / 1024 / 1024;
	}
	
	//SQL-запрос
	public function sql($sql, $time = false, $result = false, $module = 'start', $function = false)
	{
		self::$sql[] = array(
			'sql' => $sql,
			'time' => $time,
			'result' => count($result),
			'module' => $module,
			'function' => $function
		);
	}
	
	//Показать статистику
	public function showStat()	{
		self::setStop();
		
		global $user_ip;
		if (model::$settings['show_stat'] == 'shirt') {
			self::setStop();
/*
			$xhprof_data = xhprof_disable();
			$XHPROF_ROOT = model::$config['path']['core'].'/../libs';
			if( is_readable( $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_lib.php" ) ){
				include_once $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_lib.php";
				include_once $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_runs.php";

				// save raw data for this profiler run using default
				// implementation of iXHProfRuns.
				$xhprof_runs = new XHProfRuns_Default();

				// save the run under a namespace "xhprof_foo"
				$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
				
				$xhprof = ', <a href="http://mars.sitko.ru/xhprof/xhprof_html/index.php?run='.$run_id.'&source=xhprof_foo">xhprof</a>';
			}                                 
*/
				
			pr('Генерация заняла ' . number_format(self::$time_stop - self::$time_start, 5, '.', ' ') . ' секунд, использовано ' . number_format(self::$memory_total, 2, '.', ' ') . ' мегабайт памяти, сделано ' . count(self::$sql) . ' запросов, кеширование '.(model::$config['cache']?'включено ('.model::$config['cache']['type'].')':'отключено').''.($xhprof?$xhprof:'').'.');
			
		} elseif (model::$settings['show_stat'] == 'all') {
			pr('Генерация страницы: ' . number_format(self::$time_stop - self::$time_start, 5, '.', ' ') . ' секунд.');
			pr('Использовано памяти: ' . number_format(self::$memory_total, 2, '.', ' ') . ' мегабайт памяти.');
			pr_r(self::$sql);
			
			$desc   = array();
			$unique = array();
			$time   = false;
			foreach (self::$sql as $q) {
				$desc[] = $q['sql'].' [time:'.$q['time'].']';
				if (!in_array($q['sql'], $unique))
					$unique[] = $q['sql'];
				$time += $q['time'];
			}
			sort($desc);
			
			pr('Все вопросы');
			pr_r($desc);
			pr('Уникальные запросы');
			pr_r($unique);
			pr('Суммарное время запросов: ' . $time . ' секунд.');
		}
		
	}
	
	public static function pr($a){
		if( class_exists(user) ){
			if( user::is_admin() ) {
				if (!headers_sent())
					header('Content-Type: text/html; charset=utf-8');
				print('
					<div style="border-radius:10px; background-color:#FEE9CC; margin:5px; padding:5px; color:black; font-family: Arial; font-size:12px; font-weight:normal;">
						<b>Служебный вывод, виден только администраторам (длина: '.strlen($a).'):</b>
						<br>' . $a . '<br />
					</div>');
			}
		}else{
			if (!headers_sent())
				header('Content-Type: text/html; charset=utf-8');
			print('
				<div style="border-radius:10px; background-color:#FEE9CC; margin:5px; padding:5px; color:black; font-family: Arial; font-size:12px; font-weight:normal;">
					<b>Служебный вывод, виден только администраторам (длина: '.strlen($a).'):</b>
					<br>' . $a . '<br />
				</div>');
		}
	}
	
	public static function pr_r($a){
		if( class_exists(user) ){
			if( user::is_admin() ) {
				if (!headers_sent())
					header('Content-Type: text/html; charset=utf-8');
				print('
					<div style="border-radius:10px; background-color:#FEE9CC; margin:5px; padding:5px; color:black; font-family: Arial; font-size:12px; font-weight:normal; text-align:left;">
						<b>Служебный вывод, виден только администраторам (элементов в массиве: ' . count($a) . '):</b>
						<pre>');
				print_r($a);
				print('
						</pre>
					</div>');
			}
		}else{
				if (!headers_sent())
					header('Content-Type: text/html; charset=utf-8');
				print('
					<div style="border-radius:10px; background-color:#FEE9CC; margin:5px; padding:5px; color:black; font-family: Arial; font-size:12px; font-weight:normal; text-align:left;">
						<b>Служебный вывод, виден только администраторам (элементов в массиве: ' . count($a) . '):</b>
						<pre>');
				print_r($a);
				print('
						</pre>
					</div>');
		}
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

function pr($a){log::pr($a);}
function pr_r($a){log::pr_r($a);}
function translitIt($a){log::translitIt($a);}

?>