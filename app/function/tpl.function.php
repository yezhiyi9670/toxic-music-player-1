<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 包含并打印网页模板（目前不包含任何特殊编译功能）
function tpl($n){
    $tplfile=TEMPLATE.$n.".php";
    if(file_exists($tplfile)) require($tplfile);
}
