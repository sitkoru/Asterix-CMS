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
	public function __construct($model){
		$this->model = $model;
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
    
	}
  
	//Функция, которая будет загружать наши данные
	public function preloadData($params, &$smarty)
	{

		//Вызов происходит не по названию модуля, а по прототипу
		if (IsSet($params['prototype'])) {
			$module_sid       = $this->model->getModuleSidByPrototype($params['prototype']);
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
					$result        = model::$modules[$params['module']]->prepareInterface($function_name, $params);
					if( $result )
						//Записываем в шаблонизатор
						$this->tmpl->assign($params['result'], $result);
				}
			}
		//Подгрузка данных Социального Графа
		} elseif (IsSet($params['graph']) && IsSet($this->model->extensions['graph']) ) {
			//Имя интерфейса, которую будем вызывать
			$function_name = $params['graph'];
			//Запрашиваем данные
			$result        = $this->model->extensions['graph']->askFromTemplate($function_name, $params);
			//Записываем в шаблонизатор
			$this->tmpl->assign($params['result'], $result);
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
		$result = default_controller::addUserCSS( $params['val'] );
		$this->tmpl->assign($params['result'], $result);
	}
	public function addJS($params, &$smarty){
		$result = default_controller::addUserJS( $params['val'] );
		$this->tmpl->assign($params['result'], $result);
	}
	public function unserialize($params, &$smarty){
		$result = unserialize( $params['value'] );
		pr_r($result);
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
  public function fetch($filename, $template_from_other_pack = false)
  {
    $filename = $this->correctTemplatePackPath($filename, $template_from_other_pack);
    if ($filename) {
      return $this->tmpl->fetch($filename);
    }
    return false;
  }
  
}

?>