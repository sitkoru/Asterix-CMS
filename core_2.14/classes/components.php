<?php

class components{

	public static function load( $module ){
		
		//Стандартные
		$system_prepares=array(
			'recs'=>array('function'=>'prepareRecs','title'=>'Список всех записей'),
			'parent'=>array('function'=>'prepareParent','title'=>'Родительская запись', 'hidden'=>true),
			'tags'=>array('function'=>'prepareTags','title'=>'Облако тегов раздела'),
			'map'=>array('function'=>'prepareMap','title'=>'Дерево раздела'),

			//Устаревщие
			'rec'=>array('function'=>'prepareRec','title'=>'Одна запись', 'hidden'=>true),
			'anons'=>array('function'=>'prepareAnons','title'=>'Анонс одной записи', 'hidden'=>true),
			'anonslist'=>array('function'=>'prepareAnonsList','title'=>'Анонс нескольких записей', 'hidden'=>true),
			'random'=>array('function'=>'prepareRandom','title'=>'Меню случайной записи', 'hidden'=>true),
			'randomlist'=>array('function'=>'prepareRandomList','title'=>'Меню списка случайных записей', 'hidden'=>true),
			'pages'=>array('function'=>'preparePages','title'=>'Страницы записей', 'hidden'=>true),
			'count'=>array('function'=>'prepareCount','title'=>'Подсчитать количество записей'),
		);

		//Предобъявленные в модуле
		foreach($system_prepares as $sid=>$prepare)
			if( !IsSet( $module->prepares[$sid] ) ){
				$module->prepares[$sid]=$prepare;
			}
		
		//Новый способ объявления компонент в функции
		if( method_exists($module, 'setComponents') )
			$module->setComponents();
		
		return $module->prepares;
	}
	
	//Запуск подготовки данных в компоненте
	public static function init( $module, $prepare, $params ){
		$result = false;
		
		if( IsSet( $module->prepares[$prepare] ) ){
		
			if( IsSet($module->prepares[ $prepare ]['function']) )
				$function_name=$module->prepares[ $prepare ]['function'];
			else
				$function_name=$module->prepares[ $prepare ]['control'];
			
			/*
				В параметры функции передаются 
				как значения из вызова в шаблоне, 
				так и описанные значения 
				в описании компонента
			*/
			if( IsSet( $module->prepares[ $prepare ]['params'] ) )
				$params = array_merge( @$module->prepares[ $prepare ]['params'], $params );
			
/* TODO: Разобраться почему три разных метода */
			
			if( is_callable( array( $module, $function_name ) ) ){
				$result = $module->$function_name($params);	

			}elseif( method_exists($module, $function_name) ){
				$result = call_user_func( array($this, $function_name), $params);	
				
			}elseif( method_exists( 'components', $funciton_name) ){
				$result = components::$function_name($params);
			}
			
			// Если в компоненте указан шаблон, в который выводить, то сохранем эту отметку
			if( $module->prepares[ $prepare ]['template'] )
				$result['template'] = $module->prepares[ $prepare ]['template'];
		
		}
		
		return $result;
	}

	

	//Анонс - последние N записей
	public function prepareRec($params){

		//Получаем условия
		$where=components::convertParamsToWhere($params);

		//Определяем структуру к которой обращается
		$structure_sid='rec';
		if(IsSet($params['structure_sid']))$structure_sid=$params['structure_sid'];

		//Забираем запись
		$rec=model::makeSql(
			array(
				'tables'=>array($this->getCurrentTable( $structure_sid )),
				'where'=>$where,
				'order'=>'order by `date_public` desc',
			),
			'getrow'
		);//pr(model::$last_sql);

		//Раскрываем сложные поля
		$rec=$this->explodeRecord($rec,$structure_sid);
		$rec=$this->insertRecordUrlType($rec);

		//Готово
		return $rec;
	}

	//Анонс - последние N записей
	public function prepareAnons($params){

		//Получаем условия
		$where=components::convertParamsToWhere($params);

		//Забираем запись
		$rec=model::makeSql(
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
		$where=components::convertParamsToWhere($params);

		//Условия отображения на сайте
		$where['and'][]='`shw`=1';
		if( IsSet($this->structure['rec']['fields']['show_in_anons']) )
			$where['and'][]='`show_in_anons`=1';

		//По умолчанию не более 100 записей
		if( !IsSet($params['limit']) )
			$params['limit'] = 'limit 100';
		
		//Забираем записи
		$recs=model::makeSql(
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
		$params['data'] = 'recs';
	
		//Получаем условия
		$where=components::convertParamsToWhere($params);
		
		//По умолчанию не более 100 записей
		if( !IsSet($params['limit']) )
			$params['limit'] = 'limit 100';
		
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
			$order=components::getOrderBy($structure_sid);

		//Требуется разбивка на страницы
		if( $params['chop_to_pages'] ){

			//Текущая страница
			$current_page = model::$ask->current_page;

			//Всего записей по запросу
			$num_of_records = model::execSql('select count(`id`) as `counter` from `'.$this->getCurrentTable($structure_sid).'` where '.implode(' and ', $where['and']) . ' and (' . ($where['or']?implode(' or ', $where['or']):'1') .')'.' and '.model::pointDomain().'','getrow');
			$num_of_records = $num_of_records['counter'];

			//Записей на страницу
			if(IsSet($params['items_per_page']))$items_per_page=$params['items_per_page'];
			elseif(IsSet(model::$settings['items_per_page']))$items_per_page=model::$settings['items_per_page'];
			else $items_per_page=10;

			//Количество страниц
			$num_of_pages = ceil( $num_of_records / $items_per_page );

			//Забираем записи
			$recs=model::makeSql(
				array(
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'order'=>$order,
					'limit'=>'limit '.($current_page*$items_per_page).', '.$items_per_page,
				),
				'getall'
			);//pr('1: ' . model::$last_sql);

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
				if( count(model::$ask->mode)>0 ){
					$modifiers='.'.implode('.', model::$ask->mode);
				}

				//Зацикливаем перелистывание страниц вправо и влево.
				if($current_page>0)$prev=$current_page-1;else $prev=$num_of_pages-1;
				if($current_page<$num_of_pages-1)$next=$current_page+1;else $next=0;

				//Предыдущая страница
				$pages['prev']['url'] = model::$ask->rec['url'].$modifiers.'.'.$prev.'.'.model::$ask->output_format.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['prev']['num'] = $prev;

				//Следующая страница
				$pages['next']['url'] = model::$ask->rec['url'].$modifiers.'.'.$next.'.'.model::$ask->output_format.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['next']['num'] = $next;

				//Другие страницы
				$hide_flag = false;
				for($i=0;$i<$num_of_pages;$i++){
					$pages['items'][$i]['url']=model::$ask->rec['url'].$modifiers.'.'.$i.'.'.model::$ask->output_format.($get_vars?'?'.implode('&', $get_vars):'');
					
					// Скрываем большие списки страниц
					if( (abs($i-$current_page) > 5) && ($i>0) && ($i+1<$num_of_pages) )
						$pages['items'][$i]['hide'] = true;
					else
						$pages['items'][$i]['hide'] = false;
					
					// Отмечаем пограничные страницы как страницы с троеточием
					if( $i && $pages['items'][$i-1]['hide'] && !$pages['items'][$i]['hide'] )
						$pages['items'][$i]['dots'] = true;
					
				}
			}

			//Заказанные наименования
			if( IsSet(model::$modules['basket']) && ($structure_sid == 'rec') )
				if( method_exists( model::$modules['basket'], 'insertOrdered' ) ){
					$recs = model::$modules['basket']->insertOrdered($recs);
				}

			//Результат
			$result=array(
				'current'	=>	$current_page,									//Номер текущей страницы
				'from'		=>	$current_page*$items_per_page,					//Номер первой записи на странице
				'till'		=>	($current_page+1)*$items_per_page,				//Номер последней записи на странице
				'limit'		=>	$items_per_page,								//Количество записей на странице
				'count'		=>	$num_of_records,		//Общее количество страниц
				'recs'		=>	$recs,											//Все записи на странице
				'pages'		=>	$pages,											//Страницы
			);
			
			if(!count($recs)){
				$result['recs'] = false;
				$result['pages'] = false;
			}
			
			//Готово
			return $result;

		//Без разбивки на страницы
		}else{

			$limit = false;
			if( is_int($params['limit']) )
				$limit = 'limit '.(IsSet($params['start'])?intval($params['start']).', ':'').intval($params['limit']);
			elseif( is_string($params['limit']) )
				$limit = mysql_real_escape_string($params['limit']);

			//Забираем записи
			$recs=model::makeSql(
				array(
					'tables'=>array($this->getCurrentTable($structure_sid)),
					'where'=>$where,
					'limit'=>$limit,
					'order'=>$order,
				),
				'getall'
			);//pr('2: ' . model::$last_sql);

			//Раскрываем сложные поля
			if($recs)
			foreach($recs as $i=>$rec){
				$rec=$this->explodeRecord($rec,$structure_sid);
				$rec=$this->insertRecordUrlType($rec, 'html', $params['insert_host']);
				$recs[$i]=$rec;
			}
			
			//Заказанные наименования
			if( IsSet(model::$modules['basket']) && ($structure_sid == 'rec') )
				if( method_exists( model::$modules['basket'], 'insertOrdered' ) ){
					$recs = model::$modules['basket']->insertOrdered($recs);
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
		$where=components::convertParamsToWhere($params);
		$where['and']['shw']='`shw`=1';

		//Получаем записи
		$res=model::makeSql(
			array(
				'tables' => array($this->getCurrentTable($structure_sid)),
				'fields' => array( 'count(`id`) as `counter`' ),
				'where' => $where,
			),
			'getrow'
		);//pr(model::$last_sql);

		//Готово
		return $res['counter'];
	}

	//Случайные записи
	public function prepareRandom($params){

		//Получаем условия
		$where=components::convertParamsToWhere($params);

		//Забираем запись
		$rec=model::makeSql(
			array(
				'tables'=>array($this->getCurrentTable('rec')),
				'where'=>$where,
				'order'=>'order by RAND()'
			),
			'getrow'
		);//pr(model::$last_sql);

		//Раскрываем сложные поля
		$rec=$this->explodeRecord($rec,'rec');
		$rec=$this->insertRecordUrlType($rec);

		//Готово
		return $rec;
	}

	//Случайные записи
	public function prepareRandomList($params){

		//Получаем условия
		$where=components::convertParamsToWhere($params);

		//По умолчанию не более 100 записей
		if( !IsSet($params['limit']) )
			$params['limit'] = 'limit 100';
		
		//Забираем запись
		$recs=model::makeSql(
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
		$link_field=model::$types[ $this->structure['rec']['dep_path']['link_type'] ]->link_field;

		//SID родителя
		$parent_sid=$params[$link_field];

		//Сортировка
		$order=$this->getOrderBy('rec');

		//Забираем запись
		$rec=model::makeSql(
			array(
				'tables'=>array($this->getCurrentTable($parent_structure_sid)),
				'where'=>array('and'=>array('`'.$link_field.'`="'.mysql_real_escape_string($parent_sid).'"')),
				'order'=>$order,
			),
			'getrow'
		);//pr(model::$last_sql);

		//Раскрываем сложные поля
		$rec=$this->explodeRecord($rec,'rec');
		$rec=$this->insertRecordUrlType($rec);

		//Готово
		return $rec;
	}

	//Родительский раздел текущей записи
	public function prepareMap($params){

		//Дерево
		if( !$params['module_sid'] )
			$params['module_sid'] = 'start';
		$recs=model::prepareShirtTree($params['module_sid'], 'rec', false,5,array('and'=>array('`shw`=1')));

		//Раскрываем сложные поля
		foreach($recs as $i=>$rec){
			$rec=$this->explodeRecord($rec,'rec');
			$rec=$this->insertRecordUrlType($rec);
			$recs[$i]=$rec;
		}

		//Готово
		return $recs;
	}

	//Список страниц
	public function preparePages($params){

		//Достаём общее количество записей
		if(IsSet($params['count'])){
			$recs['counter']=$params['count'];
		}else{
			$recs=model::execSql('select count(`id`) as `counter` from `'.$this->getCurrentTable(model::$ask->structure_sid).'` where `shw`=1 and '.model::pointDomain().'','getrow');
		}


		//Настройка для разбивки записей на страницы
		if(IsSet($params['limit']))$items_per_page=$params['limit'];
		elseif(IsSet(model::$settings['items_per_page']))$items_per_page=model::$settings['items_per_page'];
		else $items_per_page=10;

		//Текущая страница
		$page=intval(model::$ask->rec['page']);//current_page;

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
				if( count(model::$ask->mode)>0 ){
					$modifiers='.'.implode('.', model::$ask->mode);
				}

				//Зацикливаем перелистывание страниц вправо и влево.
				if($page>0)$prev=$page-1;else $prev=$pages['count']-1;
				if($page<$pages['count']-1)$next=$page+1;else $next=0;

				//Предыдущая страница
				$pages['prev']['url'] = model::$ask->rec['url'].$modifiers.'.'.$prev.'.'.model::$ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['prev']['num'] = $prev;

				//Следующая страница
				$pages['next']['url'] = model::$ask->rec['url'].$modifiers.'.'.$next.'.'.model::$ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				$pages['next']['num'] = $next;

				//Другие страницы
				for($i=0;$i<$pages['count'];$i++){
					$pages['items'][$i]['url']=model::$ask->rec['url'].$modifiers.'.'.$i.'.'.model::$ask->output.($get_vars?'?'.implode('&', $get_vars):'');
				}
			}
		}

		//Готово
		return $pages;
	}

	//Вход на сайт
	public function prepareTags($params){
		$tags=model::$types['tags']->getTagsCloud();
		return $tags;
	}

	//Переводим список переданных параметров в условия запроса
	public function convertParamsToWhere($params){
		
		//Условия из GET
		if($params['params_from_get'])
			$params = array_merge($_GET, $params);
			
		//Текущий вывод
		$prepare=$params['data'];

		//Определяем структуру к которой обращается
		$structure_sid='rec';
		if(IsSet($params['structure_sid']))$structure_sid=$params['structure_sid'];

		//Разрешённые параметры в каждом из выводов
		$allowed_params=array(
			'anons'=>array('nid','dir','access'),
			'anonslist'=>array('nid','dir','access','limit','start'),
			'recs'=>array('nid','dir','access','limit','start'),
			'rec'=>array('nid','dir','access','limit','start'),
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
				}elseif( ($var=='dir') and ($val !== false) ){
					//Простые структуры
					if($this->structure[$structure_sid]['type']=='simple')
						$field_name='dep_path_'.$this->structure[$structure_sid]['dep_path']['structure'];
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
						$where['and']['access']='`access` LIKE "%|'.mysql_real_escape_string( user::$info['group'] ).'=r__|%"';
				}
			}

			//Для других полей, объявленных в структуре
			//Flag для того, чтобы исключить повторное добавление системных полей
			if(!$flag){
				if(IsSet($this->structure[$structure_sid]['fields'][$var])){
					if( in_array( $this->structure[$structure_sid]['fields'][$var]['type'] ,array('menum','linkm') ) ){
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
						
					//за Сегодня
					}elseif( $val === 'today' ){
						$where['and'][$var]='`'.$var.'`>"'.date("Y-m-d").'"';

					//за 24 часа
					}elseif( $val === 'day' ){
						$where['and'][$var]='`'.$var.'`>"'.date("Y-m-d H:i:s", strtotime("-24 hours")).'"';

					//за Сегодня
					}elseif( $val === 'yesterday' ){
						$where['and'][$var]='TO_DAYS(`'.$var.'`) = TO_DAYS("'.date("Y-m-d", strtotime("-1 day")).'")';

					//за Неделю
					}elseif( $val === 'week' ){
						$where['and'][$var]='`'.$var.'`>"'.date("Y-m-d", strtotime("-1 week")).'"';

					//за Месяц
					}elseif( $val === 'month' ){
						$where['and'][$var]='`'.$var.'`>"'.date("Y-m-d", strtotime("-1 month")).'"';

					//за Год
					}elseif( $val === 'year' ){
						$where['and'][$var]='`'.$var.'`>"'.date("Y-m-d", strtotime("-1 year")).'"';

					}else{
						if(is_array($val)){
							foreach($val as $i=>$v)if(!strlen($v))UnSet($val[$i]);
							if($val[0])
								$where['and'][$var] = '((`'.$var.'`="'.implode('") or (`'.$var.'`="', $val).'") )';
						}elseif( $val !== false ){
							$where['and'][$var] = '`'.$var.'`="'.mysql_real_escape_string($val).'"';
						}
					}
				
				// Прямая вставка SQL-запроса
				}elseif( ($var == 'sql') || ($var == 'where') ){
					$where['and'][] = $val;
				
				// Гео-привязка
				// Работает только при установленном модуле городов с доступным методом "myCity"
				}elseif( ($var == 'city') && ($val == 'current') ){
					if( IsSet( model::$modules['city']) ){
						$city = model::$modules['city']->myCity();
						$where['and']['city'] = '( (`city` LIKE "%|'.mysql_real_escape_string( $city['id'] ).'|%") || (`region` LIKE "%|'.mysql_real_escape_string( $city['region'] ).'|%") || (`macroregion` LIKE "%|'.mysql_real_escape_string( $city['macroregion'] ).'|%") )';
					}
				}
			}
		}

		// Прямая передача запроса
		
		//Готово
		return $where;
	}

	//Сортировка по умолчанию
	public function getOrderBy($structure_sid){
		//Сортировка деревьев
		if($this->structure[$structure_sid]['type']=='tree')return 'order by `left_key`';
		//Сортирвка по POS
		elseif(IsSet($this->structure[$structure_sid]['fields']['pos']))return 'order by `pos`,`title`';
		//Сортировка по публичной дате
		else return 'order by `date_public` desc,`title`';
	}

	
}

?>