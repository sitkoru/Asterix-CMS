<?php

/************************************************************/
/*                              */
/*  Ядро системы управления Asterix  CMS           */
/*    Интерфейс работы с СУБД MySQL           */
/*                              */
/*  Версия ядра 2.0.b5                    */
/*  Версия скрипта 1.00                    */
/*                              */
/*  Copyright (c) 2009  Мишин Олег             */
/*  Разработчик: Мишин Олег                 */
/*  Email: dekmabot@gmail.com               */
/*  WWW: http://mishinoleg.ru               */
/*  Создан: 10 февраля 2009  года              */
/*  Модифицирован: 25 сентября 2009 года         */
/*                              */
/************************************************************/

global $active_mysql_connection;
$active_mysql_database=false;

class mysql extends database{
  public $title = 'ACMS Класс работы с базой данных MySQL';
  public $version = '1.0';
  
  public $type='mysql';
  public $active;

  public $query_counter=0;
  public $query_log=array();
  public $name;

  public function Connect($host,$user,$password,$name){
    $this->name=$name;
    $this->connection=@mysql_connect($host,$user,$password) or $this->error('connection');
  }
  
  public function PConnect($host,$user,$password,$name){
    $this->name=$name;
    $this->connection=@mysql_pconnect($host,$user,$password) or $this->error('connection');
  }
  
  public function GetAll($sql){
    global $active_mysql_database;
    
    if(func_num_args()>1){
      $t=func_num_args();
      pr($sql);
    }
    
    if($active_mysql_database!=$this->name)$this->activate();
    $items=array();
    if($result=mysql_query($sql)){
      while($row = mysql_fetch_array($result,MYSQL_ASSOC))$items[]=$row;
      mysql_free_result($result);
    }
    else $this->error($sql);
    
    $this->query_counter++;
    $this->query_log[]=$sql;
    return $items;
  }
  
  public function GetRow($sql){
    global $active_mysql_database;
    
    if(func_num_args()>1){
      $t=func_num_args();
      pr($sql);
    }
    
    if($active_mysql_database!=$this->name)$this->activate();
    $row=array();
    if($result=mysql_query($sql)){
      $row = mysql_fetch_array($result,MYSQL_ASSOC);
      mysql_free_result($result);
    }
    else $this->error($sql);
    
    $this->query_counter++;
    $this->query_log[]=$sql;
    
    return $row;
  }
  
  public function Execute($sql){
    
    if(func_num_args()>1){
      $t=func_num_args();
      pr($sql);
    }
    
    $result=mysql_query($sql);
    if(!$result)$this->error($sql);

    $this->query_counter++;
    $this->query_log[]=$sql;
    return $result;
  }
  
  public function error($sql){
//    pr( $sql.'<br />'.mysql_errno() . ": " . mysql_error() );
  }
  
}

/*
Разработка: Мишин Олег.
Email: mishinoleg@mail.ru
Web: http://www.mishinoleg.ru/
*/

?>