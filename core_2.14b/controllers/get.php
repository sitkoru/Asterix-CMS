<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Контроллер GET-запросов								*/
/*															*/
/*	Версия ядра 2.0.b5										*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 3 ноября 2009 года						*/
/*															*/
/************************************************************/

require_once('default_controller.php');

class controller_get extends default_controller
{
	public function start(){
		if( (model::$ask->output_format == 'html') or (model::$ask->output_format == '404') )
			$this->getHTML();
			
		elseif( model::$ask->output_format == 'json' ) 
			$this->getJSON();
			
		elseif( model::$ask->output_format == 'tpl' ) 
			$this->getHTML();
	}


	//Выдать результат в формате JSON
	private function getJSON(){
		if (!headers_sent()){
			header('Content-Type: text/html; charset=utf-8');
			header("HTTP/1.0 200 Ok");
		}

		$result = array(
			'status' => 'ok',
			'url' => 'http://'.model::$ask->host.model::$ask->rec['url'],
			'module_sid' => model::$ask->module,
			'structure_sid' => model::$ask->structure_sid,
			'data' => model::$ask->rec,
		);
		
		if( model::$ask->mode[0] ){
		
			//Интерфейсы
			if( IsSet( model::$modules[ model::$ask->module ]->interfaces[ model::$ask->mode[0] ] ) )
				$result = model::$modules[ model::$ask->module ]->prepareInterface( model::$ask->mode[0], array('record'=>model::$ask->rec), true);
			
			//Компоненты
			elseif( IsSet( model::$modules[ model::$ask->module ]->prepares[ model::$ask->mode[0] ] ) )
				$result = model::$modules[ model::$ask->module ]->prepareComponent( model::$ask->mode[0], array('record'=>model::$ask->rec), true);
		}

		print json_encode( $result );
		exit();
	}
		
	//Выдать результат обычной HTML-страничкой
	private function getHTML(){
		//JavaScript
		
		$this->addJS('http://src.sitko.ru/3.0/j/jquery-1.7.1.min.js');
		$this->addJS('http://src.sitko.ru/3.0/jquery-ui/js/jquery-ui-1.8.17.custom.min.js');
		if( model::$settings['bootstrap'] ){
			$this->addJS('http://src.sitko.ru/3.0/bootstrap/bootstrap-alert.js');
			$this->addJS('http://src.sitko.ru/3.0/bootstrap/bootstrap-button.js');
			$this->addJS('http://src.sitko.ru/3.0/bootstrap/bootstrap-dropdown.js');
			$this->addJS('http://src.sitko.ru/3.0/bootstrap/bootstrap-tab.js');
		}
		$this->addJS('http://src.sitko.ru/3.0/j/panel.js');
		$this->addJS('http://src.sitko.ru/3.0/j/j.js');

		//Библиотеки для Администратора
		if( model::$settings['bootstrap'] ){
			$this->addCSS('http://twitter.github.com/bootstrap/assets/css/bootstrap.css');
			$this->addCSS('http://twitter.github.com/bootstrap/assets/css/bootstrap-responsive.css');
		}
		$this->addCSS('http://src.sitko.ru/3.0/c/panel.css');
		
		//Подключаем шаблонизатор
		require_once(model::$config['path']['core'] . '/classes/templates.php');
		$tmpl = new templater($this->model);
		
		//Для панели управления - модули
		if( user::is_authorized() ) {
			$add = $this->listModule(model::$modules);
			$tmpl->assign('add', $add);
		}

		//Задаём кодировку ответа
		if (!headers_sent())
			header('Content-Type: text/html; charset=utf-8');

		//Если не найдена - сначала проверяем наличие редиректов
		if ($main_record == '404 not found') {
			if( file_exists( model::$config['path']['www'].'/../http301.txt' ) ){
				//Читаем библиотеку редиректов
				$links = file(model::$config['path']['www'].'/../http301.txt');
				//Ищем нужный
				foreach($links as $link){
					list($old, $new) = explode('|', $link);
					//Перенаправляем
					if( $old == model::$ask->original_url ){
						header( 'HTTP/1.1 301 Moved Permanently' ); 
						header( 'Location: http://'.$this->model->extensions['domains']->domain['host'].$new );
						exit();
					}
				}
			}
		}
		
		//Шаблон и заголовок
		if( !model::$ask->rec ){
			model::$ask->rec = array('title' => 'Страница не найдена');
			model::$ask->module = 'start';
			header("HTTP/1.0 404 Not Found");
			$current_template_file = '404.tpl';
		}elseif( model::$ask->mode[0] == 'print' ){
			header("HTTP/1.0 404 Not Found");
			$current_template_file = model::$modules[model::$ask->module]->info['prototype'] . '_' . model::$ask->output_type.'_print.tpl';
		}else{
			header("HTTP/1.0 200 Ok");
			$current_template_file = model::$modules[model::$ask->module]->info['prototype'] . '_' . model::$ask->output_type.'.tpl';
		}

		//Файл основного шаблона
		if( model::$ask->output_format == 'tpl' )
			$template_file_path = model::$config['path']['templates'] . '/' . basename( end(model::$ask->mode) ).'.tpl';
		else
			$template_file_path = model::$config['path']['templates'] . '/' . $current_template_file;

		//Если шаблон не установлен - копируем обычный текстовый шаблон
		if (!file_exists($template_file_path))
			if( !copy(model::$config['path']['templates'].'/start_content.tpl', $template_file_path) )
				log::stop('500 Internal Server Error', 'Шаблон "' . $current_template_file . '" не установлен на домене.');

		//Основная запись
		$main_record = model::$modules[ model::$ask->module ]->explodeRecord(model::$ask->rec, model::$ask->structure_sid);
		$main_record = model::$modules[ model::$ask->module ]->insertRecordUrlType($main_record, model::$ask->output_format);
		//Интерфейсы
		if( model::$ask->mode[0] )
			if( IsSet( model::$modules[ model::$ask->module ]->interfaces[ model::$ask->mode[0] ] ) )
				$main_record['interface'] = model::$modules[ model::$ask->module ]->prepareInterface( model::$ask->mode[0], array('record'=>model::$ask->rec), true);
		//Компоненты
		if( model::$ask->mode[0] )
			if( IsSet( model::$modules[ model::$ask->module ]->prepares[ model::$ask->mode[0] ] ) )
				$main_record['component'] = model::$modules[ model::$ask->module ]->prepareComponent( model::$ask->mode[0], array('record'=>model::$ask->rec), true);
		//contentPrepare
		if( is_callable( array( model::$modules[ model::$ask->module ], 'contentPrepare' )) )
			$main_record = model::$modules[ model::$ask->module ]->contentPrepare( $main_record, $structure_sid='rec' );
				
		//Данные
		$tmpl->assign('head_add', self::$add);
		$tmpl->assign('mainmenu', $this->model->prepareMainMenu($levels));
		$tmpl->assign('original_url', model::$ask->original_url);
		$tmpl->assign('ask', model::$ask);
		$tmpl->assign('paths', model::$config['path']);
		$tmpl->assign('config', model::$config['settings']);
		$tmpl->assign('openid', model::$config['openid']);
		$tmpl->assign('path', $this->model->prepareModelPath(0));
		$tmpl->assign('domain', model::getDomain() );
		$tmpl->assign('settings', model::$settings);
		$tmpl->assign('user', user::$info);
		$tmpl->assign('get_vars', $_GET);
		$tmpl->assign('path_admin_templates', 	model::$config['path']['admin_templates']);
		$tmpl->assign('content', $main_record);

		//Компилируем
		try {
			$ready_html = $tmpl->fetch($template_file_path);
		} catch (Exception $e) {
			log::stop('500 Internal Server Error', 'Шаблон ['.$current_template_file.'] содержит синтаксические ошибки.', '<textarea style="width:500px; height:400px;">'.stripslashes( $this->vars['html'] ).'</textarea><br />'.$e);
		}
		
		//Ответ сервера
		print($ready_html);

		//Показать статистику
		log::showStat();
	}
	
	//Укажите модуль, в который будет добавлена запись
	private function listModule($modules){
		$recs = array();
		$subs = array();
		foreach ($modules as $module_sid => $module)
			if ($module->structure) {
				foreach ($module->structure as $structure_sid => $structure) {
					if (!$structure['hide_in_tree']) {
						if ($structure_sid == 'rec')
							$recs[] = array(
								'title' => $module->info['title'],
								'structure' => $structure['title'],
								'structure_sid' => $structure_sid,
								'module' => $module_sid
							);
						else
							$subs[] = array(
								'title' => $module->info['title'],
								'structure' => $structure['title'],
								'structure_sid' => $structure_sid,
								'module' => $module_sid
							);
					}
				}
			}
		return array(
			'recs' => $recs,
			'subs' => $subs
		);
	}

	//Возвращает компоненты для текущей страницы
	private function getComponents($settings, $main_record = false){

		//Если выбран модификатор, для которого есть внешний компонент
		if( IsSet(model::$ask->mode[0]) and in_array( str_replace('_','|',model::$ask->mode[0]), (array)$settings['components_ext'] ) ){
			//Вызываем компонент
			$component_sid = str_replace('_', '|', model::$ask->mode[0]);
			$url_mode = model::$ask->mode[0];
			
			$main_record['components'][ $url_mode ] = $this->getComponentOne( $component_sid );
		
		//Показываем внутренние компоненты записи
		}elseif( $settings['components_int'] and !IsSet(model::$ask->mode[0]) ){
			foreach($settings['components_int'] as $component_sid){
				$url_mode = str_replace('|', '_', $component_sid);
				$main_record['components'][ $url_mode ] = $this->getComponentOne( $component_sid );
			}
		}

		//Осталось построить меню внешних компонентов, если они есть
		if( $settings['components_ext'] ){
			$main_record['components_menu'] = $this->getComponentExtMenu($settings);
		}
		
		//Готово
		return $main_record;
	}
	//Показывает один компонент
	private function getComponentOne($value){

		//SID модуля и компонента
		list($module_prototype, $component_sid) = explode('|', $value);
		$module_sid = $this->model->getModuleSidByPrototype($module_prototype);
		
		//Запускаем компонент
		return model::$modules[ $module_sid ]->initComponent($component_sid, $main_record);
	}

	//Возвращает меню внешних компонентов записи
	private function getComponentExtMenu($settings){
		$menu = false;
		foreach($settings['components_ext'] as $value){
			//SID модуля и компонента
			list($module_prototype, $component_sid) = explode('|', $value);
			$module_sid = $this->model->getModuleSidByPrototype($module_prototype);
			$url_mode = str_replace('|', '_', $value);
			if( IsSet(model::$modules[ $module_sid ]->prepares[ $component_sid ]) ){
				$component = model::$modules[ $module_sid ]->prepares[ $component_sid ];
				$menu[] = array(
					'sid' => $url_mode,
					'title' => $component['title'],
					'url' => model::$ask->rec['url'].'.'.$url_mode.'.html',
					'selected' => ($url_mode == model::$ask->mode[0]),
				);
			}
		}
		return $menu;	
	}
	
	private function getInterfaces($settings, $main_record = false){

		//Если выбран модификатор, для которого есть внешний интерфейс
		if( IsSet(model::$ask->mode[0]) and in_array( str_replace('_','|',model::$ask->mode[0]), (array)$settings['interfaces_ext'] ) ){
			//Вызываем компонент
			$interface_sid = str_replace('_', '|', model::$ask->mode[0]);
			$url_mode = model::$ask->mode[0];
			$main_record['interfaces'][ $url_mode ] = $this->getInterfaceOne( $interface_sid, $main_record );
		
		//Показываем внутренние интерфейсы записи
		}elseif( $settings['interfaces_int'] and !IsSet(model::$ask->mode[0]) ){
			foreach($settings['interfaces_int'] as $interface_sid){
				$url_mode = str_replace('|', '_', $interface_sid);
				$main_record['interfaces'][ $url_mode ] = $this->getInterfaceOne( $interface_sid, $main_record );
			}
		}

		//Осталось построить меню внешних компонентов, если они есть
		if( $settings['interfaces_ext'] ){
			$main_record['interfaces_menu'] = $this->getInterfaceExtMenu($settings);
		}
		
		return $main_record;
	}
	//Показывает один компонент
	private function getInterfaceOne($value, $main_record){

		//SID модуля и компонента
		list($module_prototype, $interface_sid) = explode('|', $value);
		$module_sid = $this->model->getModuleSidByPrototype($module_prototype);
		
		//Если компонент доступен в модуле
		if( IsSet(model::$modules[ $module_sid ]->interfaces[ $interface_sid ]) ){
			$int = model::$modules[ $module_sid ]->interfaces[ $interface_sid ];
		
			//Проверка доступа к интерфейсу
			if( 
				( !$int['auth'] ) or //Авторизация не требуется
				( ($int['auth'] === true) and user::is_authorized() ) or //Пользователь должен быть просто авторизован
				( ($int['auth'] === 'admin') and user::is_admin() )
			)
				return model::$modules[ $module_sid ]->prepareInterface( $interface_sid, $main_record );
		}

		//Не нашли доступного компонента
		return false;
	}
	//Возвращает меню внешних компонентов записи
	private function getInterfaceExtMenu($settings){
		$menu = false;
		foreach($settings['interfaces_ext'] as $value){
			//SID модуля и компонента
			list($module_prototype, $interface_sid) = explode('|', $value);
			$module_sid = $this->model->getModuleSidByPrototype($module_prototype);
			$url_mode = str_replace('|', '_', $value);
			if( IsSet(model::$modules[ $module_sid ]->interfaces[ $interface_sid ]) ){

				$int = model::$modules[ $module_sid ]->interfaces[ $interface_sid ];

				//Проверка доступа к интерфейсу
				if( 
					( !$int['auth'] ) or //Авторизация не требуется
					( ($int['auth'] === true) and user::is_authorized() ) or //Пользователь должен быть просто авторизован
					( ($int['auth'] === 'admin') and user::is_admin() )
				)
					$menu[] = array(
						'sid' => $url_mode,
						'title' => $int['title'],
						'url' => model::$ask->rec['url'].'.'.$url_mode.'.html',
						'selected' => ($url_mode == model::$ask->mode[0]),
					);
			}
		}
		return $menu;	
	}
	
}
?>
