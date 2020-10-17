<?php
	error_reporting(E_ALL & (~E_NOTICE));
	header("Content-Type:text/css");
	// $color="#1eaaf1";
	$color2="#ffffff";
	$color3="#888888";
	// $color4="#15a0e5";
	$color5="#dddddd";
	$color6="#eeeeee";
	$color7="#f7f7f7";
	$w=($_GET["w"]=="1");
	header("Cache-Control: public max-age=1296000");
	header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>

/* <style> */

body {
	user-select: none;
}

code,
.lrc-content,
.codeblock,
.allow-select {
	user-select: text;
}

/* </style> */
