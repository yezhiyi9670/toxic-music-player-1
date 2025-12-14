<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function setting_def() {
	return array(
		'global-font'=>'sans-serif',
		'lyric-font-formal'=>'sans-serif',
		'lyric-font-title'=>'sans-serif',
		'lyric-font-comment'=>'sans-serif',
		'title-font'=>'sans-serif',
		'name-font'=>'sans-serif',
		'input-font'=>'sans-serif',
		'code-font'=>'Consolas,Monospace',
		'allplay-rand'=>'Y',
		'no-color-switch'=>'N',
		'limited-selection'=>'Y',
		'aggressive-optimize'=>'N',
		'wap-font-size'=>'16.8',
		'wap-scale'=>'0.92'
	);
}

function setting_gt($i,$f = "") {
	$s = setting_def();
	$t = json_decode($_COOKIE[APP_PREFIX . '-user-settings'] ?? '{}',true);
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
	setcookie(APP_PREFIX . '-user-settings',"",time(),"/");
	setcookie(APP_PREFIX . '-user-settings',encode_data($ret,true),time()+86400*365,"/");
}

