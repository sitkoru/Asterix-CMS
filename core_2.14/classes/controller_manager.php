<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Менеджер контроллеров								*/
/*															*/
/*	Версия ядра 2.0											*/
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

class controller_manager
{
	public static $config;
	public $vars = array();
	
	//Все стандартные контроллеры
	public $controllers = array(
		'get' => array('methods' => array('GET'), 'title' => 'Получение содержания страницы', 'protection' => false, 'model' => true, 'path' => 'get.php', 'format' => array('html')),
		'admin' => array('methods' => array('GET', 'POST'), 'title' => 'Система управления', 'protection' => false, 'model' => true, 'path' => 'admin.php', 'format' => array('html')), 
		'tree' => array('methods' => array('GET', 'POST'), 'title' => 'Дерево сайта', 'protection' => false, 'model' => true, 'path' => 'tree.php', 'format' => array('html')),
		'feedback' => array('methods' => array('GET', 'POST'), 'title' => 'Форма обратной связи', 'protection' => array('referer'), 'model' => true, 'path' => 'feedback.php', 'format' => array('html')),
		'register' => array('methods' => array('GET', 'POST'), 'title' => 'Регистрация', 'protection' => array('referer', 'captcha'), 'model' => true, 'path' => 'register.php', 'format' => array('html')),
	);
	
	//Загружаемся
	public function __construct($config, $log, $cache = false)
	{
		self::$config = $config;
		$this->log    = $log;
		$this->cache  = $cache;
		
		//Подгружаем все переданные параметры
		$this->loadData();
		
		//Загружаем модель
		$this->loadModel();
		
		//Если сайт работает в режиме тестирования - проверяем можно ли показывать
		$this->checkTestMode();
		
		//Определение контроллера
		$this->activeController = $this->defineController();
		
		//Записываем контроллер
		model::$ask->controller = $this->activeController;
		
		//Инициализация контроллера
		$result = $this->execController($this->activeController);
		
		//Готово
		return $result;
	}
	
	//Подгрузка переданных параметров
	function loadData(){
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
			$this->vars = $_GET;
		elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
			$this->vars = array_merge($_GET, $_POST, $_FILES);
	}
	
	//Загружаем модель
	private function loadModel(){
		require_once(self::$config['path']['core'] . '/classes/model.php');
		$this->model = new model(self::$config, $this->log, $this->cache);
	}
	
	//Определение контроллера
	private function defineController(){
		//Админка определяется раньше, до разбора URL
		if( IsSet(model::$ask->controller) )
			return model::$ask->controller;
		//если контроллер уже определёт (Admin)
		if( IsSet( $this->activeController ) )
			return $this->activeController;

		//спец.контроллеры
		if (IsSet($this->vars['action'])) {
			if (IsSet($this->controllers[$this->vars['action']])) {
				$controller = $this->vars['action'];
				return $controller;
			} else {
				$controller = 'admin';
				return $controller;
			}
			
		//Обычный контроллер
		} else {
			if ($_SERVER['REQUEST_METHOD'] == 'GET')
				return 'get';
			elseif ( $_SERVER['REQUEST_METHOD'] == 'POST' and model::$ask->mode[0] ){
				model::$modules[ model::$ask->module ]->controlInterface(model::$ask->mode[0], $this->vars, true);
			}
		}
		
		//Перебираем все контроллеры
		foreach ($this->controllers as $sid => $controller)
		//Если допустимый метод
			if (in_array($_SERVER['REQUEST_METHOD'], $controller['methods']))
				if (in_array(model::$ask->output, $controller['format']))
					return $sid;
		
		//Если существует указание
		if (IsSet($this->controllers[$_SERVER['REQUEST_METHOD']]))
			return $_SERVER['REQUEST_METHOD'];
		
		//Контроллер по умолчанию
		return 'get';
	}
	
	//Ищем обработчик данного запроса
	public function execController($controller){
		$current_module = model::$ask->module;

		//Существует запрошенный интерфейс
		if( IsSet( model::$modules[ $current_module ]->interfaces[ $this->vars['interface'] ] ) ){
			//Запрос данных интерфейса
			if( model::$ask->method == 'GET'){
				model::$modules[ $current_module ]->prepareInterface($this->vars['interface'], $this->vars);
			
			//Обработка интерфейса			
			}elseif( model::$ask->method == 'POST'){
				model::$modules[ $current_module ]->controlInterface($this->vars['interface'], $this->vars);
			}
		
		//Контроллера нет - запускаем стандартные
		}elseif( $controller == 'admin' ){
			require_once self::$config['path']['core'] . '/controllers/admin.php';
			$controller = new controller_admin($this->model, $this->vars, $this->cache);
			$controller -> start();
		
		//Контроллера нет - запускаем стандартные
		}else{
			require_once self::$config['path']['core'] . '/controllers/get.php';
			$controller = new controller_get($this->model, $this->vars, $this->cache);
			$controller -> start();
		
		}
	}
	
	//Проверка работы в режиме тестирования
	private function checkTestMode(){
		return false;
		if (intval(@model::$settings['test_mode'])) {
			$current_ip = 0;//GetUserIP();
			
			//Обработка ошибки
			if (!file_exists(self::$config['path']['core'] . '/ip_good.txt'))
				log::err('file_not_found', self::$config['path']['core'] . '/ip_good.txt');
			
			//Подгружаем список администраторских IP-адресов
			$white_ips = file(self::$config['path']['core'] . '/ip_good.txt');
			
			//Чистим
			foreach ($white_ips as $i => $p)
				$white_ips[$i] = trim($p);
			
			//Проверяем
			if (!in_array($current_ip, $white_ips)) {
				if (!headers_sent())
					header('Content-Type: text/html; charset=utf-8');
				print(model::$settings['test_mode_text'] . ' <!--' . $current_ip . '-->');
				exit();
			}
		}
	}
}

?>