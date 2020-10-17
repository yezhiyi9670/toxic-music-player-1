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

html{
	overscroll-behavior:contain;
}
body{
	cursor:default;
}
a{
	cursor:pointer;
}
.txmp-header{
	text-align: left;
}
.txmp-header-title{
	text-align:left !important;
	margin:0 !important;
}
.txmp-r-ico{
	margin-right:16px;
	margin-left:4px;
}
.txmp-nav-icos > a{
	color:<?php echo $color2 ?>;
	font-size:24px;
	margin-left:8px;
	margin-top:2px;
}
.txmp-page-main{
	position:relative;
}
.txmp-page-full{
	padding:16px;
	width:100%;
	position:absolute;
	height:100%;
}


<?php if(!$w){ ?>
.txmp-page-left{
	padding:16px;
	width:<?php echo $w?"100%":"50%" ?>;
	position:absolute;
	height:<?php echo $w?"50%":"100%" ?>;
}
.txmp-page-right{
	position:absolute;
	left:50%;
	width:50%;
	height:100%;
	padding:16px;
}
<?php } else { ?>
.txmp-page-left{
	padding:16px;
	width:100%;
}
.txmp-page-right{
	padding:16px;
	width:100%;
}
<?php } ?>

input[type=text],
input[type=password],
input[type=select]{
	height:37px;width:30%;padding:4px;
}

strong, b{
	font-weight:bold;
}

.addition-cmt{
	font-size:14px;
	color:#BBB;
}

.font-bold {
	font-weight:bold;
}

.txmp-tag {
	border: 1px solid transparent;
	background-color: transparent;
	font-size: 12px;
	margin-right:6px;
	padding: 1px 2px;
	color: #000;
	white-space: nowrap;
}

.txmp-tag.tag-default {
	border-color: #9E9E9E;
	background-color: #E0E0E0;
	color: #000;
}

.txmp-tag.tag-canonical {
	border-color: #03A9F4;
	background-color: #81D4FA;
	color: #000;
}

.txmp-tag.tag-red-g {
	border-color: #F00;
	background-color: #F00;
	color: #FFF;
}

.txmp-tag.tag-cyan-g {
	border-color: #00BCD4;
	background-color: #00BCD4;
	color: #FFF;
}

.txmp-tag.tag-orange-g {
	border-color: #FFC107;
	background-color: #FFC107;
	color: #FFF;
}

.txmp-tag.tag-blue-g {
	border-color: #2196F3;
	background-color: #2196F3;
	color: #FFF;
}

.txmp-tag.tag-purple-g {
	border-color: #BA68C8;
	background-color: #BA68C8;
	color: #FFF;
}

.song-item {
	padding-bottom: 6px;
}

.song-list-item {
	padding-bottom: 5px;
}

.am-list-item-hd {
	margin-bottom:6px !important;
}

.am-list .am-list-item-dated a{
	text-overflow:unset !important;
}

body ::selection {
	/* color: #FFF; */
	text-shadow:none;
}
body ::-moz-selection {
	/* color: #FFF; */
	text-shadow:none;
}
button,
input[type=button],
input[type=submit] {
	outline:none !important;
}

.footnote {
	font-size: 0.85em;
	color: #999;
	padding-left: 2px;
	padding-right: 2px;
	margin-top: 0;
}

.list-focus {
	background-image: linear-gradient(135deg,#00000011,#00000022);
}
.am-list-news {
	outline: none;
}
.am-list-news {
	margin-bottom: 300px;
}

.text-danger {
	color:red;
	font-weight:700;
}

/* ----- PATCHING ----- */

.am-modal-dialog {
	outline:none;
	transition:box-shadow .3s;
}
.am-modal-dialog:focus {
	box-shadow: 0 0 4px 2px #FFFFFF77;
}
input.am-modal-prompt-input[type=text],
input.am-modal-prompt-input[type=password] {
	border: 1px solid #CCC;
	transition:box-shadow .3s;
}
input.am-modal-prompt-input[type=text]:focus,
input.am-modal-prompt-input[type=password]:focus {
	border: 1px solid #CCC;
	box-shadow: inset 0 0 4px 0 #00000044;
}
.am-modal-hd {
	font-weight:700;
}
/* </style> */
