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
/*	Модифицирован: 20 декабря 2012 года						*/
/*															*/
/************************************************************/

class user
{
	var $title = 'Auth';
	public static $table_name = 'users';
	public static $info;
	
	public $auth_types = array('user' => 'Регистрация на сайте', 'yandex' => 'Яндекс', 'google' => 'Google', 'livejournal' => 'LiveJournal', 'openid' => 'OpenId');

	public function __construct(){
		//Logout
		if (IsSet($_GET['logout'])) {
			self::logout();
			
			header('Location: '.$_SERVER['HTTP_REFERER']);
			exit();
		}

		//Авторизация
		if (IsSet($_SESSION['auth'])) {
			self::authUser();

		//Авторизация
		} elseif (IsSet($_GET['openid_assoc_handle'])) {
			UnSet($_SESSION['auth']);
			session_regenerate_id();
			self::deleteCookie('auth');
			self::finish_OAuthUser();
			//$this->authUser();

		//Авторизация Oauth
		} elseif (IsSet($_GET['login_oauth'])) {
			self::start_OAuthUser();

		//Авторизация
		} elseif (IsSet($_COOKIE['auth'])) {
			self::authUser();

		//Авторизация
		} elseif (IsSet($_GET['login']) && IsSet($_GET['auth'])) {
			self::authUser();

		//Начальные данные пользователя
		} else {
			self::$info = array(
				'id' => 0,
				'title' => 'Гость',
				'admin' => false
			);
		}
	}

	// Выйти из учётной записи
	public static function logout(){
		UnSet($_SESSION['auth']);
		session_regenerate_id();
		self::deleteCookie('auth');
		self::$info = array(
			'id' => 0,
			'title' => 'Гость',
			'admin' => false
		);
	}
	
	//Учёт последнего входа в систему
	public static function updateMyLoginDate($my_id){
		model::execSql('update `'.self::$table_name.'` set `date_logged`=NOW() where `id`="'.$my_id.'" limit 1','update');
	}

	//Запоминаем пользователя после удачной авторизации
	public static function all_ok($user){

		//Аккаунт-ссылка на основной аккаунт
		if( $user['is_link_to'] ){
			$user = model::makeSql(array(
				'tables' => array(
					self::$table_name
				),
				'where' => array(
					'and' => array(
						'`id`="' . intval( $user['is_link_to'] ) . '"',
						'`active`' => '1'
					)
				)
			), 'getrow');
		}
		//Проверка на пустой URL, такое могло случаться на движке моложе 2.14
		if( !$user['url'] ){
			$user = model::$types['sid']->toValue('sid', $user);
			model::execSql('update `'.self::$table_name.'` set `url`="/users/'.mysql_real_escape_string( $user['sid'] ).'" where `id`='.intval($user['id']).' limit 1','update');
		}
		
		$_SESSION['auth'] = $user['session_id'];

		$user 						= default_module::insertRecordUrlType($user);
		user::$info       			= $user;
		user::$info['public_auth'] 	= md5($user['session_id']);
		user::updateMyLoginDate(user::$info['id']);
		//user::$info = default_module::insertRecordUrlType(user::$info);

		if(IsSet($_GET['login']) || IsSet($_GET['openid']) || IsSet($_GET['openid.ns']))
		if( !IsSet( $_SESSION['oauth_referer'] ) ){
			header('Location: /');
			exit();
		}
			
	}
	
	public static function authUser_fast(){
		self::authUser_localhost();
	}
	public static function authUser_long(){
		if( 
			IsSet($_GET['openid_assoc_handle']) and 
			IsSet($_GET['openid_identity']) and 
			IsSet($_GET['openid_mode']) and 
			IsSet($_GET['openid_return_to']) and 
			IsSet($_GET['openid_sig']) and 
			IsSet($_GET['openid_signed'])
		){
			self::finish_OAuthUser();
		}
	}
	//Выбор способа авторизации пользователя
	public static function authUser(){
	
		//Возврат авторизации по OpenID
		if( 
			IsSet($_GET['openid_assoc_handle']) and 
			IsSet($_GET['openid_identity']) and 
			IsSet($_GET['openid_mode']) and 
			IsSet($_GET['openid_return_to']) and 
			IsSet($_GET['openid_sig']) and 
			IsSet($_GET['openid_signed'])
		){
			self::finish_OAuthUser();
		
		//Авторизация пользователя по локальной базе пользователей
		}else{
			self::authUser_localhost();
		}
	}

	//Авторизация пользователя по локальной базе пользователей
	private static function authUser_localhost(){
		//Авторизация по логину/паролю
		if (IsSet($_POST['login']) && IsSet($_POST['password']) && (!IsSet($_POST['title'])) ) {
			$user = model::$types['password'] -> tryAuth( 'login',  $_POST );
			
			UnSet($_POST['login']);
			UnSet($_POST['password']);

			//Залогинелся
			if ( $user ) {

				self::setCookie('auth', $user['session_id']);
				self::all_ok($user);
				$_SESSION['just_logged']=date('H:i:s',strtotime('+10 seconds'));
		
				$result = array(
					'result' => 'redirect',
					'url' => $_SERVER['HTTP_REFERER'],
					'close' => true,
				);
			//Не подошло
			} else {
				user::deleteCookie('auth');
				$result = array(
					'result' => 'message',
					'message' => 'Не получилось, попробуйте ещё',
					'close' => false,
				);
			}
			
			//Ответ
			if( IsSet($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ){
				print( json_encode( $result ) );
				exit();
			}else{
			
			}

		//Авторизация по GET-параметру
		} elseif (IsSet($_GET['login']) && IsSet($_GET['auth'])) {
			$user = model::$types['password'] -> tryAuth( 'auth',  $_POST );
			UnSet($_GET['login']);
			UnSet($_GET['auth']);

		//Авторизация по сессии
		} elseif (strlen(@$_SESSION['auth'])) {
			$user = model::$types['password'] -> tryAuth( 'session',  $_SESSION['auth'] );
			
		//Авторизация по Cookies
		} elseif (strlen(@$_COOKIE['auth'])) {
			$user = model::$types['password'] -> tryAuth( 'session',  $_COOKIE['auth'] );
		}
		
		// Удачно
		if( $user ){
		
			self::setCookie('auth', $user['session_id']);
			self::all_ok($user);
			$_SESSION['just_logged']=date('H:i:s',strtotime('+10 seconds'));
		
		// Не удачно		
		}else{
			self::deleteCookie('auth');
			self::$info = array(
				'id' => 0,
				'title' => 'Гость',
				'admin' => false
			);
		}
		
	}

	
	//Установка Cookie
	private static function setCookie($name, $value){
		if( !IsSet( $_POST['no_cookie'] ) ){
			$time   = time() + 60 * 60 * 24 * 365;
			$path   = '/';
			$domain = '.' . $_SERVER['HTTP_HOST'];
			setcookie($name, $value, $time, $path, $domain);
		}
	}

	public static function setCookiePublic($name, $value){
		if( !IsSet( $_POST['no_cookie'] ) ){
			$time   = time() + 60 * 60 * 24 * 365;
			$path   = '/';
			$domain = '.' . $_SERVER['HTTP_HOST'];
			setcookie($name, $value, $time, $path, $domain);
		}
	}

	//Установка Cookie
	public static function deleteCookie($name){
		$time   = time() - 3600;
		$path   = '/';
		$domain = '.' . $_SERVER['HTTP_HOST'];
		setcookie($name, '', $time, $path, $domain);
	}
	
	
	
	
	//Старт авторизации по OAuth - запрос в сторону провайдера
	private static function start_OAuthUser(){
		
		$provider = $_GET['login_oauth'];
		
		if( !IsSet( $_SESSION['oauth_referer'] ) ){
			$_SESSION['oauth_referer'] = $_SERVER['HTTP_REFERER'];
			if( IsSet( $_GET['anchor'] ) )
				$_SESSION['oauth_referer'] .= '#'.$_GET['anchor'];
		}

		if( in_array($provider, array('vk.com','vk') ) ){
			if( IsSet($_GET['error']))
				return false;
				
			//дефолтные настройки из конфига
			$app_id = model::$settings['oauth_vk_id'];
			$app_secret = model::$settings['oauth_vk_s_key'];
			$my_url = 'http://'.model::$ask->host.'/?login_oauth=vk';

			session_start();
			$code = $_REQUEST["code"];
				
			//получаем код доступа
			if( empty( $code ) ) {
				$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
				$dialog_url = 'https://oauth.vk.com/authorize?client_id='.$app_id.'&scope=notify,friends,photos,status,groups,offline&display=page&response_type=code&redirect_uri=http://'.model::$ask->host.'/?login_oauth=vk';
				//$dialog_url = 'http://api.vk.com/oauth/authorize?client_id='.$app_id.'&redirect_uri=http://'.model::$ask->host.'/?login_oauth=vk';
				echo("<script> top.location.href='" . $dialog_url . "'</script>");
				exit();
			}

			//Получаем Token
			$token_url = 'https://oauth.vk.com/access_token?client_id='.$app_id.'&client_secret='.$app_secret.'&code='.$code.'&redirect_uri=http://'.model::$ask->host.'/?login_oauth=vk';
			
			$f = @file_get_contents( $token_url );
			$token = (array)json_decode( $f );
			//Запрос данных
			$url2="https://api.vk.com/method/getProfiles?uid=".$token['user_id']."&access_token=".$token['access_token']."&fields=uid,first_name,last_name,bdate,photo_big,has_mobile";
			$datas = json_decode(@file_get_contents($url2));
			$datas=(array)$datas;
			
			if( !IsSet( $datas['response'] ) )
				return false;

			$datas=(array)$datas['response'][0];

			self::$info = array(
				'login' => 'vk'.$datas['uid'],
				'password' => $datas['uid'].'thisismyverybigwordformd5',
				'admin' => false,
				'title' => $datas['first_name'].' '.$datas['last_name'],
				'img' => $datas['photo_big'],
				'session_id' => session_id(),
			);
				
			$_POST['login'] = self::$info['login'];
			$_POST['password'] = self::$info['password'];
			//Авторизуем
			self::authUser_localhost();
			$login = model::$types['sid']->correctValue( self::$info['login'] );
	
			//Регистрируем
			if( !self::$info['id']){
				self::$info = array(
					'sid' => model::$types['sid']->correctValue( 'vk'.$datas['uid'] ),
					'shw' => true,
					'active' => true,
					'admin' => intval( @model::$config['openid'][ $_GET['login_oauth'] ] == 'admin' ),
					'session_id' => session_id(),
					'login' => 'vk'.$datas['uid'],
					'password' => $datas['uid'].'thisismyverybigwordformd5',
					'admin' => false,
					'title' => $datas['first_name'].' '.$datas['last_name'],
					'img' => array( 'tmp_name' => $datas['photo_big'] ),
					'session_id' => session_id(),
				);
				
				// День рождения, если требуется
				if( IsSet( model::$modules['users']->structure['rec']['fields']['birthday'] ) ){
					$bdate = explode( '.', $datas['bdate'] );
					$bdate = date( "Y-m-d", strtotime( $bdate[2] . '-' . $bdate[1] . '-' . $bdate[0] ) );
					self::$info['birthday'] = array( 'date' => $bdate );
				}
				
				// Страница на ВК
				if( IsSet( model::$modules['users']->structure['rec']['fields']['web'] ) )
					self::$info['web'] = 'http://vk.com/id' . $datas['uid'];

				// Есть поле для хранения OpenID-данных - записываем
				if( IsSet( model::$modules['users']->structure['rec']['fields']['openid_data'] ) )
					self::$info['openid_data'] = json_encode( $datas );

				//Первый пользователь в системе всегда становится админом
				if( self::ifFirstThenAdmin() )
					self::$info['admin'] = true;
						
				$_POST['login'] = self::$info['login'];
				$_POST['password'] = self::$info['password'];
                $_SESSION['newUserOuath']=true;
				model::addRecord('users', 'rec', self::$info);
				self::authUser_localhost();

			}
		
			//На главную
			header('Location: '.$_SESSION['oauth_referer']);
			UnSet( $_SESSION['oauth_referer'] );
			exit();
		
		
		}elseif( in_array($provider, array('facebook.com', 'facebook')) ){
			if( IsSet( $_GET['error'] ) )
				return;

			//дефолтные настройки из конфига
			$app_id = model::$settings['oauth_facebook_id'];
			$app_secret = model::$settings['oauth_facebook_s_key'];
			$my_url = 'http://'.model::$ask->host.'/?login_oauth=facebook';
			
			session_start();
			$code = $_REQUEST["code"];
				
			//получаем код доступа
			if( empty( $code ) ) {
				$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
//				$dialog_url = 'https://www.facebook.com/dialog/oauth?client_id='.$app_id.'&redirect_uri='.urlencode($my_url).'&scope=email&response_type=token';
				$dialog_url = "http://www.facebook.com/dialog/oauth?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&scope=email&state=".$_SESSION['state'];
				echo("<script> top.location.href='" . $dialog_url . "'</script>");
			}
				
			//получаем токен
			if( $_REQUEST['state'] == $_SESSION['state'] ) {
				
				$token_url = "https://graph.facebook.com/oauth/access_token?"."client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&client_secret=".$app_secret."&code=".$code;
				$response = @file_get_contents($token_url);
				$params = null;
				parse_str($response, $params);
	
				$graph_url = "https://graph.facebook.com/me?access_token=".$params['access_token'];

				//получаем данные пользователя с помощью токена
				$datas = json_decode(@file_get_contents($graph_url));
				$datas=(array)$datas;

				self::$info = array(
					'login' => 'facebook'.$datas['id'],
					'password' => $datas['id'].'thisismyverybigwordformd5',
					'admin' => false,
					'title' => $datas['name'],
					'avatar'=> NULL,
					'photo' => NULL,
					'email' => $datas['email'],
					'session_id' => session_id(),
				);

				$_POST['login'] = self::$info['login'];
				$_POST['password'] = self::$info['password'];
					
				//Авторизуем
				self::authUser_localhost();
				$login = model::$types['sid']->correctValue( self::$info['login'] );
								
				//Регистрируем
				if( !self::$info['id'] ){

					self::$info = array(
						'sid' => model::$types['sid']->correctValue( 'facebook'.$datas['id'] ),
						'shw' => true,
						'active' => true,
						'admin' => intval( @model::$config['openid'][ $_GET['login_oauth'] ] == 'admin' ),
						'session_id' => session_id(),
						'login' => 'facebook'.$datas['id'],
						'password' => $datas['id'].'thisismyverybigwordformd5',
						'admin' => false,
						'title' => $datas['name'],
						'img' => $datas['profile_image_url'],
						'session_id' => session_id(),
					);

					// Есть поле для хранения OpenID-данных - записываем
					if( IsSet( model::$modules['users']->structure['rec']['fields']['openid_data'] ) )
						self::$info['openid_data'] = json_encode( $datas );

					//Первый пользователь в системе всегда становится админом
					if( self::ifFirstThenAdmin() )
						self::$info['admin'] = true;
						
					$_POST['login'] = self::$info['login'];
					$_POST['password'] = self::$info['password'];
                    $_SESSION['newUserOuath']=true;
					model::addRecord('users', 'rec', self::$info);
					self::authUser_localhost();
				}

				header('Location: '.$_SESSION['oauth_referer']);
				UnSet( $_SESSION['oauth_referer'] );
				exit();
				
			}else
				echo("Произошла ошибка авторизации. Попробуйте еще раз.");
				exit();
		
		
		}elseif( in_array($provider, array('twitter.com','twitter') ) ){

			$TWITTER_CONSUMER_KEY = model::$settings['oauth_twitter_id'];
			$TWITTER_CONSUMER_SECRET = model::$settings['oauth_twitter_s_key'];
			$TWITTER_URL_CALLBACK = 'http://' . model::$ask->host . '/?login_oauth=twitter';

			$URL_REQUEST_TOKEN = 'https://api.twitter.com/oauth/request_token';
			$URL_AUTHORIZE = 'https://api.twitter.com/oauth/authorize';
			$URL_ACCESS_TOKEN = 'https://api.twitter.com/oauth/access_token';
			$URL_ACCOUNT_DATA = 'https://api.twitter.com/1.1/users/show.json';

			if (!IsSet($_GET['oauth_verifier'])) {

				// рандомная строка (для безопасности)
				$oauth_nonce = md5(uniqid(rand(), true));

				// время когда будет выполняться запрос (в секундых)
				$oauth_timestamp = time();

				$oauth_base_text = "GET&";
				$oauth_base_text .= urlencode($URL_REQUEST_TOKEN) . "&";
				$oauth_base_text .= urlencode("oauth_callback=" . urlencode($TWITTER_URL_CALLBACK) . "&");
				$oauth_base_text .= urlencode("oauth_consumer_key=" . $TWITTER_CONSUMER_KEY . "&");
				$oauth_base_text .= urlencode("oauth_nonce=" . $oauth_nonce . "&");
				$oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
				$oauth_base_text .= urlencode("oauth_timestamp=" . $oauth_timestamp . "&");
				$oauth_base_text .= urlencode("oauth_version=1.0");

				//Ключ
				$key = $TWITTER_CONSUMER_SECRET . "&";
				$oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

				//составляем гет запрос
				$url = $URL_REQUEST_TOKEN;
				$url .= '?oauth_callback=' . urlencode($TWITTER_URL_CALLBACK);
				$url .= '&oauth_consumer_key=' . $TWITTER_CONSUMER_KEY;
				$url .= '&oauth_nonce=' . $oauth_nonce;
				$url .= '&oauth_signature=' . urlencode($oauth_signature);
				$url .= '&oauth_signature_method=HMAC-SHA1';
				$url .= '&oauth_timestamp=' . $oauth_timestamp;
				$url .= '&oauth_version=1.0';

				//отправляем запрос.
				$response = @file_get_contents($url);
				parse_str($response, $result);
				$oauth_token = $result['oauth_token'];
				$oauth_token_secret = $result['oauth_token_secret'];

				self::setCookie('oauth_token_secret', $oauth_token_secret);

				$url = $URL_AUTHORIZE;
				$url .= '?oauth_token=' . $oauth_token;
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: " . $url);
				exit();

			} else {

				$oauth_nonce = md5(uniqid(rand(), true));
				// время когда будет выполняться запрос (в секундых)
				$oauth_timestamp = time();

				// oauth_token
				$oauth_token = $_GET['oauth_token'];

				// oauth_verifier
				$oauth_verifier = $_GET['oauth_verifier'];

				// oauth_token_secret получаем из сессии, которую зарегистрировали
				// во время запроса request_token
				$oauth_token_secret = $_COOKIE['oauth_token_secret'];

				$oauth_base_text = "GET&";
				$oauth_base_text .= urlencode($URL_ACCESS_TOKEN) . "&";
				$oauth_base_text .= urlencode("oauth_consumer_key=" . $TWITTER_CONSUMER_KEY . "&");
				$oauth_base_text .= urlencode("oauth_nonce=" . $oauth_nonce . "&");
				$oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
				$oauth_base_text .= urlencode("oauth_token=" . $oauth_token . "&");
				$oauth_base_text .= urlencode("oauth_timestamp=" . $oauth_timestamp . "&");
				$oauth_base_text .= urlencode("oauth_verifier=" . $oauth_verifier . "&");
				$oauth_base_text .= urlencode("oauth_version=1.0");

				$key = $TWITTER_CONSUMER_SECRET . "&" . $oauth_token_secret;
				$oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

				$url = $URL_ACCESS_TOKEN;
				$url .= '?oauth_nonce=' . $oauth_nonce;
				$url .= '&oauth_signature_method=HMAC-SHA1';
				$url .= '&oauth_timestamp=' . $oauth_timestamp;
				$url .= '&oauth_consumer_key=' . $TWITTER_CONSUMER_KEY;
				$url .= '&oauth_token=' . $oauth_token;
				$url .= '&oauth_verifier=' . $oauth_verifier;
				$url .= '&oauth_signature=' . urlencode($oauth_signature);
				$url .= '&oauth_version=1.0';

				$response = @file_get_contents($url);

				parse_str($response, $result);

				// GET USER DATA
				$oauth_nonce = md5(uniqid(rand(), true));

// время когда будет выполняться запрос (в секундых)
				$oauth_timestamp = time();

				$oauth_token = $result['oauth_token'];
				$oauth_token_secret = $result['oauth_token_secret'];
				$screen_name = $result['screen_name'];

				$oauth_base_text = "GET&";
				$oauth_base_text .= urlencode($URL_ACCOUNT_DATA) . '&';
				$oauth_base_text .= urlencode('oauth_consumer_key=' . $TWITTER_CONSUMER_KEY . '&');
				$oauth_base_text .= urlencode('oauth_nonce=' . $oauth_nonce . '&');
				$oauth_base_text .= urlencode('oauth_signature_method=HMAC-SHA1&');
				$oauth_base_text .= urlencode('oauth_timestamp=' . $oauth_timestamp . "&");
				$oauth_base_text .= urlencode('oauth_token=' . $oauth_token . "&");
				$oauth_base_text .= urlencode('oauth_version=1.0&');
				$oauth_base_text .= urlencode('screen_name=' . $screen_name);
				$key = $TWITTER_CONSUMER_SECRET . '&' . $oauth_token_secret;
				$signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
// Формируем GET-запрос
				$url = $URL_ACCOUNT_DATA;
				$url .= '?oauth_consumer_key=' . $TWITTER_CONSUMER_KEY;
				$url .= '&oauth_nonce=' . $oauth_nonce;
				$url .= '&oauth_signature=' . urlencode($signature);
				$url .= '&oauth_signature_method=HMAC-SHA1';
				$url .= '&oauth_timestamp=' . $oauth_timestamp;
				$url .= '&oauth_token=' . urlencode($oauth_token);
				$url .= '&oauth_version=1.0';
				$url .= '&screen_name=' . $screen_name;
				$response = @file_get_contents($url);
				$datas = json_decode($response);
				$datas = (array)$datas;
				$image_url = str_replace('_normal', '', $datas['profile_image_url']);
				$tmp_img = str_replace('https://', 'http://', $image_url);
				$image_url = str_replace(['https://', 'http://'], '//', $image_url);
				self::$info = array(
					'login' => 'twitter' . $datas['id'],
					'password' => $datas['id'] . 'thisismyverybigwordformd5',
					'admin' => false,
					'title' => $datas['name'],
					//'avatar' => $datas['profile_image_url'],
					//'photo' => $datas['profile_image_url'],
					'session_id' => session_id(),
				);

				$_POST['login'] = self::$info['login'];
				$_POST['password'] = self::$info['password'];

				//Авторизуем
				self::authUser_localhost();

				//Регистрируем
				if (!self::$info['id']) {

					self::$info = array(
						'sid' => model::$types['sid']->correctValue('twitter' . $datas['id']),
						'shw' => true,
						'active' => true,
						'session_id' => session_id(),
						'login' => 'twitter' . $datas['id'],
						'password' => $datas['id'] . 'thisismyverybigwordformd5',
						'admin' => false,
						'title' => $datas['name'],
						'img' => $image_url,
						'avatar' => array('tmp_name' => $tmp_img),
					);

					// Страница
					if (IsSet(model::$modules['users']->structure['rec']['fields']['web']))
						self::$info['web'] = $datas['url'];

					// Блок
					if (IsSet(model::$modules['users']->structure['rec']['fields']['blog']))
						self::$info['blog'] = 'https://twitter.com/' . $datas['screen_name'];

					// Есть поле для хранения OpenID-данных - записываем
					if (IsSet(model::$modules['users']->structure['rec']['fields']['openid_data']))
						self::$info['openid_data'] = json_encode($datas);

					//Первый пользователь в системе всегда становится админом
					if (self::ifFirstThenAdmin())
						self::$info['admin'] = true;

					$_POST['login'] = self::$info['login'];
					$_POST['password'] = self::$info['password'];

					model::$modules['users']->addRecord(self::$info);
					self::authUser_localhost(true);
					header('Location: /');
                    $_SESSION['newUserOuath']=true;
					UnSet($_SESSION['oauth_referer']);
					exit();

				}
			}

			header('Location: ' . $_SESSION['oauth_referer']);
			UnSet($_SESSION['oauth_referer']);
			exit();


		}elseif( $provider == 'yandex.ru' ){
			echo("Авторизация через yandex временно не доступна. Пожалуйста, используйте аккаунты других соц. сетей для входа на сайт.");
                        exit();

			$url = 'http://openid.yandex.ru/trusted_request/
				?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
				&openid.mode=checkid_setup
				&openid.return_to=http://' . model::$ask->host . '/?login
				&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select
				&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select
				&openid.realm=http://' . model::$ask->host . '/
				&openid.ui.ns=http://specs.openid.net/extensions/ui/1.0
				&openid.ui.icon=true&openid.ns.ax=http://openid.net/srv/ax/1.0
				&openid.ax.mode=fetch_request
				&openid.ax.required=firstname,email,language
				&openid.ax.type.firstname=http://axschema.org/namePerson
				&openid.ax.type.email=http://axschema.org/contact/email
				&openid.ax.type.language=http://axschema.org/pref/language
			';

			header('Location: '.$url);
			exit();


		} elseif( $provider == 'google.com' ) {

            if(model::$ask->host == 'elobogrev.ru' || model::$ask->host == 'uimpex.ru' || model::$ask->host == 'yamobi.ru' || 1==1){

                $client_id = '421241735500-mvpgl1rgttjsn2ig9ohcu2sn8332udo6.apps.googleusercontent.com';
                $client_secret = '38zcTt8D4lBLgS8OBiBjJ1MP';

                if(!isset($_GET['code'])){

                    $authorize_endpoint = 'https://accounts.google.com/o/oauth2/auth';
                    $url = $authorize_endpoint;
                    $url .= '?response_type=code';
                    $url .= '&client_id='.$client_id;
                    $url .= '&scope=email%20profile';
                    $url .= '&redirect_uri=http://' . model::$ask->host . '/?login_oauth=google.com';
                    $url .= '&approval_prompt=force';

                    header('Location: '.$url);

                }else{
                    $code = $_GET['code'];
                    $access_endpoint = 'https://accounts.google.com/o/oauth2/token';


                    $request = implode(
                        '&',
                        [
                            'code=' . $code,
                            'client_id=' . $client_id,
                            'client_secret=' . $client_secret,
                            'redirect_uri=http://' . model::$ask->host . '/?login_oauth=google.com',
                            'grant_type=authorization_code',
                        ]
                    );
                    $opts = [
                        'http' => [
                            'method'  => "POST",
                            'content' => $request,
                            'header'  => "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\nContent-Length: "
                                . strlen($request)
                        ]
                    ];
                    try {
                        $cntnt = file_get_contents($access_endpoint, 0, stream_context_create($opts));
                    } catch (ErrorException $ex) {
                        throw new HttpException(500, "Error!");
                    }
                    $response = json_decode($cntnt, true);

                    //$data_url = 'https://www.googleapis.com/plus/v1/people/104687879974677335810';
                    $data_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
                    $user_data_url = $data_url.'?access_token='.$response['access_token'];
                    $user_data = file_get_contents($user_data_url);

                    if($user_data){
                        $data = json_decode($user_data, true);
                        self::$info = array(
                            'login' => 'googlecom_'.$data['id'],
                            'password' => $data['id'].'thisismyverybigwordformd5',
                            'admin' => false,
                            'title' => $data['name'],
                            'img' => $data['picture'],
                            'session_id' => session_id(),
                        );

                        $_POST['login'] = self::$info['login'];
                        $_POST['password'] = self::$info['password'];

                        //Авторизуем
                        self::authUser_localhost();
                        $login = model::$types['sid']->correctValue( self::$info['login'] );

                        //Регистрируем
                        if( !self::$info['id'] ){

                            $admin = isset(model::$config['openid']) && isset(model::$config['openid'][ $data['hd'] ]) && (model::$config['openid'][ $data['hd'] ] == 'admin');

                            self::$info = array(
                                'sid' => model::$types['sid']->correctValue( 'googlecom_'.$data['id'] ),
                                'shw' => true,
                                'active' => true,
                                'admin' => $admin,
                                'login' => 'googlecom_'.$data['id'],
                                'password' => $data['id'].'thisismyverybigwordformd5',
                                'title' => $data['name'],
                                'img' => array( 'tmp_name' => $data['picture'] ),
                                'session_id' => session_id(),
                                'email' => $data['email'],
                            );


                            $_POST['login'] = self::$info['login'];
                            $_POST['password'] = self::$info['password'];

                            $_SESSION['newUserOuath']=true;
                            model::addRecord('users', 'rec', self::$info);
                            self::authUser_localhost();

                        }
                    }

                    header('Location: /');
                    exit();

                }

            }else{

                $url = 'https://www.google.com/accounts/o8/ud
					?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
					&openid.mode=checkid_setup
					&openid.return_to=http://' . model::$ask->host . '/?login
					&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select
					&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select
					&openid.realm=http://' . model::$ask->host . '/
					&openid.ui.ns=http://specs.openid.net/extensions/ui/1.0
					&openid.ui.icon=true
					&openid.ns.ax=http://openid.net/srv/ax/1.0
					&openid.ax.mode=fetch_request
					&openid.ax.required=firstname,lastname,email,language
					&openid.ax.type.firstname=http://axschema.org/namePerson/first
					&openid.ax.type.lastname=http://axschema.org/namePerson/last
					&openid.ax.type.email=http://axschema.org/contact/email
					&openid.ax.type.language=http://axschema.org/pref/language
					';
                $url = str_replace( "\n", '', $url );
                $url = str_replace( "	", '', $url );
                $url = str_replace( " ", '', $url );

                header('Location: '.$url);

            }

            exit();
		}
	}
	
	//Авторизация пользователя на удалённом сервере
	private static function finish_OAuthUser(){
	
		//Если есть разрешённые сервера для авторизации
		if( model::$config['openid'] ){
		
			require_once( model::$config['path']['libraries'].'/openid.php' );
			$openid = new LightOpenID( 'http://' . $_SERVER['HTTP_HOST'] );
			
			try {
				if(!$openid->mode) {
				}elseif($openid->mode == 'cancel') {
					echo 'User has canceled authentication!';
				}else{
				
					$params = $openid->getAttributes();

					//Проверяем email на наличие нужного домена
					if( substr_count($params['contact/email'], '@') === 1 ){
						
						$openid_domain = parse_url( $_GET['openid_op_endpoint'] );
						$openid_domain = $openid_domain['host'];
						$openid_domain = str_replace('openid.','', $openid_domain);
						$openid_domain = str_replace('www.','', $openid_domain);
								
						if( @in_array($openid_domain, model::$settings['oauth_openid']) ){

							//Смотрим на конфиг, давать ли пользователям этого домена админа
							$openid_user_admin = false;
							if( (model::$config['openid']['sitko.ru'] == 'admin') && substr_count($params['contact/email'], '@sitko.ru') )
								$openid_user_admin = true;

							$login = model::$types['sid']->correctValue( $openid_domain.'_'.$params['contact/email'] );
							if( IsSet($params['namePerson/first']) ) 
								$title = $params['namePerson/first'].' '.$params['namePerson/last'];
							else
								$title = $params['namePerson'];
					
							//Начинаем регить
							self::$info = array(
								'login' => $login,
								'password' => $_GET['openid_identity'],	//$openid->data['openid_assoc_handle'],
								'admin' => $openid_user_admin,
								'title' => $title,
								'email' => $params['contact/email'],
								'session_id' => session_id(),
							);
							$_POST['login'] = self::$info['login'];
							$_POST['password'] = self::$info['password'];
							
							//Авторизуем
							$user = self::$info;
							self::authUser_localhost();
							
							//Регистрируем
							if( !self::$info['id'] ){
								self::$info = $user;
								self::$info['sid'] = $login;
								self::$info['shw'] = true;
								self::$info['active'] = true;
								self::$info['admin'] = $openid_user_admin;
								self::$info['session_id'] = session_id();

								//Первый пользователь в системе всегда становится админом
								if( self::ifFirstThenAdmin() )
									self::$info['admin'] = true;
									
								$_POST['login'] = self::$info['login'];
								$_POST['password'] = self::$info['password'];
								
								//Проверяем уже заполненный профиль указанного человека
								if( strlen(self::$info['email'])>5 ){
									$old = model::execSql('select `id` from `'.self::$table_name.'` where `email`="'.mysql_real_escape_string( self::$info['email'] ).'"','getrow');
									if( $old )
										self::$info['is_link_to'] = $old['id'];										
								}
				
								model::addRecord('users', 'rec', self::$info);
								self::authUser_localhost();
							}
							
							header('Location: '.$_SESSION['oauth_referer']);
							UnSet( $_SESSION['oauth_referer'] );
							exit();
						}
					}else{echo "Ошибка передачи данных";}
					
				}
				
			} catch(ErrorException $e) {
				echo $e->getMessage();
			}
		}
	}
	public static function ifFirstThenAdmin(){
		$c = model::execSql('select count(`id`) as `count` from `'.self::$table_name.'`', 'getrow');
		return !$c['count'];
	}
	
	
	public static function is_authorized(){
		return !!self::$info['id'];		
	}
	public static function is_admin(){
		return self::$info['admin'];		
	}
	public static function is_moder(){
		return self::$info['moder'];		
	}

}

?>
