<?php

/************************************************************/
/*                              							*/
/*  Ядро системы управления Asterix  CMS          			*/
/*    Интерфейс работы с шаблонизатором         			*/
/*                              							*/
/*  Версия ядра 2.0                     					*/
/*  Версия скрипта 1.1                   					*/
/*                              							*/
/*  Copyright (c) 2009  Мишин Олег             				*/
/*  Разработчик: Мишин Олег                 				*/
/*  Email: dekmabot@gmail.com               				*/
/*  WWW: http://mishinoleg.ru               				*/
/*  Создан: 10 февраля 2009  года              				*/
/*  Модифицирован: 8 апреля 2010 года            			*/
/*                              							*/
/************************************************************/

class templater{

	private $version = 'Smarty-3.0.8';

	//Запускаемсо
	public function __construct(){
		$this->paths = model::$config['path'];
		include_once(model::$config['path']['core'] . '/../libs/'.$this->version.'/libs/Smarty.class.php');
		
		$this->tmpl               = new Smarty;
		$this->tmpl->template_dir = model::$config['path']['templates'];
		$this->tmpl->compile_dir  = model::$config['path']['templates'] . '/c/';
		
		//Обработчик специальных символов
		@$this->tmpl->register_modifier('mb_lower', 'mb_strtolower');
		
		//Пишем свои функции и модификаторы
		@$this->tmpl->register_function('preload', array(
			$this,
			'preloadData'
		));
		
		@$this->tmpl->register_function('treesort', array(
			$this,
			'treesortData'
		));

		@$this->tmpl->register_function('numeric', array(
			$this,
			'numeric'
		));

		@$this->tmpl->register_modifier('cut', array(
			$this,
			'cutData'
		));

		@$this->tmpl->register_function('addjs', array(
			$this,
			'addJS'
		));

		@$this->tmpl->register_function('addcss', array(
			$this,
			'addCSS'
		));
    
		@$this->tmpl->register_function('unserialize', array(
			$this,
			'unserialize'
		));
    
		@$this->tmpl->register_function('admin', array(
			$this,
			'admin'
		));
    
	}
  
	//Функция, которая будет загружать наши данные
	public function preloadData($params, &$smarty)
	{

		$t                	= explode(' ', microtime());
		$time_start 	= $t[1] + $t[0];
		$sql_start = count( log::$sql );

		//Вызов происходит не по названию модуля, а по прототипу
		if (IsSet($params['prototype'])) {
			$module_sid       = model::getModuleSidByPrototype($params['prototype']);
			$params['module'] = $module_sid;
			UnSet($params['prototype']);
		}
		if( IsSet($params['module']) )
			if( !$params['module'] )
				$params['module'] = 'start';
    
		//Если модуль указан верно
		if (IsSet($params['module'])){
			if (IsSet( model::$modules[ $params['module'] ] ) ) {
				
				//Подгрузка данных
				if (IsSet($params['data'])) {
				
					//Имя функции, которую будем вызывать
					$component_name = $params['data'];

					//Запрашиваем данные
					$result        = model::$modules[ $params['module'] ]->prepareComponent($component_name, $params);

					//Записываем в шаблонизатор
					$this->tmpl->assign($params['result'], $result);

				//Подгрузка интерфейса
				} elseif (IsSet($params['interface'])) {
					//Имя интерфейса, которую будем вызывать
					$function_name = $params['interface'];
					//Запрашиваем данные
					$result        = model::$modules[ $params['module'] ]->prepareInterface($function_name, $params);
					if( $result )
						//Записываем в шаблонизатор
						$this->tmpl->assign($params['result'], $result);
				}
			}
		//Подгрузка данных Социального Графа
		} elseif (IsSet($params['graph']) && IsSet(model::$extensions['graph']) ) {
			//Имя интерфейса, которую будем вызывать
			$function_name = $params['graph'];
			//Запрашиваем данные
//			$result        = model::$extensions['graph']->askFromTemplate($function_name, $params);
			//Записываем в шаблонизатор
			$this->tmpl->assign($params['result'], $result);
		}

		$t               	= explode(' ', microtime());
		$time_stop 	= $t[1] + $t[0];
		$sql_stop = count( log::$sql );
		if( model::$settings['show_stat'] == 'all' ){
			$id = str_replace('.', '', 'log_'.microtime(true) );
			$time = number_format($time_stop - $time_start, 5, '.','');
			$s = $sql_stop-$sql_start;
			$q = '';
			for( $i=$sql_start; $i<$sql_stop; $i++ )
				$q .= $i.': '.log::$sql[ $i ]['sql'].' <sub>'.number_format(log::$sql[ $i ]['time'], 5, '.', ' ').' секунд, '.log::$sql[ $i ]['result'].' найдено</sub><br />';
			pr('
				Preload '.$params['module'].'->'.$params['data'].': 
				' . ($time>0.01?'<span style="color:red">':'') . $time . ' секунд' . ($time>0.01?'</span>':'') . ', 
				<span style="'.($s>10?'color:red':'').'" OnClick="$(\'#'.$id.'\').toggle(\'fast\'); return false;">'.($s).'<sub>/'.count(log::$sql).'</sub> запросов</span>.
				<div id="'.$id.'" style="
					display:none;
					white-space:nowrap;
					position: absolute;
					background: white; 
					z-index:1000;
					border-radius: 10px;
					padding: 10px;
					border: 2px solid grey;
				">'.$q.'</div>
			');
		}

	}
  
	//Функция, которая сортирует деревья и поддеревья по заданному ключу
	public function treesortData($params, &$smarty){
    
		//Подгрузка библиотеки
		include_once(model::$config['path']['core'] . '/../libs/tree_sort.php');
		
		//Сортировка
		$result = prepareTreeSort( $params );
		
		//Записываем в шаблонизатор
		$this->tmpl->assign($params['result'], $result);
  }
  
  //Согласование форм с числительными (value, form1, form2, form5)
  public function numeric($params, &$smarty){
    $n = intval($params['value']);
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $params['form5'];
    if ($n1 > 1 && $n1 < 5) return $params['form2'];
    if ($n1 == 1) return $params['form1'];
    return $params['form5'];
  }
  
  //Функция, которая будет обрезать текст до нужной длины
  public function cutData($value, $length, $right = false)
  {
    mb_internal_encoding('utf-8');
    
    //Обрезаем
    if (mb_strlen($value) > $length) {
      
      //Убираем теги
      $value=strip_tags($value);
      
      //Разбиваем относительно заданной границы
      $left_part  = mb_substr($value, 0, $length);
      $right_part = mb_substr($value, $length);
      
      //Урезаем в большую сторону
      if ($right) {
        $right_part = mb_substr($right_part, 0, mb_strpos($right_part, ' '));
        
        //Урезаем в меньшую сторону
      } else {
        $left_part  = mb_substr($left_part, 0, mb_strrpos($left_part, ' ') + 1);
        $right_part = '';
      }
      
      //Склеиваем
      $new_value = $left_part . $right_part;
      
      //...
      $new_value .= '...';
      
      //Готово
      return $new_value;
    } else
      return $value;
  }
  

	public function addCSS($params, &$smarty){
		$val = explode("\n", $params['val']);
		foreach($val as $i=>$value)
			if( strlen( $value ) ){
				$value = trim( $value );
				default_controller::$add['css'][] = array(
					'path' => $value,
				);
			}
		$this->tmpl->assign('head_add', default_controller::$add);
	}
	public function addJS($params, &$smarty){
		$val = explode("\n", $params['val']);
		foreach($val as $i=>$value)
			if( strlen( $value ) ){
				$value = trim( $value );
				default_controller::$add['js'][] = array(
					'path' => $value,
				);
			}
		$this->tmpl->assign('head_add', default_controller::$add);
	}
	public function unserialize($params, &$smarty){
		$result = unserialize( htmlspecialchars_decode( $params['value'] ) );
		$this->tmpl->assign($params['result'], $result);
	}

	public function admin($params, &$smarty){
		
		include_once(model::$config['path']['core'] . '/controllers/admin.php');
		$result = controller_admin::templateExec( $params );
		$this->tmpl->assign($params['result'], $result);
	}
  
  //Функция, которая будет загружать наши данные
  public function cache($params, &$smarty)
  {
    pr_r($params);
  }
  
  //Записываем данные в шаблонизатор
  public function assign($sid, $value)
  {
    $this->tmpl->assign($sid, $value);
  }
  
  //Изменяем путь к файлу шаблона в зависимости от используемого пакета.
  public function correctTemplatePackPath($filename, $template_from_other_pack)
  {
    //Если нужно взять стандартный шаблон из внешнего пакета
    if ($template_from_other_pack)
    //Если путь к такому шаблону есть в конфиге
      if (IsSet($this->paths[$template_from_other_pack]))
      //ну и если сам файл существует и доступен
        if (file_exists($this->paths[$template_from_other_pack] . '/' . $filename))
        //Только тогда прописываем путь к другому шаблоону
          return $this->paths[$template_from_other_pack] . '/' . $filename;
    return $filename;
  }
  
	//Вставляем все данные в шаблон
	public function fetch($filename, $template_from_other_pack = false){
		
		$filename = $this->correctTemplatePackPath($filename, $template_from_other_pack);
		
		if ($filename){
			return $this->tmpl->fetch( $filename );
		}
			
		return false;
	}
  
}

?>