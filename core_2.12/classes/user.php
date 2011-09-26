<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Класс пользователя									*/
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

class user
{
	var $title = 'Auth';
	var $table_name = 'users';

	public $auth_types = array('user' => 'Регистрация на сайте', 'yandex' => 'Яндекс', 'google' => 'Google', 'livejournal' => 'LiveJournal', 'openid' => 'OpenId');

	public function __construct($model)
	{
		$this->model = $model;

		//Logout
		if (IsSet($_GET['logout'])) {
			UnSet($_SESSION['auth']);
			$this->deleteCookie('auth');
			$this->info = array(
				'id' => 0,
				'title' => 'Гость',
				'admin' => false
			);
			header('Location: ' . $_SERVER['HTTP_REFERER']);
			exit();
		}

		//Авторизация
		if (IsSet($_SESSION['auth'])) {
			$this->authUser();

		//Авторизация
		} elseif (IsSet($_COOKIE['auth'])) {
			$this->authUser();

		//Авторизация
		} elseif (IsSet($_GET['login']) && IsSet($_GET['auth'])) {
			$this->authUser();

		//Начальные данные пользователя
		} else {
			$this->info = array(
				'id' => 0,
				'title' => 'Гость',
				'admin' => false
			);
		}

		//Помним дато последней авторизации
		if( $this->info['id'] ){
			//Учёт последнего входа в систему
			$this->updateMyLoginDate($this->info['id']);
		}

	}

	//Учёт последнего входа в систему
	public function updateMyLoginDate($my_id){
		$this->model->execSql('update `'.$this->table_name.'` set `date_logged`=NOW() where `id`="'.$my_id.'" limit 1','update');
//		pr($this->model->last_sql);
	}

	//Запоминаем пользователя после удачной авторизации
	function all_ok($user){
		$_SESSION['auth'] = $user['session_id'];
		$this->info       = $user;
		$this->info['public_auth'] = md5($user['session_id']);
	}
	
	public function authUser()
	{
		
		//Авторизация по логину/паролю
		if (IsSet($_POST['login']) && IsSet($_POST['password']) && (!IsSet($_POST['title'])) ) {
			//Поиск по базе
			$user = $this->model->makeSql(array(
				'tables' => array(
					$this->table_name
				),
				'where' => array(
					'and' => array(
						'`login`="' . mysql_real_escape_string($_POST['login']) . '"',
						'`password`="' . $this->model->types['password']->encrypt($_POST['password']) . '"',
						'access' => '1'
					)
				)
			), 'getrow');

			UnSet($_POST);
			
			//Запоминаем
			if (IsSet($user['id'])) {
				$this->setCookie('auth', $user['session_id']);
				$this->all_ok($user);
				$_SESSION['just_logged']=date('H:i:s',strtotime('+10 seconds'));
			}

		//Авторизация по сессии
		} elseif (strlen(@$_SESSION['auth'])) {
			//Поиск по базе
			$user = $this->model->makeSql(array(
				'tables' => array(
					$this->table_name
				),
				'where' => array(
					'and' => array(
						'`session_id`="' . mysql_real_escape_string($_SESSION['auth']) . '"',
						'access' => '1'
					)
				)
			), 'getrow');

			//Запоминаем
			if (IsSet($user['id']))
				$this->all_ok($user);

			//Первая страница после авторизации через форму "логин/пароль"
			if($_SESSION['just_logged']){
				if($_SESSION['just_logged'] >= date('H:i:s')){
					$this->info['just_logged']=$_SESSION['just_logged'];
				}else{
					UnSet($_SESSION['just_logged']);
				}
			}
			
			
		//Авторизация по Cookies
		} elseif (strlen(@$_COOKIE['auth'])) {
			//Поиск по базе
			$user = $this->model->makeSql(array(
				'tables' => array(
					$this->table_name
				),
				'where' => array(
					'and' => array(
						'`session_id`="' . mysql_real_escape_string($_COOKIE['auth']) . '"',
						'`active`' => '1'
					)
				)
			), 'getrow');

			//Запоминаем
			if (IsSet($user['id']))
				$this->all_ok($user);
	
				
		//Авторизация по GET-параметру
		} elseif (IsSet($_GET['login']) && IsSet($_GET['auth'])) {
			//Поиск по базе
			$user = $this->model->makeSql(array(
				'tables' => array(
					$this->table_name
				),
				'where' => array(
					'and' => array(
						'`login`="' . mysql_real_escape_string($_GET['login']) . '"',
						'MD5(`session_id`)="' . mysql_real_escape_string($_GET['auth']) . '"',
						'access' => '1'
					)
				)
			), 'getrow');

			//Запоминаем
			if (IsSet($user['id'])) {
				$this->setCookie('auth', $user['session_id']);
				$this->all_ok($user);
			}

		//Не авторизован
		} else {
			$this->deleteCookie('auth');
			$this->info = array(
				'id' => 0,
				'title' => 'Гость',
				'admin' => false
			);
		}
	}

	//Установка Cookie
	private function setCookie($name, $value)
	{
		$time   = time() + 60 * 60 * 24 * 365;
		$path   = '/';
		$domain = '.' . $_SERVER['HTTP_HOST'];
		setcookie($name, $value, $time, $path, $domain);
	}

	//Установка Cookie
	public function deleteCookie($name)
	{
		$time   = time() - 3600;
		$path   = '/';
		$domain = '.' . $_SERVER['HTTP_HOST'];
		setcookie($name, $value, $time, $path, $domain);
	}

}

?>
