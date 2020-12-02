<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

define('DEFAULT_LANG', 'en_us');

// 读取并解析“语言文件”
function readLNG($file) {
	$text = file_get_contents($file);
	$text = str_replace(["\r\n","\r"],["\n","\n"],$text);
	$lines = explode("\n",$text);

	$arr = [];
	foreach($lines as $item) {
		$item = trim($item);
		if($item == '') continue;
		
		if($item[0] == '#') {
			continue;
		}
		if(strlen($item) >= 2 && substr($item,0,2) == '//') {
			continue;
		}
		
		$pos = strpos($item,'=');
		if($pos === false) {
			$pos = -1;
		}

		if($pos == -1) {
			continue;
		}

		$lt = trim(substr($item,0,$pos));
		$rt = trim(substr($item,$pos+1));

		@$rtd = json_decode($rt,true);

		if(gettype($rtd) == 'string') {
			$rt = $rtd;
		}

		$arr[$lt] = $rt;
	}

	return $arr;
}

global $langCache;
$langCache = [];
function getLangArray($lang = DEFAULT_LANG) {
	global $langCache;

	if(isset($langCache[$lang])) {
		return $langCache[$lang];
	}

	$d1 = readLNG(I18N . DEFAULT_LANG . '.lang');
	if(file_exists(I18N_USER . DEFAULT_LANG . '.lang')) {
		$d2 = readLNG(I18N_USER . DEFAULT_LANG . '.lang');
		$d1 = array_merge($d1,$d2);
	}

	if($lang != DEFAULT_LANG) {
		$f1 = readLNG(I18N . $lang . '.lang');
		if(file_exists(I18N_USER . $lang . '.lang')) {
			$f2 = readLNG(I18N_USER . $lang . '.lang');
			$f1 = array_merge($f1,$f2);
		}

		foreach($f1 as $k => $v) {
			$d1[$k] = $v;
		}
	}

	$langCache[$lang] = $d1;
	return $d1;
}

function getSupportedLanguage() {
	return [
		'en_us' => 'English',
		'zh_cn' => '简体中文'
	];
}

function getBrowserLanguage() {
	$ret = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	if(isset($_COOKIE[APP_PREFIX.'-user-language'])) {
		$ret = $_COOKIE[APP_PREFIX.'-user-language'];
	}
	$ret = strtolower(preSubstr($ret,','));
	$ret = str_replace('-','_',$ret);
	if(!isset(getSupportedLanguage()[$ret])) {
		return DEFAULT_LANG;
	}
	return $ret;
}

global $userLanguage;
$userLanguage = '';
function i18nInit() {
	global $userLanguage;
	$userLanguage = getBrowserLanguage();
	define("_FLAG_I18N_INIT");
}
i18nInit();

function userLanguage() {
	global $userLanguage;
	return $userLanguage;
}

global $currentLanguageArr;
$currentLanguageArr = getLangArray($userLanguage);
/**
 * 获取语言值
 */
function LNG($key = '', ...$templates) {
	global $currentLanguageArr;

	if(strlen($key) > 0) {
		if(isset($currentLanguageArr[$key])) {
			$text = $currentLanguageArr[$key];
			foreach($templates as $k => $v) {
				$text = str_replace('${'.$k.'}',$v,$text);
			}
			return $text;
		} else {
			return $key;
		}
	}
	return $currentLanguageArr;
}

function printLNGScript() {
	header('Content-Type: text/javascript');

	echo 'window.LNG_array = ';

	echo encode_data(LNG());

	echo ';';
}

/**
 * 语言键引号转义
 */
function LNGk($key = '', ...$templates) {
	return addslashes(LNG($key, ...$templates));
}

/**
 * 语言键文本输出
 */
function LNGe($key = '', ...$templates) {
	echo htmlspecial2(LNG($key, ...$templates));
}

require(I18N . '../definition.php');
