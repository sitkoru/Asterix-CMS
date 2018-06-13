<?php

/************************************************************/
/*															*/
/*	Ядро системы управления Asterix	CMS						*/
/*		Менеджер контроллеров								*/
/*															*/
/*	Версия ядра 2.0											*/
/*	Версия скрипта 1.00										*/
/*															*/
/*	Copyright (c) 2009  Мишин Олег							*/
/*	Разработчик: Мишин Олег									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Создан: 10 февраля 2009	года							*/
/*	Модифицирован: 25 сентября 2009 года					*/
/*															*/

/************************************************************/

class controller_manager
{
    public static $config;
    public $vars = array();

    public static $output_formats = array('html', 'json', 'xml', 'tpl', 'txt', 'php');

    //Все стандартные контроллеры
    public $controllers = array(
        'get' => array('methods' => array('GET'), 'title' => 'Получение содержания страницы', 'protection' => false, 'model' => true, 'path' => 'get.php', 'format' => array('html')),
        'admin' => array('methods' => array('GET', 'POST'), 'title' => 'Система управления', 'protection' => false, 'model' => true, 'path' => 'admin.php', 'format' => array('html')),
        'dev' => array('methods' => array('GET', 'POST'), 'title' => 'Панель разработчика', 'protection' => false, 'model' => true, 'path' => 'dev.php', 'format' => array('html')),
    );

    //Загружаемся
    public function __construct($config, $log, $cache = false)
    {
        self::$config = $config;
        $this->log = $log;
        $this->cache = $cache;
        //Подгружаем все переданные параметры
        $this->loadData();

        //Загружаем модель
        $this->loadModel();

        // Обрабатываем стандартные пути, вроде favicon/sitemap/robots/etc...
        $this->ansverDefinedPaths();

        //Если сайт работает в режиме тестирования - проверяем можно ли показывать
        $this->checkTestMode();

        //Определение контроллера
        $this->activeController = $this->defineController();

        //Записываем контроллер
        model::$ask->controller = $this->activeController;
        //Инициализация контроллера
        $result = $this->execController($this->activeController);

        //Готово
        return $result;
    }

    //Подгрузка переданных параметров
    function loadData()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
            $this->vars = $_GET;
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
            $this->vars = array_merge($_GET, $_POST, $_FILES);
    }

    //Загружаем модель
    private function loadModel()
    {
        require_once(self::$config['path']['core'] . '/classes/model.php');
        $this->model = new model(self::$config, $this->log, $this->cache);
    }

    //Определение контроллера
    private function defineController()
    {
        //Админка определяется раньше, до разбора URL
        if (IsSet(model::$ask->controller))
            return model::$ask->controller;
        //если контроллер уже определёт (Admin)
        if (IsSet($this->activeController))
            return $this->activeController;

        //Admin
        if (model::$ask->url[0] == 'admin') {
            return 'admin';

            //Обычный контроллер
            //Admin
        } elseif (model::$ask->url[0] == 'dev') {
            return 'dev';

            //Обычный контроллер
        } else {

            if ($_SERVER['REQUEST_METHOD'] == 'GET')
                return 'get';
            elseif (($_SERVER['REQUEST_METHOD'] == 'POST') && (IsSet(model::$ask->mode[0]) || IsSet($this->vars['interface']))) {
                if (!IsSet($this->vars['interface']))
                    $this->vars['interface'] = model::$ask->mode[0];
                model::$modules[model::$ask->module]->controlInterface($this->vars['interface'], $this->vars, true);
            }
        }

        //Перебираем все контроллеры
        foreach ($this->controllers as $sid => $controller)
            //Если допустимый метод
            if (in_array($_SERVER['REQUEST_METHOD'], $controller['methods']))
                if (in_array(model::$ask->output, $controller['format']))
                    return $sid;

        //Если существует указание
        if (IsSet($this->controllers[$_SERVER['REQUEST_METHOD']]))
            return $_SERVER['REQUEST_METHOD'];

        //Контроллер по умолчанию
        return 'get';
    }

    //Ищем обработчик данного запроса
    public function execController($controller)
    {
        $current_module = model::$ask->module;

        //Контроллера нет - запускаем стандартные
        if ($controller == 'admin') {
            require_once self::$config['path']['core'] . '/controllers/admin.php';
            $controller = new controller_admin($this->model, $this->vars, $this->cache);
            $controller->start();

            //Контроллера нет - запускаем стандартные
        } elseif ($controller == 'dev') {
            require_once self::$config['path']['core'] . '/controllers/dev.php';
            $controller = new controller_dev($this->model, $this->vars, $this->cache);
            $controller->start();

            //Существует запрошенный интерфейс
        } elseif ((model::$ask->method == 'POST') && (IsSet(model::$modules[$current_module]->interfaces[$this->vars['interface']]) || IsSet(model::$modules[$current_module]->interfaces[model::$ask->mode[0]]))) {
            /*
            До сюда не доходит, вызывается в defineController
            */

            if (!IsSet($this->vars['interface']))
                $this->vars['interface'] = model::$ask->mode[0];
            model::$modules[$current_module]->controlInterface($this->vars['interface'], $this->vars);
            pr('Вызванный контроллер интерфейса не вернул результата.');
            exit();

            //Устаревший контроллер Feedback
        } elseif ((model::$ask->method == 'POST') && ($this->vars['action'] == 'feedback') && IsSet(model::$ask->rec['feedback'])) {
            $this->feedback();
            exit();

            //Контроллера нет - запускаем стандартные
        } else {
            require_once self::$config['path']['core'] . '/controllers/get.php';
            $controller = new controller_get($this->model, $this->vars, $this->cache);
            $controller->start();

        }
    }

    //Проверка работы в режиме тестирования
    private function checkTestMode()
    {

        if (IsSet(model::$settings['test_mode']))
            if (intval(model::$settings['test_mode']) && (!user::is_admin())) {
                $current_ip = $_SERVER['REMOTE_ADDR'];

                //Обработка ошибки
                if (!file_exists(self::$config['path']['core'] . '/ip_good.txt'))
                    log::err('file_not_found', self::$config['path']['core'] . '/ip_good.txt');

                //Подгружаем список администраторских IP-адресов
                $white_ips = file(self::$config['path']['core'] . '/ip_good.txt');

                //Чистим
                foreach ($white_ips as $i => $p)
                    $white_ips[$i] = trim($p);

                //Проверяем
                if (!in_array($current_ip, $white_ips)) {
                    if (!headers_sent()) {
                        header('Content-Type: text/html; charset=utf-8');
                        header('HTTP/1.0 404 Not Found');
                    }
                    print(model::$settings['test_mode_text'] . ' <!--' . $current_ip . '-->');
                    exit();
                }
            }
    }

    //Контроллер устаревшей формы обратной связи
    private function feedback()
    {
        $form = unserialize(model::$ask->rec['feedback']);
        if (!is_array($form)) return false;

        $allow_files = array(
            'image/jpeg', 'image/png', 'image/gif',
            'application/msword', 'application/msexcel',
            'application/pdf', 'application/rtf'
        );

        $message = '';
        foreach ($form['fields'] as $i => $field) {
            if ($field['type'] == 'file') {
                $message .= $field['title'] . ': <strong>Смотреть в прикрепленных файлах</strong><br />';
            } elseif (IsSet($this->vars['f' . $i]))
                $message .= $field['title'] . ': <strong>' . $this->vars['f' . $i] . '</strong><br />';
        }

        foreach ($_FILES as $key => $file) {

            if (!is_array($file['name'])) {

                foreach ($file as $prop => $value) {

                    $new_file[$prop][] = $value;
                }

                $file = $new_file;
            }

            foreach ($file['type'] as $i => $mimetype) {

                if (!in_array($mimetype, $allow_files)) {

                    $errors['file'] = "Недопустимый тип файла '{$file[ 'name' ][$i]}'({$file[ 'type' ][$i]})";
                } else

                    $files[] = array('file' => $file['tmp_name'][$i],
                        'type' => $mimetype,
                    );
            }
        }

        $subject = 'Соощение с сайта ' . model::$ask->host;
        $footer = 'Сообщение отослано со страницы: <a href="' . $_SERVER['HTTP_REFERER'] . '">' . urldecode($_SERVER['HTTP_REFERER']) . '</a><br />' . date('d-m-Y H:i');
        $message = 'Пользователь отправил сообщение с сайта http://' . model::$ask->host . '/<br /><br />' . $message . '<br /><hr />' . $footer;

        $email = model::initEmail();

        //$email->send($form['email'],$subject,$message,'html');
        $email->send($form['email'], $subject, $message, 'html', $files);

        $_SESSION['messages']['feedback']['ok'] = 'Сообщение успешно отправлено';

        //Готово
        header('Location: ' . model::$ask->rec['url'] . '.html#feedback');
        exit();
    }

    // Отдаём ответы на стандартные запросы
    private function ansverDefinedPaths()
    {
        $path = strtolower($_SERVER['REQUEST_URI']);

        // favicon.ico
        if ($path == '/favicon.ico') {
            $rec = model::execSql('select `value` from `settings` where `var`="favicon"', 'getrow');
            $rec = unserialize(htmlspecialchars_decode($rec['value']));
            if (is_readable(model::$config['path']['www'] . $rec['path'])) {
                header("HTTP/1.0 200 Ok");
                header('Content-Type: image/x-icon; charset=utf-8');
                readfile(model::$config['path']['www'] . $rec['path']);
            } else {
                header("HTTP/1.0 404 Not Found");
            }
            exit();

            // sitemap.xml
        } elseif ($path == '/sitemap.xml') {
            require(model::$config['path']['core'] . '/classes/templates.php');
            $tmpl = new templater($model);
            $main_tmpl = new templater($model);
            $data = $this->siteMap();
            $current_template_file = model::$config['path']['admin_templates'] . '/sitemap.tpl';
            $sub_sitemap_template_file = model::$config['path']['admin_templates'] . '/sub_sitemap.tpl';

            $urls =array();
            foreach ($data as $model_sid => $recs) {
                if ($recs) {
                    $tmpl->assign('recs', $recs);
                    $html = $tmpl->fetch($sub_sitemap_template_file);
                    $path = '/sitemap.' . $model_sid . '.xml';
                    $file_url = 'http://' . $_SERVER['HTTP_HOST'] . $path;
                    file_put_contents(model::$config['path']['www'] . $path, $html);
                    $urls[] = $file_url;
                }
            }
            $main_tmpl->assign('urls', $urls);
            $main_tmpl->assign('date', date('Y-m-d'));
            header("HTTP/1.0 200 Ok");
            header('Content-Type:text/xml');
            $ready_html = $main_tmpl->fetch($current_template_file);
            print($ready_html);
            exit();

            // robots.txt
        } elseif ($path == '/robots.txt') {
            $rec = model::execSql('select `value` from `settings` where `var`="robots"', 'getrow');
            if ($rec) {
                header("HTTP/1.0 200 Ok");
                header('Content-Type: text/plain; charset=utf-8');
                print($rec['value']);
            } else {
                header("HTTP/1.0 404 Not Found");
            }
            exit();
        } elseif ($path == '/opensearch_desc.xml') {
            if (IsSet(model::$modules['search'])) {
                $rec = model::execSql('select `url` from `start_rec` where `is_link_to_module`="search" limit 1', 'getrow');
                if (IsSet($rec['url'])) {
                    $data = '<?xml version="1.0"?>
<OpenSearchDescription 
	xmlns="http://a9.com/-/spec/opensearch/1.1/" 
	xmlns:moz="http://www.mozilla.org/2006/browser/search/">
	
	<ShortName>Поиск по сайту</ShortName>
	<Description>Поиск по сайту</Description>
	<Image height="16" width="16" type="image/x-icon">http://' . $_SERVER['HTTP_HOST'] . '/favicon.ico</Image>
	<Url type="text/html" method="get" template="http://' . $_SERVER['HTTP_HOST'] . $rec['url'] . '.html?q={searchTerms}" />
	<Url type="application/x-suggestions+json" method="get" template="http://' . $_SERVER['HTTP_HOST'] . $rec['url'] . '.html?q={searchTerms}" />
	<Url type="application/x-suggestions+xml" method="get" template="http://' . $_SERVER['HTTP_HOST'] . $rec['url'] . '.html?format=xml&amp;q={searchTerms}" />
	<moz:SearchForm>http://' . $_SERVER['HTTP_HOST'] . $rec['url'] . '.html</moz:SearchForm>
</OpenSearchDescription>';
                    header("HTTP/1.0 200 Ok");
                    header('Content-Type: text/xml; charset=utf-8');
                    print($data);
                    exit();
                }
            }
        }

    }

    //Учёт приходов поисковиков
    private function siteMap()
    {

        // Фильтровать подобные адреса, и не показывать их в карте сайта
        $filter = array();

        // Читаем robots.txt и учитываем его в выдаче
        $f = explode("\n", model::$settings['robots']);
        foreach ($f as $fi)
            if (substr_count($fi, 'Disallow')) {
                $path = substr($fi, strpos($fi, ' ') + 1);
                if (substr_count($fi, '*'))
                    $path = substr($path, 0, strrpos($path, '*'));
                $filter[] = trim($path);
            }
        $result = array();
        foreach (model::$modules as $module_sid => $module) {
            if (IsSet($module->structure) && !$module->structure['rec']['hide_in_tree']) {

                $fields = '`url`,`date_public`';
                if (IsSet(model::$extensions['seo']))
                    $fields .= ',`seo_changefreq`,`seo_priority`';
                $current_recs = model::execSql('select ' . $fields . ' from `' . $module->getCurrentTable('rec') . '` where `shw`=1', 'getall');
                $recs = array();
                foreach ($current_recs as $i => $rec) {

                    $rec['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $rec['url'] . (strlen($rec['url']) > 2 ? '.html' : '');

                    if ($rec['date_public']) {
                        if (date("U", strtotime($rec['date_public'])) < date("U", strtotime("1995-01-01")))
                            $rec['date_public'] = '2000-01-01 00:00:00';
                    } else {
                        $rec['date_public'] = date("Y-m-d H:i:s");
                    }

                    $rec['date_public'] = date("c", strtotime($rec['date_public']));
                    $recs[$i] = $rec;

                    foreach ($filter as $fi) {
                        if (substr_count($rec['url'], $fi))
                            UnSet($recs[$i]);
                    }
                }
                $result[$module_sid] = $recs;
            }
        }
        //Форматируем данные
        return $result;
    }


}

?>