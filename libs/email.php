<?php

/************************************************************/
/*															*/
/*	Ð¯Ð´Ñ€Ð¾ ÑÐ¸ÑÑ‚ÐµÐ¼Ñ‹ ÑƒÐ¿Ñ€Ð°Ð²Ð»ÐµÐ½Ð¸Ñ Asterix	CMS						*/
/*		Ð Ð°Ð±Ð¾Ñ‚Ð° Ñ Ð¿Ð¾Ñ‡Ñ‚Ð¾Ð¹										*/
/*															*/
/*	Ð’ÐµÑ€ÑÐ¸Ñ ÑÐ´Ñ€Ð° 2.02										*/
/*	Ð’ÐµÑ€ÑÐ¸Ñ ÑÐºÑ€Ð¸Ð¿Ñ‚Ð° 1.00										*/
/*															*/
/*	Copyright (c) 2009  ÐœÐ¸ÑˆÐ¸Ð½ ÐžÐ»ÐµÐ³							*/
/*	Ð Ð°Ð·Ñ€Ð°Ð±Ð¾Ñ‚Ñ‡Ð¸Ðº: ÐœÐ¸ÑˆÐ¸Ð½ ÐžÐ»ÐµÐ³									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	Ð¡Ð¾Ð·Ð´Ð°Ð½: 10 Ñ„ÐµÐ²Ñ€Ð°Ð»Ñ 2009	Ð³Ð¾Ð´Ð°							*/
/*	ÐœÐ¾Ð´Ð¸Ñ„Ð¸Ñ†Ð¸Ñ€Ð¾Ð²Ð°Ð½: 17 Ð¤ÐµÐ²Ñ€Ð°Ð»Ñ 2010 Ð³Ð¾Ð´Ð°						*/
/*															*/

/************************************************************/

class email
{
    private $from = 'no-reply@yamobi.ru';
    private $encoding = 'koi8-r';

    private $supported_encodings = array('koi8-r', 'utf8');

    public function send(
        $to,
        $subject,
        $message,
        $type = 'plain',
        $files = array()
    )
    {

        require_once('/var/www/tools/libs/phpmailer/smtp.php');

        $address = $this->prepareAddress($to);
        $subject = $this->prepareSubject($subject, $type);
        $headers = $this->prepareHeaders($type, $files);

        foreach ($address as $addr) {
            send($addr, $this->from, $subject, $message, 'smtp.postal.0xdev.ru', 25, 'test', 'msBTD8i8h6hljEnK4TCFjoab');
        }

        //Ð“Ð¾Ñ‚Ð¾Ð²Ð¾
        return true;
    }


    public function __construct($model)
    {
        $this->model = $model;
    }

    //ÐŸÐ¾Ð´Ð³Ð¾Ñ‚Ð¾Ð²Ð¸Ñ‚ÑŒ Ñ‚ÐµÐ¼Ñƒ
    private function prepareSubject($subject, $type)
    {

        //Plain
        if ($type == 'plain') {
            return '=?koi8-r?B?' . base64_encode(iconv('UTF-8', 'KOI8-R//IGNORE', stripslashes($subject))) . '?=';

            //HTML
        } elseif ($type == 'html') {
            return '=?koi8-r?B?' . base64_encode(iconv('UTF-8', 'KOI8-R//IGNORE', stripslashes($subject))) . '?=';
//			return iconv('utf-8', 'koi8-r//IGNORE', $subject);
        }

    }

    //ÐŸÐ¾Ð´Ð³Ð¾Ñ‚Ð¾Ð²Ð¸Ñ‚ÑŒ ÑÐ¾Ð¾Ð±Ñ‰ÐµÐ½Ð¸Ðµ
    private function prepareMessage($message, $subject, $type, $files)
    {

        //Plain
        if ($type == 'plain') {
            //$message=iconv('utf-8', 'koi8-r//IGNORE', $message);
            $message = str_replace("?", "[question_mark]", $message);
            $message = mb_convert_encoding($message, 'KOI8-R', 'UTF8');
            $message = str_replace("?", "", $message);
            $message = str_replace("[question_mark]", "?", $message);
            //HTML
        } elseif ($type == 'html') {

            //ÐŸÑ€Ð¸ÐºÑ€ÐµÐ¿Ð»ÑÐµÐ¼ Ñ„Ð°Ð¹Ð»Ñ‹
            if (is_array($files))
                foreach ($files as $file) $attachment .= $this->addAttachment($file);

            //$message=iconv('utf-8', 'koi8-r//IGNORE', $message);
            $message = str_replace("?", "[question_mark]", $message);
            $message = mb_convert_encoding($message, 'KOI8-R', 'UTF8');
            $message = str_replace("?", "", $message);
            $message = str_replace("[question_mark]", "?", $message);


            $message = '--' . md5(1) . '
Content-Type: multipart/alternative; boundary="' . md5(2) . '"

--' . md5(2) . '
Content-Type: text/plain; charset="koi8-r"
Content-Transfer-Encoding: base64

' . base64_encode(strip_tags($message)) . '
--' . md5(2) . '
Content-Type: text/html; charset="koi8-r"
Content-Transfer-Encoding: base64

' . base64_encode('<html><head><title>' . $subject . '</title></head><body><p>' . $message . '</p></body></html>') . '
--' . md5(2) . '--

' . $attachment . '
--' . md5(1) . '--
';
        }

        //Ð“Ð¾Ñ‚Ð¾Ð²Ð¾
        return $message;
    }

    //ÐŸÐ¾Ð´Ð³Ð¾Ñ‚Ð¾Ð²Ð¸Ñ‚ÑŒ Ð°Ð´Ñ€ÐµÑÐ° Ð´Ð»Ñ Ñ€Ð°ÑÑÑ‹Ð»ÐºÐ¸
    private function prepareAddress($to)
    {

        //ÐšÐ¾Ð¼Ñƒ Ñ€Ð°ÑÑÑ‹Ð»Ð°Ñ‚ÑŒ
        $address = array();

        //ÐÐµÑÐºÐ¾Ð»ÑŒÐºÐ¾ Ð°Ñ€Ð´Ñ€ÐµÑÐ°Ñ‚Ð¾Ð²
        if (substr_count($to, ' '))
            $address = explode(' ', $to);
        //ÐžÐ´Ð¸Ð½ Ð°Ð´Ñ€ÐµÑ
        else
            $address[] = $to;

        //Ð“Ð¾Ñ‚Ð¾Ð²Ð¾
        return $address;
    }

    //ÐŸÐ¾Ð´Ð³Ð¾Ñ‚Ð¾Ð²Ð¸Ñ‚ÑŒ ÑÐ¾Ð¾Ð±Ñ‰ÐµÐ½Ð¸Ðµ
    private function prepareHeaders($type, $files)
    {

        //Plain
        if ($type == 'plain') {
            $headers = 'from:' . $this->from;

            //HTML
        } elseif ($type == 'html') {
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'from: ' . $_SERVER['HTTP_HOST'] . ' <' . $this->from . ">\r\n";
            $headers .= 'Content-Type: multipart/mixed; boundary="' . md5(1) . '"' . "\r\n";
        }

        //Ð“Ð¾Ñ‚Ð¾Ð²Ð¾
        return $headers;
    }

    //ÐŸÑ€Ð¸ÐºÑ€ÐµÐ¿Ð¸Ñ‚ÑŒ Ñ„Ð°Ð¹Ð» Ðº ÑÐ¾Ð¾Ð±Ñ‰ÐµÐ½Ð¸ÑŽ
    function addAttachment($file)
    {
        //Ð˜Ð¼Ñ Ñ„Ð°Ð¹Ð»Ð°
        if ($file['name'])
            $fname = $file['name'];
        else
            $fname = substr(strrchr($file['file'], "/"), 1);
        $type = $file['type'];
        //Ð¡Ð¾Ð´ÐµÑ€Ð¶Ð¸Ð¼Ð¾Ðµ
        $content_type = 'Content-Type: ' . $type;
        $data = file_get_contents($file['file']);
        $content = '--' . md5('1') . '
--' . md5(1) . '
' . $content_type . '; charset="windows-1251"; name="' . $fname . '"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="' . $fname . '"

' . chunk_split(base64_encode($data), 68, "\n") . '
';
        return $content;
    }


    //Ð¡Ð¼ÐµÐ½Ð° ÐºÐ¾Ð´Ð¸Ñ€Ð¾Ð²ÐºÐ¸ Ð¿Ð¾ ÑƒÐ¼Ð¾Ð»Ñ‡Ð°Ð½Ð¸ÑŽ
    public function setEncoding($encoding = 'koi8-r')
    {
        //ÐŸÑ€Ð¸Ð²Ð¾Ð´Ð¸Ð¼ Ðº ÐµÐ´Ð¸Ð½Ð¾Ð¼Ñƒ Ñ„Ð¾Ñ€Ð¼Ð°Ñ‚Ñƒ
        $encoding = strtolower($encoding);
        //ÐŸÑ€Ð¾Ð²ÐµÑ€ÑÐµÐ¼ Ð¿Ð¾Ð´Ð´ÐµÑ€Ð¶ÐºÑƒ Ð´Ð°Ð½Ð½Ð¾Ð¹ ÐºÐ¾Ð´Ð¸Ñ€Ð¾Ð²ÐºÐ¸
        if (in_array($encoding, $this->supported_encodings))
            //Ð£ÑÑ‚Ð°Ð½Ð°Ð²Ð»Ð¸Ð²Ð°ÐµÐ¼
            $this->encoding = $encoding;
    }

}

?>