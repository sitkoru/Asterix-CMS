<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс работы с кешем контроллеров					*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 0.01										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 16 марта 2011 года		    			*/
/*															*/
/************************************************************/

class cache
{
	public static $config;
	private static $sql_cache = array();

	//Конструктор
	public function __construct($config, $log)
	{
        self::$config = $config;
//        $this->log = $log;

        //Добавляем в реестр экземпляр кэширования в файлы
        if( self::$config['cache']['type'] == 'files' )
            BASE_Registry::set('cache', new CACHE_Manager(new CACHE_File( self::$config['cache']['cache_path'] )));
        elseif( self::$config['cache']['type'] == 'memcache' )
            BASE_Registry::set('cache', new CACHE_Manager(new CACHE_MemCache( self::$config['cache']['cache_host'] , self::$config['cache']['cache_port'] )));

        //Получаем экземпляр класса кеширования из реестра
        $this->cache = BASE_Registry::get('cache');

	}

	public function readCache(){return false;}
	public function makeCache(){return false;}
	public function clearCache(){return false;}
	
	public static function readSqlCache( $sql ){
		if( IsSet( self::$sql_cache[ $sql ] ) )
			return self::$sql_cache[ $sql ];
		else
			return false;
	}
	public static function makeSqlCache( $sql, $result ){
		self::$sql_cache[ $sql ] = $result;
	}
	
}

class CACHE_File implements CACHE_ICache
{
	private $path;

	public function __construct($path = 'cache')
	{
		$this->path = $path;
	}

	public function save($value, $valueID)
	{
		$str_val = serialize($value);
		$file_name = $this->pathCache($valueID) .
			$this->nameCache($valueID);
		$f = fopen($file_name, 'w+');
		if (flock($f, LOCK_EX)) {
			fwrite($f, $str_val);
			flock($f, LOCK_UN);
		}
		fclose($f);
		unset($str_val);
	}

	public function load($valueID, $time)
	{
		$file_name = $this->getPathCache($valueID) .
			$this->nameCache($valueID);
		if (!file_exists($file_name)) return false;
		if ((filemtime($file_name) + $time) < time()) {
			return false;
		}
		if (!$data = file($file_name))  return false;
		return unserialize(implode('', $data));
	}

	public function delete($valueID)
	{
		$file_name = $this->getPathCache($valueID) .
			$this->nameCache($valueID);
		unlink($file_name);
	}

	private function pathCache($valueID)
	{
		$md5 = $this->nameCache($valueID);
		$first_literal = array($md5{0}, $md5{1}, $md5{2}, $md5{3});
		$path = $this->path . '/';
		foreach ($first_literal as $dir) {
			$path .= $dir . '/';
			if (!file_exists($path)) {
				if (!mkdir($path, 0777)) return false;
			}
		}
		return $path;
	}

	private function getPathCache($valueID)
	{
		$md5 = $this->nameCache($valueID);
		$first_literal = array($md5{0}, $md5{1}, $md5{2}, $md5{3});
		return $this->path . '/' .
			implode('/', $first_literal) . '/';
	}

	private function nameCache($valueID)
	{
		return md5($valueID);
	}
}

class CACHE_MemCache implements CACHE_ICache
{
	private $memcache;
	private $timeLife;
	private $compress;

	/**
	 *
	 * @param string $host - хост сервера memcached
	 * @param int $port - порт сервера memcached
	 * @param int $compress - [0,1], сжимать или нет данные перед
	 * помещением в память
	 */
	public function __construct($host, $port = 11211, $compress = 0)
	{
		$this->memcache = memcache_connect($host, $port);
		$this->compress = ($compress) ? MEMCACHE_COMPRESSED : 0;
	}

	public function load($valueID, $timeLife)
	{
		$this->timeLife = $timeLife;
		return memcache_get($this->memcache, md5($valueID));
	}

	public function save($value, $valueID)
	{
		return memcache_set($this->memcache, md5($valueID), $value, $this->compress, $this->timeLife);
	}

	public function delete($valueID)
	{
		memcache_delete($this->memcache, md5($valueID));
	}

	public function __destruct()
	{
		memcache_close($this->memcache);
	}
}

interface CACHE_ICache
{
	public function save($value, $valueID);
	public function load($valueID, $timeLife);
	public function delete($valueID);
}

class BASE_Registry
{
	private static $_vars = array();

	public function __construct() {}

	public static function set($key, $var)
	{
		if (isset(self::$_vars[$key]) == true) {
			throw new Exception('Данная переменная [' . $key . '] уже существует!');
		}
		self::$_vars[$key] = $var;
		return true;
	}

	public static function get($key)
	{
		if (isset(self::$_vars[$key]) == false) { return null; }
		return self::$_vars[$key];
	}

	public static function remove($var)
	{
		unset(self::$_vars[$key]);
	}
}

class CACHE_Manager
{
	private $_cache;

	public function __construct(CACHE_ICache $cache)
	{
		$this->_cache = $cache;
	}

	public function load($valueID, $timeLife)
	{
		return $this->_cache->load($valueID, $timeLife);
	}

	public function save($value, $valueID)
	{
		$this->_cache->save($value, $valueID);
	}

	public function delete($valueID)
	{
		$this->_cache->delete($valueID);
	}
}

?>
