<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Прототип модуля										*/
/*															*/
/*	Версия ядра 2.1											*/
/*	Версия скрипта 1.1										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 04 Февраля 2010 года						*/
/*															*/
/************************************************************/

//Модуль по умолчанию
class default_module{

	//Приставка перед таблицей в дазе данных - пока не используется
	public $database_table_preface=false;

	// Идентификатор базы данных, у основного модуля всегда system,
	// он же используется в конфигурации при выставлении параметров доступа
	public $db_sid='system';

	//Системные поля, необходимые каждому модулю
	public $system_fields=array(
		'id'=>					array('sid'=>'id',				'group'=>'system',		'type'=>'id', 			'title'=>'ID', 									'file'=>'table_id.php'),
		'sid'=>					array('sid'=>'sid',				'group'=>'system',		'type'=>'sid', 			'title'=>'Системное имя', 						'file'=>'table_sid.php'),
		'date_public'=>			array('sid'=>'date_public',		'group'=>'main',			'type'=>'datetime', 	'title'=>'Публичная дата', 						'file'=>'table_datetime.php'),
		'date_added'=>			array('sid'=>'date_added',		'group'=>'system',		'type'=>'datetime', 	'title'=>'Дата создания записи', 				'file'=>'table_datetime.php'),
		'date_modify'=>			array('sid'=>'date_modify',		'group'=>'system',		'type'=>'datetime', 	'title'=>'Дата последнего изменения записи', 	'file'=>'table_datetime.php'),
		'title'=>				array('sid'=>'title',			'group'=>'main',			'type'=>'text', 		'title'=>'Заголовок', 							'file'=>'table_text.php'),
		'shw'=>					array('sid'=>'shw',				'group'=>'show',			'type'=>'check', 		'title'=>'Показывать на сайте', 				'file'=>'table_check.php', 'default'=>true),
		'access'=>				array('sid'=>'access',			'group'=>'system',		'type'=>'hidden', 		'title'=>'Доступ к записи', 					'default' => '|admin=rwd|moder=rw-|all=r--|'),
	);

	//Шаблоны в модуле по умолчанию
	public $templates=array(
		'index'=>array('sid'=>'index','title'=>'Главная страница модуля'),
		'content'=>array('sid'=>'content','title'=>'Страница модуля одной записи'),
	);

	//Шаблоны в модуле по умолчанию
	public $prepares=array();

////////////////////////////////////
/// ИНИЦИАЛИЗАЦИЯ МОДУЛЯ ///
////////////////////////////////////

	//Настройка модуля, по идее должна заменяться такой же настройко в самом модуле, а не в прототипе
	public function setStructure(){
		$this->structure=array(
			'rec'=>array(
				'title'=>'Раздел',
				'fields'=>false,
				'type'=>'tree',
				'dep_path'=>false,
				'dep_param'=>false,
			),
		);
	}

	//Инициализация интерфейсов
	public function setInterfaces(){
		$this->interfaces=array();
	}

	//Инициализация системных интерфейсов
	public function setSystemInterfaces(){
	}

	//Инициализация модуля
	public function __construct($model,$settings){
		//Запоминаем данные для работы
		$this->model=$model;	//модель данных
		$this->info=$settings;	//параметры, с которыми инициализируется модуль

		//Устанавливаем внутреннюю структуру модуля
		$this->initStructure();

		//Устанавливаем внутренние интерфейсы
		$this->initInterfaces();

		//Устанавливаем выводы
		$this->initPrepares();

		//Проверка корректности деревьев
		if($this->model->config['settings']['dbtree_check'])
			$this->checkTree();
	}

	//Достройка структуры, связей, параметров, типов данных
	public function initStructure(){
		//Подгружаем заданную структуру модуля
		$this->setStructure();

		//Если в конфиге разрешена привязка интерфейсов к записям - нужно такое поле
		if( $this->model->config['settings']['dock_interfaces_to_records'] )
			$thid->system_fields['acms_settings'] = array('sid'=>'acms_settings',	'group'=>'system',		'type'=>'text', 		'title'=>'Настройки отображения записи');
		
		//Дозаносим системные поля
		if($this->structure)
		foreach($this->structure as $structure_sid=>$part){
			//Заносим системные поля
			$this->structure[$structure_sid]['fields']=$this->system_fields;
			//Заносим пользовательские поля
			if($part['fields']){
				foreach($part['fields'] as $field_sid=>$field){
					$this->structure[$structure_sid]['fields'][$field_sid]=array_merge(
						array('sid'=>$field_sid),
						$field
					);
				}
			}
		}

		//Если модуль Start - будем клеить поле-ссылку
		if(!$this->info['sid'])
			$this->structure['rec']['fields']['is_link_to_module']=array('sid'=>'is_link_to_module','type'=>'module','group'=>'system','title'=>'Раздел является ссылкой на модуль','variants'=>$all_modules);
	}


	//Выводы
	public function initPrepares(){

		//Установка компонентов по новой схеме
		if( is_callable( array($this, 'setComponents') ) )
			call_user_func( array($this, 'setComponents') );

		//Стандартные
		$system_prepares=array(
			'recs'=>array('function'=>'prepareRecs','title'=>'Список всех записей', 'hidden'=>true),
			'parent'=>array('function'=>'prepareParent','title'=>'Родительская запись', 'hidden'=>true),
			'tags'=>array('function'=>'prepareTags','title'=>'Облако тегов раздела'),
			'map'=>array('function'=>'prepareMap','title'=>'Дерево раздела'),

		//Устаревщие
			'anons'=>array('function'=>'prepareAnons','title'=>'Анонс одной записи', 'hidden'=>true),
			'anonslist'=>array('function'=>'prepareAnonsList','title'=>'Анонс нескольких записей', 'hidden'=>true),
			'random'=>array('function'=>'prepareRandom','title'=>'Меню случайной записи', 'hidden'=>true),
			'randomlist'=>array('function'=>'prepareRandomList','title'=>'Меню списка случайных записей', 'hidden'=>true),
			'pages'=>array('function'=>'preparePages','title'=>'Страницы записей', 'hidden'=>true),
		);

		//Предобъявленные в модуле
		foreach($system_prepares as $sid=>$prepare)
			if(!IsSet($this->prepares[$sid])){
				$this->prepares[$sid]=$prepare;
			}
	}

	//Интерфейсы
	public function initInterfaces(){
		$this->setInterfaces();
		$this->setSystemInterfaces();
	}


//////////////////////////////////////
/// ЗАПУСКИ ВЫВОДОВ И КОНТРОЛЛЕРОВ ///
//////////////////////////////////////

	//Поготовки данных
	public function prepareData($prepare,$params){
		//Если контроллер существует
		if(IsSet($this->prepares[$prepare])){
			$function_name=$this->prepares[$prepare]['function'];
			//Запускаем
			if( method_exists($this, $function_name) )
				return $this->$function_name($params);
		}
		return false;
	}

	//Поготовки интерфейсов
	public function prepareInterface($prepare,$params){

		//Если контроллер существует
		if(IsSet($this->interfaces[$prepare])){

			//Требуется авторизация
			if($this->interfaces[$prepare]['auth'] and (!$this->model->user->info['id']) ){
				return false;
			}

			//Структура
			$structure_sid=$this->interfaces[$prepare]['structure_sid'];
			//Поля из структуры, которые нужны для интерфейса
			$fields=array();

			//Перебираем поля
			if( is_array($this->interfaces[$prepare]['fields']) )
			foreach($this->interfaces[$prepare]['fields'] as $sid=>$field){
				if(!IsSet($field['sid']))
					$field['sid'] = $sid;
				//Поле из струкуруы модуля
				if(IsSet($this->structure[$structure_sid]['fields'][$sid])){
					$fields[$sid]=array_merge($this->structure[$structure_sid]['fields'][$sid],$field);
				//Поле по требованию интерфейса
				}else{
					$fields[$sid]=$field;
				}
			}

/*
			foreach($this->structure[$structure_sid]['fields'] as $sid=>$field)
				if(IsSet($this->interfaces[$prepare]['fields'][$sid]))
					$fields[$sid]=array_merge($field,$this->interfaces[$prepare]['fields'][$sid]);
*/
			//Родитель
			if($this->structure[$structure_sid]['dep_path'])
				if(IsSet($this->interfaces[$prepare]['fields']['dep_path_'.$this->structure[$structure_sid]['dep_path']['structure']])){
					$name='dep_path_'.$this->structure[$structure_sid]['dep_path']['structure'];
					$type=$this->structure[$structure_sid]['dep_path']['link_type'];
					$title=$this->structure[$this->structure[$structure_sid]['dep_path']['structure']]['title'];
					$where=@$this->interfaces[$prepare]['fields']['dep_path_'.$this->structure[$structure_sid]['dep_path']['structure']]['where'];
					$fields[$name]=array('sid'=>$name,'type'=>$type,'group'=>'main','title'=>$title,'module'=>$this->info['sid'],'structure_sid'=>$this->structure[$structure_sid]['dep_path']['structure'],'where'=>$where);
				}

			//Используем значения записи
			if($this->interfaces[$prepare]['use_record'])
				$record=$this->getRecordById($structure_sid,$params['id']);

			//Если поля необходимо получать дополнительно - обращаемся к соответствующей функции
			if( $this->interfaces[$prepare]['getfields'] ){
				//Название функции для вызова
				$name=$this->interfaces[$prepare]['getfields'];
				//Записываем подготовленные поля внутрь интерфейса
				$this->interfaces[$prepare]['fields']=$fields;
				//Вызов
				$fields=$this->$name($prepare);
			}

			//Теперь развернём поля для контроллера
			foreach($fields as $sid=>$field){
				if($field['type']){
					//Значения полей, переданные через шаблон
					if(IsSet($params['set_'.$sid])){
						$fields[$sid]['value']=$this->model->types[$field['type']]->getAdmValueExplode( $params['set_'.$sid], $field );
					}elseif($this->interfaces[$prepare]['use_record']){
						$fields[$sid]['value']=$this->model->types[$field['type']]->getAdmValueExplode( ($field['value']?$field['value']:$record[$sid]) , $field );
					}else{
						//pr_r($field);
						$fields[$sid]['value']=$this->model->types[$field['type']]->getAdmValueExplode( false, $field );
					}
				}
			}

			//Готово
			$result=array(
				'interface'=>$prepare,								//Идентификатор интерфейса
				'title'=>$this->interfaces[$prepare]['title'],		//Название интерфейса
				'comment'=>@$this->interfaces[$prepare]['comment'],		//Название интерфейса
				'fields'=>$fields,									//Поля и значения
				'auth'=>$this->interfaces[$prepare]['auth'],
				'action'=>(IsSet($this->interfaces[$prepare]['action'])?$this->interfaces[$prepare]['action']:'/'.$this->info['sid']).'.html',
				'ajax'=>$this->interfaces[$prepare]['ajax'],
				'protection'=>$this->interfaces[$prepare]['protection'],
			);

			//Captcha
			if ($this->interfaces[$prepare]['protection'] == 'captcha') {
				include_once($this->model->config['path']['libraries'] . '/captcha.php');

				//Готовим Captcha
				$captcha = new captcha($this->model->config);
				list($image, $code) = $captcha->generate();

				//Запоминаем код
				$_SESSION['form_captcha_code'] = $code;

				//Чиатем исходник файла
				$path = tempnam($this->model->config['path']['tmp'], 'FOO');
				imagepng($image, $path);

				//Записываем
				if( IsSet($this->model->config['settings']['no_data_url']) ){
					$filename = $this->model->config['settings']['no_data_url'].'/captcha_'.date("YmdHis").rand(0,1000).'.png';
					imagepng($image, $this->model->config['path']['www'].$filename);
					$result['captcha'] = $filename;
				}else
					$result['captcha'] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
			}

			return $result;
		}else return false;
	}
	//Ответ на запрос интерфейса
	public function answerInterface($interface,$result){

		//Ajax
		if( $this->interfaces[$interface]['ajax'] === 'internal' ){
			return $result;
		
		//Ajax
		}elseif($this->interfaces[$interface]['ajax']){
			print( json_encode( $result ,JSON_HEX_QUOT) );

		//Не Ajax
		}else{
			if(IsSet($result['url']))
				header('Location: '.$result['url']);
			else
				header('Location: '.$_SERVER['HTTP_REFERER']);
			exit();
//			pr('ответ интерфейса "'.$interface.'"');
//			pr_r($result);
		}

		exit();
	}

	//Перехват управления контроллеров
	public function execController($controller,$vars){
		//Если контроллер существует
		if(IsSet($this->controls[$controller])){
			$function_name=$this->controls[$controller]['function'];
			//Запускаем
			return $this->$function_name($vars);
		}else return false;
	}

	//Перехват управления контроллера интерфейса
	public function execInterfaceController($interface,$vals){
		//Действие
		if(IsSet($vals['id']))$action='edit';else $action='add';
		//Проверяем доступность данного действия в интерфейсе
		if(!in_array($action,$this->interfaces[$interface]['actions'])){
			print('error 2102');
			return false;
		}

		//Автор
		if(IsSet($this->interfaces[$interface]['fields']['author']))
			//Неверно выбран автор
			if($vals['author']!=$this->model->user->info['id']){
				print('error 2101');
				return false;
			}

		$what=array();

		//Родитель
		$structure_sid=$this->interfaces[$interface]['structure_sid'];
		if($this->structure[$structure_sid]['dep_path'])
			if(IsSet($this->interfaces[$interface]['fields']['dep_path_'.$this->structure[$structure_sid]['dep_path']['structure']])){
				$name='dep_path_'.$this->structure[$structure_sid]['dep_path']['structure'];
				$type=$this->structure[$structure_sid]['dep_path']['link_type'];
				$title=$this->structure[$this->structure[$structure_sid]['dep_path']['structure']]['title'];
				$where=@$this->interfaces[$interface]['fields']['dep_path_'.$this->structure[$structure_sid]['dep_path']['structure']]['where'];

				$this->interfaces[$interface]['fields'][$name]=array('sid'=>$name,'type'=>$type,'group'=>'main','title'=>$title,'module'=>$this->info['sid'],'structure_sid'=>$this->structure[$structure_sid]['dep_path']['structure'],'where'=>$where);
				$this->structure[$this->interfaces[$interface]['structure_sid']]['fields'][$name]=array('sid'=>$name,'type'=>$type,'group'=>'main','title'=>$title,'module'=>$this->info['sid'],'structure_sid'=>$this->structure[$structure_sid]['dep_path']['structure'],'where'=>$where);
			}

		//Собираем данные для всех объявленных в интерфейсе полей
		foreach($this->interfaces[$interface]['fields'] as $sid=>$field){
			//Дополнием описание поля
			$field=array_merge($this->structure[$this->interfaces[$interface]['structure_sid']]['fields'][$sid],$field);

			//Если значение поля прислано - учитываем его
			if(IsSet($vals[$field['sid']])){
				$what[$sid]=$this->model->types[$field['type']]->toSql($sid,$vals,false,$field);

			//Иначе считаем предупреждение
			}else{
				$errors[]=array('sid'=>$sid,'title'=>$field['title']);
			}
		}

		//Добавление
		if($action=='add'){
			//Необходимо дополнить другие поля до добавления в базу
			$what['sid']='`sid`=`id`';

			//Другие поля
			foreach($this->structure[$this->interfaces[$interface]['structure_sid']]['fields'] as $sid=>$field)if($sid!='id')
				if(!IsSet($what[$sid])){
					//Значение по умолчанию
					$vals[$sid]=$this->model->types[$field['type']]->getDefaultValue($field);
					$what[$sid]=$this->model->types[$field['type']]->toSql($sid,$vals,false,$field);
				}

			//Вносим запись
			$this->model->makeSql(
				array(
					'tables'=>array($this->getcurrentTable($this->interfaces[$interface]['structure_sid'])),
					'fields'=>$what
				),
				'insert'
			);

			//Обновляем `sid`,`url`
			$sql='update `'.$this->getcurrentTable($this->interfaces[$interface]['structure_sid']).'` set `sid`=`id`, `url`=CONCAT("/'.$this->info['sid'].'/",`sid`) where `sid`="0"';
			$this->model->execSql($sql,'update');

		//Редактирование
		}elseif($action=='edit'){

		}

		//Результирующий URL
		$url='/'.$this->info['sid'].'.html';
		header('Location: '.$url);
		exit();

	}


///////////////////////////////////////////////////////////////
/// PREPARES //////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

	//Анонс - последние N записей
	public function prepareAnons($params){

		//Получаем условия
		$where=$this->convertParamsToWhere($params);

		//Забираем запись
		$rec=$this->model->makeSql(
			array(
				'tables'=>array($this->getCurrentTable('rec')),
				'where'=>$where,
				'order'=>'order by `date_public` desc',
			),
			'getrow'
		);

		//Раскрываем сложные поля
		$rec=$this->explodeRecord($rec,'rec');
		$rec=$this->insertRecordUrlType($rec);

		//Готово
		return $rec;
	}

	//Анонс - последние N записей
	public function prepareAnonsList($params){

		//Получаем условия
		$where=$this->convertParamsToWhere($params);

		//Условия отображения на сайте
		$where['and'][]='`shw`=1';
		$where['and'][]='`show_in_anons`=1';

		//Забираем записи
		$recs=$this->model->makeSql(
			array(
				'tables'=>array($this->getCurrentTable('rec')),
				'where'=>$where,
				'order'=>'order by `date_public` desc',
				'limit'=>(isSet($params['limit'])?'limit '.(IsSet($params['start'])?intval($params['start']).', ':'').intval($params['limit']):'')
			),
			'getall'
		);

		//Раскрываем сложные поля
		if($recs)
		foreach($recs as $i=>$rec){
			$rec=$this->explodeRecord($rec,'rec');
			$rec=$this->insertRecordUrlType($rec);
			$recs[$i]=$rec;
		}

		//Готово
		return $recs;
	}

	//Записи - полный список записей
	public function prepareRecs($params){

		//Брать параметры из GET
		if($params['params_from_get']){
			//Получаем условия
			$where=$this->convertParamsToWhere($_GET);

		}else{
			//Получаем условия
			$where=$this->convertParamsToWhere($params);

		}
		
		//Определяем структуру к которой обращается
		$structure_sid='rec';
		if(IsSet($params['structure_sid']))$structure_sid=$params['structure_sid'];

		//Условия отображения на сайте
		if(!IsSet($params['shw']))
			$where['and'][]='`shw`=1';

		//Сортировка
		if(IsSet($params['order']))
			$order=$params['order'];
		else
			$order=$this->getOrderBy($structure_sid);

		//Требуется разбивка на страницы
		if( $params['chop_to_pages'] ){

			//Текущая страница
			$current_page = $this->model->ask->rec['page'];

			//Всего записей по запросу
			$num_of_records = $this->model->execSql('select count(`id`) as `counter` from `'.$this->getCurrentTable($structure_sid).'` where '.implode(' and ', $where['and']) . ' and (' . implode(' or ', $where['or']) .')'.' and '.$this->model->extensions['domains']->getWhere().'','getrow');
			$num_of_records = $num_of_records['counter'];

			//Записей на страницу
			if(IsSet($params['items_per_page']))$items_per_page=$params['items_per_page'];
			elseif(IsSet($this->model->settings['items_per_page']))$items_per_page=$this->model->settings['items_per_page'];
			else $items_per_page=10;

			//Количество страниц
			$num_of_pages = ceil( $num_of_records / $items_per_page );

			//Забираем записи
			$recs=$this->model->makeSql(
				array(
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'order'=>$order,
					'limit'=>'limit '.($current_page*$items_per_page).', '.$items_per_page,
				),
				'getall'
			);//pr($this->model->last_sql);

			//Раскрываем сложные поля
			if($recs)
			foreach($recs as $i=>$rec){
				$rec=$this->explodeRecord($rec,$structure_sid);
				$rec=$this->insertRecordUrlType($rec, 'html', $params['insert_host']);
				$recs[$i]=$rec;
			}

			//Перелистывания страниц
			$pages=array();
			if( $num_of_pages > 1 ){

				//Учитываем GET-переменные
				$get_vars=false;
				if(IsSet($_GET))
					foreach($_GET as $var=>$val){
						if( is_array($val) ){
							foreach($val as $v)
								$get_vars[]=$var.'[]='.$v;
						}else{
							$get_vars[]=$var.'='.$val;
						}
					}

				//Учитываем другие модификаторы
				$modifiers=false;
				if( count($this->model->ask->mode)>0 ){
					$modifiers='.'.implode('.', $this->model->ask->mode);
				}

				//Зацикливаем перелистывание страниц вправо и влево.
				if($current_page>0)$prev=$current_page-1;else $prev=$num_of_pages-1;
				if($current_page<$num_of_pages-1)$next=$current_page+1;else $next=0;

				//Предыдущая страница
				$pages['prev']['url'] = $this->model->ask->rec['url'].$modifiers.'.'.$prev.'.'.$this->model->ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['prev']['num'] = $prev;

				//Следующая страница
				$pages['next']['url'] = $this->model->ask->rec['url'].$modifiers.'.'.$next.'.'.$this->model->ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['next']['num'] = $next;

				//Другие страницы
				for($i=0;$i<$num_of_pages;$i++){
					$pages['items'][$i]['url']=$this->model->ask->rec['url'].$modifiers.'.'.$i.'.'.$this->model->ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				}
			}

			//Результат
			$result=array(
				'current'	=>	$current_page,									//Номер текущей страницы
				'from'		=>	$current_page*$items_per_page,					//Номер первой записи на странице
				'till'		=>	($current_page+1)*$items_per_page,				//Номер последней записи на странице
				'limit'		=>	$items_per_page,								//Количество записей на странице
				'count'		=>	ceil($num_of_records / $items_per_page),		//Общее количество страниц
				'recs'		=>	$recs,											//Все записи на странице
				'pages'		=>	$pages,											//Страницы
			);

			//Готово
			return $result;

		//Без разбивки на страницы
		}else{

			//Забираем записи
			$recs=$this->model->makeSql(
				array(
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'limit'=>(isSet($params['limit'])?'limit '.(IsSet($params['start'])?intval($params['start']).', ':'').intval($params['limit']):''),
					'order'=>$order,
				),
				'getall'
			);//pr($this->model->last_sql);

			//Раскрываем сложные поля
			if($recs)
			foreach($recs as $i=>$rec){
				$rec=$this->explodeRecord($rec,$structure_sid);
				$rec=$this->insertRecordUrlType($rec, 'html', $params['insert_host']);
				$recs[$i]=$rec;
			}

			//Готово
			return $recs;
		}
	}

	//Комментарии к записи
	public function prepareCount($params){

		//Указание на структуру
		if( IsSet($params['structure_sid']) )
			$structure_sid = $params['structure_sid'];
		else
			$structure_sid = 'rec';

		//Получаем условия
		$where=$this->convertParamsToWhere($params);
		$where['and']['shw']='`shw`=1';

		//Получаем записи
		$res=$this->model->makeSql(
			array(
				'tables' => array($this->getCurrentTable($structure_sid)),
				'fields' => array( 'count(`id`) as `counter`' ),
				'where' => $where,
			),
			'getrow'
		);//pr($this->model->last_sql);

		//Готово
		return $res['counter'];
	}

	//Случайные записи
	public function prepareRandom($params){

		//Получаем условия
		$where=$this->convertParamsToWhere($params);

		//Забираем запись
		$rec=$this->model->makeSql(
			array(
				'tables'=>array($this->getCurrentTable('rec')),
				'where'=>$where,
				'order'=>'order by RAND()'
			),
			'getrow'
		);

		//Раскрываем сложные поля
		$rec=$this->explodeRecord($rec,'rec');
		$rec=$this->insertRecordUrlType($rec);

		//Готово
		return $rec;
	}

	//Случайные записи
	public function prepareRandomList($params){

		//Получаем условия
		$where=$this->convertParamsToWhere($params);

		//Забираем запись
		$recs=$this->model->makeSql(
			array(
				'tables'=>array($this->getCurrentTable('rec')),
				'where'=>$where,
				'order'=>'order by RAND()',
				'limit'=>(isSet($params['limit'])?'limit '.(IsSet($params['start'])?intval($params['start']).', ':'').intval($params['limit']):''),
			),
			'getall'
		);

		//Раскрываем сложные поля
		foreach($recs as $i=>$rec){
			$rec=$this->explodeRecord($rec,'rec');
			$rec=$this->insertRecordUrlType($rec);
			$recs[$i]=$rec;
		}

		//Готово
		return $recs;
	}

	//Родительский раздел текущей записи
	public function prepareParent($params){

		//Определяем структруру и SID родителя
		if($this->structure['rec']['type']=='simple'){
			$parent_structure_sid=$this->structure['rec']['dep_path']['structure'];
		}else{
			$parent_structure_sid='rec';
		}

		//Поле, при помощи которого происходит связка
		$link_field=$this->model->types[ $this->structure['rec']['dep_path']['link_type'] ]->link_field;

		//SID родителя
		$parent_sid=$params[$link_field];

		//Сортировка
		$order=$this->getOrderBy('rec');

		//Забираем запись
		$rec=$this->model->makeSql(
			array(
				'tables'=>array($this->getCurrentTable($parent_structure_sid)),
				'where'=>array('and'=>array('`'.$link_field.'`="'.mysql_real_escape_string($parent_sid).'"')),
				'order'=>$order,
			),
			'getrow'
		);//pr($this->model->last_sql);

		//Раскрываем сложные поля
		$rec=$this->explodeRecord($rec,'rec');
		$rec=$this->insertRecordUrlType($rec);

		//Готово
		return $rec;
	}

	//Родительский раздел текущей записи
	public function prepareMap($params){

		//Дерево
		$recs=$this->model->prepareShirtTree(false, 'rec', false,5,array('and'=>array('`shw`=1')));
//		pr_r($recs);

		//Раскрываем сложные поля
		foreach($recs as $i=>$rec){
			$rec=$this->explodeRecord($rec,'rec');
			$rec=$this->insertRecordUrlType($rec);
			$recs[$i]=$rec;
		}

		//Готово
		return $rec;
	}

	//Список страниц
	public function preparePages($params){

		//Достаём общее количество записей
		if(IsSet($params['count'])){
			$recs['counter']=$params['count'];
		}else{
			$recs=$this->model->execSql('select count(`id`) as `counter` from `'.$this->getCurrentTable($this->model->ask->structure_sid).'` where `shw`=1 and '.$this->model->extensions['domains']->getWhere().'','getrow');
		}


		//Настройка для разбивки записей на страницы
		if(IsSet($params['limit']))$items_per_page=$params['limit'];
		elseif(IsSet($this->model->settings['items_per_page']))$items_per_page=$this->model->settings['items_per_page'];
		else $items_per_page=10;

		//Текущая страница
		$page=intval($this->model->ask->rec['page']);//current_page;

		//Сюда будем складывать страницы
		$pages=array();

		//Если записи найдены
		if($recs['counter']){
			$pages=array(
				'current'=>$page,
				'from'=>$page*$items_per_page,
				'till'=>($page+1)*$items_per_page,
				'limit'=>$items_per_page,
				'count'=>ceil($recs['counter']/$items_per_page),
			);

			//Если страниц больше одной
			if($pages['count']>1){

				//Учитываем GET-переменные
				$get_vars=false;
				if(IsSet($_GET))
					foreach($_GET as $var=>$val)
						$get_vars[]=$var.'='.$val;

				//Учитываем другие модификаторы
				$modifiers=false;
				if( count($this->model->ask->mode)>0 ){
					$modifiers='.'.implode('.', $this->model->ask->mode);
				}

				//Зацикливаем перелистывание страниц вправо и влево.
				if($page>0)$prev=$page-1;else $prev=$pages['count']-1;
				if($page<$pages['count']-1)$next=$page+1;else $next=0;

				//Предыдущая страница
				$pages['prev']['url'] = $this->model->ask->rec['url'].$modifiers.'.'.$prev.'.'.$this->model->ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['prev']['num'] = $prev;

				//Следующая страница
				$pages['next']['url'] = $this->model->ask->rec['url'].$modifiers.'.'.$next.'.'.$this->model->ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['next']['num'] = $next;

				//Другие страницы
				for($i=0;$i<$pages['count'];$i++){
					$pages['items'][$i]['url']=$this->model->ask->rec['url'].$modifiers.'.'.$i.'.'.$this->model->ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				}
			}
		}

		//Готово
		return $pages;
	}

	//Вход на сайт
	public function prepareTags($params){
		$tags=$this->model->types['tags']->getTagsCloud();
		return $tags;
	}

/*
	//	Возвращает форму обратной связи для записи
	//	{preload data=feedback result=feedback}
	public function prepareFeedback($params){
	}
	
	//	Возвращает интерфейсы, настроенные для записи
	//	{preload data=interface result=interfaces}
	public function prepareInterface($params){
	}
	
	//	Возвращает интерфейсы, настроенные для записи
	//	{preload data=interface result=interfaces}
	public function prepareComponent($params){
	}
*/	
	
	//Переводим список переданных параметров в условия запроса
	public function convertParamsToWhere($params){

		//Текущий вывод
		$prepare=$params['data'];

		//Разрешённые параметры в каждом из выводов
		$allowed_params=array(
			'anons'=>array('nid','dir','access'),
			'anonslist'=>array('nid','dir','access','limit','start'),
			'recs'=>array('nid','dir','access','limit','start'),
			'random'=>array('nid','dir','access'),
			'randomlist'=>array('nid','dir','access','limit','start'),
			'parent'=>array(),
		);

		//Формируем условия
		$where=array();
		foreach( (array)$params as $var=>$val){

			$flag=false;

			//Если текущий параметр присутствует в выводе
			if(IsSet($allowed_params[$prepare]))
			if(in_array($var,$allowed_params[$prepare])){

				//nid - исключить запись с указанным ID из списка искомых
				if($var=='nid'){
					$flag=true;
					$where['and']['id']='(not(`id`="'.mysql_real_escape_string($val).'"))';

				//dir - ограничить записи указанным родительским разделом, чей ID указан
				}elseif($var=='dir'){
					//Простые структуры
					if($this->structure['rec']['type']=='simple')
						$field_name='dep_path_'.$this->structure['rec']['dep_path']['structure'];
					//Древовидные структуры
					else
						$field_name='dep_path_parent';

					$flag=true;

					$where['and'][$field_name]='`'.$field_name.'`="'.mysql_real_escape_string($val).'"';

				//Доступ таргетирован по группам
				}elseif($var=='access'){
					//Только публичные записи
					if($val=='public'){
						$flag=true;
						$where['and']['access']='`access` LIKE "%|all=r__|%"';
					}elseif($val=='group')
						$flag=true;
						$where['and']['access']='`access` LIKE "%|'.mysql_real_escape_string($this->model->user->info['group']).'=r__|%"';
				}
			}

			//Для других полей, объявленных в структуре
			//Flag для того, чтобы исключить повторное добавление системных полей
			if(!$flag){
				if(IsSet($this->structure['rec']['fields'][$var])){
					if( in_array( $this->structure['rec']['fields'][$var]['type'] ,array('menum','linkm') ) ){
						if(is_array($val)){
							foreach($val as $i=>$v)if(!strlen($v))UnSet($val[$i]);
							if($val[0])
								$where['and'][$var] = '( (`'.$var.'` LIKE "%|'.implode('|%") or (`'.$var.'` LIKE "%|', urldecode($val) ).'|%") )';
						}else{
							$where['and'][$var] = '`'.$var.'` LIKE "%|'.mysql_real_escape_string( urldecode($val) ).'|%"';
						}
					}elseif( $val === 'notnull' ){
						$where['and'][$var]='`'.$var.'`!="0"';
					}elseif( $val === 'notempty' ){
						$where['and'][$var]='`'.$var.'`!=""';
					}else{
						if(is_array($val)){
							foreach($val as $i=>$v)if(!strlen($v))UnSet($val[$i]);
							if($val[0])
								$where['and'][$var] = '((`'.$var.'`="'.implode('") or (`'.$var.'`="', $val).'") )';
						}else{
							$where['and'][$var] = '`'.$var.'`="'.mysql_real_escape_string($val).'"';
						}
					}
				}
			}
		}

		//Готово
		return $where;
	}

	public function getOrderBy($structure_sid){
		//Сортировка деревьев
		if($this->structure[$structure_sid]['type']=='tree')return 'order by `left_key`';
		//Сортирвка по POS
		elseif(IsSet($this->structure[$structure_sid]['fields']['pos']))return 'order by `pos`,`title`';
		//Сортировка по публичной дате
		else return 'order by `date_public` desc,`title`';
	}

////////////////////////////////////////////
/// РАБОТА С ДЕРЕВЬЯМИ МОДУЛЯ И СТРУКТУР ///
////////////////////////////////////////////

	//Показать краткое дерево модуля
	public function getModuleShirtTree(
			$root_record_id,		//id записи, с которой начинать счетать дерево
			$structure_sid,			//интересующая нас структура
			$levels_to_show,		//количество уровней, которые необходимо найти
			$conditions=array()	//уcловия выборки веток
		){

		return $this->getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
	}

	//Показать краткое дерево сруктуры
	public function getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions){
		//Некоторые структуры скрываются из деревьев
		if(!$this->structure[$structure_sid]['hide_in_tree']){
			//Древовидные структуры
			if($this->structure[$structure_sid]['type']=='tree'){
				$recs=$this->getStructureShirtTree_typeTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
			//Линейные структуры
			}else{
				$recs=$this->getStructureShirtTree_typeSimple($root_record_id,$structure_sid,$levels_to_show,false,$conditions);
			}
		}

		return $recs;
	}

	//Поиск краткого дерева в древовидной структуре
	private function getStructureShirtTree_typeTree($root_record_id,$structure_sid,$levels_to_show,$conditions){

		//Если не установлен обработчик таблицы деревьев - устанавливаем
		if(!IsSet($this->structure[$structure_sid]['db_manager'])){
			require_once($this->model->config['path']['core'].'/classes/nestedsets.php');
			$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
		}

		//Обработка расширениями - получаем в Where подстановки от расширений
		if($this->model->extensions)foreach($this->model->extensions as $ext){
			list($a,$a,$where,$a,$a,$a)=$ext->onSql(false,false,$where,false,false,false);
		}

		//Учитываем переданные в функцию условия
		if(is_array($conditions['and'])){
			if($where)
				$where['and']=array_merge($where['and'],$conditions['and']);
			else
				$where['and']=$conditions['and'];
		}

		//Учитываем уровень
		if($levels_to_show > 0){
			//Если указан корень откуда брать дерево - будем брать количество уровней относительно указанного
			if($root_record_id){
				$rec=$this->getRecordById($structure_sid,$root_record_id);
				if($rec['tree_level']==1){
					$where['and']['tree_level']='( (`tree_level`>='.intval($rec['tree_level']).') and (`tree_level`<'.($rec['tree_level']+$levels_to_show).') )';
				}else{
					$where['and']['tree_level']='( (`tree_level`>'.intval($rec['tree_level']).') and (`tree_level`<='.($rec['tree_level']+$levels_to_show).') )';
				}
			}else{
				$where['and']['tree_level']='`tree_level`<='.$levels_to_show.'';
			}
		}
		
		//Если указано откуда считать дерево - счетаем поддерево
		if($root_record_id){

			//Засекаем время
			$t=explode(' ',microtime());
			$sql_start=$t[1]+$t[0];

			//Поля для вывода
			$what = $this->getMainFields($structure_sid);
						
			//Забираем записи полного дерева
			$recs=$this->structure[$structure_sid]['db_manager']->getSub($root_record_id, $what, $where);
			
			//Сколько прошло
			$t=explode(' ',microtime());
			$sql_stop=$t[1]+$t[0];
			$time=$sql_stop-$sql_start;

			//Статистика
			$this->model->log->sql('nested_sets -> getSub',$time,$recs,$this->info['sid'],'getStructureShirtTree_typeTree');

		//Иначе получаем полное дерево структуры
		}else{
			//Засекаем время
			$t=explode(' ',microtime());
			$sql_start=$t[1]+$t[0];

			//Поля для вывода
			$what = $this->getMainFields($structure_sid);

			//ЗДЕСЬ ДОДУМАТЬ ОТКУДА БЕРУТСЯ ОТРИЦАТЕЛЬНЫЕ ЗНАЧЕНИЯ И ВОССТАНОВИТЬ ИХ АККУРАТНО
			//Переход между модулями тратит 2 уровня "tree_level", восстанавливаем их
			if(count($this->structure)>1)
				if(IsSet($where['and']['tree_level']))
					$where['and']['tree_level']='`tree_level`>1';

			//Забираем записи полного дерева
			$recs=$this->structure[$structure_sid]['db_manager']->getFull($what,$where);

			//Сколько прошло
			$t=explode(' ',microtime());
			$sql_stop=$t[1]+$t[0];
			$time=$sql_stop-$sql_start;

			//Статистика
			$this->model->log->sql('nested_sets -> getFull',$time,$recs,$this->info['sid'],'getStructureShirtTree_typeTree');
		}

		if(!count($recs)){
//			pr('not found');
			if( ($this->model->ask->structure_sid != 'rec') ){
				// Сначала смотрим зависимые структуры
				// потом к ним будем вызывать рекурсии
				$search_children=false;
				foreach($this->structure as $s_sid=>$s)
					if($s['dep_path']['structure']==$this->model->ask->structure_sid){
						$search_children=$s_sid;
						$link_type = $s['dep_path']['link_type'];
					}

				if($search_children){
					//Связь производится по полю
					$dep_field_sid = $this->model->types[$link_type]->link_field;
					
					//Ищем только значимых родителей
					if( $rec[$dep_field_sid] ){
						$where = array( 
							'and' => array(
								'dep_path_'.$this->model->ask->structure_sid.''=>'`dep_path_'.$this->model->ask->structure_sid.'`="'.mysql_real_escape_string($rec[ $dep_field_sid ]).'"'
							)
						);
						$recs = $this -> getStructureShirtTree_typeSimple(false,$search_children,$levels_to_show,$where);
					}
					
				}
			}
		}
		
		//Ищем ссылки на модули и считаем их деревья
		if($recs)
		foreach($recs as $i=>$rec){

			//Вложенные модули
			if($levels_to_show>2)
			if(strlen($rec['is_link_to_module'])){

				$recs[$i]['module']=$this->info['sid'];
				$recs[$i]['structure_sid']=$structure_sid;

				if(IsSet($this->model->modules[$rec['is_link_to_module']])){
					if(is_object($this->model->modules[$rec['is_link_to_module']])){

						//Корневая структура зависимого модуля
						$tree = $this->model->modules[$rec['is_link_to_module']]->getLevels('rec');
						$dep_structure_sid = $tree[count($tree)-1];
					
						//Ищем записи вложеного модуля
						$tmp=$this->model->modules[$rec['is_link_to_module']]->getModuleShirtTree(false,$dep_structure_sid,$levels_to_show-2,$conditions);
						//Нашли вложенные модули
						if(count($tmp)){

							//Если вложенные записи есть на ряду с вложенными модулями - суммируем
							if(IsSet($recs[$i]['sub'])){
								$recs[$i]['sub']=array_merge($recs[$i]['sub'],$tmp);
							//Отсутствуют вложенные записи, только вложенные модули
							}else{
								$recs[$i]['sub']=$tmp;
							}
						}
					}
				}
			}

			//Вложенные структуры в пределах этого модуля
			if(count($this->structure)>1){
				//Ищем следующий уровень
				$levels=$this->getLevels('rec', array());
				$levels=array_reverse($levels);
				$next_structure_sid=false;
				foreach($levels as $j=>$level)if($level==$structure_sid)$next_structure_sid=@$levels[$j+1];
				//Нашли вложенную структуру в данном модуле
				if($next_structure_sid){
					//Название поля-связки с текущей структурой
					$field_name='dep_path_'.$structure_sid;
					//Добавялем условие поиска
					$where=$conditions;
					$where['and'][$field_name]='`'.mysql_real_escape_string($field_name).'`="'.mysql_real_escape_string($rec['sid']).'"';
				}
				//Забираем вложенные записи структуры
				$subs=$this->getStructureShirtTree(false,$next_structure_sid,$levels_to_show-1,$where);
				//Нашли вложенные модули
				if($subs){
					//Если вложенные записи есть на ряду с вложенными модулями - суммируем
					if(IsSet($recs[$i]['sub'])){
						$recs[$i]['sub']=array_merge($recs[$i]['sub'],$subs);
					//Отсутствуют вложенные записи, только вложенные модули
					}else{
						$recs[$i]['sub']=$subs;
					}
				}
			}

		}

		//Перекомпановка из линейного массива во вложенные списки
		$recs=$this->reformRecords($recs,$recs[0]['tree_level'],0,count($recs));

		//Помним какая запись из какого модуля
		foreach($recs as &$rec){
			if(!IsSet($rec['module'])){
				$rec['module']=$this->info['sid'];
				$rec['structure_sid']=$structure_sid;
			}
		}

		return $recs;
	}

	//Поиск краткого дерева в линейной структуре
	private function getStructureShirtTree_typeSimple($root_record_id,$structure_sid,$levels_to_show,$where=false,$conditions=false){

		if($root_record_id){
//			pr('-> '.$this->info['sid'].'_'.$structure_sid.' ['.$root_record_id.']');

			// Сначала смотрим зависимые структуры
			// потом к ним будем вызывать рекурсии
			$search_children=false;
			if($structure_sid!='rec')
				if($this->structure)
					foreach($this->structure as $s_sid=>$s)
						if($s['dep_path']['structure']==$structure_sid){
							$search_children=$s_sid;
						}

			//Найдена структура-потомок
			if($search_children){
				$parent=$this->getRecordById($structure_sid,$root_record_id);

				//В разных типах используются разные поля для связки
				//Берём нужное поле связки
				$link_field=$this->model->types[$this->structure[$search_children]['dep_path']['link_type']]->link_field;

				//Условие связи элементов
				$where['and']=array('`dep_path_'.$structure_sid.'`="'.$parent[$link_field].'"');

				//Учитываем переданные в функцию условия
				if(is_array($conditions['and'])){
					$where['and']=array_merge($where['and'],$conditions['and']);
				}

				//Ищем потомков
				if($search_children){
					$recs=$this->getStructureShirtTree_typeSimple(false,$search_children,$levels_to_show,$where);
				}
			}

		//Смотрим всю структуру
		}else{
			//pr('-> '.$this->info['sid'].'_'.$structure_sid.'');

			//Учитываем переданные в функцию условия
			if(is_array($conditions['and']) && is_array($where) ){
				$where['and']=array_merge($where['and'],$conditions['and']);
			}elseif(is_array($conditions['and'])){
				$where=$conditions;
			}

			// Сначала смотрим зависимые структуры
			// потом к ним будем вызывать рекурсии
			$search_children=false;
			if($structure_sid!='rec')
				if($this->structure)
					foreach($this->structure as $s_sid=>$s)
						if($s['dep_path']['structure']==$structure_sid){
							$search_children=$s_sid;
						}

			//Сортировка:
			//если есть поле POS - сортируем по нему,
			//иначе сортируем по публичной дате, в обратном порядке
			$order=IsSet($this->structure[$structure_sid]['fields']['pos'])?'order by `pos`':'order by `date_public` desc';

			//Получаем записи
			if($levels_to_show > 0){
				$recs=$this->model->makeSql(
					array(
						'tables'=>array($this->getCurrentTable($structure_sid)),
						'where'=>$where,
						'order'=>$order
					),
					'getall'
				);
			}//pr($this->model->last_sql);
			
			//Вставляем завиcимые записи если нужно
			if(is_array($recs))
			if($search_children)
			if($structure_sid!='rec')
			if($levels_to_show > 1)
			foreach($recs as $i=>$rec){

				//В разных типах используются разные поля для связки
				//Берём нужное поле связки
				$link_field=$this->model->types[$this->structure[$search_children]['dep_path']['link_type']]->link_field;

				//Условие связи элементов
				$where['and']=array('`dep_path_'.$structure_sid.'`="'.$rec[$link_field].'"');

				//Учитываем переданные в функцию условия
				if(is_array($conditions['and'])){
					$where['and']=array_merge($where['and'],$conditions['and']);
				}

				//Ищем потомков
				if($search_children){
					$children=$this->getStructureShirtTree_typeSimple($root_record_id,$search_children,$levels_to_show-1,$where);
					if($children)$recs[$i]['sub']=$children;
				}
			}
		}
		//Вставляем окончание .html
		$recs=$this->insertRecordUrlType($recs);

		//Помним какая запись из какого модуля
		if($recs)
		foreach($recs as &$rec){
			if(!IsSet($rec['module'])){
				$rec['module']=$this->info['sid'];
				$rec['structure_sid']=$structure_sid;
			}
		}

		//Готово
		if(count($recs))return $recs;else return false;
	}

//////////////////////
/// ПОИСКИ ЗАПИСЕЙ ///
//////////////////////

	//Забрать запись по запросу - только для деревьев
	public function getRecordByAsk(){
		//Главная страница модуля
		if($this->model->ask->output_type=='index'){
		
			//Для основного модуля сайта здесь будет "Главная страница"
			if(!$this->info['sid']){
				$rec=$this->getRecordBySid('rec','index');

				//Пишем основную запись в ASK, пускай другие модули пользуют
				$this->model->ask->rec=array_merge($this->model->ask->rec,$rec);

				//Разворачиваем значения записи перед выводом в браузер
				$rec=$this->explodeRecord($rec,$this->model->ask->structure_sid);

				//Вставляем окончание .html
				$rec=$this->insertRecordUrlType($rec);

			//Указываем запись в ASK
			}elseif($rec){
				$this->model->ask->rec=array_merge($rec,$this->model->ask->rec);
				
			}else{
				//Дообновляем данные в ASK
				$this->model->ask->rec=array_merge($this->info,$this->model->ask->rec);

				//Всёже не забываем про запись, часть её параметров нам может приходиться
				$rec=$this->model->ask->rec;

				//Разворачиваем значения записи перед выводом в браузер
				$rec=$this->model->modules[0]->explodeRecord($rec,$this->model->ask->structure_sid);

				//Добавляем параметры записи нового модуля, сохраняя отличные от него параметры старой страницы
				$rec=array_merge($this->info,$rec);

				//Вставляем окончание .html
				$rec=$this->insertRecordUrlType($rec);
			}

		//Содержание
		}else{

			//Условия отбора
			$where=array('and'=>array(
				'sid'		=>	'`sid`="'.mysql_real_escape_string($this->model->ask->rec['sid']).'"',
				'url'		=>	'`url` LIKE "%'.mysql_real_escape_string('/'.implode('/',$this->model->ask->url)).'"',
				'domain'	=>	$this->model->extensions['domains']->getWhere(),
			));

			//Скрытую запись могут просматривать только авторизованные пользователи, являющиеся администраторами сайта
			if(!$this->model->user->info['admin'])
				$where['and']['shw']='`shw`=1';

			//Получаем запись
			$rec=$this->model->makeSql(
				array(
					'tables'=>array($this->getCurrentTable($this->model->ask->structure_sid)),
					'where'=>$where,
				),
				'getrow'
			);
			if(!$rec)return false;
			if(!count($rec))return false;

			//Пишем основную запись в ASK, пускай другие модули пользуют
			$this->model->ask->rec=array_merge($this->model->ask->rec,$rec);

			//Дополнительная обработка записи при типе вывода "content"
			$rec=$this->contentPrepare($rec,$this->model->ask->structure_sid);

			//Разворачиваем значения записи перед выводом в браузер
			$rec=$this->explodeRecord($rec,$this->model->ask->structure_sid);

			//Вставляем окончание .html
			$rec=$this->insertRecordUrlType($rec);

			if($this->model->ask->structure_sid=='rec')
				$this->model->ask->output_type='content';
			else
				$this->model->ask->output_type='list';
		}
		
		return $rec;
	}

	//Дополнительная обработка записи при типе вывода "content"
	public function contentPrepare($rec,$structure_sid='rec'){
		return $rec;
	}

	//Забрать запись по ID
	public function getRecordById($structure_sid,$id){
		//Получаем записи
		$rec=$this->model->makeSql(
			array(
				'fields'=>false,
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>array('and'=>array('`id`="'.mysql_real_escape_string($id).'"')),//,'`shw`=1'
				'order'=>$order
			),
			'getrow'
		);

		//Чистим данные
		$rec=$this->clearAfterDB($rec);

		//Вставляем окончание .html
		$rec=$this->insertRecordUrlType($rec);
		return $rec;
	}

	//Забрать запись по SID
	public function getRecordBySid($structure_sid,$sid){
		//Получаем записи
		$rec=$this->model->makeSql(
			array(
				'fields'=>false,
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>array('and'=>array('`sid`="'.mysql_real_escape_string($sid).'"','`shw`=1'))
			),
			'getrow'
		);

		//Чистим данные
		$rec=$this->clearAfterDB($rec);

		//Вставляем окончание .html
		$rec=$this->insertRecordUrlType($rec);
		return $rec;
	}

	//Забрать запись по WHERE
	public function getRecordsByWhere($structure_sid,$where){
		//Сортировка
		$order='order by `date_public` desc';
		if(IsSet($this->structure[$structure_sid]['fields']['pos']))$order='order by `pos`';
		if($this->structure[$structure_sid]['type']=='tree')$order='order by `left_key`';

		//Получаем записи
		$recs=$this->model->makeSql(
			array(
				'fields'=>false,
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>$where,
				'order'=>$order
			),
			'getall'
		);

		//Чистим данные
		$recs=$this->clearAfterDB($recs);

		//Вставляем окончание .html
		$recs=$this->insertRecordUrlType($recs);
		return $recs;
	}

	//Забрать запись по WHERE
	public function getShirtRecordsByWhere($structure_sid,$where){
		//Сортировка
		$order='order by `date_public` desc';
		if(IsSet($this->structure[$structure_sid]['fields']['pos']))$order='order by `pos`';
		if($this->structure[$structure_sid]['type']=='tree')$order='order by `left_key`';

		//Получаем записи
		$recs=$this->model->makeSql(
			array(
				'fields'=>array('id','sid','title','url'),
				'tables'=>array($this->getCurrentTable($structure_sid)),
				'where'=>$where,
				'order'=>$order
			),
			'getall'
		);

		//Чистим данные
		$recs=$this->clearAfterDB($recs);

		//Вставляем окончание .html
		$recs=$this->insertRecordUrlType($recs);
		return $recs;
	}


//////////////////////////////////////////////////////////////////////
/// ДЕЙСТВИЯ МОДУЛЯ ПО ОТНОШЕНИЮ К СВОИМ ОБЪЕКТАМ ///
/////////////////////////////////////////////////////////////////////

	//Добавление записи в структуру модуля
	public function addRecord($structure_sid,$record,$conditions=false){
		//Выполняем действие
		return $this->do_addRecord($structure_sid,$record,$conditions);
	}

	//Добавление записи в структуру модуля
	public function updateRecord($structure_sid,$record,$conditions=false){
		//Выполняем действие
		return $this->do_updateRecord($structure_sid,$record,$conditions);
	}

	//Удаление записи
	public function deleteRecord($structure_sid,$record,$conditions=false){
		//Выполняем действие
		return $this->do_deleteRecord($structure_sid,$record,$conditions);
	}

	//Переместить на одну позицию выше
	public function moveUp($structure_sid,$record,$conditions=false){
		//Выполняем действие
		return $this->do_moveUp($structure_sid,$record,$conditions);
	}

	//Переместить на одну позицию ниже
	public function moveDown($structure_sid,$record,$conditions=false){
		//Подключаем расширения по управлению записями модуля
		return $this->do_moveDown($structure_sid,$record,$conditions);
	}


////////////////////////////////////////////////////////////////////////////////
/// УСТАНОВКА, ПЕРЕУСТАНОВКА И УДАЛЕНИЕ ТАБЛИЦ В БАЗЕ ДАННЫХ ///
////////////////////////////////////////////////////////////////////////////////

	//Установка модуля
	public function install($part_sid){
		require_once($this->model->config['path']['core'].'/classes/table_manage.php');
		$table=new table_manage($this->model->db[$this->db_sid],$this->getCurrentTable($part_sid),$this->structure[$part_sid],$this->model->config['path']['core']);
		$table->create();
		
		//Вставляем первую корневую запись в дерево
		if( $this->structure[$part_sid]['type'] == 'tree' )
			$this->model->execSql('insert into `' . $this->getCurrentTable($part_sid) . '` set `sid`="index", `title`="'.$this->info['title'].'", `left_key`=1, `right_key`=2, `tree_level`=1, `url`="/'.$this->info['sid'].'", `domain`="all", `shw`=1, `ln`=1','insert');
	}

	//Переустановка модуля
	public function reinstall($part_sid){
		require_once($this->model->config['path']['core'].'/classes/table_manage.php');
		$table=new table_manage($this->model->db[$this->db_sid],$this->getCurrentTable($part_sid),$this->structure[$part_sid],$this->model->config['path']['core']);
		$table->delete();
		$table->create();
		
		//Вставляем первую корневую запись в дерево
		if( $this->structure[$part_sid]['type'] == 'tree' )
			$this->model->execSql('insert into `' . $this->getCurrentTable($part_sid) . '` set `sid`="index", `title`="'.$this->info['title'].'", `left_key`=1, `right_key`=2, `tree_level`=1, `url`="/'.$this->info['sid'].'", `domain`="all", `shw`=1, `ln`=1','insert');
	}

	//Удаление модуля
	public function uninstall($part_sid){
		require_once($this->model->config['path']['core'].'/classes/table_manage.php');
		$table=new table_manage($this->model->db[$this->db_sid],$this->getCurrentTable($part_sid),$this->structure[$part_sid],$this->model->config['path']['core']);
		$table->delete();
	}

	//Проверка корректности установки модуля
	public function check(){

		//Все структуры
		if($this->structure)
		foreach($this->structure as $structure_sid=>$structure){

			//Проверяем есть ли таблицы, пр необходимости переустанавливаем
			$res=$this->model->execSql('show tables like "'.$this->getCurrentTable($structure_sid).'"');

			if(!count($res)){
				//Переустановка
				$this->reinstall($structure_sid);

			}else{

				//Все имеющиеся поля
				$table_fields=$this->model->execSql('show columns from `'.$this->getCurrentTable($structure_sid).'`','getall');

				//Все поля
				foreach($structure['fields'] as $sid=>$field){
					$flag=false;
					foreach($table_fields as $f)
						if($f['Field']==$sid)$flag=true;
					if(!$flag){
						$sql='alter table `'.$this->getCurrentTable($structure_sid).'` add '.$this->model->types[$field['type']]->creatingString($sid);
						$this->model->execSql($sql,'update');
//						pr('Поле "'.$sid.'" в таблице "'.$this->getCurrentTable($structure_sid).'" отсутствовало - исправлено.');
//						pr($sql);
					}
				}
			}
		}
	}

	//Проверка корректности индексов таблиц деревьев
	public function checkTree(){

		if($this->structure)
		foreach($this->structure as $structure_sid=>$structure){
			if($structure['type']=='tree'){

				$message=false;

				//Забираем все записи дерева
				$recs=$this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` order by `left_key`','getall');

				//Проверяем общую контрольную сумму дерева
				if($recs[0]['right_key']!=count($recs)*2)
					$message.'['.$this->info['sid'].'] > ['.$structure_sid.'] checksum error'."\n\n";

				//Проверяем все записи на совпадение разницы индексов и числа подразделов
				if($recs)
				foreach($recs as $i=>$rec){
					$subrecs=$this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `left_key`>'.$rec['left_key'].' and `right_key`<'.$rec['right_key'].' and `tree_level`>'.$rec['tree_level'].' order by `left_key`','getall');

					//Проверка числа подразделов
					if($rec['left_key']+1+($subrecs?count($subrecs)*2:0)!=$rec['right_key']){
						$message.='['.$this->info['sid'].'] > ['.$structure_sid.'] -> ['.$rec['id'].'] subs checksum error'."\n";
						$message.=$this->model->last_sql."\n";
						$message.=$rec['left_key'].'+1+'.($subrecs?count($subrecs)*2:0).' != '.$rec['right_key']."\n\n";
					}
				}
				
				//Если были ошибки - высылаем сообщение
				if($message){
//					print('<h1 style="color:#f00">Редактировать что-либо временно не рекомендуется.</h1>');
//					mail('dekmabot@gmail.com',$this->model->extensions['domains']->domain['host'].' tree checksum error',$message);
//					pr_r($message);

					error_reporting(E_ERROR | E_WARNING | E_PARSE);	
					
					//Строим нормальное дерево
					$counter = 0;
					$recs = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `sid`="index"','getall');
					foreach($recs as $i=>$rec){
						$counter ++;
						$rec['left_key'] = $counter;
						$recs2 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec['sid'].'" order by `left_key`, `id`','getall');
						foreach($recs2 as $i2=>$rec2){
							$counter ++;
							$rec2['left_key'] = $counter;
							$recs3 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec2['sid'].'" order by `left_key`, `id`','getall');
							foreach($recs3 as $i3=>$rec3){
								$counter ++;
								$rec3['left_key'] = $counter;
								$recs4 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec3['sid'].'" order by `left_key`, `id`','getall');
								foreach($recs4 as $i4=>$rec4){
									$counter ++;
									$rec4['left_key'] = $counter;
									$recs5 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec4['sid'].'" order by `left_key`, `id`','getall');
									foreach($recs5 as $i5=>$rec5){
										$counter ++;
										$rec5['left_key'] = $counter;
										$recs6 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec5['sid'].'" order by `left_key`, `id`','getall');
										foreach($recs6 as $i6=>$rec6){
											$counter ++;
											$rec6['left_key'] = $counter;
											$recs7 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec6['sid'].'" order by `left_key`, `id`','getall');
											foreach($recs7 as $i7=>$rec7){
												$counter ++;
												$rec7['left_key'] = $counter;
												$recs8 = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `dep_path_parent`="'.$rec7['sid'].'" order by `left_key`, `id`','getall');
												foreach($recs8 as $i8=>$rec8){
													$counter ++;
													$rec8['left_key'] = $counter;
													$counter ++;
													$rec8['right_key'] = $counter;
													$tree_level=8;
													$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec8['left_key'] ).', `right_key`='.intval( $rec8['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'/'.$rec6['sid'].'/'.$rec7['sid'].'/'.$rec8['sid'].'" where `id`='.intval($rec8['id']).'','update');
												}
												$counter ++;
												$rec7['right_key'] = $counter;
												$tree_level=7;
												$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec7['left_key'] ).', `right_key`='.intval( $rec7['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'/'.$rec6['sid'].'/'.$rec7['sid'].'" where `id`='.intval($rec7['id']).'','update');
											}
											$counter ++;
											$rec6['right_key'] = $counter;
											$tree_level=6;
											$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec6['left_key'] ).', `right_key`='.intval( $rec6['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'/'.$rec6['sid'].'" where `id`='.intval($rec6['id']).'','update');
										}
										$counter ++;
										$rec5['right_key'] = $counter;
										$tree_level=5;
										$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec5['left_key'] ).', `right_key`='.intval( $rec5['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'/'.$rec5['sid'].'" where `id`='.intval($rec5['id']).'','update');
									}
									$counter ++;
									$rec4['right_key'] = $counter;
									$tree_level=4;
									$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec4['left_key'] ).', `right_key`='.intval( $rec4['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'/'.$rec4['sid'].'" where `id`='.intval($rec4['id']).'','update');
								}
								$counter ++;
								$rec3['right_key'] = $counter;
								$tree_level=3;
								$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec3['left_key'] ).', `right_key`='.intval( $rec3['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'/'.$rec3['sid'].'" where `id`='.intval($rec3['id']).'','update');
							}
							$counter ++;
							$rec2['right_key'] = $counter;
							$tree_level=2;
							$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec2['left_key'] ).', `right_key`='.intval( $rec2['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'/'.$rec2['sid'].'" where `id`='.intval($rec2['id']).'','update');
						}
						$counter ++;
						$rec['right_key'] = $counter;
						$tree_level=1;
						$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `left_key`='.intval( $rec['left_key'] ).', `right_key`='.intval( $rec['right_key'] ).', `tree_level`='.intval($tree_level).', `url`="'.($this->info['sid']?'/'.$this->info['sid']:'').'" where `id`='.intval($rec['id']).'','update');
					}
				}
			}
		}
	}

//////////////////////////////////////////
/// ДЕЙСТВИЯ НАД ОБЪЕКТАМИ МОДУЛЯ ///
//////////////////////////////////////////

	//Добавление записи в структуру модуля
	public function do_addRecord($structure_sid,$values){

		$what=array();

		//Корректиуем SID
		$values['sid'] = $this->model->types['sid']->toValue('sid', $values);
		$values['sid'] = $this->model->types['sid']->makeUnique($this->info['sid'], $structure_sid, $values['sid']);
		$what['sid']='`sid`="'.mysql_real_escape_string( $values['sid'] ).'"';

		//Обновляем дату добавления и дату последней модификации
		$what['date_added']='`date_added`=NOW()';
		$what['date_modify']='`date_modify`=NOW()';

		//ID при создании не нужен
		if( IsSet( $values['id'] ) )
			UnSet( $values['id'] );
		
		//Обработка присланных значений
		$fields=$this->structure[$structure_sid]['fields'];
		foreach( $fields as $field_sid => $field )
			if( !IsSet( $what[ $field_sid ] ) and IsSet( $values[ $field_sid ] ) ){
				//Значение
				$value = $this->model -> types[ $field['type'] ]->toSQL($field_sid, $values, array(), $field);
				//Запоминаем
				if($value)
					$what[ $field_sid ]=$value;
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
		}
		
		//Получаем родителя
		if( !$url ){
			$parent=$this->model->makeSql(
				array(
					'fields'=>array('id','url'),
					'tables'=>array( $this->getCurrentTable( $parent_field_structure ) ),
					'where'=>array(
						'and'=>array(
							'`'.$this->model->types[ $parent_field_type ]->link_field.'`="'.mysql_real_escape_string( $values[ $parent_field_sid ] ).'"'
						)
					)
				),
				'getrow'
			);
			$url = @$parent['url'].'/'.$values['sid'];
			$what[ $parent_field_sid ]='`'.$parent_field_sid.'`="'.mysql_real_escape_string( $values[ $parent_field_sid ] ).'"';
		}
		
		//Что записывать будем
		$what['url']='`url`="'.mysql_real_escape_string( $url ).'"';

		//Настройки автоматом не перезаписывать
		UnSet($what['acms_settings']);

		//Вставляем в дерево
		if( $parent_field_type == 'tree' ){
			//Если не установлен обработчик таблицы
			if(!IsSet($this->structure[ $structure_sid ]['db_manager'])){
				require_once($this->model->config['path']['core'].'/classes/nestedsets.php');
				$this->structure[ $structure_sid ]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}
			//Исключаем ошибку при доюавлении, когда родитель не найден
			if(IsSet($parent['id'])){
				$res=$this->structure[ $structure_sid ]['db_manager']->addChild($parent['id'], $what);
			}
			
		//Вставляем просто так
		}else{
			//Вставляем запись
			$res=$this->model->makeSql(
				array(
					'fields'=>$what,
					'tables'=>array( $this->getCurrentTable( $structure_sid ) ),
				),
				'insert'
			);
		}

		//Сохраняем дополнительные настройки записи
		if( $this->model->config['settings']['dock_interfaces_to_records'] )
			$this->saveRecordSettings($structure_sid, $values);

		//Возвращаем URL, на который будет переброшен пользователь
		return $url.'.html';
	}

	//Добавление записи в структуру модуля
	public function do_updateRecord($structure_sid,$values,$conditions=false){

		$what=array();

		//Старые данные, до обновления
		$data_before=$this->getRecordById( 'rec', $values['id'] );

		//Корректиуем SID
		$values['sid'] = $this->model->types['sid']->toValue('sid', $values);
		$values['sid'] = $this->model->types['sid']->makeUnique($this->info['sid'], $structure_sid, $values['sid'], $values['id']);
		$what['sid']='`sid`="'.mysql_real_escape_string( $values['sid'] ).'"';

		//Обновляем дату добавления и дату последней модификации
		$what['date_modify']='`date_modify`=NOW()';

		//Не стоит скрывать главную страницу =)
		if( $values['sid'] == 'index' ){
			$values['shw'] = 1;
			UnSet($values['dep_path_parent']);
		}
		
		//Обработка присланных значений
		$fields=$this->structure[$structure_sid]['fields'];
		foreach( $fields as $field_sid => $field )
			if( !IsSet( $what[ $field_sid ] ) and IsSet( $values[ $field_sid ] ) ){
				//Значение
				$value = $this->model -> types[ $field['type'] ]->toSQL($field_sid, $values, array(), $field);
				//Запоминаем
				if($value)
					$what[ $field_sid ]=$value;
			}
	
		//Настройки автоматом не перезаписывать
		UnSet($what['acms_settings']);

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
			$parent=$this->model->makeSql(
				array(
					'fields'=>array('id','url'),
					'tables'=>array( $this->getCurrentTable( $parent_field_structure ) ),
					'where'=>array(
						'and'=>array(
							'`'.$this->model->types[ $parent_field_type ]->link_field.'`="'.mysql_real_escape_string( $values[ $parent_field_sid ] ).'"'
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
		$where['id'] = '`id`="'.mysql_real_escape_string( $values['id'] ).'"';
		
		//Настройки автоматом не перезаписывать
		UnSet($what['settings']);

		//Вносим изменения
		$this->model->makeSql(
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
				require_once($this->model->config['path']['core'].'/classes/nestedsets.php');
				$this->structure[ $structure_sid ]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}
			//Исключаем ошибку при доюавлении, когда родитель не найден
			if( IsSet( $parent['id'] ) ){
				//Обновление индексов дерева
				$this->structure[ $structure_sid ]['db_manager']->moveChild($parent['id'], $values['id']);
			}
		}

		//Обновляем поддерево
		$this->do_updateChildren($structure_sid,$data_before,$values,$url);

		//Сохраняем дополнительные настройки записи
		if( $this->model->config['settings']['dock_interfaces_to_records'] )
			$this->saveRecordSettings($structure_sid, $values);

		//Приписываем окончание для результирующего URL`а
		if(strlen($url))$url.='.html';

		//Возвращаем URL, на который будет переброшен пользователь
		return $url;
	}

	//Удаление записи
	public function do_deleteRecord($structure_sid,$record,$conditions){
		
		//Удаляем поддерево
		if($this->structure[$structure_sid]['type']=='tree'){

			//Если раздел не пуст - запрещаем удаление
			if($record['left_key']+1!=$record['right_key']){
				print('Удаление раздела не возможно, сначала удалите все подразделы.');
				exit();
			}

			//Если не установлен обработчик таблицы
			if(!IsSet($this->structure[$structure_sid]['db_manager'])){
				require_once($this->model->config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}

			//Удаляем
			$this->structure[$structure_sid]['db_manager']->delete($record['id']);

		//Удаляем простую запись
		}else{
			//Выставляем POS у линейных структур
			$res=$this->model->makeSql(
				array(
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`="'.mysql_real_escape_string($record['id']).'"'))
				),
				'delete'
			);
		}
	}
	
	//Сохранение дополнительных настроек записи
	public function saveRecordSettings($structure_sid, $values){
		$settings = array(
			'interfaces_int' => (array)$values['interfaces_int'],
			'interfaces_ext' => (array)$values['interfaces_ext'],
			'components_int' => (array)$values['components_int'],
			'components_ext' => (array)$values['components_ext'],
		);
		
		//Вносим изменения
		$this->model->execSql('update `'.$this->getCurrentTable( $structure_sid ).'` set `acms_settings`="'.mysql_real_escape_string( serialize($settings) ).'" where `id`="'.mysql_real_escape_string( $values['id'] ).'" limit 1','update');
	}

	//Переместить на одну позицию выше
	public function do_moveUp($structure_sid,$record,$conditions){

		//Дерево - переносим структуры
		if($this->structure[$structure_sid]['type']=='tree'){

			//Выбираем вторую запись, с которой будем меняться местами
			$other=$this->model->makeSql(
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
				require_once($this->model->config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}

			//Меняем местами
			if(is_array($other)){
				//Условие для обновления деревьев
				$conditions=array('and'=>array($this->model->extensions['domains']->getWhere()));

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
			$other=$this->model->makeSql(
				array(
					'fields'=>array('id','pos'),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'order'=>'order by `pos` desc'
				),
				'getrow'
			);

			//Обновляем первую запись
			$this->model->makeSql(
				array(
					'fields'=>array('`pos`='.$other['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$record['id'].''))
				),
				'update'
			);

			//Обновляем вторую запись
			$this->model->makeSql(
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
	public function do_moveDown($structure_sid,$record,$conditions){

		//Условие для обновления деревьев
		$conditions=array('and'=>array($this->model->extensions['domains']->getWhere()));

		//Переместить на одну позицию ниже
		if($this->structure[$structure_sid]['type']=='tree'){

			//Выбираем вторую запись, с которой будем меняться местами
			$other=$this->model->makeSql(
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
				require_once($this->model->config['path']['core'].'/classes/nestedsets.php');
				$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
			}

			//Меняем местами
			if(is_array($other)){
				//Условие для обновления деревьев
				$conditions=array('and'=>array('domain'=>$this->model->extensions['domains']->getWhere()));

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
			$other=$this->model->makeSql(
				array(
					'fields'=>array('id','pos'),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'order'=>'order by `pos`'
				),
				'getrow'
			);

			//Обновляем первую запись
			$this->model->makeSql(
				array(
					'fields'=>array('`pos`='.$other['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$record['id'].''))
				),
				'update'
			);

			//Обновляем вторую запись
			$this->model->makeSql(
				array(
					'fields'=>array('`pos`='.$record['pos'].''),
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>array('and'=>array('`id`='.$other['id'].''))
				),
				'update'
			);
		}
	}

	//Обновляем поддерево зависимых записей
	private function do_updateChildren($structure_sid,$old_data,$new_data,$new_url,$condition = false, $domain = false){

		//Если модуль дерево - найти и обновить все записи в поддереве, поискать ссылки на другие модули
		//Если модуль сложный - найти и обновить все зависимые структуры
		//Если модуль простой - ничего не делать
	
		//Деревья
		if($this->structure[$structure_sid]['type']=='tree'){
			
			//Дочерние модули по ссылке в записи
			if($old_data['in_link_to_module']){
				$linked_module = $old_data['in_link_to_module'];
				$tree = $this->model->modules[ $linked_module ]->getLevels('rec', array());
				$root_level_structure_sid = $tree[ count($tree)-1 ];
				
				//Обновляем все корневые записи того модуля
				$this->model->execSql('update `'.$this->model->modules[ $linked_module ]->getCurrentTable($root_level_structure_sid).'` set `url`=CONCAT("'.mysql_real_escape_string($new_url).'/",`sid`) where '.$this->model->extensions['domains']->getWhere().'','update');
				
				//Теперь запускаем по ним рекурсию
				$recs = $this->model->execSql('select * from `'.$this->model->modules[ $linked_module ]->getCurrentTable($root_level_structure_sid).'` where '.$this->model->extensions['domains']->getWhere().'','getall');
				foreach($recs as $rec)
					
					//Рекурсия - спуск
					$this->model->modules[ $linked_module ]->do_updateChildren(
						$root_level_structure_sid,
						$rec,
						$rec,
						$new_url.'/'.$rec['sid'],
						$condition, 
						$domain
					);
			}
			
			//Обновляем все дочерние записи этого модуля
			$this->model->execSql('update `'.$this->getCurrentTable($structure_sid).'` set `url`=CONCAT("'.mysql_real_escape_string($new_url).'/", `sid`), `dep_path_parent`="'.mysql_real_escape_string($new_data['sid']).'" where `left_key`>'.intval($old_data['left_key']).' and `right_key`<'.intval($old_data['right_key']).' and `tree_level`='.intval($old_data['tree_level']+1).' and '.$this->model->extensions['domains']->getWhere().'','update');
			
			//Теперь запускаем по ним рекурсию
			$recs = $this->model->execSql('select * from `'.$this->getCurrentTable($structure_sid).'` where `left_key`>'.intval($old_data['left_key']).' and `right_key`<'.intval($old_data['right_key']).' and `tree_level`='.intval($old_data['tree_level']+1).' and '.$this->model->extensions['domains']->getWhere().'','getall');
			foreach($recs as $rec){
				
				//Рекурсия - спуск
				$this->do_updateChildren(
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
					$dep_path_field = $this->model->types[ $str['dep_path']['link_type'] ]->link_field;
				
					//Обновляем все дочерние записи этого модуля
					$this->model->execSql('update `'.$this->getCurrentTable($dep_structure).'` set `url`=CONCAT("'.mysql_real_escape_string($new_url).'/", `sid`), `dep_path_'.$structure_sid.'`="'.mysql_real_escape_string($new_data[ $dep_path_field ]).'" where `dep_path_'.$structure_sid.'`="'.mysql_real_escape_string($old_data[ $dep_path_field ]).'" and '.$this->model->extensions['domains']->getWhere().'','update');
					
					//Теперь запускаем по ним рекурсию
					$recs = $this->model->execSql('select * from `'.$this->getCurrentTable($dep_structure).'` where `dep_path_'.$structure_sid.'`="'.mysql_real_escape_string($new_data[ $dep_path_field ]).'" and '.$this->model->extensions['domains']->getWhere().'','getall');
					foreach($recs as $rec){
						
						//Рекурсия - спуск
						$this->do_updateChildren(
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

//////////////////////////////////////////////////
/// ВНУТРЕННИЕ СЛУЖЕБНЫЕ ФУНКЦИИ МОДУЛЯ ///
//////////////////////////////////////////////////

	//Вернуть массив основных полей структуры
	public function getMainFields($structure_sid = 'rec'){
		$fields = array('id','sid','title','url');
		$main=array('id','sid','date_public','title','url','shw','dep_path_darent','dep_path_dir','left_key','right_key','is_link_to_module','seo_title','seo_keywords','seo_description','seo_changefreq','seo_priority');

		if(is_array($this->structure[$structure_sid]['fields']))
		foreach($this->structure[$structure_sid]['fields'] as $sid=>$f)
			if( (in_array($sid, $main) || @$f['main']) and (!IsSet($fields[$sid])) )
				$fields[$sid]=$sid;
				
		return $fields;
	}

	//Возвращаем название таблицы текущей структуры
	public function getCurrentTable($part = 'rec'){
		return $this->database_table_preface.$this->info['prototype'].'_'.$part;
	}

	//Форматирование полученных данных после запроса из базы
	public function clearAfterDB($recs){

		//Список записей
		if(IsSet($recs[0]['id'])){
			foreach($recs as $i=>$rec){
				foreach($rec as $var=>$val){
					if(!is_array($val)){
						$recs[$i][$var]=htmlspecialchars_decode(stripslashes($val));
					}else{
						$recs[$i][$var]=$this->clearAfterDB($val);
					}
				}
			}

		//Одна запись
		}elseif(IsSet($recs['id'])){
			foreach($recs as $var=>$val){
				if(!is_array($val)){
					$recs[$var]=htmlspecialchars_decode(stripslashes($val));
				}else{
					$recs[$var]=$this->clearAfterDB($val);
				}
			}
		}

		return $recs;
	}

	//Разворачиваем значения полей перед выводом в браузер
	public function explodeRecord($rec,$structure_sid='rec'){

		$second_level_explodable_fields=array('image','gallery');

		if(is_array($rec))
		foreach($rec as $sid=>$value){
			//Настройки поля в структуре модуля
			$field_settings=$this->structure[$structure_sid]['fields'][$sid];

			//Разворачиваем значение
			if($field_settings['type']){
				if(IsSet($this->model->types[ $field_settings['type'] ]))

				//Разворачиваем ненулевые значения
//				if($value){

					$rec[$sid]=$this->model->types[ $field_settings['type'] ]->getValueExplode($value, $this->structure[$structure_sid]['fields'][$sid], $rec);

					//Разварачиваем картинки у связанных записей
					if( $field_settings['type'] == 'link' ){

						if(IsSet($this->model->modules[ $field_settings['module'] ]->structure[$field_settings['structure_sid']]))
						foreach($this->model->modules[ $field_settings['module'] ]->structure[ $field_settings['structure_sid'] ]['fields'] as $sub_field_sid => $sub_field)
							if($sub_field['type'] == 'image'){

								//Разворачиваем занчение
								$new_val = $this->model->types[ 'image' ] -> getValueExplode(
									$rec[ $sid ][ $sub_field_sid ],
									$this->model->modules[ $field_settings['module'] ]->structure[ $field_settings['structure_sid'] ]['fields'][ $sub_field_sid ],
									$field_settings
								);

								//Если значение развернулось
								if( $new_val )
								if( is_array($new_val) )
									$rec[ $sid ][ $sub_field_sid ] = $new_val;
							}

					//Разварачиваем картинки у связанных записей
					}elseif( $field_settings['type'] == 'user' ){

						$module_sid = $this->model->getModuleSidByPrototype('users');
						foreach($this->model->modules[ $module_sid ]->structure[ 'rec' ]['fields'] as $sub_field_sid => $sub_field)
							if($sub_field['type'] == 'image'){

								//Разворачиваем занчение
								$new_val = $this->model->types[ 'image' ] -> getValueExplode(
									$rec[ $sid ][ $sub_field_sid ],
									$this->model->modules[ $module_sid ]->structure[ 'rec' ]['fields'][ $sub_field_sid ],
									$field_settings
								);

								//Если значение развернулось
								if( $new_val )
								if( is_array($new_val) )
									$rec[ $sid ][ $sub_field_sid ] = $new_val;
							}

					}
//				}
			}
		}

		//Если установлено расширение социального графа - дополняем записи значением вершины графа
		if(IsSet($this->model->extensions['graph'])){
			$rec['graph_top']=$this->getGraphTop($rec['id'],$structure_sid);
			$rec['graph_top_text']=implode('|', $this->getGraphTop($rec['id'],$structure_sid));
		}

		return $rec;
	}

	//Вставка html или других окончаний для URL-ов записей
	//с версии 2.0 добавлена ссылка на версию для печати
	public function insertRecordUrlType($recs, $type='html', $insert_host = false){

		//Передана одна запись
		if(IsSet($recs['url'])){
			//Обычная запись
			if(strlen($recs['url'])){
				//Ссылка на версию для печати
				$recs['url_print']=$recs['url'].'.print.'.$type;
				//Основная ссылка
				$recs['url']=$recs['url'].'.'.$type;
			//Ссылка на главную страницу
			}else{
				$recs['url']='/';
			}
			
			//Делать полный путь, а не относительный
			if($insert_host)
				$recs = $this->insertHostToUrl($recs);

		//Несколько записей
		}else{
			if($recs)
			foreach($recs as $i=>$rec){
			
				//Обычная запись
				if(strlen($recs[$i]['url'])){
					//Ссылка на версию для печати
					$recs[$i]['url_print']=$recs[$i]['url'].'.print.'.$type;
					//Основная ссылка
					$recs[$i]['url']=$recs[$i]['url'].'.'.$type;
				//Ссылка на главную страницу
				}elseif(IsSet($recs[$i]['id'])){
					$recs[$i]['url']='/';
				}
				
				//Делать полный путь, а не относительный
				if($insert_host)
					$recs[$i] = $this->insertHostToUrl($rec);
			}
		}
		return $recs;
	}
	
	//Указать путь, включая хост
	public function insertHostToUrl($rec){
		$rec['url'] = 'http://'.$rec['domain'][0]['host'].$rec['url'];
		return $rec;
	}

	//Получить иерархию структур модуля
	public function getLevels($structure, $level_tree = false){
		$level_tree[]=$structure;

		//Структура без зависимостей
		if($this->structure[$structure]['type']=='tree'){
			$level_tree[]=$structure;

		//Учитываем найденную зависимость
		}elseif($this->structure[$structure]['dep_path']){
			$new_structure=$this->structure[$structure]['dep_path']['structure'];
			$level_tree=$this->getLevels($new_structure, $level_tree);
		}

		return $level_tree;
	}

	//Рекурсивная функция переформирования линейного списка записей в дерево
	public function reformRecords($recs,$level,$from,$to){
		$found=array();
		for($i=$from;$i<$to;$i++){
			if($recs[$i]['tree_level']==$level){
				$found[]=array('id'=>$i,'from'=>$i+1);
			}
		}
		$res=array();
		foreach($found as $i=>$f){
			if($i+1==count($found)){
				$new_subs=$this->reformRecords($recs,$level+1,$f['from'],$to);
			}elseif($f['from']<$found[$i+1]['from']){
				$new_subs=$this->reformRecords($recs,$level+1,$f['from'],$found[$i+1]['from']);
			}
			if($new_subs){
				//Уже есть какие-то подразделы
				if(is_array($recs[$f['id']]['sub']))
					$recs[$f['id']]['sub']=array_merge($new_subs,$recs[$f['id']]['sub']);
				//Подразделов пока нет
				else
					$recs[$f['id']]['sub']=$new_subs;
			}
			$res[]=$recs[$f['id']];
		}
		foreach($res as $i=>$r){
			if(strlen($r['url']))
				$res[$i]['url'].='.html';
			else
				$res[$i]['url']='/';
		}
		return $res;
	}

	//Следующий свободный ID в структуре
	public function getNextId($structure_sid='rec'){
		//Достаём последний
		$last=$this->model->execSql('select `id` from `'.$this->getCurrentTable($structure_sid).'` order by `id` desc','getrow');
		//Проверяем существование
		if(!IsSet($last['id']))$last['id']=1;
		//Возвращаем
		return $last['id']+1;
	}

	//Представить запись в виде вершины социального графа
	public function getGraphTop($record_id, $structure_sid='rec'){
		return array( 'module' => $this->info['sid'], 'structure_sid' => $structure_sid, 'id' => $record_id );
	}

	//Проверка наличия доступа к записи
	public function checkAccess($record, $interface_sid){
		
		//Авторы
		if( $record['author'] == $this->model->user->info['id'] )
			return true;
/*		
		//Модераторы и Администраторы
		if( ($this->model->user->info['admin']) or ($this->model->user->info['moder']) )
			return true;
*/	
		//Ответ
		return false;
	}

}

?>
