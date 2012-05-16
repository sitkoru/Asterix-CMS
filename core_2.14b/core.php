<?php

//До момента запуска ядра ошибки показываются, потом - в зависимости от режима test_mode
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set("display_errors", "on");

session_start();

ini_set('error_log', $config['path']['www'].'../error_'.$_SERVER['HTTP_HOST'].'.log');
ini_set('include_path', implode(';',$config['path']));

//Система контроля исключений работы системы
require_once($config['path']['core'].'/classes/log.php');
$log=new log($config);

//Кеш
require_once($config['path']['core'].'/classes/cache.php');
$cache=new cache($config,$log);
$cache->readCache();

//Обхявленная переменная позволяет просто запустить ядро, без выполнения контроллеров
if( !$start_only ){
	//Менеджер контроллеров
	require_once($config['path']['core'].'/classes/controller_manager.php');
	$controller_manager=new controller_manager($config,$log,$cache);
	exit();

}else{
	require_once($config['path']['core'] . '/classes/model.php');
	$model = new model($config, $log, $cache);

}

?>