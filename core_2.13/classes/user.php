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
			header('Location: ?');
			exit();
		}

		//Авторизация
		if (IsSet($_SESSION['auth'])) {
			$this->authUser();

		//Авторизация
		} elseif (IsSet($_GET['openid_assoc_handle'])) {
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
	}

	//Запоминаем пользователя после удачной авторизации
	function all_ok($user){
		$_SESSION['auth'] = $user['session_id'];
		$this->info       = $user;
		$this->info['public_auth'] = md5($user['session_id']);
	}
	
	public function authUser()
	{
	
		//Возврат аутентификации по OpenID
		if( 
			IsSet($_GET['openid_assoc_handle']) and 
			IsSet($_GET['openid_identity']) and 
			IsSet($_GET['openid_mode']) and 
			IsSet($_GET['openid_return_to']) and 
			IsSet($_GET['openid_sig']) and 
			IsSet($_GET['openid_signed'])
		) {
			$this->authUser_openid();
		
		//Авторизация пользователя по локальной базе пользователей
		}else{
			$this->authUser_localhost();
		}
	}

	//Авторизация пользователя по локальной базе пользователей
	private function authUser_localhost(){
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

			UnSet($_POST['login']);
			UnSet($_POST['password']);
			
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

	//Авторизация пользователя на удалённом сервере
	private function authUser_openid(){
		//Если есть разрешённые сервера для авторизации
		if( $this->model->config['openid'] ){
			require_once( $this->model->config['path']['libraries'].'/openid.php' );
			$openid = new LightOpenID( 'http://' . $_SERVER['HTTP_HOST'] );
			try {
				if(!$openid->mode) {
				} elseif($openid->mode == 'cancel') {
					echo 'User has canceled authentication!';
				} else {
					$params = $openid->getAttributes();
					
					//Проверяем email на наличие нужного домена
					if( substr_count($params['contact/email'], '@') === 1 ){
						$openid_domain = substr($params['contact/email'], strpos($params['contact/email'], '@')+1);
						if( IsSet($this->model->config['openid'][ $openid_domain ]) ){
							
							//Смотрим на конфиг, давать ли пользователям этого домена админа
							$openid_user_admin = ( $this->model->config['openid'][ $openid_domain ] == 'admin' );
						
							//Начинаем регить
							$this->info = array(
								'login' => $params['contact/email'],
								'password' => $_GET['openid_identity'],
								'admin' => true,
								'title' => $params['namePerson/first'].' '.$params['namePerson/last'],
								'email' => $params['contact/email'],
								'session_id' => session_id(),
							);
							$_POST['login'] = $this->info['login'];
							$_POST['password'] = $this->info['password'];
							
							//Авторизуем
							$this->authUser_localhost();
							
							//Регистрируем
							if( !$this->info['id'] ){
								$sql = 'insert into `users` set 
									`sid`="'.$this->info['login'].'",
									`date_public`=NOW(),
									`date_added`=NOW(),
									`date_modify`=NOW(),
									`title`="'.$this->info['title'].'",
									`shw`=1,
									`login`="'.$this->info['login'].'",
									`password`="'.$this->model->types['password']->encrypt( $this->info['password'] ).'",
									`email`="'.$this->info['email'].'",
									`access`="|admin=rwd|moder=rw-|all=r--|",
									`admin`='.intval($openid_user_admin).',
									`active`=1,
									`session_id`="'.session_id().'",
									`domain`="all",
									`ln`=1
								';
								$this->model->execSql($sql, 'insert');
								$this->authUser_localhost();
							}
								}
					
					}
				}
				
			} catch(ErrorException $e) {
				echo $e->getMessage();
			}
			}
}
	
	//Установка Cookie
	private function setCookie($name, $value)
	{
		if( !IsSet( $_POST['no_cookie'] ) ){
			$time   = time() + 60 * 60 * 24 * 365;
			$path   = '/';
			$domain = '.' . $_SERVER['HTTP_HOST'];
			setcookie($name, $value, $time, $path, $domain);
		}
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
