<?php

class ModelLoader{

	public static $config;
	
	//Загрузка конфига
	public function loadConfig( $config ){
	
		//Совместимость с версиями до 2.14
		$config['path']['libraries'] = 			$config['path']['core'].'/../libs';
		$config['path']['admin_templates'] = 	$config['path']['core'].'/templates';
	
		if( !IsSet( $config['path']['templates'] ) )
			$config['path']['templates'] = 			$config['path']['www'].'/../templates';
			
		$config['path']['modules'] = 			$config['path']['www'].'/../modules';
		$config['path']['backup'] = 			$config['path']['www'].'/../backup';
		$config['path']['temp'] = 				$config['path']['www'].'/../tmp';
		$config['path']['tmp'] = 				$config['path']['www'].'/../tmp';
		$config['path']['cache'] = 				$config['path']['www'].'/../cache';
		$config['path']['admin'] = 				$config['path']['www'].'/admin';

		if( !IsSet( $config['path']['public_javascript'] ) ){
			$config['path']['public_images'] = 		$config['path']['images'];
			$config['path']['public_files'] = 		$config['path']['files'];
			$config['path']['public_styles'] = 		$config['path']['styles'];
			$config['path']['public_javascript'] = 	$config['path']['javascript'];

			$config['path']['images'] = 			$config['path']['www'].$config['path']['public_images'];
			$config['path']['files'] = 				$config['path']['www'].$config['path']['public_files'];
			$config['path']['styles'] = 			$config['path']['www'].$config['path']['public_styles'];
			$config['path']['javascript'] = 		$config['path']['www'].$config['path']['public_javascript'];
		}

		$config['settings']['phpversion'] = phpversion();

		@ini_set('include_path',implode(';',$config['path']));

		return $config;
	}

	//Подключение базы данных
	public function loadDatabase(){
		if( !$this->db ){
		
			//Поддерживаемые форматы баз данных
			$supported_databases = array(
				false => array(
					'mysql' => 'mysql.php'
				),
				'ado' => array(
					'mysql' => 'adodb5/adodb.inc.php'
				)
			);

			//Инициализация баз данных
			$db = array();
			foreach (model::$config['db'] as $name => $one) {
				if( IsSet( $supported_databases[ $one['lib_pack'] ][ $one['type'] ] ) ) {
					
					model::$config['db'][$name]['supported'] = true;
					require_once(model::$config['path']['libraries'] . '/' . $supported_databases[ $one['lib_pack'] ][ $one['type'] ] );
					$n = $one['type'];
					
					//ADO
					if( $one['lib_pack'] == 'ado' ) {
						$db[ $name ] = ADONewConnection( $one['type'] );
						$db[ $name ]->SetFetchMode( ADODB_FETCH_ASSOC );
						$db[ $name ]->debug = false;
						
					//не ADO
					} else {
						$db[ $name ] = new $n( $model );
					}
					
					$db[$name]->Connect($one['host'], $one['user'], $one['password'], $one['name']);
					if( !$db[$name]->connection ){
						print('Ошибка соединения с базой данных.');
						exit();
					}
					
					$db[$name]->Execute('set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"');
					
				} else {
					model::$config['db'][$name]['supported'] = false;
				}
			}

			return $db;
		
		}
	}

	//Подгружаем типы данных
	public function loadTypes(){
		
		//Загружаем библиотеку типов данных
		$supported_types = array(
			'default' => 'field_type_default.php',
			'id' => 'field_type_id.php',
			
			'sid' => 'field_type_sid.php',
			'ln' => 'field_type_ln.php',
			'int' => 'field_type_int.php',
			'float' => 'field_type_float.php',
			
			'label' => 'field_type_label.php',
			'hidden' => 'field_type_hidden.php',
			
			'text' => 'field_type_text.php',
			'textarea' => 'field_type_textarea.php',
			'text_editor' => 'field_type_texteditor.php',
			'textwiki' => 'field_type_textwiki.php',
			'textmeta' => 'field_type_textmeta.php',
			
			'password' => 'field_type_password.php',
			'tags' => 'field_type_tags.php',
			'count' => 'field_type_count.php',
			'map' => 'field_type_map.php',
			'tree' => 'field_type_tree.php',
			'comments' => 'field_type_comments.php',
			'votes' => 'field_type_votes.php',
			'rating' => 'field_type_rating.php',
			
			'date' => 'field_type_date.php',
			'domain' => 'field_type_domain.php',
			'graph' => 'field_type_graph.php',
			'datetime' => 'field_type_datetime.php',
			
			'image' => 'field_type_image.php',
			'gallery' => 'field_type_gallery.php',
			'video' => 'field_type_video.php',
			'file' => 'field_type_file.php',
			'user' => 'field_type_user.php',
			'access' => 'field_type_access.php',
			'html' => 'field_type_html.php',
			'feedback' => 'field_type_feedback.php',
			'robots' => 'field_type_robots.php',
			
			'menu' => 'field_type_menu.php',
			'menum' => 'field_type_menum.php',
			'module' => 'field_type_module.php',
			
			'params' => 'field_type_params.php',
			'array' => 'field_type_array.php',
			
			'link' => 'field_type_link.php',
			'linkm' => 'field_type_linkm.php',
			
			'check' => 'field_type_check.php',
			
			'tree' => 'field_type_tree.php'
		);

		//Инициализируем типы данных
		$types = array();
		foreach ($supported_types as $type_sid => $path){
			$type_path = model::$config['path']['core'] . '/classes/types/' . $path;
			if( file_exists( $type_path ) ){
				require_once( $type_path);
				$type_name = 'field_type_' . $type_sid;
				$types[ $type_sid ] = new $type_name( $this );
			}
		}
		
		return $types;
	}

	//Подгружаем пользовательский тип данных
	public function loadUserType( $field ){
		if( ( $field['type'][0] == '_' ) && IsSet( $field['type_path'] ) ){
			$type_path = model::$config['path']['www'] . '/' . $field['type_path'];

			if( file_exists( $type_path ) ){
				require_once( $type_path);
				$type_sid = $field['type'];
				$type_name = 'field_usertype' . $type_sid;
				model::$types[ $type_sid ] = new $type_name( $this );
				
				return true;
			}
		}
		return false;
	}

	//Подключение модулей
	public function loadModules(){
		$modules = array();
		require_once( model::$config['path']['core'] . '/classes/default_module.php' );
		
		//Подгружаем модули
		$mods = $this->execSql('select * from `modules` where `active`=1 order by `id`','getall');

		//Обратная совместимость с ядрами 2.13 и меньше
		foreach($mods as $i=>$mod)
			if($mod['prototype'] == 'start')
				$mods[$i]['sid'] = 'start';

		foreach ($mods as $module) {
			
			$module_path = model::$config['path']['modules'] . '/' . $module['prototype'] . '.php';
			if (file_exists($module_path)) {
				require_once($module_path);

				$module['url']  = '/' . $module['sid'];
				$module['path'] = $module_path;
				
				$name = $module['prototype'] . '_module';
				$this->modules[ $module['sid'] ] = new $name($this, $module);
			}
		}
		
		return $this->modules;
	}

	//Подгрузка расширений к модулю
	public function loadExtensions(){

		$extensions = array();
		//Подгрузка библиотеки расширений к модулям, инициализация
		if( is_array(model::$config['extensions']) )
		foreach (model::$config['extensions'] as $extention_sid => $filename) {
			//Подключаем файл библиотеки
			require_once(model::$config['path']['core'] . '/extensions/' . $filename);
			//Создаём
			$name                             = 'extention_' . $extention_sid;
			$extensions[ $extention_sid ] = new $name($this);
			$extensions[ $extention_sid ] -> execute();
		}

		return $extensions;
	}

	//Загрузка настроек домена
	public function loadSettings(){
		$settings = array();
		
		$res = $this->execSql('select * from `settings` where ' . model::pointDomain(), 'getall');

		// Добавление поля field для старых версий ядра
		if( !IsSet($res[0]['field']) )
			model::execSql('alter table `settings` add `field` TEXT NOT NULL','insert');
		
		// Разворачиваем поля настроек
		foreach ($res as $r)
			if( is_object( model::$types[ trim($r['type']) ] ) )
				$settings[ trim($r['var']) ] = model::$types[ trim($r['type']) ]->getValueExplode( trim($r['value']), false, $res );

		$settings = ModelLoader::checkNeededSettings( $settings );

		// Вывод ошибок для режима разработки
		if( $settings['test_mode'] ){
			error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_STRICT);
			ini_set("display_errors", "on");
		}else{
			error_reporting(0);
			ini_set("display_errors", "off");
		}
		
		// older version support
		$this->settings = $settings;
		
		return $settings;
	}

	//Разбор параметров запроса
	public function loadAsk(){
		$ask = new StdClass;
		$ask->original_url = self::getCurrentUrl();

		$ask->method = $_SERVER['REQUEST_METHOD'];
		$ask->protocol = substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'));
		$ask->host = $_SERVER['HTTP_HOST'];
		$ask->output_type = '404';
		$ask->output_format = 'html';

		if($ask->original_url != '/'){
			$ask->url = array_values( explode('/', substr($ask->original_url, 1) ) );
			foreach($ask->url as $i=>$url){
				if( substr_count($url, '.') ){
					$mode = explode('.', $url);
					$url = array_shift( $mode );
					$ask->tree[ $i ] = array('url' => $url, 'mode' => $mode);
					$ask->url[ $i ] = $url;
					
					if( class_exists( 'controller_manager' ) )
						$formats = controller_manager::$output_formats;
					else
						$formats = array('html','json','xml','tpl');
					
					if( in_array( end( $ask->tree[ $i ]['mode'] ), $formats ) )
						$ask->output_format = array_pop( $ask->tree[ $i ]['mode'] );

					//Модификаторы
					$ask->mode = $ask->tree[ $i ]['mode'];
					
					//Номер страницы
					if( is_numeric( end( $ask->mode ) ) ){
						$ask->current_page = intval( array_pop( $ask->mode) );
					}
					
				}else{
					$ask->tree[ $i ] = array(
						'url' => $url,
					);			
				}
			}
		}else{
			$ask->output_format = 'html';
		}
		
		//Панель управления - адреса типа "/admin/..."
		if( $ask->url[0] == 'admin' ){
			$ask->controller = array_shift( $ask->url );
//			$ask->tree = $ask->url;
		}
		
		pr_r($ask);
		
		return $ask;
	}
	
	//Определить текущий URL
	public static function getCurrentUrl(){
		if( $_SERVER['REDIRECT_URL'][0] == '/' )
			$_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
		$original_url = urldecode($_SERVER['REDIRECT_URL']);
		if(substr_count($original_url, '?'))
			$original_url = substr($original_url, 0, strpos($original_url, '?'));
		return $original_url;
	}

	//Проверяем наличие необходимых настроек
	private function checkNeededSettings( $settings = array() ){
		if(	IsSet( $settings['test_mode'] ) )
			if( !$settings['test_mode'] )
				return $settings;
		
		$needed_settings = array(
			//Общие
			'domain_title' => array( 
				'group' => 'main', 
				'title' => 'Название сайта', 
				'type' => 'text', 
				'default_value' => 'ООО "Наша Компания"', 
			),
			'copyright' => array( 
				'group' => 'main', 
				'title' => 'Копирайты', 
				'type' => 'text', 
				'default_value' => 'ООО "Наша Компания"', 
			),
			'test_mode' => array( 
				'group' => 'main', 
				'title' => 'Режим тестирования - закрыть сайт для всех, кроме разрешённых IP-адресов', 
				'type' => 'check', 
				'default_value' => true, 
			),
			'test_mode_text' => array( 
				'group' => 'main', 
				'title' => 'Название сайта', 
				'type' => 'textarea', 
				'default_value' => 'Сайт находится в разработке, ориентировочная дата открытия: '.date("d.m.Y", strtotime("+1 month")), 
			),
			'bootstrap' => array( 
				'group' => 'main', 
				'title' => 'Использовать на сайте библиотеку bootstrap', 
				'type' => 'check', 
				'default_value' => false, 
			),
			
			//SEO
			'counter' => array( 
				'group' => 'seo', 
				'title' => 'Код счётчика', 
				'type' => 'html', 
				'default_value' => '[Код счётчика]', 
			),
			'seo_keywords' => array( 
				'group' => 'seo', 
				'title' => 'Ключевые слова (до 255 символов)', 
				'type' => 'textarea', 
				'default_value' => '', 
			),
			'seo_description' => array( 
				'group' => 'seo', 
				'title' => 'Краткое описание сайта (до 255 символов)', 
				'type' => 'textarea', 
				'default_value' => '', 
			),
			'robots' => array( 
				'group' => 'seo', 
				'title' => 'Файл robots.txt для домена', 
				'type' => 'robots', 
				'default_value' => '', 
			),
			'meta_add' => array( 
				'group' => 'seo', 
				'title' => 'Дополнительные теги в head', 
				'type' => 'html', 
				'default_value' => '', 
			),

			//Оформление
			'logo' => array( 
				'group' => 'media', 
				'title' => 'Логотип сайта', 
				'type' => 'image', 
				'default_value' => 0, 
			),
			'favicon' => array( 
				'group' => 'media', 
				'title' => 'Иконка в адресной строке браузера', 
				'type' => 'file', 
				'default_value' => 0, 
			),
			
			'latin_url_only' => array( 
				'group' => 'config', 
				'title' => 'Ограничить URL`ы только латинскими буквами, цифрами, и знаком подчёркивания', 
				'type' => 'check', 
				'default_value' => true, 
			),
			'show_stat' => array( 
				'group' => 'config', 
				'title' => 'Показывать статистику генерации страницы', 
				'type' => 'menu', 
				'default_value' => 'shirt',
				'variants' => array(
					0 => 'не показывать', 
					'shirt' => 'Показывать краткую статистику', 
					'all' => 'Показывать полную статистику'),
			),
			'mainmenu_levels' => array( 
				'group' => 'config', 
				'title' => 'Сколько уровней главного меню обсчитывать', 
				'type' => 'menu', 
				'default_value' => 2, 
				'variants' => array(
					1 => 1, 
					2 => 2, 
					3 => 3,
					4 => 4,
					5 => 5,
				),
			),
			'no_www' => array( 
				'group' => 'config', 
				'title' => 'Как относиться к приставке www в адресах', 
				'type' => 'menu', 
				'default_value' => 0,
				'variants' => array(
					0 => 'Не важно',
					'no_www' => 'Использовать адреса только без приставки www',
					'www' => 'Использовать адреса только с приставкой www',
				),
			),
			'doctype' => array( 
				'group' => 'config', 
				'title' => 'Используемый стандарт HTML', 
				'type' => 'menu', 
				'default_value' => 'XHTML 1.0 Transitional',
				'variants' => array(
					'HTML 4.01 Strict',
					'HTML 4.01 Transitional',
					'XHTML 1.0 Strict',
					'XHTML 1.0 Transitional',
					'XHTML 1.1',
					'HTML 5',
				),
			),
			'oauth_openid' => array( 
				'group' => 'OAuth', 
				'title' => 'Разрешённые Openid/OAuth провайдеры', 
				'type' => 'menum', 
				'default_value' => '|google.com|',
				'variants' => array(
					'yandex.ru' => 'Яндекс',
					'google.com' => 'Google',
					'facebook.com' => 'Facebook',
					'vk.com' => 'Вконтакте',
					'twitter.com' => 'Twitter',
				), 
			),
		);
		

		//Добиваем системные настройки настряоками из модулей
		foreach(model::$modules as $module_sid=>$module)
			if( IsSet( $module->settings ) )
				$needed_settings = array_merge($module->settings, $needed_settings);

		$i = 1;
		foreach($needed_settings as $key=>$set)
		if($key){
			$i++;
			
			//Добавляем поле
			if( !IsSet( $settings[ $key ] ) ){
				$this->execSql('insert into `settings` set 
					`pos`="'.($i*10).'", 
					`group`="'.mysql_real_escape_string( $set['group'] ).'", 
					`domain`="|'.model::pointDomainID().'|", 
					`var`="'.mysql_real_escape_string( $key ).'", 
					`title`="'.mysql_real_escape_string( $set['title'] ).'", 
					`type`="'.mysql_real_escape_string( $set['type'] ).'", 
					`value`="'.mysql_real_escape_string( $set['default_value'] ).'",
					`field`="'.mysql_real_escape_string( serialize( $set ) ).'"
				','insert');
				$settings[ $key ] = $set['default_value'];
				
			}else{
				$this->execSql('update `settings` set 
					`field`="'.mysql_real_escape_string( serialize( $set ) ).'"
					where
					`var`="'.mysql_real_escape_string( $key ).'" and
					'.model::pointDomain().'
				','update');
			}
		}
		
		return $settings;
	}
	
	public function makeBackCompatible(){
		
		if( IsSet( model::$config['back_compatible'] ) ){
			require_once( model::$config['path']['core'].'/tests/back_comp.php' );
			back_comp::comp_213_to_214();
		}
		
	}
	
}

?>
