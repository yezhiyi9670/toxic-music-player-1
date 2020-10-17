<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function printFontStyle($key,$family,$selector) {
    $config = setting_gt($key);
    if(strlen($config) == 0) return;
    if(substr($config,0,1) == '@') {
        echo '@font-face {font-family: \'' . $family . '\';src:url(\'' . substr($config,1) . '\');}';
        $config = "'" . $family . "'";
    }
    echo $selector . '{font-family:' . $config . ';}';
}
