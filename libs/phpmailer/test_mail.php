<?php

require_once('/var/www/tools/libs/phpmailer/smtp.php');

$result = send('sonicgd@gmail.com', 'test@yamobi.ru', 'Test mail', '<b>content</b>', 'smtp.0xdev.ru', 587, 'test@yamobi.ru', 'kqEvd7j2uV');

var_dump($result);