<?php
/**
 * Created by PhpStorm.
 * User: brain_000
 * Date: 29.12.2015
 * Time: 10:14
 */
function smarty_function_include_file($params, $template)
{
    include($params['file']);
}