<?php

class interfaces{

	public static function load( $module ){
		
		//Грузим интерфейсы модуля
		$module->setInterfaces();

		//Интерфейсы для администраторов
		$module->interfaces['addRecord'] = array(
			'title' => 'Добавить '.mb_strtolower($module->title_to, 'utf-8'),				//Название интерфейса
			'structure_sid' => false,													//Используемая структура текущего модуля
			'fields' => false,															//Поля для интерфейса
			'system' => true,
			'ajax' => false,															//Отправка при помощи AJAX
			'protection' => false,														//Защита формы
			'auth' => true,																//Необходимый уровень доступа к интерфейсу
			'use_record' => false,														//Использовать ли уже имеющуюся запись
			'getfields' => 'getFields',
			'control' => 'addRecord',													//Функция, отвечающая за обработку интерфейса после отправки
		);
		$module->interfaces['editRecord'] = array(
			'title' => 'Изменить '.mb_strtolower($module->title_to, 'utf-8'),
			'structure_sid' => false,
			'fields' => false,
			'system' => true,
			'ajax' => false,
			'protection' => false,
			'auth' => true,
			'use_record' => true,
			'getfields' => 'getFields',
			'control' => 'editRecord',
		);
		$module->interfaces['deleteRecord'] = array(
			'title' => 'Удалить '.mb_strtolower($module->title_to, 'utf-8'),
			'structure_sid' => false,
			'fields' => false,
			'system' => true,
			'ajax' => false,
			'protection' => false,
			'auth' => true,
			'use_record' => true,
			'getfields' => 'getFields',
			'control' => 'deleteRecord',
		);
		$module->interfaces['moveUp'] = array(
			'title' => 'Переместить выше по дереву '.mb_strtolower($module->title_to, 'utf-8'),
			'structure_sid' => false,
			'fields' => false,
			'system' => true,
			'ajax' => false,
			'protection' => false,
			'auth' => 'admin',
			'use_record' => true,
			'getfields' => 'getFields',
			'control' => 'moveUp',
		);
		$module->interfaces['moveDown'] = array(
			'title' => 'Переместить ниже по дереву '.mb_strtolower($module->title_to, 'utf-8'),
			'structure_sid' => false,
			'fields' => false,
			'system' => true,
			'ajax' => false,
			'protection' => false,
			'auth' => 'admin',
			'use_record' => true,
			'getfields' => 'getFields',
			'control' => 'moveDown',
		);
		$module->interfaces['moveTo'] = array(
			'title' => 'Переместить по дереву '.mb_strtolower($module->title_to, 'utf-8'),
			'structure_sid' => false,
			'fields' => false,
			'system' => true,
			'ajax' => false,
			'protection' => false,
			'auth' => 'admin',
			'use_record' => true,
			'getfields' => 'getFields',
			'control' => 'moveTo',
		);
		$module->interfaces['toggleRecord'] = array(
			'title' => 'Показать/скрыть '.mb_strtolower($module->title_to, 'utf-8'),
			'structure_sid' => false,
			'fields' => false,
			'system' => true,
			'ajax' => false,
			'protection' => false,
			'auth' => 'admin',
			'use_record' => true,
			'getfields' => 'getFields',
			'control' => 'toggleRecord',
		);
		
		return $module->interfaces;
	}

	//Получить содержимое интерфейса
	public function prepareInterface($prepare, $params, $public = false){
		
		//Если контроллер существует
		if( IsSet( $this->interfaces[$prepare] ) ){

			$structure_sid=$this->interfaces[$prepare]['structure_sid'];
			if( !$structure_sid )$structure_sid = model::$ask->structure_sid;
				
			//Используем значения записи
			if($this->interfaces[$prepare]['use_record']){
				if( IsSet( $params['record'] ) )
					$record = $params['record'];
				else
					$record=$this->getRecordById($structure_sid,$params['id']);
			}
			
			//Адрес отправки интерфейса
			if( $record )
				$url = $record['url'].'.'.$prepare.'.html';
			else
				$url = $this->info['url'].'.'.$prepare.'.html';

			$access = true;
			//Требуется авторизация
			if( !user::is_authorized() && $this->interfaces[$prepare]['auth'] )$access = false;

			//Требуется авторизация админ, которого нет
			if( ($this->interfaces[$prepare]['auth']==='admin') and ( !user::is_admin() ) )$access = false;
			//Не автор при редактировании записи
			if( !user::is_admin() and !user::is_moder() and ($record['author'] != user::$info['id']) and $this->interfaces[$prepare]['use_record'] and ($this->info['sid']!='users') )$access = false;
			if( !user::is_admin() and !user::is_moder() and ($record['id'] != user::$info['id']) and $this->interfaces[$prepare]['use_record'] and ($this->info['sid']=='users') )$access = false;
			
			if( !$access )
				log::stop('401 Unauthorized','Нет доступа к интерфейсу '.$prepare, $this->interfaces);

			$fields = interfaces::getFields($record, $prepare, $public);

			//Готово
			$result=array(
				'sid'=>$prepare,									//Идентификатор интерфейса
				'interface'=>$prepare,								//Идентификатор интерфейса
				'url'=>$url,
				'title'=>$this->interfaces[$prepare]['title'],		//Название интерфейса
				'comment'=>@$this->interfaces[$prepare]['comment'],	//Название интерфейса
				'fields'=>$fields,									//Поля и значения
				'auth'=>$this->interfaces[$prepare]['auth'],
				'action'=>(IsSet($this->interfaces[$prepare]['action'])?$this->interfaces[$prepare]['action']:'/'.$this->info['sid'].'.'.$prepare).'.html',
				'ajax'=>$this->interfaces[$prepare]['ajax'],
				'protection'=>$this->interfaces[$prepare]['protection'],
			);
			$result = array_merge($this->interfaces[$prepare], $result);

			//Captcha
			if ($this->interfaces[$prepare]['protection'] == 'captcha') {
				include_once(model::$config['path']['core'] . '/../libs/captcha.php');

				//Готовим Captcha
				$captcha = new captcha(model::$config);
				list($image, $code) = $captcha->generate();

				//Запоминаем код
				$_SESSION['form_captcha_code'] = $code;

				//Чиатем исходник файла
				$path = tempnam(model::$config['path']['tmp'], 'FOO');
				imagepng($image, $path);

				//Записываем
				if( IsSet(model::$config['settings']['no_data_url']) ){
					$filename = model::$config['settings']['no_data_url'].'/captcha_'.date("YmdHis").rand(0,1000).'.png';
					imagepng($image, model::$config['path']['www'].$filename);
					$result['captcha'] = $filename;
				}else
					$result['captcha'] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
			}
			
		}

		return $result;
	}
	
	//Запустить обработчик интерфейса
	public function controlInterface($interface, $params, $public = false){
		$structure_sid=$this->interfaces[$interface]['structure_sid'];
		if( !$structure_sid )$structure_sid = model::$ask->structure_sid;
		
		// Старый интерфейс login теперь не обрабатываем модулем
		if( $interface === 'login' ){
			if( substr_count($_SERVER['HTTP_REFERER'], 'users.') )
				$this->answerInterface('login',array('result'=>'redirect', 'url'=>'/', 'close'=>true));
			else
				$this->answerInterface('login',array('result'=>'redirect', 'url'=>$_SERVER['HTTP_REFERER'], 'close'=>true));
			exit();
		}
		
		//Captcha checkout
		if( $this->interfaces[ $interface ]['protection'] == 'captcha' )
			if( @$_SESSION['form_captcha_code'] != $params['captcha'] )
				log::stop('401 Unauthorized', 'Captcha не введена, либо введена неверно');
		
		//Фильтрация переданных значений - убираем лишние
		$fields = interfaces::getFields($params, $interface, $public);
		foreach($params as $var=>$val)
			if( !IsSet( $fields[$var] ) )
				if( $var != 'interface' )
					UnSet( $params[ $var ] );

		$function_name = $this->interfaces[$interface]['control'];
		
		if( is_callable(array($this, $function_name)) )
			$this->answerInterface( $interface, $this->$function_name( $params, $structure_sid ) );
		else
			log::stop('500 Internal Server Error', 'Не могу запустить контроллер "'.$function_name.'" интерфейса "'.$interface.'".');
	}
	
	//Собираем поля для интерфейса
	public function getFields($record = false, $interface = false, $public = false){
		$structure_sid=$this->interfaces[$interface]['structure_sid'];
		if( !$structure_sid )$structure_sid = model::$ask->structure_sid;
		
		//Если поля необходимо получать дополнительно - обращаемся к соответствующей функции
		if( IsSet($this->interfaces[$interface]['getfields']) ){
			$name=$this->interfaces[$interface]['getfields'];
			if( $name == 'getFields' ){
				foreach($this->structure[ $structure_sid ]['fields'] as $field_sid=>$field)
					if( ( !$public or user::is_admin() ) or ( $public && $field['public'] ) )
						$fields[$field_sid] = $field;

				// В интерфейсе добавления не должно быть ID
				if( $interface == 'addRecord' )
					if( IsSet( $fields['id'] ) )
						UnSet( $fields['id'] );
						
			}else{
//				$this->interfaces[$interface]['fields']=$fields;
				$fields=$this->$name($interface);
			}
		// Поля заданы в интерфейсе
		}elseif( IsSet( $this->interfaces[$interface]['fields'] ) ){
			$fields = $this->interfaces[$interface]['fields'];
		}
		
		// Дополняем поля
		if( is_array( $fields ) )
			foreach($fields as $sid=>$field){
				if( !IsSet( $field['sid'] ) )
					$field['sid'] = $sid;
				//Поле из струкуруы модуля
				if(IsSet($this->structure[$structure_sid]['fields'][$sid])){
					$fields[$sid]=array_merge($this->structure[$structure_sid]['fields'][$sid],$field);
				//Поле по требованию интерфейса
				}else{
					$fields[$sid]=$field;
				}
			}

		//Раскрываем поля
		if( $fields )
		foreach( $fields as $i=>$field ){
			if( !IsSet($field['type']))
				$field['type'] = 'text';
			
			if( IsSet( model::$types[ $field['type'] ] ) ){
				if( !IsSet($field['value']) )
					if( IsSet($record[ $field['sid'] ]) )
						$field['value'] = $record[ $field['sid'] ];
				if( !IsSet($field['value']) )
					$field['value'] = model::$types[ $field['type'] ]->getDefaultValue($field);
				if( !is_array( $field['value'] ) )
					$field['value'] = model::$types[ $field['type'] ]->getAdmValueExplode( $field['value'], $field, $record);

				$field['template_file'] = model::$types[ $field['type'] ]->template_file;
				
				//Пользовательский шаблон для поля
				if( IsSet( model::$types[ $field['type'] ]->template_userfile ) )
					$field['template_userfile'] = model::$types[ $field['type'] ]->template_userfile;
					
				$fields[ $field['sid'] ] = $field;
			}
		}
		
		return $fields;
	}
	
	//Ответ на запрос интерфейса
	public function answerInterface($interface,$result){

		if( model::$ask->controller == 'admin' )
			header('Location: /admin.'.model::$ask->module.'.html');
	
		//Ajax внутренним скриптом
		elseif( $this->interfaces[$interface]['ajax'] === 'action' )
			return $result;
		
		//Ajax стандартный
		elseif($this->interfaces[$interface]['ajax'])
			print( json_encode( $result, JSON_HEX_QUOT) );

		//Не Ajax вовсе
		elseif(IsSet($result['url']))
			header('Location: '.$result['url']);
			
		else
			header('Location: '.$_SERVER['HTTP_REFERER']);

		exit();
	}

	//Добавление записи в структуру модуля
	public function addRecord($values, $structure_sid = 'rec'){
		
	pr_r( $values );
		
		model::check_demo();

	/*
		1. Если есть ID - проверяем наличие записи
		2. Иначе создаём ID
		3. Вносим изменения через editRecord
	*/
	
		// Замена записи, REPLACE
		if( IsSet( $values['id'] ) ){

			/*
				Сам REPLACE использовать нельзя, так как 
				при выполнении REPLACE только с ID мы обнуляем другие поля
				а позже при внесении изменений может возникнуть ситуация
				что кол-во полей структуры не совпадают с полями в базе,
				тогда данные этой записи будут безвозвратно потеряны.
				
				Такая ситуация может возникнуть, когда разработчик создал поле в структуре,
				но не отразил это в базе, а unit-тесты оказались выключены.
			*/
		
			// Ищем запись
			$rec = model::execSql('select `id` from `'.$this->getCurrentTable( $structure_sid ).'` where `id`='.intval( $values['id'] ).'', 'getrow');
			if( !$rec )
				model::execSql('insert into `'.$this->getCurrentTable( $structure_sid ).'` set `id`='.intval( $values['id'] ).', `author`='.intval( user::$info['id'] ).', `date_added`=NOW()', 'insert');
			
		// Создание новой записи
		}else{
		
			// Новый ID
			$values['id'] = $this->genNextId( $structure_sid );
			
			// Древовидные структуры
			if( $this->structure[ $structure_sid ]['type'] == 'tree' ){
				
				//Родитель
				$parent = model::execSql('select `id`, `url` from `'.$this->getCurrentTable( $structure_sid ).'` where `'.model::$types['tree']->link_field.'`="'.mysql_real_escape_string( $values['dep_path_parent'] ).'" and '.model::pointDomain().'', 'getrow');
				
				// Если не установлен обработчик Nested Sets - подгружаем его
				if( !IsSet( $this->structure[ $structure_sid ]['db_manager'] ) ){
					require_once(model::$config['path']['core'].'/classes/nestedsets.php');
					$this->structure[ $structure_sid ]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
				}
			
				// Будем добавлять запись только с ID
				$what = array(
					'id' => '`id`='.intval( $values['id'] ),
					'author' => '`author`='.intval( user::$info['id'] ),
					'date_added' => '`date_added`=NOW()',
				);
				
				// Добавляем
				if( IsSet( $parent['id'] ) )
					$res=$this->structure[ $structure_sid ]['db_manager']->addChild($parent['id'], $what);
					
			// Не древовидные структуры
			}else
				model::execSql('insert into `'.$this->getCurrentTable( $structure_sid ).'` set `id`='.intval( $values['id'] ).', `author`='.intval( user::$info['id'] ).', `date_added`=NOW()', 'insert');
		}
		
		// Заполняем пустые поля значениями по умолчанию
		foreach( $this->structure[ $structure_sid ]['fields'] as $feild_sid=>$field)
			if( !IsSet( $values[ $feild_sid ] ) )
				if( !in_array($feild_sid, array('author', 'date_added')) ){
					
					if( is_object( model::$types[ $field['type'] ] ) )
						$values[ $feild_sid ] = model::$types[ $field['type'] ]->getDefaultValue( $field );
					else
						log::stop(
							'500 Internal Server Error', 
							'Запрашиваемый тип данных "'.$field['type'].'" не существует в структуре "'.$structure_sid.'" модуля "'.$this->info['sid'].'".', 
							$this->structure[ $structure_sid ]['fields']
						);
				}
		
		// Готово
		return $this->editRecord($values, $structure_sid);
	}

	//Добавление записи в структуру модуля
	public function editRecord($values, $structure_sid = 'rec', $conditions=false){
	
		model::check_demo();

		$what=array();
		UnSet($values['url']);

		//Старые данные, до обновления
		$data_before=$this->getRecordById( $structure_sid, $values['id'] );

		//Корректиуем SID
		$values = model::$types['sid']->implodeValue('sid', $values, false, $this->structure[$structure_sid]['fields']['sid'], $this->info['sid'], $structure_sid);
		$what['sid']='`sid`="'.mysql_real_escape_string( $values['sid'] ).'"';

		//Обновляем дату добавления и дату последней модификации
		$what['date_modify']='`date_modify`=NOW()';

		//Не стоит скрывать главную страницу =)
		if( in_array($values['sid'], array('index', 'start') ) ){
			$values['shw'] = 1;
			UnSet($values['dep_path_parent']);
		}

		$k=0;
		
		//Обработка присланных значений
		$fields=$this->structure[$structure_sid]['fields'];
		foreach( $fields as $field_sid => $field )
			if( $field_sid != 'edit_history' )
			if( IsSet( model::$types[ $field['type'] ] ) )
			if( !IsSet( $what[ $field_sid ] ) and IsSet( $values[ $field_sid ] ) ){
				
				//Значение
				$values = model::$types[ $field['type'] ]->implodeValue($field_sid, $values, $data_before, $field, $this->info['sid'], $structure_sid);
				
				//Запоминаем
				if( IsSet( $values[ $field_sid ] ) )
					$what[ $field_sid ]='`'.$field_sid.'`="' . mysql_real_escape_string( $values[ $field_sid ] ) . '"';
			}
		
		//Зависимые структуры
		if( $this->structure[ $structure_sid ]['dep_path']['structure'] ){
			//Родитель
			$parent_field_structure = $this->structure[ $structure_sid ]['dep_path']['structure'];
			$parent_field_sid = 'dep_path_'.$parent_field_structure;
			$parent_field_type = $this->structure[ $structure_sid ]['dep_path']['link_type'];

		//Деревья
		}elseif( $this->structure[ $structure_sid ]['type'] == 'tree' ){
			//Родитель
			$parent_field_structure = $structure_sid;
			$parent_field_sid = 'dep_path_parent';
			$parent_field_type = 'tree';
			
		//Линейная структура
		}else{
			$url = $this->info['url'].'/'.$values['sid'];
			$what['url'] = '`url`="'.mysql_real_escape_string( $url ).'"';
		}
		
		//Получаем родителя
		if( IsSet( $values[ $parent_field_sid ] ) ){
			$parent=model::makeSql(
				array(
					'fields'=>array('id','url'),
					'tables'=>array( $this->getCurrentTable( $parent_field_structure ) ),
					'where'=>array(
						'and'=>array(
							'`'.model::$types[ $parent_field_type ]->link_field.'`="'.mysql_real_escape_string( $values[ $parent_field_sid ] ).'"'
						)
					)
				),
				'getrow'
			);
			
			$url = @$parent['url'].'/'.$values['sid'];
			$what['url'] = '`url`="'.mysql_real_escape_string( $url ).'"';
			$what[ $parent_field_sid ] = '`'.$parent_field_sid.'`="'.mysql_real_escape_string( $values[ $parent_field_sid ] ).'"';
		}
		
		//Условия обновления
		$where['id'] = '`id`='.intval( $values['id'] ).'';
		
		// История редактирования записи
		model::execSql('update `'.$this->getCurrentTable( $structure_sid ).'` set `edit_history`=CONCAT(`edit_history`, ",'.user::$info['id'].'") where '.$where['id'].' limit 1', 'update');
		
		//Настройки автоматом не перезаписывать
		UnSet($what['acms_settings']);
		UnSet($what['settings']);

		//Empty URL in main page, control headshot...
		if( in_array($values['sid'], array('index', 'start') ) ){
			$what['url'] = '`url`=""';
			$url = '';
		}

		//Вносим изменения
		model::makeSql(
			array(
				'fields'=>$what,
				'tables'=>array( $this->getCurrentTable( $structure_sid ) ),
				'where'=>array(
					'and' => $where
				)
			),
			'update'
		);
		
		//Обновляем элемент дерева вместе с переносом
		if( ($parent_field_type == 'tree') and (@$values['dep_path_parent'] != @$data_before['dep_path_parent']) and ($values['sid'] != 'index') ){

			//Если не установлен обработчик таблицы
			if( !IsSet( $this->structure[ $structure_sid ]['db_manager'] ) ){
				require_once(model::$config['path']['core'].'/classes/nestedsets.php');
				$this->structure[ $structure_sid ]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}
			//Исключаем ошибку при доюавлении, когда родитель не найден
			if( IsSet( $parent['id'] ) ){
				//Обновление индексов дерева
				$this->structure[ $structure_sid ]['db_manager']->moveChild($parent['id'], $values['id']);
			}
		}
		$data_after=$this->getRecordById( $structure_sid, $values['id'] );

		//Обновляем поддерево
		interfaces::updateChildren($structure_sid,$data_before,$data_after,$url);

		//Сохраняем дополнительные настройки записи
		if( model::$config['settings']['dock_interfaces_to_records'] )
			interfaces::saveRecordSettings($structure_sid, $values);

	/*
		НЕ ОТКРЫВАТЬ ДО 2.15
	
		// Готово
		return $data_after;
	*/
		//Приписываем окончание для результирующего URL`а
		if( $values['sid'] == 'start' )
			$url = '';
		elseif( strlen($url) )
			$url .= '.html';
		else
			$url = '';

		//Возвращаем URL, на который будет переброшен пользователь
		return array(
			'action' => 'redirect',
			'url' => $url.'.html',
		);

	}

	//Удаление записи
	public function deleteRecord($record, $structure_sid = 'rec', $conditions){
		
		model::check_demo();

		//Удаляем поддерево
		if($this->structure[$structure_sid]['type']=='tree'){

			//Если раздел не пуст - запрещаем удаление
			if($record['left_key']+1!=$record['right_key']){
				print('Удаление раздела не возможно, сначала удалите все подразделы.');
				exit();
			}

			//Если не установлен обработчик таблицы
			if(!IsSet($this->structure[$structure_sid]['db_manager'])){
				require_once(model::$config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}

			//Удаляем
			$this->structure[$structure_sid]['db_manager']->delete($record['id']);

		//Удаляем простую запись
		}else{
		
			//Выставляем POS у линейных структур
			$res=model::makeSql(
				array(
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`="'.mysql_real_escape_string($record['id']).'"'))
				),
				'delete'
			);
		}
		
		return array(
			'action' => 'redirect',
			'url' => $this->info['url'].'.html',
		);
	}
	
	//Сохранение дополнительных настроек записи
	public function saveRecordSettings($values, $structure_sid = 'rec'){
		$settings = array(
			'interfaces_int' => (array)$values['interfaces_int'],
			'interfaces_ext' => (array)$values['interfaces_ext'],
			'components_int' => (array)$values['components_int'],
			'components_ext' => (array)$values['components_ext'],
		);
		
		//Вносим изменения
		model::execSql('update `'.$this->getCurrentTable( $structure_sid ).'` set `acms_settings`="'.mysql_real_escape_string( serialize($settings) ).'" where `id`="'.mysql_real_escape_string( $values['id'] ).'" limit 1','update');
	}

	//Переместить на одну позицию выше
	public function moveUp($record, $structure_sid = 'rec',$conditions){

		model::check_demo();

		//Дерево - переносим структуры
		if($this->structure[$structure_sid]['type']=='tree'){

			//Выбираем вторую запись, с которой будем меняться местами
			$other=model::makeSql(
				array(
					'fields'=>array('id'),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array(
						'`right_key`<"'.mysql_real_escape_string($record['left_key']).'"',
						'`tree_level`="'.$record['tree_level'].'"',
					)),
					'order'=>'order by `left_key` desc'
				),
				'getrow'
			);

			//Если не установлен обработчик таблицы
			if(!IsSet($this->structure[$structure_sid]['db_manager'])){
				require_once(model::$config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}

			//Меняем местами
			if(is_array($other)){
				//Условие для обновления деревьев
				$conditions=array('and'=>array( model::pointDomain() ));

				//Вносим изменения
				$res=$this->structure[$structure_sid]['db_manager']->move($other['id'],$record['id']);
			}

		//Линейные записи - просто обмениваемся POS`ами
		}else{
			$field_sid=false;
			if($this->structure[$structure_sid]['dep_path']['structure'])$field_sid='dep_path_'.$this->structure[$structure_sid]['dep_path']['structure'];

			//Условия выборки, учитываем родителя если указан
			$where=array();
			$where['and'][]='`pos`<'.$record['pos'].'';
			if($field_sid)$where['and'][]='`'.$field_sid.'`="'.mysql_real_escape_string($record[$field_sid]).'"';

			//Выбираем вторую запись, с которой будем меняться местами
			$other=model::makeSql(
				array(
					'fields'=>array('id','pos'),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'order'=>'order by `pos` desc'
				),
				'getrow'
			);

			//Обновляем первую запись
			model::makeSql(
				array(
					'fields'=>array('`pos`='.$other['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$record['id'].''))
				),
				'update'
			);

			//Обновляем вторую запись
			model::makeSql(
				array(
					'fields'=>array('`pos`='.$record['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$other['id'].''))
				),
				'update'
			);
		}
	}

	//Переместить на одну позицию ниже
	public function moveDown($record, $structure_sid = 'rec',$conditions){

		model::check_demo();

		//Условие для обновления деревьев
		$conditions=array('and'=>array( model::pointDomain() ));

		//Переместить на одну позицию ниже
		if($this->structure[$structure_sid]['type']=='tree'){

			//Выбираем вторую запись, с которой будем меняться местами
			$other=model::makeSql(
				array(
					'fields'=>array('id'),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array(
						'`left_key`>"'.mysql_real_escape_string($record['right_key']).'"',
						'`tree_level`="'.$record['tree_level'].'"',
					)),
					'order'=>'order by `left_key`',
				),
				'getrow'
			);

			//Если не установлен обработчик таблицы
			if(!IsSet($this->structure[$structure_sid]['db_manager'])){
				require_once(model::$config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}

			//Меняем местами
			if(is_array($other)){
				//Условие для обновления деревьев
				$conditions=array('and'=>array('domain'=> model::pointDomain() ));

				//Вносим изменения
				$res=$this->structure[$structure_sid]['db_manager']->move($record['id'],$other['id']);
			}
		}else{
			$field_sid=false;
			if($this->structure[$structure_sid]['dep_path']['structure'])$field_sid='dep_path_'.$this->structure[$structure_sid]['dep_path']['structure'];

			//Условия выборки, учитываем родителя если указан
			$where=array();
			$where['and'][]='`pos`>'.$record['pos'].'';
			if($field_sid)$where['and'][]='`'.$field_sid.'`="'.mysql_real_escape_string($record[$field_sid]).'"';

			//Выбираем вторую запись, с которой будем меняться местами
			$other=model::makeSql(
				array(
					'fields'=>array('id','pos'),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'order'=>'order by `pos`'
				),
				'getrow'
			);

			//Обновляем первую запись
			model::makeSql(
				array(
					'fields'=>array('`pos`='.$other['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$record['id'].''))
				),
				'update'
			);

			//Обновляем вторую запись
			model::makeSql(
				array(
					'fields'=>array('`pos`='.$record['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$other['id'].''))
				),
				'update'
			);
		}
	}

	//Переместить на одну позицию ниже
	public function moveTo($params, $structure_sid = 'rec',$conditions){

		$record_id = $params['record'];
		$after_id = $params['target'];
	
		model::check_demo();

		//Условие для обновления деревьев
		$conditions=array('and'=>array( model::pointDomain() ));

		//Переместить на одну позицию ниже
		if($this->structure[$structure_sid]['type']=='tree'){

			//Если не установлен обработчик таблицы
			if(!IsSet($this->structure[$structure_sid]['db_manager'])){
				require_once(model::$config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}
			
			//Условие для обновления деревьев
			$conditions = array( 'and' => array( 'domain' => model::pointDomain() ) );

			//Вносим изменения
			$res=$this->structure[$structure_sid]['db_manager']->moveTo($record_id,$after_id);

		}else{

			// Выравниваем порядковые номера в структуре
			$recs = model::execSql('select `id`, `pos` from `'.$this->getCurrentTable($structure_sid).'` order by `pos`, `date_public`', 'getall');
			foreach( $recs as $i => $rec ){
				model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `pos`='.intval($i+1).' where `id`='.$rec['id'].' limit 1', 'update');
			}
		
			// Переносимая запись, которую тащили мышкой
			$first = model::execSql('select `id`, `pos`, `title` from `'.$this->getCurrentTable($structure_sid).'` where `id`='.intval($record_id).' limit 1', 'getrow');

			// Вторая запись, за которой ставим первую
			$second = model::execSql('select `id`, `pos`, `title` from `'.$this->getCurrentTable($structure_sid).'` where `id`='.intval($after_id).' limit 1', 'getrow');

			$pos_min = intval( min( $first['pos'], $second['pos'] ) );
			$pos_max = intval( max( $first['pos'], $second['pos'] ) );
			$up = ($pos_max == $first['pos']);
			
			// Записи, которые смещаются между первой и второй записью
			model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `pos`=(`pos`' . ($up?'+1':'-1') . ') where `pos`'.($up?'>':'>=').'>'.$pos_min.' and `pos`<='.$pos_max.' and `id`!='.$record_id.' order by `pos`, `date_public`', 'update');
			
			// Обновляем переносимую запись
			model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `pos`='.$second['pos'].' where `id`='.$first['id'].' limit 1', 'update');
			
		}
	}
	
	//Переместить на одну позицию ниже
	public function toggleRecord($params, $structure_sid = 'rec',$conditions){
		if( $this->structure[ $structure_sid ]['fields'][ $params['field'] ]['type'] == 'check')
			model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `'.mysql_real_escape_string($params['field']).'` = NOT(`'.mysql_real_escape_string($params['field']).'`) where `id`='.intval($params['record_id']).' limit 1','update');
	}

	//Обновляем поддерево зависимых записей
	public function updateChildren($structure_sid,$old_data,$new_data,$new_url,$condition = false, $domain = false){

		model::check_demo();

		//Если модуль дерево - найти и обновить все записи в поддереве, поискать ссылки на другие модули
		//Если модуль сложный - найти и обновить все зависимые структуры
		//Если модуль простой - ничего не делать
	
		//Деревья
		if($this->structure[$structure_sid]['type']=='tree'){
/*			
			//Дочерние модули по ссылке в записи
			if($old_data['is_link_to_module']){
				$linked_module = $old_data['is_link_to_module'];
				$tree = model::$modules[ $linked_module ]->getLevels('rec', array());
				$root_level_structure_sid = $tree[ count($tree)-1 ];
				
				//Обновляем все корневые записи того модуля
				model::execSql('update `'.model::$modules[ $linked_module ]->getCurrentTable($root_level_structure_sid).'` set `url`=CONCAT("'.mysql_real_escape_string($new_url).'/",`sid`) where '.model::pointDomain().'','update');

				//Теперь запускаем по ним рекурсию
				$recs = model::execSql('select * from `'.model::$modules[ $linked_module ]->getCurrentTable($root_level_structure_sid).'` where '.model::pointDomain().'','getall');
				foreach($recs as $rec)
					
					//Рекурсия - спуск
					model::$modules[ $linked_module ]->updateChildren(
						$root_level_structure_sid,
						$rec,
						$rec,
						$new_url.'/'.$rec['sid'],
						$condition, 
						$domain
					);
			}
*/			
			//Обновляем все дочерние записи этого модуля
			model::execSql('update `'.$this->getCurrentTable($structure_sid).'` set `url`=CONCAT("'.mysql_real_escape_string($new_url).'/", `sid`), `dep_path_parent`="'.mysql_real_escape_string($new_data['sid']).'" where `left_key`>'.intval($new_data['left_key']).' and `right_key`<'.intval($new_data['right_key']).' and `tree_level`='.intval($new_data['tree_level']+1).' and '.model::pointDomain().'','update');
			
			//Теперь запускаем по ним рекурсию
			$recs = model::execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `left_key`>'.intval($new_data['left_key']).' and `right_key`<'.intval($new_data['right_key']).' and `tree_level`='.intval($new_data['tree_level']+1).' and '.model::pointDomain().'','getall');
			foreach($recs as $rec){
				
				//Рекурсия - спуск
				interfaces::updateChildren(
					$structure_sid,
					$rec,
					$rec,
					$new_url.'/'.$rec['sid'],
					$condition, 
					$domain
				);
			}
					
		}
		
		//Сложные модули
		if( count($this->structure)>1 ){
			
			//Ищем зависимые структуры
			foreach($this->structure as $dep_structure => $str)
				if($str['dep_path']['structure'] == $structure_sid){

					//Связка по разным типам полей
					$dep_path_field = model::$types[ $str['dep_path']['link_type'] ]->link_field;
				
					//Обновляем все дочерние записи этого модуля
					model::execSql('update `'.$this->getCurrentTable($dep_structure).'` set `url`=CONCAT("'.mysql_real_escape_string($new_url).'/", `sid`), `dep_path_'.$structure_sid.'`="'.mysql_real_escape_string($new_data[ $dep_path_field ]).'" where `dep_path_'.$structure_sid.'`="'.mysql_real_escape_string($old_data[ $dep_path_field ]).'" and '.model::pointDomain().'','update');
					
					//Теперь запускаем по ним рекурсию
					$recs = model::execSql('select * from `'.$this->getCurrentTable($dep_structure).'` where `dep_path_'.$structure_sid.'`="'.mysql_real_escape_string($new_data[ $dep_path_field ]).'" and '.model::pointDomain().'','getall');
					foreach($recs as $rec){
						
						//Рекурсия - спуск
						interfaces::updateChildren(
							$dep_structure,
							$rec,
							$rec,
							$new_url.'/'.$rec['sid'],
							$condition, 
							$domain
						);
					}
				}
		}
	}

	public function getAllInterfaces(){
	
		$groups = array(
			'user' => array(
				'title' => 'Пользователь',
				'sid' => 'user',
			),
			'reader' => array(
				'title' => 'Участник сети',
				'sid' => 'owner',
			),
			'owner' => array(
				'title' => 'Основатель сети',
				'sid' => 'owner',
			),
			'manager' => array(
				'title' => 'Руководитель компании',
				'sid' => 'manager',
			),
			'mayor' => array(
				'title' => 'Мэр города',
				'sid' => 'mayor',
			),
			'moder' => array(
				'title' => 'Модератор',
				'sid' => 'moder',
			),
		);
	
		foreach(model::$modules as $module_sid=>$module)
		if( $module->structure )
			$fields[] = array(
				'title' => $module->info['title'],
				'module_sid' => $module_sid,
				'structure_sid' => $interface['structure_sid'],
				'group' => 'main',
				'groups' => $groups,
			);
		
		return $fields;
	}

}

?>