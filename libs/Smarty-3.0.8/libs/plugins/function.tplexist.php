<?php
function smarty_function_tplexist($params, &$smarty)
{   
    if ($smarty->templateExists($params['file']))
        $result=true;
    else
        $result=false;
        
    $smarty->assign($params['result'], $result);
}
?>