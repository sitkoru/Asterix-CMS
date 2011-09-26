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

class templater
{
  //Запускаемсо
  public function __construct($model)
  {
    $this->model = $model;
    $this->paths = $this->model->config['path'];
    include_once($this->model->config['path']['libraries'] . '/Smarty-3.0.8/libs/Smarty.class.php');
    
    $this->tmpl               = new Smarty;
    $this->tmpl->template_dir = $this->model->config['path']['templates'];
    $this->tmpl->compile_dir  = $this->model->config['path']['templates'] . '/c/';
    
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
    
    @$this->tmpl->register_modifier('stat', array(
      $this,
      'statData'
    ));
    
    
    //Регистрируем функцию подсчёта просмотров
    @$this->tmpl->register_modifier('count_view', array(
      $this,
      'countView'
    ));
    
    //Регистрируем функцию подсчёта переходов по ссылкам
    @$this->tmpl->register_modifier('count_click', array(
      $this,
      'countClick'
    ));
    
    //Регистрируем функцию подсчёта переходов по ссылкам
    @$this->tmpl->register_modifier('count_click_js', array(
      $this,
      'countClickJS'
    ));

    //Регистрируем функцию подсчёта просмотров
    @$this->tmpl->register_function('preload_stat', array(
      $this,
      'preloadStat'
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
    
    //Если модуль указан верно
    if (IsSet($params['module'])){
      if (IsSet($this->model->modules[$params['module']])) {
        //Подгрузка данных
        if (IsSet($params['data'])) {
          //Имя функции, которую будем вызывать
          $function_name = $params['data'];
          //Запрашиваем данные
          $result        = $this->model->modules[$params['module']]->prepareData($function_name, $params);
          //Записываем в шаблонизатор
          $this->tmpl->assign($params['result'], $result);
          
          //Учитываем действие в логе
          $this->model->log->step_mem('Подгрузка данных в шаблоне', $params['module'], $function_name);
          
        //Подгрузка интерфейса
        } elseif (IsSet($params['interface'])) {
          //Имя интерфейса, которую будем вызывать
          $function_name = $params['interface'];
          //Запрашиваем данные
          $result        = $this->model->modules[$params['module']]->prepareInterface($function_name, $params);
          if( $result )
			  //Записываем в шаблонизатор
			  $this->tmpl->assign($params['result'], $result);
          
          //Учитываем действие в логе
          $this->model->log->step_mem('Подгрузка интерфейса в шаблоне', $params['module'], $function_name);
        
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
      
      //Учитываем действие в логе
      $this->model->log->step_mem('Обращение к графу', $params['module'], $function_name);
    }
  }
  
  //Функция, которая сортирует деревья и поддеревья по заданному ключу
  public function treesortData($params, &$smarty){
    
    //Подгрузка библиотеки
    include_once($this->model->config['path']['libraries'] . '/tree_sort.php');
    
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
  
  //Функция, которая будет обрезать текст до нужной длины
  public function statData($value, $company_id)
  {
    return '" OnClick="document.location=\'/click.php?from='.$this->model->ask->rec['url'].'&company='.$company_id.'&link='.$value.'\'; return false;';
//    return '/click.php?from='.$this->model->ask->rec['url'].'&company='.$company_id.'&link='.$value.'';
  }
  
  
  

  //Регистрируем функцию подсчёта просмотров
  //Отобразим JavaScript, показывающий картинку в один пиксель, которая будет запрашиваться со скрипта /stat.php
  public function countView($value, $gtaph_top_text = false, $anons = false, $label = false){//Передаем любой элемент
    //Готовими код, который будет приклеен к подсчитываемому элементу
    $plus_code = '<img src="/stat.php?a=v&i='.$gtaph_top_text.'|'.intval($anons).'&l='.$label.'&rand='.rand(0,1000).'" alt="stat" style="position:absolute; z-index:-1000; width:1px; height:1px;" />';
    //Приклеиваем код
    $value .= $plus_code;
    //Готово
    return $value;    
  }
  
  //Регистрируем функцию подсчёта переходов по ссылкам
  //Переделаем ссылку с записи, которая будет вести на /stat.php и передавать параметры что посчитать и куда направить после.
  public function countClick($value, $gtaph_top_text = false, $anons = false, $label = false, $urlencode = false){//Передаем ссылку
    //Готовим сслыку, которая будет вести статистику
    $value = '/stat.php?h='.urlencode($value).'&a=c&i='.$gtaph_top_text.'&l='.$label;
    //Дополнительное кодирование - применяется когда URL уже передаётся параметром
    if( $urlencode )
      $value = urlencode($value);
    //Готово
    return $value;    
  }
  
  //Регистрируем функцию подсчёта переходов по ссылкам
  //Переделаем ссылку с записи, которая будет вести на /stat.php и передавать параметры что посчитать и куда направить после.
  public function countClickJS($value, $graph_top_text = false, $anons = false, $label = false, $urlencode = false){//Передаем ссылку
    //Готовим сслыку, которая будет вести статистику
//        $value = '/stat.php?h='.urlencode($value).'&a=c&i='.$graph_top_text.'&l='.$label;
        $value = '" OnClick="document.location.href=\'/stat.php?h='.urlencode($value).'&a=c&i='.$graph_top_text.'&l='.$label.'\'; return false;';
    //Дополнительное кодирование - применяется когда URL уже передаётся параметром
    if( $urlencode )
      $value = urlencode($value);
    //Готово
    return $value;
  }

  //Функция, которая будет загружать наши данные
  public function preloadStat($params, &$smarty)
  {
        if(in_array($params['type'],array('click','view')))
            $type=$params['type'];
        else
            $type='view';

        //Вывести все записи
        if( $params['data'] == 'recs' ){
            $stat = $this->model->execSql('select * from `stat_'.$type.'`'.($params['where']?' where '.$params['where']:'').($params['order']?' '.$params['order']:'').'', 'getall');
            $result = $stat;

        //Количество записей
        }elseif($params['data'] == 'count'){
            $stat = $this->model->execSql('select COUNT(`id`) as `counter` from `stat_'.$type.'`'.($params['where']?' where '.$params['where']:'').($params['order']?' '.$params['order']:'').'', 'getrow');
            $result = intval($stat['counter']);

        //Сумма записей
        }elseif($params['data'] == 'sum'){
            $stat = $this->model->execSql('select SUM(`count`) as `counter` from `stat_'.$type.'`'.($params['where']?' where '.$params['where']:'').($params['order']?' '.$params['order']:'').'', 'getrow');
            $result = intval($stat['counter']);
        }

        //Записываем в шаблонизатор
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