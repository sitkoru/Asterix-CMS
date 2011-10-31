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

require('default_controller.php');

class controller_get extends default_controller
{
	public function start()
	{
		//Подготавливаем запись
		$main_record = $this->model->prepareMainRecord();

		//Сколько уровней главного меню выводить
		if(IsSet($this->model->config['settings']['mainmenu_levels']))
			$levels=$this->model->config['settings']['mainmenu_levels']+1;
		else
			$levels=4;

		//Готовим другие специальные блоки
		$mainmenu = $this->model->prepareMainMenu($levels);

		//Готовим "Хлебные крошки"
		$path = $this->model->prepareModelPath(0);

		//Текущий шаблон
		$current_template_file = $this->model->modules[$this->model->ask->module]->info['prototype'] . '_' . $this->model->ask->output_type;

		//Версия для печати
		if (IsSet($this->model->ask->mode))
			if (in_array('print', $this->model->ask->mode)) {
				//Проверяем существование шаблона версии для печати для данного контента
				if (file_exists($this->model->config['path']['templates'] . '/' . $current_template_file . '_print.tpl')) {
					$current_template_file .= '_print';
				} else {
					pr('Отсутствует шаблон версии для печати: [' . $current_template_file . '_print.tpl]');
				}
			}

		//Расширение файла для шаблоона - добавляем после выбора модификации шаблона
		$current_template_file .= '.tpl';

		//Подключаем шаблонизатор
		require_once($this->model->config['path']['core'] . '/classes/templates.php');
		$tmpl = new templater($this->model);

		//Пишем данные в шаблонизатор
		$tmpl->assign('mainmenu', $mainmenu);
		$tmpl->assign('original_url', $this->model->ask->original_url);
		$tmpl->assign('ask', $this->model->ask);
		$tmpl->assign('paths', $this->model->config['path']);
		$tmpl->assign('config', $this->model->config['settings']);
		$tmpl->assign('openid', $this->model->config['openid']);
		$tmpl->assign('path', $path);
		$tmpl->assign('domain', $this->model->extensions['domains']->domain);
		$tmpl->assign('settings', $this->model->settings);
		$tmpl->assign('user', $this->model->user->info);
		$tmpl->assign('get_vars', $_GET);
		
		//Различные типы контента, только для авторизованных
		if (IsSet($this->model->user->info['id'])) {
			$add = $this->listModule($this->model->modules);
			$tmpl->assign('add', $add);
		}

		//Сообщения на один шаг - обычно используются для сообщений
		//после выполнения некоторых действия
		if (IsSet($_SESSION['messages'])) {
			$tmpl->assign('messages', $_SESSION['messages']);
			UnSet($_SESSION['messages']);
		} else {
			$tmpl->assign('messages', false);
		}

		//Задаём кодировку ответа
		if (!headers_sent())
			header('Content-Type: text/html; charset=utf-8');

		//Если не найдена - сначала проверяем наличие редиректов
		if ($main_record == '404 not found') {
			if( file_exists( $this->model->config['path']['www'].'/../http301.txt' ) ){
				//Читаем библиотеку редиректов
				$links = file($this->model->config['path']['www'].'/../http301.txt');
				//Ищем нужный
				foreach($links as $link){
					list($old, $new) = explode('|', $link);
					//Перенаправляем
					if( $old == $this->model->ask->original_url ){
						header( 'HTTP/1.1 301 Moved Permanently' ); 
						header( 'Location: http://'.$this->model->extensions['domains']->domain['host'].$new );
						exit();
					}
				}
			}
		}
		
		//Если не найдена - шаблон ошибки
		if ($main_record == '404 not found') {
			$main_record = array(
				'title' => 'Страница не найдена'
			);
			$tmpl->assign('content', $main_record);
			if (!headers_sent())
				header("HTTP/1.0 404 Not Found");
			$current_template_file = '404.tpl';
		} else {
			if ( $this->model->ask->mode[0] == 'print' )
				header("HTTP/1.0 404 Not Found");
			elseif (!headers_sent())
				header("HTTP/1.0 200 Ok");
		}

		//Файл основного шаблона
		$template_file_path = $this->model->config['path']['templates'] . '/' . $current_template_file;
		if (!file_exists($template_file_path)) {
			
			//Пытаемся склонировать шаблон материала
			copy($this->model->config['path']['templates'].'/start_content.tpl', $template_file_path);
			//Интересно, сработало?
			if (!file_exists($template_file_path)) {
				pr($template_file_path);
				print('<p>Шаблон "' . $current_template_file . '" не установлен на домене.</p>');
				exit();
			}
		}

		//Компоненты и интерфейсы
		if( $this->model->config['settings']['dock_interfaces_to_records'] ){
			$cfg = $this->model->config['path']['templates'].'/'.$current_template_file.'.cfg';
			if( file_exists($cfg) && ($main_record['url']!='/') ){

				//Компоненты и интерфейсы для записи
				$settings = unserialize( file_get_contents($cfg) );
				$t = $this->getComponents($main_record, $settings);
				if( IsSet($t['components']) )		$tmpl->assign('components', $t['components']);
				if( IsSet($t['components_menu']) )	$tmpl->assign('components_menu', $t['components_menu']);
				
				$t = $this->getInterfaces($main_record, $settings);
				if( IsSet($t['interfaces']) )		$tmpl->assign('interfaces', $t['interfaces']);
				if( IsSet($t['interfaces_menu']) )	$tmpl->assign('interfaces_menu', $t['interfaces_menu']);
			}

			//Компоненты и интерфейсы для записи
			$settings = unserialize( stripslashes( $main_record['acms_settings'] ) );
			$main_record = $this->getComponents($main_record, $settings);
			$main_record = $this->getInterfaces($main_record, $settings);

		}

		//Дописываем саму запись
		$tmpl->assign('content', $main_record);

		//Файл шаблона существует
		//Проверяем корректность шаблона
		try {
			$ready_html = $tmpl->fetch($current_template_file);
		} catch (Exception $e) {
			print('Шаблон ['.$current_template_file.'] содержит синтаксические ошибки.<br />');
			print('<textarea style="width:500px; height:400px;">'.stripslashes( $this->vars['html'] ).'</textarea>');
			pr($e);
			exit();
		}
		

		//Файл шаблона отсутствует или ещё какая ошибка
		if (!$ready_html) {
			print('Файл шаблона не найден [' . $current_template_file . '].');
			exit();
		}
		//Всё в норме
		print($ready_html);

		//Учёт посещений поисковиков
		$this->model->extensions['seo']->crawlerCount();

		//Показать статистику
		$this->model->log->showStat();

		//Учитывать в глобальной статистике
		$this->model->log->globalStat();
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
	private function getComponents($main_record, $settings){

		//Если выбран модификатор, для которого есть внешний интерфейс
		if( IsSet($this->model->ask->mode[0]) and in_array( str_replace('_','|',$this->model->ask->mode[0]), (array)$settings['components_ext'] ) ){
			//Вызываем компонент
			$component_sid = str_replace('_', '|', $this->model->ask->mode[0]);
			$url_mode = $this->model->ask->mode[0];
			$main_record['components'][ $url_mode ] = $this->getComponentOne( $component_sid );
		
		//Показываем внутренние интерфейсы записи
		}elseif( $settings['components_int'] and !IsSet($this->model->ask->mode[0]) ){
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
		
		//Если компонент доступен в модуле
		if( IsSet($this->model->modules[ $module_sid ]->prepares[ $component_sid ]) ){
			$component = $this->model->modules[ $module_sid ]->prepares[ $component_sid ];

			if( is_callable( array($this->model->modules[ $module_sid ], $component['function']) ) )
				//Подгружаем компонент
				return call_user_func( array($this->model->modules[ $module_sid ], $component['function']), $main_record);
		}

		//Не нашли доступного компонента
		return false;
	}
	//Возвращает меню внешних компонентов записи
	private function getComponentExtMenu($settings){
		$menu = false;
		foreach($settings['components_ext'] as $value){
			//SID модуля и компонента
			list($module_prototype, $component_sid) = explode('|', $value);
			$module_sid = $this->model->getModuleSidByPrototype($module_prototype);
			$url_mode = str_replace('|', '_', $value);
			if( IsSet($this->model->modules[ $module_sid ]->prepares[ $component_sid ]) ){
				$component = $this->model->modules[ $module_sid ]->prepares[ $component_sid ];
				$menu[] = array(
					'sid' => $url_mode,
					'title' => $component['title'],
					'url' => $this->model->ask->rec['url'].'.'.$url_mode.'.html',
					'selected' => ($url_mode == $this->model->ask->mode[0]),
				);
			}
		}
		return $menu;	
	}
	
	private function getInterfaces($main_record, $settings){

		//Если выбран модификатор, для которого есть внешний интерфейс
		if( IsSet($this->model->ask->mode[0]) and in_array( str_replace('_','|',$this->model->ask->mode[0]), (array)$settings['interfaces_ext'] ) ){
			//Вызываем компонент
			$interface_sid = str_replace('_', '|', $this->model->ask->mode[0]);
			$url_mode = $this->model->ask->mode[0];
			$main_record['interfaces'][ $url_mode ] = $this->getInterfaceOne( $interface_sid, $main_record );
		
		//Показываем внутренние интерфейсы записи
		}elseif( $settings['interfaces_int'] and !IsSet($this->model->ask->mode[0]) ){
			foreach($settings['interfaces_int'] as $interface_sid){
				$url_mode = str_replace('|', '_', $interface_sid);
				$main_record['interfaces'][ $url_mode ] = $this->getInterfaceOne( $interface_sid, $main_record );
			}
		}

		//Осталось построить меню внешних компонентов, если они есть
		if( $settings['interfaces_ext'] ){
			$main_record['interfaces_menu'] = $this->getInterfaceExtMenu($settings);
		}
		
		//Готово
		return $main_record;
	}
	//Показывает один компонент
	private function getInterfaceOne($value, $main_record){

		//SID модуля и компонента
		list($module_prototype, $interface_sid) = explode('|', $value);
		$module_sid = $this->model->getModuleSidByPrototype($module_prototype);
		
		//Если компонент доступен в модуле
		if( IsSet($this->model->modules[ $module_sid ]->interfaces[ $interface_sid ]) ){
			$int = $this->model->modules[ $module_sid ]->interfaces[ $interface_sid ];
		
			//Проверка доступа к интерфейсу
			if( 
				( !$int['auth'] ) or //Авторизация не требуется
				( ($int['auth'] === true) and $this->model->user->info['id'] ) or //Пользователь должен быть просто авторизован
				( ($int['auth'] === 'admin') and $this->model->user->info['admin'] )
			)
				return $this->model->modules[ $module_sid ]->prepareInterface( $interface_sid, $main_record );
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
			if( IsSet($this->model->modules[ $module_sid ]->interfaces[ $interface_sid ]) ){

				$int = $this->model->modules[ $module_sid ]->interfaces[ $interface_sid ];

				//Проверка доступа к интерфейсу
				if( 
					( !$int['auth'] ) or //Авторизация не требуется
					( ($int['auth'] === true) and $this->model->user->info['id'] ) or //Пользователь должен быть просто авторизован
					( ($int['auth'] === 'admin') and $this->model->user->info['admin'] )
				)
					$menu[] = array(
						'sid' => $url_mode,
						'title' => $int['title'],
						'url' => $this->model->ask->rec['url'].'.'.$url_mode.'.html',
						'selected' => ($url_mode == $this->model->ask->mode[0]),
					);
			}
		}
		return $menu;	
	}
	

}
?>
