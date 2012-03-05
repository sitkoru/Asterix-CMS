<?php

session_start();

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('error_log', $config['path']['www'].'../error.log');
ini_set('include_path', implode(';',$config['path']));

$config['settings']['version'] = $core_version;

//Система контроля исключений работы системы
require($config['path']['core'].'/classes/log.php');
$log=new log($config);

//Кеш
require($config['path']['core'].'/classes/cache.php');
$cache=new cache($config,$log);
$cache->readCache();

//Менеджер контроллеров
require($config['path']['core'].'/classes/controller_manager.php');
$controller_manager=new controller_manager($config,$log,$cache);

exit();

?>