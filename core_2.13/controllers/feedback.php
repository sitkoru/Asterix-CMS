<?php

/*
Скрипт отвечает за точки входа в действия.
Предназначен для использования в контроллерах,
запускает действия, за одно проверяя безопасность.
*/

require('default_controller.php');

class controller_feedback extends default_controller
{
	public $title = 'Регистрация пользователя на сайте';

// вот это хорошо бы в конфиг вынести или в настройки
	protected $allow_files = array(
		'image/jpeg', 'image/png', 'image/gif',
		'application/msword', 'application/msexcel',
		'application/pdf', 'application/rtf'
	);

	//Вывод данных
	public function start()
	{
		
		//Здесь будем копить ошибки
		$errors = false;

		//Определяемся куда указывает URL
		$this->model->prepareMainRecord();

		//Данные формы
		$form = $this->model->types['feedback']->getValueExplode($this->model->ask->rec['feedback'], false, false);

		//Protection
		if ($form['protection']) {
			if ($form['protection'] == 'captcha') {
				if (!IsSet($this->vars['captcha'])) {
					$errors['captcha'] = 'Укажите секретный код с картинки';
				} elseif (!IsSet($_SESSION['form_captcha_code'])) {
					$errors['captcha'] = 'Для работы с сайтом необходимо включить COOKIES';
				} elseif ($_SESSION['form_captcha_code'] != $this->vars['captcha']) {
					$errors['captcha'] = 'Секретный код указан неверно';
					UnSet($_SESSION['form_captcha_code']);
				}
			}
		}

		//Сообщение
		$pre      = 'Сообщение на сайте:' . "\r\n\r\n";
		$pre_user = 'Ваша заявка принята на сайте:' . "\r\n\r\n";

		//Память введённых значений
		$memory = array();

		//Ждём пользовательский email
		$user_email = false;

		//Само сообщение
		foreach ( $form['fields'] as $field ) {

			if ( $field['type'] == 'file' ) {

				$attach = true;
				continue;
			}

			if ( strlen(@$this->vars[$field['sid']]) or is_array(@$this->vars[$field['sid']]) ) {

				if( is_array($this->vars[$field['sid']]) ){
					$message[]             = $field['title'] . ': ' . strip_tags( implode(', ', $this->vars[$field['sid']]));
					$message_user[]        = $field['title'] . ': ' . strip_tags( implode(', ', $this->vars[$field['sid']]));
				}else{
					$message[]             = $field['title'] . ': ' . strip_tags($this->vars[$field['sid']]);
					$message_user[]        = $field['title'] . ': ' . strip_tags($this->vars[$field['sid']]);
				}
				$memory[$field['sid']] = strip_tags($this->vars[$field['sid']]);
			} elseif ($field['required']) {
				$errors[$field['sid']] = 'Поле "'.$field['title'].'" не заполнено';
			}

			//Ждём email
			if (substr_count($this->vars[$field['sid']], '@')) {
				$user_email = $this->vars[$field['sid']];
			}
		}

		if ( ! ( empty( $attach ) or empty( $_FILES ) ) ) {

			$message_user[] = "\nПрикреплённые файлы:";
			foreach ( $_FILES as $key => $file ) {

				if ( ! is_array( $file['name'] ) ) {

					foreach ( $file as $prop => $value ) {

						$new_file[$prop][] = $value;
					}

					$file = $new_file;
				}

				foreach ( $file['type'] as $i => $mimetype ) {

					if ( ! in_array( $mimetype, $this->allow_files ) ) {

						$errors['file'] = "Недопустимый тип файла '{$file['name'][$i]}'({$file['type'][$i]})";
						break;
					}

					$message_user[] = "\t{$file['name'][$i]}";
				}
			}

			$attach = $_FILES;
		}

		else $attach = false;

		$message[] = '';
		$message[] = '---';
		$message[] = 'Сообщение отослано со страницы: <a href="'.$_SERVER['HTTP_REFERER'].'">' . urldecode( $_SERVER['HTTP_REFERER'] ).'</a>';
		$message[] = date( 'd-m-Y H:i' );

		$message_user[] = '';
		$message_user[] = '---';
		$message_user[] = 'Сообщение отослано со страницы: <a href="'.$_SERVER['HTTP_REFERER'].'">' . urldecode( $_SERVER['HTTP_REFERER'] ).'</a>';
		$message_user[] = date( 'd-m-Y H:i' );

		//Отсылаем только в том случае если форма включена
		if (!$errors)
			if ($form['shw']) {
				//Обнуляем Captcha
				if ($form['protection'])
					if ($form['protection'] == 'captcha')
						UnSet($_SESSION['form_captcha_code']);

				//Данные для заявки администратору сайта
				$subject = 'Соощение с сайта ' . $this->model->extensions['domains']->domain['title'].': '.$form['title'];
				$body    = $pre . implode("\r\n", $message);

				//Отправляем
				$this->model->email->send($form['email'], $subject, nl2br($body), 'html', $attach );

				//Копия самому пользователю, который отправляет сообщение
				if ($user_email) {
					//Данные
					$subject = 'Ваша заявка учтена на сайте ' . $this->model->extensions['domains']->domain['title'];
					$body    = $pre . implode("\r\n", $message_user);

					//Отправляем
					$this->model->email->send( $user_email, $subject, nl2br($body), 'html' );
				}

				//Учитываем отправку формы в настройках самой формы
				$form['counter']++;
				$form_settings = serialize($form);
				$sql           = 'update `' . $this->model->modules[$this->model->ask->module]->getCurrentTable($this->model->ask->structure_sid) . '` set `feedback`="' . mysql_real_escape_string($form_settings) . '" where `id`="' . $this->model->ask->rec['id'] . '" and `domain`="|' . $this->model->extensions['domains']->domain['id'] . '|" limit 1';
				$this->model->execSql($sql, 'update');

				//Готово
				$_SESSION['messages']['feedback']['ok'] = 'Сообщение успешно отправлено';
			}

		//Выводим ошибки
		if ($errors) {
			$_SESSION['messages']['feedback']        = $errors;
			$_SESSION['messages']['feedback_memory'] = $memory;
		}

		//Готово
		$this->model->ask->original_url .= '#feedback';
		header('Location: ' . $this->model->ask->original_url);
		exit();

	}
}

?>
