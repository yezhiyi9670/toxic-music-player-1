<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

define('USR_COOKIE','txmp-user-settings');

function setting_def() {
	return array(
		'global-font'=>'\'Noto Sans SC\'',
		'lyric-font-formal'=>'\'Noto Sans SC\'',
		'lyric-font-title'=>'\'Noto Sans SC\'',
		'lyric-font-comment'=>'\'Noto Sans SC\'',
		'title-font'=>'\'Noto Sans SC\'',
		'name-font'=>'\'Noto Sans SC\'',
		'input-font'=>'\'Noto Sans SC\'',
		'code-font'=>'Consolas,Monospace',
		'new-look'=>'Y',
		'allplay-rand'=>'Y',
		'no-color-switch'=>'N',
		'limited-selection'=>'N'
	);
}

function setting_gt($i,$f = "") {
	$s = setting_def();
	$t = json_decode($_COOKIE[USR_COOKIE],true);
	if(isset($t[$i])) {
		$v = $t[$i];
		$not_allowed = '!;<>{}'; $accepted = true;
		for($i = 0; $i < strlen($not_allowed); $i++) {
			if(strstr($v,$not_allowed[$i])) {
				$accepted = false;
				break;
			}
		}
		if($accepted) return $v;
		return $s[$i];
	}
	return $s[$i];
}

function setting_upd($u=array()) {
	$d=setting_def();
	$ret=array();
	foreach($d as $k=>$v) {
		if(isset ($u[$k])) $ret[$k]=$u[$k];
		else {
			if(!setting_gt($k)) {
				// Check user input
				$not_allowed = '!;<>{}'; $accepted = true;
				for($i = 0; $i < strlen($not_allowed); $i++) {
					if(strstr($v,$not_allowed[$i])) {
						$accepted = false;
						break;
					}
				}
				if(!$accepted) $ret[$k] = setting_gt($k);
				else $ret[$k] = $v;
			}
			else {
				$ret[$k] = setting_gt($k);
			}
		}
	}
	setcookie(USR_COOKIE,"",time(),"/");
	setcookie(USR_COOKIE,json_encode($ret),time()+86400*365,"/");
}

