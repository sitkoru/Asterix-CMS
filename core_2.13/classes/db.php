<?php

/************************************************************/
/*															*/
/*	ࠤ𮠱鲲死 󯰠㬥 Asterix	CMS						*/
/*		ͥ禰 ⩡쩮򥪠ғ								*/
/*															*/
/*	å𱨿 󤰠 2.0											*/
/*	å𱨿 񪰨ೠ 1.00										*/
/*															*/
/*	Copyright (c) 2009  ͨ𨭠ϫ棉						*/
/*	Ѡ豠⯲󨪺 ͨ𨭠ϫ棉								*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Ү襠򸀱0 𥢰ᬿ 2009	䯤ɉ					*/
/*	ͮ婴鷨𮢠򸀲5 񥭲󡰿 2009 䯤ɉ			*/
/*															*/
/************************************************************/

global $config;

//Ю奥𦨢᦬󥠴ﱬ᳻ ⡧ 塭
$supported_databases = array(
	false => array(
		'mysql' => 'mysql.php'
	),
	'ado' => array(
		'mysql' => 'adodb5/adodb.inc.php'
	)
);

//˫Ჱ 󯰠㬥 ⡧᭨ 塭
class database
{
	public function __construct($model)
	{
		$this->model = $model;
		$this->connection = false;
		$this->active_database = false;
	}
	
	public function activate()
	{
		mysql_select_db($this->name, $this->connection);
		$this->active_database = $this->name;
	}
	
}

//ɭ鷨ᬨ衶鿠⡧ 塭
$db = array();
foreach ($config['db'] as $name => $one) {
	if (IsSet($supported_databases[$one['lib_pack']][$one['type']])) {
		$config['db'][$name]['supported'] = true;
		
		require_once($config['path']['libraries'] . '/' . $supported_databases[$one['lib_pack']][$one['type']]);
		
		$n = $one['type'];
		
		//ADO
		if ($one['lib_pack'] == 'ado') {
			$db[$name] = ADONewConnection($one['type']);
			$db[$name]->SetFetchMode(ADODB_FETCH_ASSOC);
			$db[$name]->debug = false;
			
		//без ADO
		} else {
			$db[$name] = new $n($model);
		}
		
		$db[$name]->PConnect($one['host'], $one['user'], $one['password'], $one['name']);
		if( !$db[$name]->_connectionID ){
			header('Content-Type: text/html; charset=utf-8');
			print('<br />Ошибка соединения с базой данных.');
			exit();
		}	
		
		$db[$name]->Execute('set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"');
		
	} else {
		$config['db'][$name]['supported'] = false;
	}
}

return $db;

?>