<?php
	error_reporting(E_ALL & (~E_NOTICE));
	header("Content-Type:text/css");
	// $color="#1eaaf1";
	$color2="#ffffff";
	$color3="#9b9b9b";
	// $color4="#15a0e5";
	$color5="#dddddd";
	$color6="#eeeeee";
	$color7="#f7f7f7";
	$w=($_GET["w"]=="1");
	header("Cache-Control: public max-age=1296000");
	header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>
/*<style>*/
.txmp-page-left{
	padding:16px;
	width:<?php echo $w?"100%":"calc(100% - 320px)" ?>;
	position:absolute;
	height:<?php echo $w?"50%":"100%" ?>;
}
.song-avatar {
	width: 240px;
	height: 240px;
	margin-left: 24px;
	margin-right:24px;
	margin-bottom:8px;
	border:1px solid #999;
	background-color:#EEE;
}
.txmp-page-right{
	position:<?php echo $w?"fixed":"absolute" ?>;
	left:<?php echo $w?"0":"calc(100% - 320px)" ?>;
	width:<?php echo $w?"100%":"320px" ?>;
	height:<?php echo $w?"48px":"100%" ?>;
	<?php echo $w?"bottom:36px;":"" ?>
	padding:16px;
}
.song-title{
	font-size:<?php echo $w?"14px":"25px;" ?>;
	margin-right:8px;
	color:black !important;
}
.song-id{
	border:solid 1px #aaaaaa;
	border-radius:2px;
	background-color:#eeeeee;
	font-size:12px;
	padding:2px;
	margin-right:4px;
	<?php echo $w?"display:none;":"" ?>
}
.song-length{
	border:solid 1px transparent;
	border-radius:2px;
	background-color:transparent;
	font-size:12px;
	padding:2px;
	margin-right:4px;
	white-space:nowrap;
}
.song-process{
	font-size:14px;
	padding:2px;
	color:<?php echo $color3 ?>;
	white-space:nowrap;
}
.right-first-row{
	/*display:<?php echo $w?"none":"block" ?>;*/
	margin-top:<?php echo $w?"-12px":"0" ?>;
	margin-bottom:<?php echo $w?"-4px":"0" ?>;
}
.right-second-row{
	margin-top:<?php echo $w?"8px":"16px" ?>;
	position:relative;
	font-size:0;
}
.right-third-row{
	margin-top:16px;
	position:relative;
	display:<?php echo $w?"none":"block" ?>;
}
.right-third-row-n{
	margin-top:8px !important;
}
.right-third-row > a{
	margin-right:16px;
	margin-bottom:8px;
}
.player-icon,
.player-icon:focus,
.player-icon:visited{
	font-size:40px !important;
	margin-right:8px;
}
.player-processbar{
	background-color:<?php echo $color6 ?>;
	margin-bottom:6px;
	height:16px;
	display:inline-block;
	position:relative;
}

/* Processbar load */
.player-processbar-o{
	height:100%;
	background-color:rgba(0,0,0,0.1);
	position:absolute;
	z-index:1;
}

/* Processbar play */
.player-processbar-i{
	height:100%;
	position:absolute;
}

.lrc-overview
.song-id {
	font-family:Consolas, Monospace;
}

.lrc-overview{
	font-size:16px;
	overflow-x:scroll;
	overflow-y:hidden;
	width:100%;
	scrollbar-width:none;
	white-space:nowrap;
}
.lrc-overview::-webkit-scrollbar,
.lrc-overview .-o-scrollbar{
	display:none;
}
.lo-item{
	border:solid 0px transparent;
	border-radius:2px;
	background-color:transparent;
	padding:4px;
	margin-right:0;
	white-space:nowrap;
	color:black;
}
.lo-active{
	border:solid 0px transparent;
	background-color:<?php echo $color6 ?>;
}
.lrc-area{
	padding:<?php echo $w?"20px":"28px" ?>;
	position:fixed;
}
.lrc-item,
.para-title {
	margin-top:-0.025em;
	margin-bottom:calc(2.8256em - 2.0em);
	line-height: 1.2em;
}
.lrc-text,
.para-title-text {
	display:inline-block;
}
.active-color-1::before,
.active-color-2::before,
.active-color-3::before,
.active-color-4::before,
.active-color-5::before,
.active-color-6::before,
.active-color-7::before{
	font-size:0.705em;
	color:#FFF;
	background:#BBB;
	padding-right:2px;
	padding-bottom:2px;
	padding: 0.151em;
	padding-top: 0em;
	padding-bottom: 0.175em;
	vertical-align: top;
	margin-right:0.151em;
	margin-left:0.302em;
	border:0.151em solid #FFF;
}
.lrc-active .active-color-1::before,
.lrc-active .active-color-2::before,
.lrc-active .active-color-3::before,
.lrc-active .active-color-4::before,
.lrc-active .active-color-5::before,
.lrc-active .active-color-6::before,
.lrc-active .active-color-7::before {
	font-weight: 400;
}
.lrc-content{
	overflow-y:scroll;
	margin-top:24px;
	text-align:center;
	padding:8px;
	padding-top:16px;
	padding-bottom:16px;
	font-size:15px;
	line-height:90%;
	overflow:-moz-scrollbars-none;
	scrollbar-width:none;
}
.lrc-content::-webkit-scrollbar,
.lrc-content .-o-scrollbar{
	display:none;
}
.lrc-content::-moz-scrollbars-vertical{
	display:none;
}
.lrc-active{
	font-weight:bold;
}

/* 角色1：红 */
.lrc-active .active-color-1 {
	color:#EF5350 !important;
}
.lrc-active .active-color-1::before {
	color:#FFF;
	background-color:#EF5350;
}
.active-color-1::before{
	content:'[1]';
}

/* 角色2：蓝 */
.lrc-active .active-color-2 {
	color:#2196F3 !important;
}
.lrc-active .active-color-2::before {
	color:#FFF;
	background-color:#2196F3;
}
.active-color-2::before{
	content:'[2]';
}

/* 组合1,2：紫 */
.lrc-active .active-color-3 {
	color:#9C27B0 !important;
}
.lrc-active .active-color-3::before {
	color:#FFF;
	background-color:#9C27B0;
}
.active-color-3::before{
	content:'[12]';
}

/* 角色3：橙黄 */
.lrc-active .active-color-4 {
	color:#F9A825 !important;
}
.lrc-active .active-color-4::before {
	color:#FFF;
	background-color:#F9A825;
}
.active-color-4::before{
	content:'[3]';
}

/* 组合1,3：橘红 */
.lrc-active .active-color-5 {
	color:#FB8C00 !important;
}
.lrc-active .active-color-5::before {
	color:#FFF;
	background-color:#FB8C00;
}
.active-color-5::before {
	content:'[13]';
}

/* 组合2,3：绿 */
.lrc-active .active-color-6 {
	color:#4CAF50 !important;
}
.lrc-active .active-color-6::before {
	color:#FFF;
	background-color:#4CAF50;
}
.active-color-6::before {
	content:'[23]';
}

/* 全组合：粉红 */
.lrc-active .active-color-7 {
	color:rgb(255,125,190) !important;
}
.lrc-active .active-color-7::before {
	color:#FFF;
	background-color:rgb(255,125,190);
}
.active-color-7::before {
	content:'[合]';
}

/* 人声倒放 */
.reverse-sound-o {
	background-color: #000;
	height: 0.1367em;
	margin-top: 0.2em;
	margin-left: 0.3111em;
	margin-right: 0.3111em;
}
.reverse-sound-i {
	background-color: #000;
	height: 0.1367em;
	width: 0.9em;
	transform: rotate(-45deg);
	margin-left: 0.2em;
	margin-top: -0.8em;
}

.para{
	border-radius:2px;
	width:100%;
	padding-top:12px;
	padding-bottom:1px;
}
.para-title{
	font-weight:bold;
	color:<?php echo $color6 ?>;
}
.para-active>.para-title{
	color:<?php echo $color3 ?>;
}
.para-active{
	background-color:<?php echo $color7 ?>;
}
#reload-button{
	<?php echo $w?"display:none;":"" ?>
}
#sync-button {
	position:fixed;
	background-color:<?php echo $color6 ?>;
	border: 1px solid <?php echo $color5 ?>;
	width: 32px;
	height: 32px;
	font-size: 24px;
	padding: 3px;
	opacity:0.9;
}

#right-menu-overlay {
	position: fixed;
	top: 0;
	width: 100%;
	left: 0;
	height: 100%;
	background-color: #00000075;
	z-index: 1022;
}
#right-menu {
	position: fixed;
	background-color: #EEE;
	/* 此box-shadow同时用于两种界面风格 */
	box-shadow: 0 0 4px 2px #00000055;
	top: 0;
	height: 100%;
	z-index: 1023;
	padding:16px;
}

.rmenu-selection-tabs {}
.rmenu-tab,
.rmenu-close {
	border: 1px solid #AAA;
	padding: 4px;
	margin-right:4px;
}
.rmenu-close {
	height: 31px;
	width: 31px;
	margin-top:-3px;
}
.rmenu-tab-active {
	background-color: #FFF;
}

.rmenu-content {
	margin-top:16px;
	width: 100%;
	height: calc(100% - 42px);
	overflow-y:scroll;
}

.song-list-item {
	padding-left:4px;
	padding-right:4px;
}
.song-list-item > a {
	color: #000;
	width: 100%;
	display:block;
}
.fake-operation > li > a {
	color: #000;
	width: 100%;
	display:block;
}
.song-list-item .txmp-tag {
	display: inline-block;
	margin-top: 3px;
	/*height: 20.5px;*/
	padding-top: 0;
	padding-bottom: 0;
}
.song-list-item .addition-cmt {
	line-height: unset !important;
}

.rmenu-toggle,
.rmenu-toggle:hover,
.rmenu-toggle:focus {
	color: #000;
}
.rmenu-clt {
	margin-bottom:-2px;
	margin-top:4px;
}
/*</style>*/
