<?php

/************************************************************/
/*                              */
/*  Ядро системы управления Asterix  CMS           */
/*    Класс ведения системного лога           */
/*                              */
/*  Версия ядра 2.0.b5                    */
/*  Версия скрипта 0.01                    */
/*                              */
/*  Copyright (c) 2009  Мишин Олег             */
/*  Разработчик: Мишин Олег                 */
/*  Email: dekmabot@gmail.com               */
/*  WWW: http://mishinoleg.ru               */
/*  Создан: 10 февраля 2009  года              */
/*  Модифицирован: 25 сентября 2009 года         */
/*                              */
/************************************************************/

class log
{
  var $sql = array();
  
  var $errors = array('file_not_found' => array('title' => 'Файл не найден', 'action' => 'stop'));
  
  //Запуск
  public function __construct($config = array())
  {
    //Помним настройки
    $this->config = $config;
    
    //Старт работы
    $this->setStart();
  }
  
  //Обработать ошибку
  public function err($code, $vars)
  {
    //Оповещение
    pr_r($this->errors[$code]);
    if (is_array($vars))
      pr_r($vars);
    else
      pr($vars);
    
    //Действие
    if ($this->errors[$code]['action']) {
      //Остановка
      if ($this->errors[$code]['action'] == 'stop') {
        pr('Остановка по коду ошибки: ' . $code);
        exit();
      }
      
    }
    
    
  }
  
  
  //Начало ведения статистики
  public function setStart()
  {
    $t                = explode(' ', microtime());
    $this->time_start = $t[1] + $t[0];
    $this->last_step  = $this->time_start;
  }
  
  //Окончание работы
  public function setStop()
  {
    //Время выполнения
    $t               = explode(' ', microtime());
    $this->time_stop = $t[1] + $t[0];
    
    //Затраченная память
    $this->memory_total = memory_get_usage() / 1024 / 1024;
  }
  
  //Окончание работы
  public function step($title)
  {
    if (@$this->config['settings']['time_trace']) {
      //Значения выше границы показываются красным цветом
      $red_level     = 0.005;
      $red_level_mem = 0.5;
      
      //Время выполнения
      $t   = explode(' ', microtime());
      $now = $t[1] + $t[0];
      
      //Затраченная память
      $this->memory_total = memory_get_usage() / 1024 / 1024;
      
      //Показываем сообщения из памяти
      if (count($this->memory)) {
        foreach ($this->memory as $i => $mem) {
          pr($mem['title'] . ' (из памяти ' . ($i + 1) . '): ' . ($mem['time'] > $red_level ? '<b style="color:red">' : '') . number_format($mem['time'], 5, '.', ' ') . ($mem['time'] > $red_level ? '</b>' : '') . ' секунд, итого '.number_format($mem['time_total'], 5, '.', ' ').' секунд, [память: ' . number_format($mem['memory'], 5, '.', ' ') . ' МБ, <span style="' . ($mem['memory_diff'] > 0 ? (abs($mem['memory_diff']) > $red_level_mem ? 'font-weight:bold; color:red' : '') : 'font-weight:bold; color:green') . '">' . ($mem['memory_diff'] > 0 ? '+' : '') . number_format($mem['memory_diff'], 5, '.', ' ') . '</b> МБ].');
        }
        $this->memory = array();
      }
      
      //Общее время выполнения
      $time_total     = $now - $this->time_start;

      //Итого разница во времени
      $diff     = $now - $this->last_step;
      $diff_mem = $this->memory_total - $this->last_step_mem;
      if (abs($diff_mem) < 0.0001)
        $diff_mem = 0;
      
      //Выводим
      pr($title . ': ' . ($diff > $red_level ? '<b style="color:red">' : '') . number_format($diff, 5, '.', ' ') . ($diff > $red_level ? '</b>' : '') . ' секунд, итого '.number_format($time_total, 5, '.', ' ').' секунд, [память: ' . number_format($this->memory_total, 5, '.', ' ') . ' МБ, <span style="' . ($diff_mem > 0 ? (abs($diff_mem) > $red_level_mem ? 'font-weight:bold; color:red' : '') : 'font-weight:bold; color:green') . '">' . ($diff_mem > 0 ? '+' : '') . number_format($diff_mem, 5, '.', ' ') . '</b> МБ].');
      
      //Память
      $this->last_step     = $now;
      $this->last_step_mem = $this->memory_total;
    }
  }
  
  //Окончание работы
  public function step_mem($title, $module = false, $prepare = false)
  {
    if (@$this->config['settings']['time_trace']) {
      //Значения выше границы показываются красным цветом
      $red_level     = 0.005;
      $red_level_mem = 0.5;
      
      //Время выполнения
      $t   = explode(' ', microtime());
      $now = $t[1] + $t[0];
      
      //Затраченная память
      $this->memory_total = memory_get_usage() / 1024 / 1024;
      
      //Общее время выполнения
      $time_total     = $now - $this->time_start;

      //Итого разница во времени
      $diff     = $now - $this->last_step;
      $diff_mem = $this->memory_total - $this->last_step_mem;
      if (abs($diff_mem) < 0.0001)
        $diff_mem = 0;
      
      //Складываем в память, чтоб не выводитть посреди шаблона
      $this->memory[] = array(
        'title' => $title . ' [' . $module . '|' . $prepare . ']',
        'time' => $diff,
        'time_total' => $time_total,
        'memory' => $this->memory_total,
        'memory_diff' => $diff_mem
      );
      
      //Память
      $this->last_step     = $now;
      $this->last_step_mem = $this->memory_total;
    }
  }
  
  //SQL-запрос
  public function sql($sql, $time = false, $result = false, $module = false, $function = false)
  {
    if ($this->config['settings']['show_stat'])
      $this->sql[] = array(
        'sql' => $sql,
        'time' => $time,
        'result' => count($result),
        'module' => $module,
        'function' => $function
      );
/*      
    //Пишем лог в файл
    if($this->model->user->info['id']){
      $path = $this->model->config['path']['core'] . '/../logs/sql/sql_' . date("Ymd_H") . '.log';
      $f    = fopen($path, 'a+');
      fwrite($f, date("Y-m-d H:i:s") . ' | core=2.06 | user=' . $this->model->user->info['id'] . ' | '.iconv('utf-8', 'cp1251', $sql).' | ask=http://' . $_SERVER['HTTP_HOST'] . iconv('utf-8', 'cp1251', $this->model->ask->original_url) . "\r\n");
      fclose($f);
    }
*/    
    
  }
  
  //Показать статистику
  public function showStat()
  {
    $this->setStop();
    
    global $user_ip;
/*    
    //Пишем лог в файл
    $path = $this->config['path']['core'] . '/../logs/get/get_' . date("Ymd_H") . '.log';
    $f    = fopen($path, 'a+');
    fwrite($f, date("Y-m-d H:i:s") . ' | core=2.06 | gen=' . number_format($this->time_stop - $this->time_start, 5, '.', ' ') . ' | mem=' . number_format($this->memory_total, 2, '.', ' ') . ' | ' . $user_ip . ' | ask=http://' . $_SERVER['HTTP_HOST'] . iconv('utf-8', 'cp1251', $this->model->ask->original_url) . "\r\n");
    fclose($f);
*/    
    if ($this->config['settings']['show_stat'] == 'shirt') {
      $this->setStop();
      pr('Генерация заняла ' . number_format($this->time_stop - $this->time_start, 5, '.', ' ') . ' секунд, использовано ' . number_format($this->memory_total, 2, '.', ' ') . ' мегабайт памяти, сделано ' . count($this->sql) . ' запросов, кеширование '.($this->model->config['cache']?'включено ('.$this->model->config['cache']['type'].')':'отключено').'.');
      
      
    } elseif ($this->config['settings']['show_stat'] == 'all') {
      pr('Генерация страницы: ' . number_format($this->time_stop - $this->time_start, 5, '.', ' ') . ' секунд.');
      pr('Использовано памяти: ' . number_format($this->memory_total, 2, '.', ' ') . ' мегабайт памяти.');
      pr_r($this->sql);
      
      $desc   = array();
      $unique = array();
      $time   = false;
      foreach ($this->sql as $q) {
        $desc[] = $q['sql'].' [time:'.$q['time'].']';
        if (!in_array($q['sql'], $unique))
          $unique[] = $q['sql'];
        $time += $q['time'];
      }
      sort($desc);
      
      pr('Все вопросы');
      pr_r($desc);
      pr('Уникальные запросы');
      pr_r($unique);
      pr('Суммарное время запросов: ' . $time . ' секунд.');
    }
    
  }
  
  //Зарегистрировать выполнение действия в системе
  public function logAction($type, $action, $link, $success, $before, $url)
  {
    /*
    Категории записей в логе:
    add - добавление записи +
    upd - изменеине записи +
    mov - перемещение записи
    del - удаление записи +
    err - возникновение ошибки при работе
    auth - попытка авторизации
    */
    
    //Новая запись в логе
    $rec = array(
      'domain' => $before['domain'],
      'ln' => '1',
      'type' => $type,
      'user' => $this->model->user->info['id'],
      'date' => date("Y-m-d H:i:s"),
      'action' => $action,
      'title' => $before['title'],
      'url' => $url,
      'link' => $link,
      'bkp' => serialize($before),
      'sql' => $this->model->last_sql,
      'controller' => $this->model->ask->method,
      'success' => $success,
      'canceled' => false,
      'access' => '|admin=rwd|moder=r--|all=r--|'
    );
    
    $what = array();
    foreach ($rec as $var => $val)
      $what[] = '`' . $var . '`="' . mysql_real_escape_string($val) . '"';
    $sql = 'insert into `bkp` set ' . implode(', ', $what) . '';
    //    pr($sql);
    $this->model->execSql($sql, 'insert');
    
  }
  
  //Ведение глобальной статистики
  public function globalStat()
  {
/*
    //Если включен подсчёт статистики
    if ($this->model->config['settings']['global_stat']) {
      $project    = $this->model->extensions['domains']->domain['host'];
      $user_ip    = GetUserIP();
      $stat_url   = $_SERVER['REQUEST_URI'];
      $referer    = urlencode($_SERVER['HTTP_REFERER']);
      $user_agent = urlencode($_SERVER['HTTP_USER_AGENT']);
      $session_id = urlencode(session_id());
      $user_login = urlencode($this->model->user->info['id']);
      $path       = 'http://stat.sitko.ru/stat.php?project=' . $project . '&ip=' . $user_ip . '&url=' . $stat_url . '&referer=' . $referer . '&browser=' . $browser . '&browser_version=' . $browser_version . '&user_agent=' . $user_agent . '&session_id=' . $session_id . '&user_login=' . $user_login . '';
      $f          = file_get_contents($path);
    }
*/
  }
  
}

?>