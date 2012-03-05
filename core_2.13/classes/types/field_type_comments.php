<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Тип данных - Комментарий							*/
/*															*/
/*	Версия ядра 2.0.b5										*/
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

/*
Комментарии
+ древовидное отображение
+ отображение списком
+ разбивка комментариев на страницы
+ выбор сортировки вывода (новые вверху/внизу)
+ быстрая память последнего комментария
+ запрет/разешение комментировать гостям
+ редактирование непрочитанных комментариев
*/


class field_type_comments extends field_type_default
{
	public $default_settings = array('sid' => false, 'title' => 'Комментарии к записи', 'value' => '', 'width' => '100%');
	
	public $template_file = 'types/comments.tpl';
	private $table = 'comments';
	
	//Поле участвует в поиске
	public $searchable = true;
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//Подготавливаем значение для SQL-запроса
	public function toValue($value_sid, $values)
	{
		return false;
		//Включены комментарии у записи
		if ($values[$value_sid]) {
			$value = $values['module'] . '|' . $values['structure_sid'] . '|' . $values['id'];
			
			//Выключены комментарии
		} else {
			$value = false;
		}
		
		//Готово
		return $value;
	}
	
	
	
	//Получить развёрнутое значение из простого значения
	public function getValueExplode($value, $settings = false, $record = array())
	{

		//Комментарии включены
		if ($value) {
		
			//Текущая запись активна - забираем все комментарии
			if ($this->model->ask->rec['comments'] === $value) {
				list($module_sid, $structure_sid, $id) = explode('|', $value);
				$recs = $this->getCommentsByRecord($module_sid, $structure_sid, $id, 10);
				
				if (!count($recs))
					return false;
				
				return $recs;
				
			//Другая запись, выводим только последний комментарий
			} else {
				list($module_sid, $structure_sid, $id, $rec['date_public'], $rec['author']['title'], $rec['author']['url'], $rec['text'], $rec['counter']) = explode('|', $value);
				
				//Разворачиваем дату
				$rec['date_public']=$this->model->types['datetime']->getValueExplode($rec['date_public']);
				if(is_object($this->model->modules[$module_sid]))$rec['author']=$this->model->modules[$module_sid]->insertRecordUrlType($rec['author']);
				
//				$rec = $this->getLastCommentByRecord($module_sid, $structure_sid, $id);
				
				if (!strlen($rec['text'])) return false;
				
				return $rec;
			}
			
		}
		
		//Комментариев нет
		return false;
	}
	
	
	
	
	
	
	
	
	//Получить комментарии определённой записи в модели
	public function getCommentsByRecord($module_sid, $structure_sid, $id, $levels = false)
	{
		
		if(!$levels)
			$levels=50;
		$levels+=2;
		
		$recs = $this->model->makeSql(array(
			'tables' => array(
				$this->table
			),
			'where' => array(
				'and' => array(
					'`module`="' . mysql_real_escape_string($module_sid) . '"',
					'`structure_sid`="' . mysql_real_escape_string($structure_sid) . '"',
					'`record_id`="' . mysql_real_escape_string($id) . '"',
					'`tree_level`<' . intval($levels),
					'`tree_level`>1',
					'access'=>false,
				)
			),
			'order' => 'order by `left_key'
		), 'getall','system',true);
//		pr($this->model->last_sql);

		$users_module_sid=$this->model->getModuleSidByPrototype('users');
		
		//Расшифровываем значения
		if ($recs)
			foreach ($recs as $i => $rec) {
				$recs[$i]['author'] = $this->model->modules[$users_module_sid]->getRecordById($structure_sid,$recs[$i]['author']);
				$recs[$i]['author']['avatar'] = $this->model->types['image']->getValueExplode($recs[$i]['author']['avatar'], $this->model->modules[$users_module_sid]->structure[$structure_sid]['fields']['avatar'] );
				$recs[$i]['date']   = $this->model->types['datetime']->getValueExplode($rec['date'], false, $rec);
				$recs[$i]['text']   = stripslashes(nl2br($rec['text']));
				
				//Если есть голосования за комментарии - разворачиваем
				if(IsSet($rec['votes']))
					$recs[$i]['votes'] = $this->model->types['votes']->getValueExplode($rec['votes']);
				
			}
		
		//Перекомпановка из линейного массива во вложенные списки
		$recs=$this->model->modules[false]->reformRecords($recs,$recs[0]['tree_level'],0,count($recs));

		$recs=array_reverse($recs);
		
		return $recs;
	}
	
	//Получить комментарии определённой записи в модели
	public function getLastCommentByRecord($module_sid, $structure_sid, $id)
	{
		$rec = $this->model->makeSql(array(
			'tables' => array(
				$this->table
			),
			'where' => array(
				'and' => array(
					'`module`="' . mysql_real_escape_string($module_sid) . '"',
					'`structure_sid`="' . mysql_real_escape_string($structure_sid) . '"',
					'`record_id`="' . mysql_real_escape_string($id) . '"',
					'`tree_level`>1'
				)
			),
			'order' => 'order by `date` desc'
		), 'getrow'); //pr($this->model->last_sql);
		
		//Расшифровываем значения
		if ($rec) {
			$rec['author'] = $this->model->types['user']->getValueExplode($rec['author'], false, $rec);
			$rec['date']   = $this->model->types['datetime']->getValueExplode($rec['date'], false, $rec);
			$rec['text']   = stripslashes(nl2br($rec['text']));
			if (strlen($rec['text']) > 200) {
				$t           = explode(' ', $rec['text']);
				$rec['text'] = '';
				foreach ($t as $s)
					if (strlen($rec['text']) < 200)
						$rec['text'] .= $s . ' ';
				$rec['text'] .= '...';
			}
		}

		return $rec;
	}
	
	//Добавляем комментарий
	public function addComment($module_sid, $structure_sid, $record_id, $values)
	{
		
		//Кому посылаем уведомления
		$send_to_parent_author=false;
		$send_to_record_author=true;

		if(substr_count($values['text'], 'Есть что сказать?')){
			return false;
		}
		
		//Текст комментария
		$comment=$values['text'];
		
		//Имя автора
		$author_title='Аноним';

		//Указано имя автора
		if( IsSet($values['author']) ){
			$author_title=$values['author'];
			
		//Пользователь авторизован
		}elseif($this->model->user->info['id'])
			$author_title=$this->model->user->info['title'];
		
		//Корень найден - будем класть в него
		if(IsSet($values['parent'])){
			
			$parent_rec = $this->model->makeSql(array(
				'tables' => array($this->table),
				'where' => array(
					'and' => array(
						'`id`="' . mysql_real_escape_string($values['parent']) . '"',
						'access'=>false
					)
				)
			), 'getrow');
			$parent_id = intval( $values['parent'] );

			//Уведомляем автора комментария, на который отвечаем
			$send_to_parent_author=true;
		
		}else{
			//Ищем корень дерево комментариев, если есть - новый комментарий будет класться в него
			$parent_rec = $this->model->makeSql(array(
				'tables' => array(
					$this->table
				),
				'where' => array(
					'and' => array(
						$this->model->extensions['domains']->getWhere(),
						'`module`="' . mysql_real_escape_string($module_sid) . '"',
						'`structure_sid`="' . mysql_real_escape_string($structure_sid) . '"',
						'`record_id`="' . mysql_real_escape_string($record_id) . '"',
						'`tree_level`=1',
						'access'=>false
					)
				)
			), 'getrow');
		
			//Корня дерева не найдено - придётся создавать
			if (!$parent_rec) {
				$this->model->makeSql(array(
					'tables' => array(
						$this->table
					),
					'fields' => array(
						'`tree_level`=1',
						'`left_key`=1',
						'`right_key`=2',
						'`date`="' . date("Y-m-d H:i:s") . '"',
						'`text`="' . mysql_real_escape_string($comment) . '"',
						'`author`="' . $this->model->user->info['id'] . '"',
						'`author_title`="' . mysql_real_escape_string($author_title) . '"',
						'`module`="' . mysql_real_escape_string($module_sid) . '"',
						'`structure_sid`="' . mysql_real_escape_string($structure_sid) . '"',
						'`record_id`="' . mysql_real_escape_string($record_id) . '"'
					)
				), 'insert');
				//Ищем последнюю по дате
				$parent_rec = $this->model->makeSql(array(
					'tables' => array(
						$this->table
					),
					'where' => array(
						'and' => array(
							$this->model->extensions['domains']->getWhere(),
							'`module`="' . mysql_real_escape_string($module_sid) . '"',
							'`structure_sid`="' . mysql_real_escape_string($structure_sid) . '"',
							'`record_id`="' . mysql_real_escape_string($record_id) . '"',
							'`tree_level`=1',
							'access'=>false
						)
					),
					'order' => 'order by `date` desc'
				), 'getrow');
			}
			//Корень найден - будем класть в него
			$parent_id = $parent_rec['id'];
		}
		
		//Если не установлен обработчик таблицы
		if (!IsSet($this->model->extensions['domains']->db_manager)) {
			require_once($this->model->config['path']['core'] . '/classes/nestedsets.php');
			$this->model->extensions['domains']->db_manager = new nested_sets($this->model, $this->table);
		}
		
		//Условие для обновления деревьев
		$conditions = array(
			'and' => array(
				$this->model->extensions['domains']->getWhere(),
				'`module`="' . mysql_real_escape_string($module_sid) . '"',
				'`structure_sid`="' . mysql_real_escape_string($structure_sid) . '"',
				'`record_id`="' . mysql_real_escape_string($record_id) . '"'
			)
		);
		
		//Ищем запись, к которой этот комментарий оставлен
		$original_rec = $this->model->modules[ $module_sid ]->getRecordById($structure_sid, $record_id);
		
		//Данные для новой записи
		$what = array(
			'date' => date("Y-m-d H:i:s"),
			'text' => strip_tags($comment, '<a><b><i><br>'),
			'author' => $this->model->user->info['id'],
			'author_title' => mysql_real_escape_string($author_title),
			'module' => mysql_real_escape_string($module_sid),
			'structure_sid' => mysql_real_escape_string($structure_sid),
			'record_id' => mysql_real_escape_string($record_id),
			'domain' => '|' . $this->model->extensions['domains']->domain['id'] . '|',
			'ln' => 1,
			'url' => $original_rec['url']
		);
		
		//Добавляем запись
		$this->model->extensions['domains']->db_manager->addChild($parent_id, $what, $conditions);
		
		//Данные комментария, для указания в записи
		$comm_data=date("Y-m-d H:i:s") . '|' . 
			str_replace('|', '', $this->model->user->info['title']). '|' . 
			$this->model->user->info['url']. '|' . 
			htmlspecialchars($comment). '|' . 
			round($parent_rec['right_key']/2)
		;
		
		//Обновляем запись, к которой был сделан комментарий
		$this->model->makeSql(
			array(
				'tables' => array($this->model->modules[$module_sid]->getCurrentTable($structure_sid)),
				'fields' => array('`comments`="' . mysql_real_escape_string($module_sid . '|' . $structure_sid . '|' . $record_id . '|' . $comm_data) . '"'),
				'where' => array('and' => array('`id`="' . $record_id . '"')),
			),
			'update'
		);
		
		$this->model->execSql('update `comments` set `domain`="all"','update');
		
		$rec=false;
		//Уведомление об ответе на комментарий
		if( $send_to_parent_author ){
			$rec = $this->model->modules[ $module_sid ]->getRecordById( $structure_sid, $record_id );

			//Если ответ не на свой комментарий
			if($parent_rec['author'] != $this->model->user->info['id']){
				$message = array(
					'subject'=>$this->model->extensions['domains']->domain['title'].': на ваш комментарий ответили.',
					'message'=>'Пользователь <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$this->model->user->info['url'].'.html">'.$this->model->user->info['title'].'</a> ответил на ваш <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$rec['url'].'#comm'.$parent_rec['id'].'">комментарий</a> к записи <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$rec['url'].'">'.$rec['title'].'</a>.',
				);
				$module_sid = $this->model->getModuleSidByPrototype('users');
				$this->model->modules[ $module_sid ]->messageTo($parent_rec['author'], $message, 'subscribe_comments');
			}
		}
		//Уведомление о комментарии к записи
		if( $send_to_record_author )if(!$rec){
			$rec = $this->model->modules[ $module_sid ]->getRecordById( $structure_sid, $record_id );
			
			//Если у записи указан автор
			if($rec['author']){
				if($rec['author'] != $this->model->user->info['id']){
					$message = array(
						'subject'=>$this->model->extensions['domains']->domain['title'].': комментарий к вашей записи.',
						'message'=>'Пользователь <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$this->model->user->info['url'].'.html">'.$this->model->user->info['title'].'</a> прокомментировал вашу запись <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$rec['url'].'">'.$rec['title'].'</a>.',
					);
					$module_sid = $this->model->getModuleSidByPrototype('users');
					$this->model->modules[ $module_sid ]->messageTo($rec['author'], $message, 'subscribe_my');
				}
			}
			
			//Если у записи указана компания
			if($rec['company']){
				if($rec['company'] != $this->model->user->info['corporate']){
					//Все корпоративные пользователи
					$users = $this->model->execSql('select * from `users` where `corporate`="'.intval($rec['company']).'" order by `id`','getall');
					$message = array(
						'subject'=>$this->model->extensions['domains']->domain['title'].': комментарий к записи вашей компании.',
						'message'=>'Пользователь <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$this->model->user->info['url'].'.html">'.$this->model->user->info['title'].'</a> прокомментировал запись <a href="http://'.$this->model->extensions['domains']->domain['host'].''.$rec['url'].'">'.$rec['title'].'</a>, которая размещена от лица вашей компании.',
					);
					$module_sid = $this->model->getModuleSidByPrototype('users');
					foreach($users as $user)
						//Рассылаем всем кроме автора материала, ему уже отправили выше.
						if($user['id'] != $rec['author'])
							$this->model->modules[ $module_sid ]->messageTo($user['id'], $message, 'subscribe_corp_comments');
					
				}
			}
		}

	}
	
	//Добавляем комментарий
	public function hideComment($comment_id){
		//Сам комментарий
		$comm = $this->model->execSql('select * from `comments` where `id`="'.mysql_real_escape_string($comment_id).'" limit 1','getrow');
		//Скрываем текст
		$this->model->execSql('update `comments` set `text`="(мой комментарий скрыт модераторами)" where `id`="'.mysql_real_escape_string($comment_id).'" limit 1','update');
		//Обновляем запись
		$comms = $this->commentsForRecord($comm['module'], $comm['structure_sid'], $comm['record_id']);
		$this->model->execSql('update `'.$this->model->modules[ $comm['module'] ]->getCurrentTable( $comm['structure_sid'] ).'` set `comments`="'.mysql_real_escape_string($comms).'" where `id`="'.$comm['record_id'].'" limit 1', 'update');
		//Готовоы
		print('Комментарий скрыт');
		exit();
	}

	
	public function commentsForRecord($module, $structure_sid, $record_id){
		$count=$this->model->execSql('select count(`id`) as `counter` from `comments` where `module`="'.mysql_real_escape_string($module).'" and `structure_sid`="'.mysql_real_escape_string($structure_sid).'" and `record_id`="'.mysql_real_escape_string($record_id).'" and `tree_level`>1', 'getrow');
		$count=$count['counter'];
		
		$rec = $this->model->execSql('select * from `comments` where `module`="'.mysql_real_escape_string($module).'" and `structure_sid`="'.mysql_real_escape_string($structure_sid).'" and `record_id`="'.mysql_real_escape_string($record_id).'" and `tree_level`>1 order by `id` desc','getrow');
		$rec['author'] = $this->model->types['user']->getValueExplode($rec['author']);
		return $module.'|'.$structure_sid.'|'.$record_id.'|'.$rec['date'].'|'.$rec['author']['title'].'|'.$rec['author']['url'].'|'.$rec['text'].'|'.$count;
	}
	
	public function reindex(){
		foreach($this->model->modules as $module_sid=>$module){
			if($module->structure)
			foreach($module->structure as $structure_sid=>$structure){
				foreach($structure['fields'] as $field_sid=>$field){
					if($field['type'] == 'comments'){
						//К каким записям есть голоса
						$t=$this->model->execSql('select distinct `record_id` from `comments` where `module`="'.mysql_real_escape_string($module_sid).'" and `structure_sid`="'.mysql_real_escape_string($structure_sid).'" and `tree_level`>1','getall');
						$ids=array();
						foreach($t as $ti)$ids[]=$ti['record_id'];
						//Сами записи
						$recs=$this->model->execSql('select * from `'.$module->getCurrentTable($structure_sid).'` where `id` IN ('.implode(', ', $ids).') order by `id`','getall');
						if($recs)
						foreach($recs as $rec){
							$comms = $this->commentsForRecord($module_sid, $structure_sid, $rec['id']);
							if($comms){
								$this->model->execSql('update `'.$module->getCurrentTable($structure_sid).'` set `'.$field_sid.'`="'.mysql_real_escape_string($comms).'" where `id`="'.$rec['id'].'" limit 1', 'update');
							}
						}
						$this->model->execSql('update `'.$module->getCurrentTable($structure_sid).'` set `'.$field_sid.'`="0" where `id` NOT IN ('.implode(', ', $ids).')','update');
					}
				}
			}
		}
	}
	

	private function messageTo(){
	
		if($this->model->user->info['admin']){
			pr('Привет для админов от Олега.');
			exit();
		}
	
	}
	
}

?>