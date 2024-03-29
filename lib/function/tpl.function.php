<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 包含并打印网页模板（目前不包含任何特殊编译功能）
function tpl($n){
	$tplfile=TEMPLATE.$n.".php";
	if(file_exists($tplfile)) require($tplfile);
}

// 加载 js
function load_js($fn,$ver=VERSION) {
	echo '<script src="' . BASIC_URL . 'static/' . $fn . '.js?v=' . $ver . '"></script>' . "\n";
}
function load_js_e($fn) {
	echo '<script src="' . $fn . '"></script>' . "\n";
}

// 加载 css
function load_css_e($fn,$class='') {
	if(strlen($class) == 0) echo '<link rel="stylesheet" href="' . $fn . '">' . "\n";
	else echo '<link rel="stylesheet" href="' . $fn . '" id="' . $class . '">' . "\n";
}
function load_css($fn,$flag='',$ver=VERSION,$colorizer_class="") {
	$has_platform_specify = (strpos($flag,'w') !== false);
	$has_colorizer = (strpos($flag,'c') !== false);

	if($has_colorizer) {
		load_css_e(BASIC_URL . 'dynamic/' . $fn . '-colored.css?v=' . $ver
			. '&A=X000000&S=X000000&G1=XNULL&G2=XNULL');
		load_css_e(BASIC_URL . 'dynamic/' . $fn . '-colored.css?v=' . $ver
			. '&A=X' . GCM()['A'] . '&S=X' . GCM()['X']
			. '&G1=X' . GCM()['G1'] . '&G2=X' . GCM()['G2'],$colorizer_class);
	}
	load_css_e(BASIC_URL . 'static/' . $fn . '.css?v=' . $ver);
	if($has_platform_specify) {
		if(is_wap()) {
			load_css_e(BASIC_URL . 'static/' . $fn . '-mobile.css?v=' . $ver);
		} else {
			load_css_e(BASIC_URL . 'static/' . $fn . '-desktop.css?v=' . $ver);
		}
	}
}

// 插入允许过翻的CSS
function declare_allow_overscroll() {
	echo '<style>';
	echo '.txmp-page-full::after,.txmp-page-left::after,.txmp-page-right::after{padding-bottom:400px;}';
	echo '</style>';
}

/**
 * 设置返回代码
 * （仅用于 API 返回！如果客户端明确表示其不是浏览器，那么代码将不会被设置）
 */
function set_response_code($code,$text) {
	header("HTTP/1.1 " . $code . ' ' . $text);
}

/**
 * 显示 JSON
 */
function show_json($code,$data) {
	header("Content-Type: application/json");
	$success = false;
	if(100 <= $code && $code < 400) $success = true;
	print json_encode([
		'success' => $success,
		'code' => $code,
		'path' => $_GET['_lnk'],
		'data' => $data,
	],JSON_PRETTY_PRINT * _CT('debug') + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
	exit;
}

/**
 * 显示错误
 */
function show_error($code, $error, $msg) {
	show_json($code, ['error' => $error, 'msg' => $msg]);
}
