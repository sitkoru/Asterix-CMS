<?php

$_GET['class'] = strip_tags($_GET['class']);

$f = file_get_contents('http://yui.yahooapis.com/3.3.0/build/cssreset/reset-min.css');
$f = substr($f, strpos($f, '}body,')+strlen('}body,'));
$f = '/*made with help of Yahoo CSS Reset*/'."\n\n".'.'.$_GET['class'].' '.str_replace(',', ',.'.$_GET['class'].' ', $f);
$f = str_replace('}', '}.'.$_GET['class'].' ', $f).'{}';

header("HTTP/1.0 200 Ok");
header('Content-type: text/css; charset=utf-8');
print($f);

?>