<?php

$root_host = 'https://github.com/dekmabot';

header("HTTP/1.0 200 Ok");
header('Content-Type: text/html; charset=utf-8');

print('
<html>
<head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js" type="text/javascript"></script>
</head>
<body>
');

if(!IsSet($_GET['step']) and !IsSet($_POST['step'])){

	//Проверка прав на запись в текущую папку
	if( $f = fopen('test.txt', 'w') ){
		fclose($f);
		unlink('test.txt');
		$check_write_access = true;
	}else{
		$check_write_access = false;
	}

	//Проверка прав на запись в родительскую папку
	if( $f = fopen('../test.txt', 'w') ){
		fclose($f);
		unlink('../test.txt');
		$check_write_parent_access = true;
	}else{
		$check_write_parent_access = false;
	}

	//Проверка прав на назначение прав на файлы
	if( $f = fopen('../test.txt', 'w') ){
		fclose($f);
		unlink('../test.txt');
		$check_chmod_access = true;
	}else{
		$check_chmod_access = false;
	}

	//Проверка прав на назначение прав на файлы
	if( file_get_contents('https://raw.github.com/dekmabot/Asterix-CMS/master/core.txt') ){
		$check_web_access = true;
	}else{
		$check_web_access = false;
	}

	//Проверка прав на запись в сессию
	$_SESSION['test_var'] = 'test_value';
	if( $_SESSION['test_var'] == 'test_value' ){
		$check_session_access = true;
	}else{
		$check_session_access = false;
	}


	$failed=false;
	if(!$check_write_access){
		$errors[] = 'Ошибка: нет доступа на запись в папку, в которой лежит файл webinstaller.php';
		$failed = true;
	}
	if(!$check_write_parent_access){
		$errors[] = 'Ошибка: нет доступа на запись в родительскую папку, в которой лежит папка с файлом webinstaller.php';
		$failed = true;
	}
	if(!$check_chmod_access){
		$errors[] = 'Ошибка: нет доступа на управлеие доступами в текущей папке';
		$failed = true;
	}
	if(!$check_web_access){
		$errors[] = 'Ошибка: отсутствует связь с сервером установки Asterix CMS';
		$failed = true;
	}
	if(!$check_session_access){
		$errors[] = 'Ошибка: отсутствует возможность работы с сессиями';
		$failed = true;
	}
	if($failed){
		print('Имеются ошибки. Их необходимо устранить до продолжения установки:<ol>');
		foreach($errors as $err)print('<li>'.$err.'</li>');
		print('</ol>');
		exit();
	}
	print('Все необходимые доступы имеются, вы можете продолжить установку Asterix CMS<br />');

	$versions = file('https://raw.github.com/dekmabot/Asterix-CMS/master/core.txt');
	$versions_int = file('/home/www/tools/core.txt');
	print('<form action="" method="get"><input type="hidden" name="step" value="2" />');
	print('<h3>Выберите версию ядра для установки</h3><form action="" method="get"><input type="hidden" name="step" value="2" /><ol>');
	$versions_int = array_reverse($versions_int);
	foreach($versions_int as $i=>$ver)
		print('<li><input type="radio" id="ver'.$i.'_int" name="version" value="int_'.$ver.'"'.(!$i?' checked="checked"':'').' /><label for="ver'.$i.'_int"'.(!$i?' style="font-weight:bold"':'').'>Обнаружена установленная версия '.$ver.', использовать её '.(!$i?'(рекомендуется)':'').'</label></li>');
	$versions = array_reverse($versions);
	foreach($versions as $i=>$ver)
		print('<li><input type="radio" id="ver'.$i.'" name="version" value="'.$ver.'" /><label for="ver'.$i.'">Скачать версию '.$ver.'</label></li>');
	print('</ol>');
	$packs = file($root_host.'/packs.txt');
	print('<input type="submit" value="Начать установку" /></form>');

}elseif($_GET['step'] == 2){

	$failed = false;
	$errors = array();

	//Используем имеющееся ядро
	if( substr_count($_GET['version'], 'int_') ){
		
		//Закачиваем и распаковываем дистрибутив
		
		$f = file_get_contents($root_host.'/ACMS-Demo_Start/zipball/master');
		print('Pack: '.strlen($f).' ('.$root_host.'/ACMS-Demo_Start/zipball/master)<br />');
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../asterix.zip', $f);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/../asterix.zip')){
			$errors[] = 'Ошибка: не удалось закачать пакет "'.$root_host.'/distr/asterix_'.trim($_GET['pack']).'.zip"';
			$failed = true;
		}
		
		//Распаковываем
		$files = unzip( 
			$_SERVER['DOCUMENT_ROOT'].'/../asterix.zip', 
			$_SERVER['DOCUMENT_ROOT'].'/..'
		);
		
		chmod($_SERVER['DOCUMENT_ROOT'].'/../templates/default/c', 0777);

		//Чистим мусор
		unlink($_SERVER['DOCUMENT_ROOT'].'/../asterix.zip');
	
	//Скачиваем новое
	}else{
	
		//Закачиваем и распаковываем ядро
		$f = file_get_contents($root_host.'/Asterix-CMS/zipball/master');
		print('Distr: '.strlen($f).' ('.$root_host.'/Asterix-CMS/zipball/master)<br />');
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../core.zip', $f);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/../core.zip')){
			$errors[] = 'Ошибка: не удалось закачать ядро "'.$root_host.'/distr/core_'.trim($_GET['version']).'.zip"';
			$failed = true;
		}
		
		//Закачиваем и распаковываем дистрибутив
		$f = file_get_contents($root_host.'/ACMS-Demo_Start/zipball/master');
		print('Pack: '.strlen($f).' ('.$root_host.'/ACMS-Demo_Start/zipball/master)<br />');
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../asterix.zip', $f);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/../asterix.zip')){
			$errors[] = 'Ошибка: не удалось закачать пакет "'.$root_host.'/distr/asterix_'.trim($_GET['pack']).'.zip"';
			$failed = true;
		}
		
		//Создаём папку для ядра
		@mkdir($_SERVER['DOCUMENT_ROOT'].'/../tools', 0775);
		chmod($_SERVER['DOCUMENT_ROOT'].'/../tools', 0775);
		
		//Распаковываем
		$files = unzip( 
			$_SERVER['DOCUMENT_ROOT'].'/../asterix.zip', 
			$_SERVER['DOCUMENT_ROOT'].'/..'
		);
		
		//Распаковываем
		unzip( 
			$_SERVER['DOCUMENT_ROOT'].'/../core.zip', 
			$_SERVER['DOCUMENT_ROOT'].'/../tools'
		);
		
		chmod($_SERVER['DOCUMENT_ROOT'].'/../templates/default/c', 0777);

		//Чистим мусор
		unlink($_SERVER['DOCUMENT_ROOT'].'/../asterix.zip');
		unlink($_SERVER['DOCUMENT_ROOT'].'/../core.zip');
	}
	
	//Создаём папку для ядра
	@mkdir($_SERVER['DOCUMENT_ROOT'].'/../tmp', 0777);
	chmod($_SERVER['DOCUMENT_ROOT'].'/../tmp', 0775);
		
	print('Дистрибутив установлен!<br /><br />');

	print('<span style="color:red">Важно: база данных и пользователь уже должны быть созданы, доступы уже должны быть выставлены.</span><br /><br />');

	print('<form action="webinstaller.php" method="post"><input type="hidden" name="step" value="3" /><input type="hidden" name="version" value="'.trim($_GET['version']).'" /><h3>Подключение к базе данных</h3>');
	print('<input type="text" name="db_host" value="localhost" /> Хост базы данных<br />');
	print('<input type="text" name="db_name" value="" /> Название базы данных<br />');
	print('<input type="text" name="db_user" value="" /> Пользователь базы данных<br />');
	print('<input type="text" name="db_password" value="" /> Пароль пользователя базы данных<br />');

	print('<h3>Настройка сайта</h3>');
	print('<input type="text" name="domain_title" value="" style="border:1px solid red;" /> Название сайта<br />');
	print('<input type="text" name="host" value="" /> Основной домен<br />');
	print('<input type="text" name="host2" value="'.$_SERVER['HTTP_HOST'].'" /> Тестовый домен<br />');
	print('<input type="text" name="host3" value="" /> Тестовый домен 2<br />');
	print('<input type="text" name="host4" value="" /> Тестовый домен 3<br />');
	
	print('<h3>Настройки режима тестирования</h3>');
	print('Текст в режиме тестирования<br /><textarea style="width:500px; height:100px" name="test_mode_text">Сайт находится в разработке, ориентировочная дата открытия: '.date("Y-m-d", strtotime("+1 month")).'</textarea><br />');
	print('IP-адреса, с которых сайт будет доступен даже в режиме тестирования<br /><textarea style="width:500px; height:100px" name="ip_good">127.0.0.1'."\n".$_SERVER['REMOTE_ADDR']."\n".'</textarea><br />');
	print('<input type="checkbox" name="test_mode" id="block"> <label for="block">После создания сайт будет доступен только с указанных IP-адресов</label><br />');

	print('<h3>Доступ администратора</h3>');
	print('<input type="text" name="admin_title" value="Администратор" /> Имя администратора сайта<br />');
	print('<input type="text" name="admin_login" value="admin" /> Логин администратора сайта<br />');
	print('<input type="text" name="admin_password" value="admin" /> Пароль администратора сайта<br />');

	print('<input type="submit" value="Готово!" /></form>');
	
}elseif($_POST['step'] == 3){

	$core_path = '$_SERVER[\'DOCUMENT_ROOT\'].\'/../tools';

	if( substr_count($_POST['version'], 'int_') ){
		$core_path = '\'/home/www/tools';
		$_POST['version'] = str_replace('int_', '', $_POST['version']);
	}
	
	$config = "<?php

\$version='".$_POST['version']."';

\$config=array(

	//Системные пути
	'path'=>array(
		'www'=>				\$_SERVER['DOCUMENT_ROOT'],
		'core'=>			".$core_path."/core_'.\$version,
		'tmp'=>				\$_SERVER['DOCUMENT_ROOT'].'/../tmp',

		'controllers'=>		".$core_path."/core_'.\$version.'/controllers',
		'actions'=>			".$core_path."/core_'.\$version.'/actions',
		'interfaces'=>		".$core_path."/core_'.\$version.'/interfaces',
		'extensions'=>		".$core_path."/core_'.\$version.'/extensions',
		'libraries'=>		".$core_path."/libs',

		'modules'=>			\$_SERVER['DOCUMENT_ROOT'].'/../modules',
		'templates'=>		\$_SERVER['DOCUMENT_ROOT'].'/../templates',
		'backup'=>			\$_SERVER['DOCUMENT_ROOT'].'/backup',
		'temp'=>			\$_SERVER['DOCUMENT_ROOT'].'/../tmp',
		'cache'=>			\$_SERVER['DOCUMENT_ROOT'].'/../cache',

		//Системные пути
		'admin'=> 			\$_SERVER['DOCUMENT_ROOT'].'/admin',
		'admin_templates'=>	".$core_path."/core_'.\$version.'/templates',

		//Внутренние пути
		'images'=>			\$_SERVER['DOCUMENT_ROOT'].'/i',
		'files'=>			\$_SERVER['DOCUMENT_ROOT'].'/u',
		'styles'=>			\$_SERVER['DOCUMENT_ROOT'].'/c',
		'javascript'=>		\$_SERVER['DOCUMENT_ROOT'].'/j',

		//Публичные пути
		'public_images'=>		'/i',
		'public_files'=>		'/u',
		'public_styles'=>		'/c',
		'public_javascript'=>	'/j',
	),


  //Базы данных
	'db'=>array(
		'system'=>array(
			'lib_pack'=>false,
			'type'=>'mysql',
			'host'=>'".$_POST['db_host']."',
			'name'=>'".$_POST['db_name']."',
			'user'=>'".$_POST['db_user']."',
			'password'=>'".$_POST['db_password']."'
		),
	),

	//Настройки
	'settings'=>array(
		'domain_switch'=>true,
		'languages'=>false,
		'templater'=>'smarty',

		//System
		'show_stat'=>'shirt',
		'modules_check'=>true,
		'dbtree_check'=>false,
		'time_trace'=>false,
		'global_stat'=>false,
		'mainmenu_levels'=>2,
		'latin_url_only' => true,
	),

	//Кеширование
	'cache'=>false,

	//Расширения к модели
	'extensions'=>array(
		'domains'=>'domains.php',
		'languages'=>'languages.php',
		'seo'=>'seo.php',
		'graph'=>'graph.php',
	),

	'openid'=>array(
		'sitko.ru'=>'admin',
	),
	
);

\$config['settings']['version'] = \$version;

ini_set('include_path',implode(';',\$config['path']));

?>";

	//Пишем конфигурационный файл
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../config.php',stripslashes($config) );
	include_once($_SERVER['DOCUMENT_ROOT'].'/../config.php');

	if( !substr_count($_POST['version'], 'int_') ){
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../tools/core_'.trim($_POST['version']).'/ip_good.txt', $_POST['ip_good']);
	}
		
	//Устанавливаем начальную базу данных
	$res = mysql_pconnect($config['db']['system']['host'],$config['db']['system']['user'],$config['db']['system']['password']) or die('cannot connect to database ['.$config['db']['system']['name'].'] with ['.$config['db']['system']['user'].':'.$config['db']['system']['password'].'] ');
	mysql_select_db($config['db']['system']['name']);

	$sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/../import.sql');
	$sql = explode('----', $sql);
/*	
	//Заводим настройки
	$fields = array(
		array('group'=>'Общие', 		'title'=>'Название сайта', 					'var'=>'domain_title', 		'type'=>'text'),
		array('group'=>'Общие', 		'title'=>'Логотип', 						'var'=>'logo', 				'type'=>'file'),
		array('group'=>'Контакты', 		'title'=>'Телефон', 						'var'=>'phone', 			'type'=>'text'),
		array('group'=>'Контакты', 		'title'=>'Код города', 						'var'=>'phone_code', 		'type'=>'text'),
		array('group'=>'Контакты', 		'title'=>'Адрес', 							'var'=>'address', 			'type'=>'text'),
		array('group'=>'Специальные', 	'title'=>'Текст в режиме тестирования', 	'var'=>'test_mode_text', 	'type'=>'textarea'),
		array('group'=>'Специальные', 	'title'=>'Работа в режиме тестирования', 	'var'=>'test_mode', 		'type'=>'check'),
		array('group'=>'Специальные', 	'title'=>'Google API Key', 					'var'=>'google_api_key', 	'type'=>'text'),
		array('group'=>'SEO', 			'title'=>'Файл robots.txt для домена', 		'var'=>'robots', 			'type'=>'robots'),
		array('group'=>'SEO', 			'title'=>'Дополнительные теги в head',		'var'=>'meta_add', 			'type'=>'html'),
		);
	//Настройки домена
	foreach($fields as $field){
		$sql[] = 'insert into `settings` set `group`="'.$field['group'].'", `domain`="|1|", `var`="'.$field['var'].'", `title`="'.$field['title'].'", `type`="'.$field['type'].'", `value`="'.mysql_real_escape_string(@$_POST[ $field['var'] ]).'"';
	}
*/
	$sql[] = 'insert into `domains` set 
		`id`=1, 
		`pos`=10, 
		`title`="'.mysql_real_escape_string($_POST['domain_title']).'",
		`host`="'.mysql_real_escape_string($_POST['host']).'",
		`host2`="'.mysql_real_escape_string($_POST['host2']).'",
		`host3`="'.mysql_real_escape_string($_POST['host3']).'",
		`host4`="'.mysql_real_escape_string($_POST['host4']).'",
		`tarif`="default",
		`templates`="default",
		`active`=1,
		`date_start`=NOW()
	';
	$sql[] = 'insert into `tarifs` set 
		`id`="default", 
		`title`="default", 
		`modules`="|0|users|"
	';
	
	$sql[] = 'insert into `users` set 
		`sid`="'.$_POST['admin_login'].'", 
		`login`="'.$_POST['admin_login'].'", 
		`password`="'.md5($_POST['admin_password']).'", 
		`title`="'.$_POST['admin_title'].'", 
		`access`="|admin=rwd|user=rw-|all=r--|", 
		`shw`=1, 
		`admin`=1, 
		`moder`=1, 
		`active`=1, 
		`domain`="all", 
		`ln`=1, 
		`session_id`="'.md5(date("Y-m-d H:i:s")).'"
	';
	
	mysql_query('set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"');
	foreach($sql as $s){
		mysql_query($s);
	}
	
	unlink('webinstaller.php');

	print('База данных готова.<br />');
	print('Теперь вам нужно настроить host на сервере, чтобы он вёл на указанную папку. Как только сделаете - сайт откроется по указанной ссылке.<br /><br />');
	print('Перейти на сайт <a href="/">'.$_POST['domain_title'].'(<a href="'.$_POST['host'].'">'.$_POST['host'].'</a>, <a href="http://'.$_POST['host2'].'">'.$_POST['host2'].'</a>)</a>');

}




//Распаковываем архив в папку
function unzip( 
		$archive_path, 
		$to_folder=false 
){
	$zip = zip_open($archive_path);
	while($entry = zip_read($zip)){
		$filename = zip_entry_name($entry);
		$filename = substr($filename, strpos($filename, '/')+1 );
		
		if( strlen($filename) ){
			if( $filename[strlen($filename)-1] != '/' ){
				$file_path = $to_folder . '/' . $filename;
				touch($file_path);
				$f = fopen($file_path, 'w+');
				fwrite($f, zip_entry_read($entry,1024*1024*128));
				fclose($f); 
				@chmod($file_path, 0775);
			
			}else{
				@mkdir($to_folder . '/' . $filename);
				@chmod($to_folder . '/' . $filename, 0775);
			}
		}
	}
}

print('
</body>
</html>
');


?>