<?php

require_once( 'default_controller.php' );

class controller_dev extends default_controller
{
	use dev_templates, dev_modules, dev_styles, dev_js, dev_help;

	public function start()
	{

		error_reporting( E_ALL^E_DEPRECATED^E_NOTICE^E_STRICT );
		ini_set( "display_errors", "on" );

		if( model::$ask->method == 'GET' ) {
			$this->preloadGet();
		} else {
			$this->controlGet();
		}

	}

	private function preloadGet()
	{

		// Шаблонизатор
		$tmpl = $this->initTemplater();

		$this->addJS( 'http://code.jquery.com/jquery-2.0.3.min.js' );

		$this->addJS( '//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js' );
		$this->addCSS( '//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css' );

		$this->addJS( 'http://src.opendev.ru/3.0/jquery-ui-1.10.3/ui/jquery-ui.js' );
		$this->addCSS( 'http://src.opendev.ru/3.0/jquery-ui-1.10.3/themes/base/jquery-ui.css' );
		$this->addCSS( 'http://src.opendev.ru/3.0/jquery-ui-1.10.3/themes/base/jquery.ui.all.css' );

		// Название закладки
		model::$settings[ 'domain_title' ] = 'Разработка :: ' . model::$settings[ 'domain_title' ];

		$tab = 'main';
		if( IsSet( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] ) )
			$tab = model::$ask->tree[ 0 ][ 'mode' ][ 0 ];

		if( $tab == 'main' ) $tmpl = $this->assignAll_main( $tmpl );
		if( $tab == 'content' ) $tmpl = $this->assignAll_content( $tmpl );
		if( $tab == 'settings' ) $tmpl = $this->assignAll_settings( $tmpl );
		if( $tab == 'modules' ) $tmpl = $this->assignAll_modules( $tmpl );
		if( $tab == 'templates' ) $tmpl = $this->assignAll_templates( $tmpl );
		if( $tab == 'styles' ) $tmpl = $this->assignAll_styles( $tmpl );
		if( $tab == 'js' ) $tmpl = $this->assignAll_js( $tmpl );
		if( $tab == 'images' ) $tmpl = $this->assignAll_images( $tmpl );
		if( $tab == 'files' ) $tmpl = $this->assignAll_files( $tmpl );
		if( $tab == 'help' ) $tmpl = $this->assignAll_help( $tmpl );

		$tmpl->assign( 'current_tab', $tab );

		// Выводим
		$this->outputTemplate( $tmpl, 'dev/index.tpl' );
	}

	private function assignAll_main( $tmpl )
	{

		$center = array(
			'title' => 'Панель управления сайтом',
			'size'  => 8,
		);
		$left   = array(
			'title' => 'Оглавление',
			'size'  => 2,
		);
		$right  = array(
			'title' => 'Помощь',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_content( $tmpl )
	{

		$center = array(
			'title'    => 'Все материалы сайта',
			'size'     => 8,
//			'iframe'   => '/admin.start.html',
			'template' => 'block_tree.tpl',
		);
		$left   = array(
			'title' => 'Помощь',
			'size'  => 2,
		);
		$right  = array(
			'title' => 'Помощь',
			'size'  => 2,
		);

		$submenu = array();
		foreach( model::$modules as $module_sid => $module ) {
			$submenu[ ] = array(
				'title' => $module->title,
				'url'   => '/admin.' . $module_sid . '.html',
			);
		}
		$left[ 'submenu' ] = $submenu;

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_settings( $tmpl )
	{

		$center = array(
			'size'   => 8,
			'iframe' => '/admin.settings.html'
		);
		$left   = array(
			'title' => 'Помощь',
			'size'  => 2,
		);
		$right  = array(
			'title' => 'Помощь',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_modules( $tmpl )
	{

		$center = $this->getModules_one();
		$left   = array(
			'title' => 'Помощь',
			'size'  => 2,
			'recs'  => $this->getModules_all(),
		);
		$right  = array(
			'title' => 'Помощь',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_templates( $tmpl )
	{

		$center = $this->getTemplates_content();
		$left   = array(
			'size'   => 2,
			'title'  => 'Основные шаблоны',
			'recs'   => $this->getTemplates_common(),
			'title2' => 'Другие шаблоны',
			'recs2'  => $this->getTemplates_other(),
		);
		$right  = array(
			'title' => 'Примеры кода',
			'size'  => 2,
			'recs'  => $this->getHelp_templates(),
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_styles( $tmpl )
	{

		$center = $this->getStyles_content();
		$left   = array(
			'size'   => 2,
			'title'  => 'Файлы стилей сайта',
			'recs'   => $this->getStyles_other(),
		);
		$right  = array(
			'title' => 'Примеры кода',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;

	}

	private function assignAll_js( $tmpl )
	{

		$center = $this->getJS_content();
		$left   = array(
			'size'   => 2,
			'title'  => 'Файлы javascript',
			'recs'   => $this->getJS_other(),
		);
		$right  = array(
			'title' => 'Примеры кода',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;

	}

	private function assignAll_images( $tmpl )
	{

		$center = array(
			'title' => 'Управление картинками на сайте',
			'size'  => 8,
		);
		$left   = array(
			'title' => 'Помощь',
			'size'  => 2,
		);
		$right  = array(
			'title' => 'Помощь',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_files( $tmpl )
	{

		$center = array(
			'title' => 'Управление другими загруженными файлами',
			'size'  => 8,
		);
		$left   = array(
			'title' => 'Помощь',
			'size'  => 2,
		);
		$right  = array(
			'title' => 'Помощь',
			'size'  => 2,
		);

		$tmpl->assign( 'left', $left );
		$tmpl->assign( 'right', $right );
		$tmpl->assign( 'center', $center );

		return $tmpl;
	}

	private function assignAll_help( $tmpl )
	{

		// Вывод помощи внутрь модального окна
		if( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] && IsSet( model::$ask->url[ 1 ] ) && IsSet( $_GET[ 'sid' ] ) ) {
			$t = explode( '.', $_GET[ 'sid' ] );

			$module = model::$ask->url[ 1 ];

			if( $module == 'templates' )
				$groups = $this->getHelp_templates();

			if( IsSet( $groups[ $t[ 0 ] ][ 'recs' ][ $t[ 1 ] ] ) ) {

				$file     = $groups[ $t[ 0 ] ][ 'recs' ][ $t[ 1 ] ];
				$filename = model::$config[ 'path' ][ 'admin_templates' ] . '/' . $file[ 'filename' ];
				if( is_readable( $filename ) ) {

					$f = file_get_contents( $filename );
					print( $f );
					exit();

				}

			}

			exit();
		}

		print( 'ok' );
		exit();

	}


	private function controlGet()
	{

		$this->defineAction();

	}


	// Инициализация шаблонизатора
	private function initTemplater()
	{
		require_once( model::$config[ 'path' ][ 'core' ] . '/classes/templates.php' );

		model::$settings[ 'domain_title' ] = 'Разработка :: ' . model::$settings[ 'domain_title' ];

		$tmpl = new templater();
		$tmpl->assign( 'ask', model::$ask );
		$tmpl->assign( 'content', model::$ask->rec );
		$tmpl->assign( 'paths', model::$config[ 'path' ] );
		$tmpl->assign( 'settings', model::$settings );
//		$tmpl->assign( 'config_openid', array_keys( model::$config[ 'openid' ] ) );
		$tmpl->assign( 'path_admin_templates', model::$config[ 'path' ][ 'admin_templates' ] );
		$tmpl->assign( 'get_vars', $_GET );

		return $tmpl;
	}

	// Компиляция и вывод на печать готового шаблона
	private function outputTemplate( $tmpl, $template_file )
	{

		//Компилируем
		try {
			$ready_html = $tmpl->fetch( $template_file, 'admin_templates' );
		} catch ( Exception $e ) {
			log::stop( '500 Internal Server Error', 'Ошибка компиляции шаблона', $e );
		}

		// Чтобы шаблон знал текущий статус запроса - сообщаем ему код
		$tmpl->assign( 'header_status', intval( $this->header ) );

		if( !headers_sent() ) {

			if( $this->header == 200 ) {
				header( 'Content-Type: text/html; charset=utf-8' );
				header( "HTTP/1.0 200 Ok" );
			} else {
//				pr( '[уже был отдан другой заголовок]' );
			}

		}

		echo $ready_html;
	}

	private function defineAction()
	{

		if( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'modules' )
			$this->controlModules();
		if( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'templates' )
			$this->controlTemplates();
		if( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'styles' )
			$this->controlStyles();
		if( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'js' )
			$this->controlJS();

		pr_r( model::$ask );
		exit();

	}

}

trait dev_templates
{

	public function getTemplates_common()
	{

		$templates = array();

		foreach( model::$modules as $module_sid => $module ) {

			if( !in_array( $module_sid, array( 'banners', 'likes' ) ) )
				$templates[ $module_sid . '_index' ] = array(
					'title'    => $module->info[ 'title' ] . ' - главная',
					'filename' => $module_sid . '_index.tpl',
					'url'      => '/dev.templates/' . $module_sid . '_index',
				);

			if( $module->countStructures()>1 )
				if( !in_array( $module_sid, array( 'basket' ) ) )
					$templates[ $module_sid . '_list' ] = array(
						'title'    => $module->info[ 'title' ] . ' - список',
						'filename' => $module_sid . '_list.tpl',
						'url'      => '/dev.templates/' . $module_sid . '_list',
					);

			if( !in_array( $module_sid, array( 'search', 'banners', 'basket', 'likes' ) ) )
				$templates[ $module_sid . '_content' ] = array(
					'title'    => $module->info[ 'title' ] . ' - страница',
					'filename' => $module_sid . '_content.tpl',
					'url'      => '/dev.templates/' . $module_sid . '_content',
				);

		}

		return $templates;
	}

	public function getTemplates_other()
	{

		$templates = array();

		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		if( class_exists( 'acmsDirs' ) ) {

			$acmsDirs = new acmsDirs();

			$files = $acmsDirs->get_files( model::$config[ 'path' ][ 'templates' ] );
			$files = $acmsDirs->select_ext( 'tpl', $files );

			$common = $this->getTemplates_common();
			foreach( $files as $i => $file ) {
				$filename = str_replace( '.tpl', '', basename( $file ) );
				if( IsSet( $common[ $filename ] ) )
					UnSet( $files[ $i ] );
			}

			if( is_array( $files ) )
				foreach( $files as $file ) {
					$file              = basename( $file );
					$sid               = str_replace( '.tpl', '', $file );
					$templates[ $sid ] = array(
						'title'    => $file,
						'filename' => $file,
						'url'      => '/dev.templates/' . $sid,
					);
				}

			ksort( $templates );
		}

		return $templates;
	}

	public function getTemplates_content()
	{

		if( ( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'templates' ) && ( IsSet( model::$ask->url[ 1 ] ) ) ) {

			$filename = basename( model::$ask->url[ 1 ] ) . '.tpl';

			if( is_readable( model::$config[ 'path' ][ 'templates' ] . '/' . $filename ) ) {

				// Резервная копия файла
				if( IsSet( $_GET[ 'bkp' ] ) ) {
					$bkp_filename = basename( $_GET[ 'bkp' ] );
					if( is_readable( model::$config[ 'path' ][ 'tmp' ] . '/templates/' . $bkp_filename ) )
						$content = file_get_contents( model::$config[ 'path' ][ 'tmp' ] . '/templates/' . $bkp_filename );
				}

				// Сам файл
				if( !$content )
					$content = file_get_contents( model::$config[ 'path' ][ 'templates' ] . '/' . $filename );

				$content = str_replace( '<textarea', '<text_area', $content );
				$content = str_replace( '</textarea>', '</text_area>', $content );
				$title   = 'Редактирование шаблона ' . mb_strtolower( $filename );

			} else {
				$content = '<!-- создать новый файл шаблона -->' . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
				$title   = 'Создание шаблона ' . mb_strtolower( $filename );
			}

			$content = array(
				'size'     => 8,
				'title'    => $title,
				'filename' => $filename,
				'template' => 'code_html.tpl',
				'code'     => htmlspecialchars( $content ),
				'backups'  => $this->getTemplates_backups( $filename ),
			);

			// Показать список всех сохранённых копий
			if( model::$ask->mode[ 0 ] == 'all_backups' ) {
				$content[ 'all_backups' ] = $this->getTemplates_backups( $filename, 1000 );
			}

		} elseif( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'templates' ) {
			$content = array(
				'size'     => 8,
				'title'    => 'Управление шаблонами',
				'template' => 'code_html.tpl',
			);
		}

		return $content;

	}

	public function getTemplates_backups( $filename, $limit = 15 )
	{
		$bkp_dir = model::$config[ 'path' ][ 'tmp' ] . '/templates';
		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		$acmsDirs = new acmsDirs;

		$sid   = substr( $filename, 0, strpos( $filename, '.' ) );
		$files = $acmsDirs->get_files( $bkp_dir );
		$files = $acmsDirs->select_ext( 'tpl', $files );

		$found = array();
		foreach( $files as $i => $file )
			if( substr_count( basename( $file ), $sid . '.' ) ) {
				$name = basename( $file );
				$t    = explode( '.', $name );

				$d    = explode( '-', $t[ 1 ] );
				$date = $d[ 0 ] . '-' . $d[ 1 ] . '-' . $d[ 2 ] . ' ' . $d[ 3 ] . ':' . $d[ 4 ] . ':' . $d[ 5 ];

				$found[ $date ] = array(
					'title'    => 'Копия от ' . $date,
					'filename' => basename( $file ),
					'date'     => $t[ 1 ],
					'url'      => '/dev.templates/' . $sid . '.recover?bkp=' . $name,
				);
			}
		if( count( $found ) )
			$found[ date( "Н-m-d H:i:s" ) ] = array(
				'title'    => 'Сохранённая версия',
				'filename' => basename( $file ),
				'date'     => date( "y-m-d H:i:s" ),
				'url'      => '/dev.templates/' . $sid,
			);

		krsort( $found );
		$found = array_values( $found );

		if( count( $found )>$limit ) {
			foreach( $found as $i => $f )
				if( $i>=$limit )
					UnSet( $found[ $i ] );
		}

		return $found;
	}

	public function controlTemplates()
	{

		$content = $this->getTemplates_content();

		if( $content ) {

			$path = model::$config[ 'path' ][ 'templates' ] . '/' . $content[ 'filename' ];
			$name = str_replace( '.tpl', '', $content[ 'filename' ] );

			if( file_exists( $path ) && !is_writable( $path ) ) {
				print( 'Файл защищён от записи [' . model::$config[ 'path' ][ 'templates' ] . '/' . $content[ 'filename' ] . '].' );
				exit();
			}

			$new_code = stripslashes( $_POST[ 'code' ] );
			$new_code = str_replace( '<text_area', '<textarea', $new_code );
			$new_code = str_replace( '</text_area>', '<textarea>', $new_code );

			// Создаём рещервную копию
			$this->templates_makeBackup( $content[ 'filename' ] );

			file_put_contents( $path, $new_code );

		}

		header( 'Location: /dev.templates/' . $name . '.saved' );
		exit();

	}

	private function templates_makeBackup( $filename )
	{

		$path = model::$config[ 'path' ][ 'templates' ] . '/' . $filename;
		$name = str_replace( '.tpl', '', $filename );

		$bkp = file_get_contents( $path );
		if( $bkp ) {

			$bkp_dir = model::$config[ 'path' ][ 'tmp' ] . '/templates';
			require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
			$acmsDirs = new acmsDirs;

			$acmsDirs->makeFolder( $bkp_dir );
			file_put_contents( $bkp_dir . '/' . $name . '.' . date( "Y-m-d-H-i-s" ) . '.tpl', $bkp );

		}

	}


}

trait dev_modules
{

	public function getModules_all()
	{

		$modules = array();

		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		$acmsDirs = new acmsDirs;
		$files    = $acmsDirs->get_files( model::$config[ 'path' ][ 'modules' ] );
		$files    = $acmsDirs->select_ext( 'php', $files );
		foreach( $files as $file ) {

			$name = basename( $file );
			$name = substr( $name, 0, strrpos( $name, '.' ) );
			include_once( model::$config[ 'path' ][ 'modules' ] . '/' . $name . '.php' );

			$class_name = $name . '_module';
			if( class_exists( $class_name ) ) {

				$modules[ $name ] = array(
					'title' => $name,
					'sid'   => $name,
					'url'   => '/dev.modules/' . $name,
				);

				if( IsSet( model::$modules[ $name ] ) )
					$modules[ $name ][ 'active' ] = true;

			}

		}

		ksort( $modules );

		return $modules;
	}

	public function getModules_one()
	{

		if( ( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'modules' ) && ( IsSet( model::$ask->url[ 1 ] ) ) ) {

			$name = basename( model::$ask->url[ 1 ] );
			$path = model::$config[ 'path' ][ 'modules' ] . '/' . $name . '.php';
			if( is_readable( $path ) ) {
				include_once( $path );

				$class_name = $name . '_module';
				if( class_exists( $class_name ) ) {

					if( IsSet( model::$modules[ $name ] ) ) {

						$content = array(
							'size'             => 8,
							'sid'              => $name,
							'title'            => model::$modules[ $name ]->info[ 'title' ],
							'filename'         => $path,
							'template'         => 'module_stat.tpl',
							'structures_count' => model::$modules[ $name ]->countStructures(),
							'structures'       => model::$modules[ $name ]->getStructures(),
							'active'           => true,
							'installed'        => true,
						);

						if( is_array( $content[ 'structures' ] ) )
							foreach( $content[ 'structures' ] as $structure_sid => $structure ) {
								$c = model::execSql( 'select count(`id`) as `count` from `' . model::$modules[ $name ]->getCurrentTable( $structure_sid ) . '`', 'getrow' );

								$content[ 'structures' ][ $structure_sid ][ 'records_count' ] = intval( $c[ 'count' ] );
							}

					} else {

						$rec = model::execSql( 'select * from `modules` where `prototype`="' . mysql_real_escape_string( $name ) . '" limit 1', 'getrow' );
						if( $rec ) {

							$content = array(
								'size'      => 8,
								'sid'       => $name,
								'title'     => $rec[ 'title' ],
								'filename'  => $path,
								'template'  => 'module_stat.tpl',
								'active'    => false,
								'installed' => true,
							);

						} else {

							$ref    = new ReflectionClass( $class_name );
							$module = $ref->newInstanceWithoutConstructor();

							$content = array(
								'size'      => 8,
								'sid'       => $name,
								'title'     => $module->title,
								'filename'  => $path,
								'template'  => 'module_stat.tpl',
								'active'    => false,
								'installed' => false,
							);

						}


					}
				}
			}

		}

		if( !$content )
			$content = array(
				'title' => 'Управление модулями на сайте',
				'template' => 'module_stat.tpl',
				'size'  => 8,
				'welcome' => true,
			);

		return $content;
	}

	private function moduleAction_install( $name )
	{

		if( IsSet( model::$modules[ $name ] ) ) {
			pr( 'Модуль уже установлен [' . $name . '].' );

			return false;
		}

		$path = model::$config[ 'path' ][ 'modules' ] . '/' . basename( $name ) . '.php';
		if( is_readable( $path ) ) {
			include_once( $path );

			$class_name = $name . '_module';
			if( class_exists( $class_name ) ) {

				$check = model::execSql( 'select * from `modules` where `name`="' . mysql_real_escape_string( $name ) . '"', 'getrow' );
				if( $check ) {
					pr( 'Модуль уже установлен [' . $name . '].' );

					return false;
				}

				$ref    = new ReflectionClass( $class_name );
				$module = $ref->newInstanceWithoutConstructor();

				$pos = model::execSql( 'select `pos` from `modules` order by `pos` desc limit 1', 'getrow' );
				$pos = $pos[ 'pos' ]+1;
				model::execSql( 'insert into `modules` set `sid`="' . $name . '", `prototype`="' . $name . '", `title`="' . $module->title . '", `pos`=' . intval( $pos ) . ', `ln`=1, `active`=0', 'insert' );

				header( 'Location: /dev.modules/' . $name . '.html' );
				exit();

			} else {
				pr( 'Неверный формат файла модуля [' . $name . ']: класс не обнаружен.' );

				return false;
			}
		} else {
			pr( 'Невозможно прочитать файл модуля [' . $name . '].' );

			return false;
		}
	}

	private function moduleAction_uninstall( $name )
	{

		if( IsSet( model::$modules[ $name ] ) ) {
			pr( 'Включенный модуль нельзя удалить [' . $name . '].' );

			return false;
		}

		$path = model::$config[ 'path' ][ 'modules' ] . '/' . basename( $name ) . '.php';
		if( is_readable( $path ) ) {
			include_once( $path );

			$class_name = $name . '_module';
			if( class_exists( $class_name ) ) {

				$record = model::execSql('select * from `modules` where `sid`="' . $name . '" and `active`=0 limit 1', 'getrow');
				model::execSql( 'delete from `modules` where `sid`="' . $name . '" and `active`=0 limit 1', 'delete' );

				$module = new $class_name( null, $record);

				$structures = $module->getStructures();
				if( $structures)
					foreach( $structures as $structure_sid => $structure){
						model::execSql('truncate table `'.$module->getCurrentTable( $structure_sid ).'`', 'delete');
						model::execSql('drop table `'.$module->getCurrentTable( $structure_sid ).'`', 'delete');
					}

				header( 'Location: /dev.modules/' . $name . '.html' );
				exit();

			} else {
				pr( 'Неверный формат файла модуля [' . $name . ']: класс не обнаружен.' );

				return false;
			}
		} else {
			pr( 'Невозможно прочитать файл модуля [' . $name . '].' );

			return false;
		}
	}

	private function moduleAction_activate( $name )
	{

		if( IsSet( model::$modules[ $name ] ) ) {
			pr( 'Модуль уже активирован [' . $name . '].' );

			return false;
		}

		$path = model::$config[ 'path' ][ 'modules' ] . '/' . basename( $name ) . '.php';
		if( is_readable( $path ) ) {
			include_once( $path );

			$class_name = $name . '_module';
			if( class_exists( $class_name ) ) {

				$check = model::execSql( 'select * from `modules` where `name`="' . mysql_real_escape_string( $name ) . '"', 'getrow' );
				if( $check[ 'active' ] ) {
					pr( 'Модуль уже активирован [' . $name . '].' );

					return false;
				}

				model::execSql( 'update `modules` set `active`=1 where `sid`="' . $name . '" and `active`=0 limit 1', 'update' );;

				model::$settings[ 'test_mode' ] = true;

				model::preloadModules();
				if( !IsSet( model::$modules[ $name ] ) ) {
					pr( 'Инициализация нового модуля не удалась [' . $name . '].' );
					pr_r( array_keys( model::$modules ) );

					return false;
				}

				model::$modules[ $name ]->unitTests();

				model::$settings[ 'test_mode' ] = false;

				header( 'Location: /dev.modules/' . $name . '.html' );
				exit();

			} else {
				pr( 'Неверный формат файла модуля [' . $name . ']: класс не обнаружен.' );

				return false;
			}
		} else {
			pr( 'Невозможно прочитать файл модуля [' . $name . '].' );

			return false;
		}
	}

	private function moduleAction_deactivate( $name )
	{

		$path = model::$config[ 'path' ][ 'modules' ] . '/' . basename( $name ) . '.php';
		if( is_readable( $path ) ) {
			include_once( $path );

			$class_name = $name . '_module';
			if( class_exists( $class_name ) ) {

				$check = model::execSql( 'select * from `modules` where `name`="' . mysql_real_escape_string( $name ) . '"', 'getrow' );
				if( $check )
					if( !$check[ 'active' ] ) {
						pr_r( $check );
						pr( 'Модуль уже деактивирован [' . $name . '].' );

						return false;
					}

				model::execSql( 'update `modules` set `active`=0 where `sid`="' . $name . '" and `active`=1 limit 1', 'update' );;

				header( 'Location: /dev.modules/' . $name . '.html' );
				exit();

			} else {
				pr( 'Неверный формат файла модуля [' . $name . ']: класс не обнаружен.' );

				return false;
			}
		} else {
			pr( 'Невозможно прочитать файл модуля [' . $name . '].' );

			return false;
		}
	}

	public function controlModules(){

		$acts = array( 'install', 'activate', 'deactivate', 'uninstall' );
		if( IsSet( model::$ask->mode[ 0 ] ) )
			if( in_array( IsSet( model::$ask->mode[ 0 ] ), $acts ) ) {

				$module_sid = model::$ask->url[ 1 ];
				$act        = model::$ask->mode[ 0 ];

				if( IsSet( model::$ask->mode[ 1 ] ) )
					if( model::$ask->mode[ 1 ] == 'ok' ) {

						if( $act == 'install' ) {
							$result = $this->moduleAction_install( $module_sid );
						}
						if( $act == 'uninstall' ) {
							$result = $this->moduleAction_uninstall( $module_sid );
						}
						if( $act == 'activate' ) {
							$result = $this->moduleAction_activate( $module_sid );
						}
						if( $act == 'deactivate' ) {
							$result = $this->moduleAction_deactivate( $module_sid );
						}

					}
			}

	}

}

trait dev_styles
{

	public function getStyles_other()
	{

		$templates = array();

		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		if( class_exists( 'acmsDirs' ) ) {

			$acmsDirs = new acmsDirs();

			$files = $acmsDirs->get_files( model::$config[ 'path' ][ 'styles' ] );
			$files = $acmsDirs->select_ext( 'css', $files );

			if( is_array( $files ) )
				foreach( $files as $file ) {
					$file              = basename( $file );
					$sid               = str_replace( '.css', '', $file );
					$templates[ $sid ] = array(
						'title'    => $file,
						'filename' => $file,
						'url'      => '/dev.styles/' . $sid,
					);
				}

			ksort( $templates );
		}

		return $templates;
	}

	public function getStyles_content()
	{

		if( ( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'styles' ) && ( IsSet( model::$ask->url[ 1 ] ) ) ) {

			$filename = basename( model::$ask->url[ 1 ] ) . '.css';

			if( is_readable( model::$config[ 'path' ][ 'styles' ] . '/' . $filename ) ) {

				// Резервная копия файла
				if( IsSet( $_GET[ 'bkp' ] ) ) {
					$bkp_filename = basename( $_GET[ 'bkp' ] );
					if( is_readable( model::$config[ 'path' ][ 'tmp' ] . '/styles/' . $bkp_filename ) )
						$content = file_get_contents( model::$config[ 'path' ][ 'tmp' ] . '/styles/' . $bkp_filename );
				}

				// Сам файл
				if( !$content )
					$content = file_get_contents( model::$config[ 'path' ][ 'styles' ] . '/' . $filename );

				$content = str_replace( '<textarea', '<text_area', $content );
				$content = str_replace( '</textarea>', '</text_area>', $content );
				$title   = 'Редактирование файла стилей ' . mb_strtolower( $filename );

			} else {
				$content = '<!-- создать новый файл стилей -->' . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
				$title   = 'Создание файла стилей ' . mb_strtolower( $filename );
			}

			$content = array(
				'size'     => 8,
				'title'    => $title,
				'filename' => $filename,
				'template' => 'code_css.tpl',
				'code'     => htmlspecialchars( $content ),
				'backups'  => $this->getStyles_backups( $filename ),
			);

			// Показать список всех сохранённых копий
			if( model::$ask->mode[ 0 ] == 'all_backups' ) {
				$content[ 'all_backups' ] = $this->getStyles_backups( $filename, 1000 );
			}

		} elseif( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'styles' ) {
			$content = array(
				'size'     => 8,
				'title'    => 'Управление стилями',
				'template' => 'code_css.tpl',
			);
		}

		return $content;

	}

	public function getStyles_backups( $filename, $limit = 15 )
	{
		$bkp_dir = model::$config[ 'path' ][ 'tmp' ] . '/styles';
		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		$acmsDirs = new acmsDirs;

		$sid   = substr( $filename, 0, strpos( $filename, '.' ) );
		$files = $acmsDirs->get_files( $bkp_dir );
		$files = $acmsDirs->select_ext( 'css', $files );

		$found = array();
		foreach( $files as $i => $file )
			if( substr_count( basename( $file ), $sid . '.' ) ) {
				$name = basename( $file );
				$t    = explode( '.', $name );

				$d    = explode( '-', $t[ 1 ] );
				$date = $d[ 0 ] . '-' . $d[ 1 ] . '-' . $d[ 2 ] . ' ' . $d[ 3 ] . ':' . $d[ 4 ] . ':' . $d[ 5 ];

				$found[ $date ] = array(
					'title'    => 'Копия от ' . $date,
					'filename' => basename( $file ),
					'date'     => $t[ 1 ],
					'url'      => '/dev.styles/' . $sid . '.recover?bkp=' . $name,
				);
			}
		if( count( $found ) )
			$found[ date( "Н-m-d H:i:s" ) ] = array(
				'title'    => 'Сохранённая версия',
				'filename' => basename( $file ),
				'date'     => date( "y-m-d H:i:s" ),
				'url'      => '/dev.styles/' . $sid,
			);

		krsort( $found );
		$found = array_values( $found );

		if( count( $found )>$limit ) {
			foreach( $found as $i => $f )
				if( $i>=$limit )
					UnSet( $found[ $i ] );
		}

		return $found;
	}

	public function controlStyles()
	{

		$content = $this->getStyles_content();

		if( $content ) {

			$path = model::$config[ 'path' ][ 'styles' ] . '/' . $content[ 'filename' ];
			$name = str_replace( '.css', '', $content[ 'filename' ] );

			if( file_exists( $path ) && !is_writable( $path ) ) {
				print( 'Файл защищён от записи [' . model::$config[ 'path' ][ 'styles' ] . '/' . $content[ 'filename' ] . '].' );
				exit();
			}

			$new_code = stripslashes( $_POST[ 'code' ] );
			$new_code = str_replace( '<text_area', '<textarea', $new_code );
			$new_code = str_replace( '</text_area>', '<textarea>', $new_code );

			// Создаём рещервную копию
			$this->styles_makeBackup( $content[ 'filename' ] );

			file_put_contents( $path, $new_code );

		}

		header( 'Location: /dev.styles/' . $name . '.saved' );
		exit();

	}

	private function styles_makeBackup( $filename )
	{

		$path = model::$config[ 'path' ][ 'styles' ] . '/' . $filename;
		$name = str_replace( '.css', '', $filename );

		$bkp = file_get_contents( $path );
		if( $bkp ) {

			$bkp_dir = model::$config[ 'path' ][ 'tmp' ] . '/styles';
			require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
			$acmsDirs = new acmsDirs;

			$acmsDirs->makeFolder( $bkp_dir );
			file_put_contents( $bkp_dir . '/' . $name . '.' . date( "Y-m-d-H-i-s" ) . '.css', $bkp );

		}

	}


}

trait dev_js
{

	public function getJS_other()
	{

		$templates = array();

		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		if( class_exists( 'acmsDirs' ) ) {

			$acmsDirs = new acmsDirs();

			$files = $acmsDirs->get_files( model::$config[ 'path' ][ 'javascript' ] );
			$files = $acmsDirs->select_ext( 'js', $files );

			if( is_array( $files ) )
				foreach( $files as $file ) {
					$file              = basename( $file );
					$sid               = str_replace( '.js', '', $file );
					$templates[ $sid ] = array(
						'title'    => $file,
						'filename' => $file,
						'url'      => '/dev.js/' . $sid,
					);
				}

			ksort( $templates );
		}

		return $templates;
	}

	public function getJS_content()
	{

		if( ( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'js' ) && ( IsSet( model::$ask->url[ 1 ] ) ) ) {

			$filename = basename( model::$ask->url[ 1 ] ) . '.js';

			if( is_readable( model::$config[ 'path' ][ 'javascript' ] . '/' . $filename ) ) {

				// Резервная копия файла
				if( IsSet( $_GET[ 'bkp' ] ) ) {
					$bkp_filename = basename( $_GET[ 'bkp' ] );
					if( is_readable( model::$config[ 'path' ][ 'tmp' ] . '/js/' . $bkp_filename ) )
						$content = file_get_contents( model::$config[ 'path' ][ 'tmp' ] . '/js/' . $bkp_filename );
				}

				// Сам файл
				if( !$content )
					$content = file_get_contents( model::$config[ 'path' ][ 'javascript' ] . '/' . $filename );

				$content = str_replace( '<textarea', '<text_area', $content );
				$content = str_replace( '</textarea>', '</text_area>', $content );
				$title   = 'Редактирование javascript ' . mb_strtolower( $filename );

			} else {
				$content = '<!-- создать новый файл javascript -->' . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
				$title   = 'Создание файла javascript ' . mb_strtolower( $filename );
			}

			$content = array(
				'size'     => 8,
				'title'    => $title,
				'filename' => $filename,
				'template' => 'code_js.tpl',
				'code'     => htmlspecialchars( $content ),
				'backups'  => $this->getJS_backups( $filename ),
			);

			// Показать список всех сохранённых копий
			if( model::$ask->mode[ 0 ] == 'all_backups' ) {
				$content[ 'all_backups' ] = $this->getJS_backups( $filename, 1000 );
			}

		} elseif( model::$ask->tree[ 0 ][ 'mode' ][ 0 ] == 'js' ) {
			$content = array(
				'size'     => 8,
				'title'    => 'Управление javascript',
				'template' => 'code_js.tpl',
			);
		}

		return $content;

	}

	public function getJS_backups( $filename, $limit = 15 )
	{
		$bkp_dir = model::$config[ 'path' ][ 'tmp' ] . '/js';
		require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
		$acmsDirs = new acmsDirs;

		$sid   = substr( $filename, 0, strpos( $filename, '.' ) );
		$files = $acmsDirs->get_files( $bkp_dir );
		$files = $acmsDirs->select_ext( 'js', $files );

		$found = array();
		foreach( $files as $i => $file )
			if( substr_count( basename( $file ), $sid . '.' ) ) {
				$name = basename( $file );
				$t    = explode( '.', $name );

				$d    = explode( '-', $t[ 1 ] );
				$date = $d[ 0 ] . '-' . $d[ 1 ] . '-' . $d[ 2 ] . ' ' . $d[ 3 ] . ':' . $d[ 4 ] . ':' . $d[ 5 ];

				$found[ $date ] = array(
					'title'    => 'Копия от ' . $date,
					'filename' => basename( $file ),
					'date'     => $t[ 1 ],
					'url'      => '/dev.js/' . $sid . '.recover?bkp=' . $name,
				);
			}
		if( count( $found ) )
			$found[ date( "Н-m-d H:i:s" ) ] = array(
				'title'    => 'Сохранённая версия',
				'filename' => basename( $file ),
				'date'     => date( "y-m-d H:i:s" ),
				'url'      => '/dev.js/' . $sid,
			);

		krsort( $found );
		$found = array_values( $found );

		if( count( $found )>$limit ) {
			foreach( $found as $i => $f )
				if( $i>=$limit )
					UnSet( $found[ $i ] );
		}

		return $found;
	}

	public function controlJS()
	{

		$content = $this->getJS_content();

		if( $content ) {

			$path = model::$config[ 'path' ][ 'javascript' ] . '/' . $content[ 'filename' ];
			$name = str_replace( '.js', '', $content[ 'filename' ] );

			if( file_exists( $path ) && !is_writable( $path ) ) {
				print( 'Файл защищён от записи [' . model::$config[ 'path' ][ 'javascript' ] . '/' . $content[ 'filename' ] . '].' );
				exit();
			}

			$new_code = stripslashes( $_POST[ 'code' ] );
			$new_code = str_replace( '<text_area', '<textarea', $new_code );
			$new_code = str_replace( '</text_area>', '<textarea>', $new_code );

			// Создаём рещервную копию
			$this->js_makeBackup( $content[ 'filename' ] );

			file_put_contents( $path, $new_code );

		}

		header( 'Location: /dev.js/' . $name . '.saved' );
		exit();

	}

	private function js_makeBackup( $filename )
	{

		$path = model::$config[ 'path' ][ 'javascript' ] . '/' . $filename;
		$name = str_replace( '.js', '', $filename );

		$bkp = file_get_contents( $path );
		if( $bkp ) {

			$bkp_dir = model::$config[ 'path' ][ 'tmp' ] . '/js';
			require_once( model::$config[ 'path' ][ 'libraries' ] . '/acmsDirs.php' );
			$acmsDirs = new acmsDirs;

			$acmsDirs->makeFolder( $bkp_dir );
			file_put_contents( $bkp_dir . '/' . $name . '.' . date( "Y-m-d-H-i-s" ) . '.js', $bkp );

		}

	}


}

trait dev_help
{

	public function getHelp_templates()
	{

		$groups = array(
			'recs'   => array(
				'title' => 'Выбрать список записей',
				'recs'  => array(

					'anonslist'  => array(
						'title'    => 'записи новые',
						'filename' => 'dev/help/html/recs_new.html',
					),
					'recsdir'    => array(
						'title'    => 'записи в разделе',
						'filename' => 'dev/help/html/recs_recsdir.html',
					),
					'randonlist' => array(
						'title'    => 'записи случайные',
						'filename' => 'dev/help/html/recs_randonlist.html',
					),
					'recs'       => array(
						'title'    => 'записи постранично',
						'filename' => 'dev/help/html/recs_paged.html',
					),
					'notid'      => array(
						'title'    => 'записи кроме...',
						'filename' => 'dev/help/html/recs_notid.html',
					),
					'24h'        => array(
						'title'    => 'записи за 24 часа',
						'filename' => 'dev/help/html/recs_date.html',
					),
					'today'      => array(
						'title'    => 'записи за сегодня',
						'filename' => 'dev/help/html/recs_date.html',
					),
					'yesterday'  => array(
						'title'    => 'записи за вчера',
						'filename' => 'dev/help/html/recs_date.html',
					),
					'week'       => array(
						'title'    => 'записи за неделю',
						'filename' => 'dev/help/html/recs_date.html',
					),
					'month'      => array(
						'title'    => 'записи за месяц',
						'filename' => 'dev/help/html/recs_date.html',
					),
					'year'       => array(
						'title'    => 'записи за год',
						'filename' => 'dev/help/html/recs_date.html',
					),
					'count'      => array(
						'title'    => 'посчитать сколько всего',
						'filename' => 'dev/help/html/recs_count.html',
					),
					'tags'       => array(
						'title'    => 'получить облаго тегов',
						'filename' => 'dev/help/html/recs_tags.html',
					),
					'map'        => array(
						'title'    => 'получить карту сайта',
						'filename' => 'dev/help/html/recs_map.html',
					),
					'all'        => array(
						'title'    => 'все возможности',
						'filename' => 'dev/help/html/recs_all.html',
					),

				),
			),

			'rec'    => array(
				'title' => 'Выбрать определённую запись',
				'recs'  => array(

					'anons'  => array(
						'title'    => 'запись последнюю',
						'filename' => 'dev/help/html/empty.html',
					),
					'dir'    => array(
						'title'    => 'запись в разделе',
						'filename' => 'dev/help/html/empty.html',
					),
					'parent' => array(
						'title'    => 'запись родительскую',
						'filename' => 'dev/help/html/empty.html',
					),
					'random' => array(
						'title'    => 'запись случайную',
						'filename' => 'dev/help/html/empty.html',
					),
					'id'     => array(
						'title'    => 'запись по ID',
						'filename' => 'dev/help/html/empty.html',
					),
					'sid'    => array(
						'title'    => 'запись по SID',
						'filename' => 'dev/help/html/empty.html',
					),
					'all'    => array(
						'title'    => 'все возможности',
						'filename' => 'dev/help/html/empty.html',
					),
				),
			),

			'insert' => array(
				'title' => 'Вставить данные в шаблон',
				'recs'  => array(

					'anonslist'    => array(
						'title'    => 'контентная запись',
						'filename' => 'dev/help/html/empty.html',
					),
					'recsdir'      => array(
						'title'    => 'анонс новостей',
						'filename' => 'dev/help/html/empty.html',
					),
					'recsdir'      => array(
						'title'    => 'главное меню',
						'filename' => 'dev/help/html/empty.html',
					),
					'randonlist'   => array(
						'title'    => 'номера страниц',
						'filename' => 'dev/help/html/empty.html',
					),
					'recs'         => array(
						'title'    => 'SEO-счётчики',
						'filename' => 'dev/help/html/empty.html',
					),
					'notid'        => array(
						'title'    => 'копирайты',
						'filename' => 'dev/help/html/empty.html',
					),
					'hour'         => array(
						'title'    => 'поле поиска по сайту',
						'filename' => 'dev/help/html/empty.html',
					),
					'basket_add'   => array(
						'title'    => 'добавить товар в корзину',
						'filename' => 'dev/help/html/empty.html',
					),
					'basket_block' => array(
						'title'    => 'корзина заказов',
						'filename' => 'dev/help/html/empty.html',
					),

				),
			),

			'types'  => array(
				'title' => 'Вставить поле в шаблон',
				'recs'  => array(

					'randonlist' => array(
						'title'    => 'автор записи',
						'filename' => 'dev/help/html/empty.html',
					),
					'recs'       => array(
						'title'    => 'дата записи',
						'filename' => 'dev/help/html/empty.html',
					),
					'notid'      => array(
						'title'    => 'фотография и фотогалерея',
						'filename' => 'dev/help/html/empty.html',
					),
					'today'      => array(
						'title'    => 'цена',
						'filename' => 'dev/help/html/empty.html',
					),
					'tags'       => array(
						'title'    => 'облако тегов',
						'filename' => 'dev/help/html/empty.html',
					),
					'map'        => array(
						'title'    => 'карта сайта',
						'filename' => 'dev/help/html/empty.html',
					),

				),
			),

			'spec'   => array(
				'title' => 'Специальные возможности',
				'recs'  => array(

					'anonslist'  => array(
						'title'    => 'подключить шаблон',
						'filename' => 'dev/help/html/empty.html',
					),
					'recsdir'    => array(
						'title'    => 'добавить JS в шапку',
						'filename' => 'dev/help/html/empty.html',
					),
					'randonlist' => array(
						'title'    => 'добавить CSS в шапку',
						'filename' => 'dev/help/html/empty.html',
					),
					'recs'       => array(
						'title'    => 'добавить шаблон в шапку',
						'filename' => 'dev/help/html/empty.html',
					),
					'notid'      => array(
						'title'    => 'подсветить ссылки в тексте',
						'filename' => 'dev/help/html/empty.html',
					),
					'hour'       => array(
						'title'    => 'обрезать текст по длине',
						'filename' => 'dev/help/html/empty.html',
					),
					'24h'        => array(
						'title'    => 'согласование с числами',
						'filename' => 'dev/help/html/empty.html',
					),
					'today'      => array(
						'title'    => 'отсортировать массив',
						'filename' => 'dev/help/html/empty.html',
					),
				),
			)

		);

		return $groups;

	}


}