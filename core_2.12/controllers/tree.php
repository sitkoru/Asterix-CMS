<?php

require('default_controller.php');

class controller_tree extends default_controller
{
	//Основная цункция контроллера, получающая управление после инициализации
	public function start()
	{
		
		//Шаблон
		$current_template_file = 'admin.tpl';
		
		//Подготавливаем запись
		$main_record = $this->model->prepareMainRecord();
		
		//Подключаем шаблонизатор
		require($this->model->config['path']['core'] . '/classes/templates.php');
		$tmpl = new templater($this->model);
		
		$action_result = $this->showTree();
		
		//Выставляем путь к пакету шаблонов
		$action_result['content_template_file'] = $tmpl->correctTemplatePackPath($action_result['content_template_file'], 'admin_templates');
		
		//Пишем всё в шаблонизатор
		$tmpl->assign('action', $action_result);
		$tmpl->assign('paths', $this->model->config['path']);
		$tmpl->assign('settings', $this->model->settings);
		$tmpl->assign('path_admin_templates', $this->model->config['path']['admin_templates']);
		
		//Выдаём необходимые заголовки
		if (!headers_sent()) {
			//Кодировка
			header('Content-Type: text/html; charset=utf-8');
			header("HTTP/1.0 200 Ok");
		}
		
		//Файл шаблона существует
		$ready_html = $tmpl->fetch($current_template_file, 'admin_templates');
		
		//Что-то не так
		if (!$ready_html) {
			print('Файл шаблона не найден [' . $current_template_file . '].');
			exit();
		}
		print($ready_html);
		
	}
	
	//Показать настройки
	private function showTree()
	{
		$res                          = array();
		$res['title']                 = 'Дерево сайта';
		$res['content_template_file'] = 'tree.tpl';
		
		$levels=2;
		
		if($this->model->ask->rec['is_link_to_module'])$this->model->ask->rec['id']=false;
		
		//Получаем дерево
		$recs = $this->model->prepareShirtTree( $this->model->ask->module, $this->model->ask->structure_sid, $this->model->ask->rec['id'], $levels, array());
		
		//Если считаем поддерево - нужен префикс для ID дочерних элементов
		$res['prefix']=$this->model->ask->rec['id'];
		
		$recs = $this->addManage($recs);
		
		if ($recs)
			$res['recs'] = $recs;
			
		return $res;
	}
	
	//Добавляем управление к записям
	private function addManage($recs)
	{
		if ($recs)
			foreach ($recs as $i => $rec) {
				$recs[$i]['manage']['edit'] = array(
					'title' => 'изменить',
					'method' => 'GET',
					'action' => 'edit',
					'param' => ''
				);
				
				//Удаление, если раздел пуст
				if (!$rec['sub'])
					if (!$rec['is_link_to_module'])
						$recs[$i]['manage']['delete'] = array(
							'title' => 'удалить',
							'method' => 'GET',
							'action' => 'delete',
							'param' => ''
						);
				
				
				//				$recs[$i]['manage']['access']=array('title'=>'доступ','method'=>'GET','action'=>'access','param'=>'');
				
				//Чистый URL
				$recs[$i]['url_clear'] = $rec['url'];
				
				if ($i < count($recs) - 1){
					$recs[$i]['manage']['move_down'] = array(
						'title' => 'ниже',
						'method' => 'GET',
						'action' => 'move_down'
					);
					$recs[$i]['url'].='?method_marker=admin&action=move_down';
				}
				
				if ($i > 0){
					$recs[$i]['manage']['move_up'] = array(
						'title' => 'выше',
						'method' => 'GET',
						'action' => 'move_up'
					);
					$recs[$i]['url'].='?method_marker=admin&action=move_up';
				}
				
				if (IsSet($recs[$i]['sub'])) {
					$recs[$i]['sub'] = $this->addManage($rec['sub']);
				}
			}
		return $recs;
	}

	
}

?>