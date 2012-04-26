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
		$this->config = $config;
		$this->log    = $log;
		$this->cache  = $cache;

		//Подгружаем все переданные параметры
		$this->loadData();
		
		//Загружаем модель
		$this->loadModel();
		
		//Если сайт работает в режиме тестирования - проверяем можно ли показывать
		$this->checkTestMode();
		
		//Определение контроллера
		$controller = $this->defineController();
		
		//Записываем контроллер
		$this->model->ask->method = $controller;
		
		//Инициализация контроллера
		$result = $this->execController($controller);
		
		//Готово
		return $result;
	}
	
	//Подгрузка переданных параметров
	function loadData()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->vars = $_GET;
		} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->vars = array_merge($_GET, $_POST, $_FILES);
		}
	}
	
	//Загружаем модель
	private function loadModel()
	{
		//Обработка ошибки отсутствия файла
		if (!file_exists($this->config['path']['core'] . '/classes/model.php'))
			$this->log->err('file_not_found', array(
				'path' => $this->config['path']['core'] . '/classes/model.php',
				'file' => 'controller_manager',
				'function' => 'loadModel',
				'vars' => $this->config
			));
		
		//Подключение класса
		require($this->config['path']['core'] . '/classes/model.php');
		
		//Инициализация модели
		$this->model = new model($this->config, $this->log, $this->cache);
	}
	
	//Определение контроллера
	private function defineController()
	{
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
			if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
				return 'xhr';
			elseif ($_SERVER['REQUEST_METHOD'] == 'GET')
				return 'get';
			elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
				return 'post';
		}
		
		//XHR-запросы
		if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$controller = 'xhr';
			
			//Убираем указательные переменные из области данных
			return $controller;
		}
		
		//Перебираем все контроллеры
		foreach ($this->controllers as $sid => $controller)
		//Если допустимый метод
			if (in_array($_SERVER['REQUEST_METHOD'], $controller['methods']))
				if (in_array($this->model->ask->output, $controller['format']))
					return $sid;
		
		//Если существует указание
		if (IsSet($this->controllers[$_SERVER['REQUEST_METHOD']]))
			return $_SERVER['REQUEST_METHOD'];
		
		//Контроллер по умолчанию
		return 'get';
	}
	
	//Ищем обработчик данного запроса
	public function execController($controller)
	{
		
		//Следующая строчка позволяет ссылать интерфейсы на корень сайта
		if(IsSet($this->model->ask->full_url[0]))
			if(IsSet($this->model->modules[ $this->model->ask->full_url[0] ]))
				$mod=$this->model->ask->full_url[0];
			else
				$mod = false;
		else
			$mod=false;
			
		
		//Костыль: если модуль находится глубже первого уровня - можно передатьуказатель на модуль дополнительно
		if( (!$mod) && (IsSet($this->vars['module'])) ){	
			$mod = $this->vars['module'];
		}
		
		//Если в модуле установлен перехватчик контроллера
		if (IsSet($this->model->modules[$mod]->controls[$controller])) {
			//Сокращаем обращение
			$module   = $this->model->modules[$mod];
			//Функция-обработчик
			$function = $this->model->modules[$mod]->controls[$controller]['function'];
			//Заупскаем обработчик
			$module->$function($this->vars);
			
		//Если указанный интерфейс существует в модуле
		} elseif (IsSet($this->model->modules[$mod]->interfaces[$this->vars['interface']]) && ($_SERVER['REQUEST_METHOD']!='GET') ) {

			//Сокращаем обращение
			$module   = $this->model->modules[$mod];

			//Требуется авторизация
			if( $module->interfaces[ $this->vars['interface'] ]['auth'] and (!$this->model->user->info['id']) ){
				exit();
			}

			//Функция-обработчик
			$function = $module->interfaces[$this->vars['interface']]['control'];
			
			//Captcha
			if($module->interfaces[ $this->vars['interface'] ]['protection'] == 'captcha'){
				if($this->vars['captcha'] !== $_SESSION['form_captcha_code']){
					$result=array(
						'result'=>'message',
						'message'=>'Неверный защитный код',
						'close'=>false,
					);
					$module->answerInterface($this->vars['interface'],$result);
				}
			}
			
			//Стандартная функция добавления
			if( $function == 'default_add'){
				$url = $this->model->addRecord($module->info['sid'], $module->interfaces[ $this->vars['interface'] ]['structure_sid'], $this->vars);
				header('Location: '.$url);
				exit();

			//Стандартная функция добавления
			}elseif( $function == 'default_edit'){
				$url = $this->model->updateRecord($module->info['sid'], $module->interfaces[ $this->vars['interface'] ]['structure_sid'], $this->vars);
				header('Location: '.$url);
				exit();

			//Своя функция-обработчик
			}else{
				//Заупскаем обработчик
				$url = $module->$function($this->vars);
				header('Location: '.$url);
				exit();
			}
			
		//Loginza OpenID Auth
		}elseif( IsSet($this->vars['token']) ){
			//Получаем данные о пользователе из системы Loginza
			$c = file_get_contents('http://loginza.ru/api/authinfo?token='.$this->vars['token']);
			$data = json_decode($c);

			//Имя пользователя
			if(IsSet($data->name->full_name))
				$name = $data->name->full_name;
			elseif( IsSet($data->name->first_name) and IsSet($data->name->last_name) )
				$name = $data->name->first_name.' '.$data->name->last_name;
			
			//Все данные что удалось собрать о пользователе
			$user = array(
				'sid' => md5($data->identity),
				'title' => $name,
				'nickname' => $data->nickname,
				'date_of_birth' => $data->dob,
				'gender' => $data->gender,
				'login' => md5($data->identity),
				'password' => md5(time().$data->identity.rand(0,100000)),
				'avatar' => $data->photo,
				'photo' => $data->photo,
				'email' => $data->email,
				'www' => $data->web->default,
			);

			//Авторизация (если пользователь существует)
			$_POST['login'] = $user['login'];
			$_POST['password'] = $user['password'];
			$this->model->authUser();
			
			//Пользователь не найден - будем регистрировать
			if(!$this->model->user->info['id']){
				$user['password_copy'] = $user['password'];
				$user['interface'] = 'registration';
				$email = $user['email'];
				
				$user['email'] = $user['id'].'@loginza.opendev.ru';
				$this->model->modules['users']->interfaces['registration']['ajax']='internal';
				
				$result = $this->model->modules['users']->registerUser($user);
				if($this->model->user->info['id']){
					$user['email'] = $email;
					$this->model->execSql('update `users` set `email`="'.mysql_real_escape_string($email).'" where `id`='.intval($this->model->user->info['id']).' limit 1','update');
				}
			}
			
			header('Location: /');
			exit();
		}
		
		//3. Проверяем защиты для контроллера и запускаем контроллер модели
		if ($this->checkProtectionOfController($controller))
			//Проверяем достаточен ли уровень доступа
			if ($this->checkAccessToController($controller)) {
			
				//Путь к файлу контроллера
				$path = $this->config['path']['controllers'] . '/' . $this->controllers[$controller]['path'];
				
				//Обработка ошибки отсутствия файла
				if (!is_file($path)){
					pr_r($_POST);
					$this->log->err('file_not_found', array(
						'path' => $path,
						'file' => 'controller_manager',
						'function' => 'execController',
						'controller' => '['.$controller.']',
						'vars' => @array_merge($controller, $this->vars)
					));
				}
			
				//Подгружаем класс контроллера
				if (is_file($path)){
					require($path);	
				} else {
					print('Не найден указанный контроллер');
					exit();
				}
				
				//Так он будет называться
				$name = 'controller_' . $controller;
				
				//Инициализируем
				$action = new $name($this->model, $this->vars, $this->cache);
				
				//Передаём управление
				$this->model->controller = $action;
				$action->start();
			}
	}
	
	//Проверка доступа текущего пользователя к указанному контроллеру
	private function checkProtectionOfController($controller)
	{
		return true;
	}
	

	//Проверка доступа текущего пользователя к указанному контроллеру
	private function checkAccessToController($controller)
	{
		return true;
	}
	
	//Проверка работы в режиме тестирования
	private function checkTestMode()
	{
	
		if (intval(@$this->model->settings['test_mode'])) {
			$current_ip = GetUserIP();
			
			//Обработка ошибки
			if (!file_exists($this->config['path']['core'] . '/ip_good.txt'))
				$this->log->err('file_not_found', $this->config['path']['core'] . '/ip_good.txt');
			
			//Подгружаем список администраторских IP-адресов
			$white_ips = file($this->config['path']['core'] . '/ip_good.txt');
			
			//Чистим
			foreach ($white_ips as $i => $p)
				$white_ips[$i] = trim($p);
			
			//Проверяем
			if (!in_array($current_ip, $white_ips)) {
				if (!headers_sent())
					header('Content-Type: text/html; charset=utf-8');
				print($this->model->settings['test_mode_text'] . ' <!--' . $current_ip . '-->');
				exit();
			}
		}
	}
}

?>