<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php


$fn = $_REQUEST['path'];

if(strpos($fn,'..') !== false) {
	return false;
}

$tail = "-colored.css";
if(substr($fn,strlen($fn)-strlen($tail)) != $tail) {
	return false;
}

$fs_file = STATICS . $fn . '.php';
if(!file_exists($fs_file)) {
	return false;
}
require($fs_file);
return true;
