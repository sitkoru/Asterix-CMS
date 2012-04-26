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
	
	public static $output_formats = array('html','json','xml','tpl');
	
	//Все стандартные контроллеры
	public $controllers = array(
		'get' => array('methods' => array('GET'), 'title' => 'Получение содержания страницы', 'protection' => false, 'model' => true, 'path' => 'get.php', 'format' => array('html')),
		'admin' => array('methods' => array('GET', 'POST'), 'title' => 'Система управления', 'protection' => false, 'model' => true, 'path' => 'admin.php', 'format' => array('html')), 
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

		//Admin
		if( model::$ask->url[0] == 'admin' ){
			return 'admin';
		
		
/*		
		if (IsSet($this->vars['action'])) {
			if (IsSet($this->controllers[$this->vars['action']])) {
				$controller = $this->vars['action'];
				return $controller;
			} else {
				$controller = 'admin';
				return $controller;
			}
*/
			
		//Обычный контроллер
		} else {
			if ($_SERVER['REQUEST_METHOD'] == 'GET')
				return 'get';
			elseif( ($_SERVER['REQUEST_METHOD'] == 'POST') && ( IsSet(model::$ask->mode[0]) || IsSet($this->vars['interface']) ) ){
				if( !IsSet($this->vars['interface']) )
					$this->vars['interface'] = model::$ask->mode[0];
				model::$modules[ model::$ask->module ]->controlInterface($this->vars['interface'], $this->vars, true);
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

		//Контроллера нет - запускаем стандартные
		if( $controller == 'admin' ){
			require_once self::$config['path']['core'] . '/controllers/admin.php';
			$controller = new controller_admin($this->model, $this->vars, $this->cache);
			$controller -> start();
		
		//Существует запрошенный интерфейс
		}elseif( (model::$ask->method == 'POST') && (IsSet( model::$modules[ $current_module ]->interfaces[ $this->vars['interface'] ] ) || IsSet( model::$modules[ $current_module ]->interfaces[ model::$ask->mode[0] ] )) ){
/*
До сюда не доходит, вызывается в defineController
*/

			if( !IsSet($this->vars['interface']) )
				$this->vars['interface'] = model::$ask->mode[0];
			model::$modules[ $current_module ]->controlInterface($this->vars['interface'], $this->vars);
			pr('Вызванный контроллер интерфейса не вернул результата.');
			exit();
		
		//Устаревший контроллер Feedback
		}elseif( (model::$ask->method == 'POST') && ($this->vars['action'] == 'feedback') && IsSet(model::$ask->rec['feedback']) ){
			$this->feedback();
			exit();
		
		//Контроллера нет - запускаем стандартные
		}else{
			require_once self::$config['path']['core'] . '/controllers/get.php';
			$controller = new controller_get($this->model, $this->vars, $this->cache);
			$controller -> start();
		
		}
	}
	
	//Проверка работы в режиме тестирования
	private function checkTestMode(){

		if (intval(@model::$settings['test_mode'])) {
			$current_ip = $_SERVER['REMOTE_ADDR'];
			
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
				if (!headers_sent()){
					header('Content-Type: text/html; charset=utf-8');
					header('HTTP/1.0 404 Not Found');
				}
				print(model::$settings['test_mode_text'] . ' <!--' . $current_ip . '-->');
				exit();
			}
		}
	}
	
	//Контроллер устаревшей формы обратной связи
	private function feedback(){
		$form = unserialize(model::$ask->rec['feedback']);
		if( !is_array($form) )return false;

		$message = '';
		foreach($form['fields'] as $i=>$field)
			$message .= $field['title'].': <strong>'.@$this->vars['f'.$i].'</strong><br />';

		$subject = 'Соощение с сайта '.model::$ask->host;
		$footer = 'Сообщение отослано со страницы: <a href="'.$_SERVER['HTTP_REFERER'].'">' . urldecode( $_SERVER['HTTP_REFERER'] ).'</a><br />'.date( 'd-m-Y H:i');
		$message = 'Пользователь отправил сообщение с сайта http://'.model::$ask->host.'/<br /><br />'.$message.'<br /><hr />'.$footer;
			
		$email = model::initEmail();
		$email->send($form['email'],$subject,$message,'html');
		
		$_SESSION['messages']['feedback']['ok'] = 'Сообщение успешно отправлено';
		
		//Готово
		header('Location: ' . model::$ask->rec['url'].'.html#feedback');
		exit();
	}
}

?>