<?php

/* * ********************************************************* */
/* 															   */
/* 	Ядро системы управления Asterix	CMS						   */
/* 	Библиотека для определения местоположения посетителя	   */
/* 													 		   */
/* 	Версия ядра 2.13                                           */
/* 	Copyright (c) 2011  Мишин Олег					 		   */
/* 											 				   */
/* 	Версия скрипта 1.00										   */
/* 	Разработчик библиотеки: Абрамов Андрей					   */
/* 	Email: andy.eatme@gmail.com								   */
/* 	Создан: 9 ноября 2011	года							   */
/* 	Модифицирован: 11 ноября 2011 года						   */
/* 															   */
/* * ********************************************************* */

class geoip {
    //настройки для БД
    private $host = 'localhost';
    private $login = 'geotargeting';
    private $password = 'm493vFbe9LZH8mDX';
    private $name = 'geotargeting';

    public function __construct($config) {
        $this->config = $config;
    }
    //Получаем IP посетителя
    private function GetIP() {
        if (IsSet($_SERVER['HTTP_X_REAL_IP']))
            return $_SERVER['HTTP_X_REAL_IP'];
        if (IsSet($_SERVER['REMOTE_ADDR']))
            return $_SERVER['REMOTE_ADDR'];
        if (IsSet($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];
    }
    //преобразовываем ip  вчисло
    private function convertIP($ip) {
        $iparray = explode(".", $ip);
        $ip = pow(256, 3) * $iparray[0] + pow(256, 2) * $iparray[1] + 256 * $iparray[2] + $iparray[3];
        return $ip;
    }
    //получаем местоположение
    public function getLocation() {

        $db = mysql_connect($this->host, $this->login, $this->password);
        if( !$db )
            $this->checkInstallGeoIP();

        mysql_select_db($this->name, $db);
        mysql_query('SET CHARACTER SET utf8');
        
        $ip=$this->convertIP($this->GetIP());
        
        $sql = mysql_query("SELECT * FROM  `ips` WHERE  `left_key` <" . $ip . " ORDER BY `left_key` DESC limit 1");
        $data = mysql_fetch_row($sql);

        $location['country'] = $data[1];
        $cityid = $data[2];
        if ($cityid != 0) {
            $sql = mysql_query("SELECT * FROM  `cities` WHERE  `id` = " . $cityid);
            $data = mysql_fetch_row($sql);
            $location['city']=$data[1];
            $location['region']=$data[2];
            $location['district']=$data[3];
        }
        mysql_close($db);

        //возвращаем ассоциативный массив
        return $location;
    }

    private function checkInstallGeoIP(){


        

        pr('Установлена библиотека GeoIP.');
        exit();
    }


}

?>