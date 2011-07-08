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
		/*Системное сообщение*/
		$this->model->log->step('запуск контроллера');

		//Подготавливаем запись
		$main_record = $this->model->prepareMainRecord();

		/*Системное сообщение*/
		$this->model->log->step('найдена главная запись');

		//Сколько уровней главного меню выводить
		if(IsSet($this->model->config['settings']['mainmenu_levels']))
			$levels=$this->model->config['settings']['mainmenu_levels']+1;
		else
			$levels=4;

		//Готовим другие специальные блоки
		$mainmenu = $this->model->prepareMainMenu($levels);
		/*Системное сообщение*/
		$this->model->log->step('сформировано главное меню');

		//Готовим "Хлебные крошки"
		$path = $this->model->prepareModelPath(0);

		/*Системное сообщение*/
		$this->model->log->step('готовы Хлебные Крошки');

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
		$tmpl->assign('content', $main_record);
		$tmpl->assign('mainmenu', $mainmenu);
		$tmpl->assign('original_url', $this->model->ask->original_url);
		$tmpl->assign('ask', $this->model->ask);
		$tmpl->assign('paths', $this->model->config['path']);
		$tmpl->assign('config', $this->model->config['settings']);
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

		/*Системное сообщение*/
		$this->model->log->step('данные записаны в шаблон');

		//Задаём кодировку ответа
		if (!headers_sent())
			header('Content-Type: text/html; charset=utf-8');

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
			if (!headers_sent())
				header("HTTP/1.0 200 Ok");
		}

		$template_file_path = $this->model->config['path']['templates'] . '/' . $current_template_file;
		if (!file_exists($template_file_path)) {
			pr($template_file_path);
			print('<p>Шаблон "' . $current_template_file . '" не установлен на домене.</p>');
			exit();
		}

		//Файл шаблона существует
		$ready_html = $tmpl->fetch($current_template_file);

		/*Системное сообщение*/
		$this->model->log->step('шаблон готов к выводу');

		//Файл шаблона отсутствует или ещё какая ошибка
		if (!$ready_html) {
			print('Файл шаблона не найден [' . $current_template_file . '].');
			exit();
		}
		//Всё в норме
		print($ready_html);

		/*Системное сообщение*/
		$this->model->log->step('данные отправлены пользователю');

		/*Системное сообщение*/
		$this->model->log->step('кеш создан');

		//Учёт посещений поисковиков
		$this->model->extensions['seo']->crawlerCount();

		/*Системное сообщение*/
		$this->model->log->step('учёт поисковиков');

		//Показать статистику
		$this->model->log->showStat();

		//Учитывать в глобальной статистике
		$this->model->log->globalStat();
	}


	//Укажите модуль, в который будет добавлена запись
	private function listModule($modules)
	{
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


}
?>
