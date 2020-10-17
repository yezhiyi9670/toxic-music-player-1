<?php
	error_reporting(E_ALL & (~E_NOTICE));
	header("Content-Type:text/css");
	$color="#1eaaf1";
	$color2="#ffffff";
	$color3="#888888";
	$color4="#15a0e5";
	$color5="#dddddd";
	$color6="#eeeeee";
	$color7="#f7f7f7";
	$w=($_GET["w"]=="1");
	if(isset($_GET['A']) && $_GET['A'][0]=='X' && strlen($_GET['A'])==7) $color='#'.substr($_GET['A'],1);
	if(isset($_GET['S']) && $_GET['A'][0]=='X' && strlen($_GET['S'])==7) $color4='#'.substr($_GET['S'],1);
	header("Cache-Control: public max-age=1296000");
	header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>
/* <style> */
.toxic-dialog-inner,
.codeblock,
.tooltip-box,
.am-btn,
input[type=text], input[type=password], input[type=select],
input[type=number],select{
	border-radius:5px;
}
input[type=text], input[type=password], input[type=select],
input[type=number],select {
	outline:none;
	border-width:0;
}
.maker-list-example{
	transition: background-color 0.3s;
}
.player-processbar-i {
	transition: width 0.3s;
}
.para,
.lo-item,
.am-dropdown-content>li>a {
	transition: background-color 0.3s;
}
.para-title,
.lrc-item {
	transition: color 0.3s,font-weight 0.3s;
}
.am-dropdown-content,
[data-am-widget=header],
.toxic-dialog-inner,
.codeblock,
.tooltip-box,
.am-btn,
.txmp-tag,
input[type=text], input[type=password], input[type=select],
input[type=number],select,
.follow-field{
	box-shadow: 0 0 4px 2px #00000022;
	transition: box-shadow 0.3s;
}
.lrc-content,
.lrc-overview {
	box-shadow: 0 0 4px 2px #00000022;
	transition: box-shadow 0.3s;
}
.am-btn {
	transition: box-shadow 0.3s, background-color 0.3s;
}
.am-dropdown-content:hover,
[data-am-widget=header]:hover,
.toxic-dialog-inner:hover,
.codeblock:hover,
.tooltip-box:hover,
.am-btn:hover,
input[type=text]:focus, input[type=password]:focus, input[type=select]:focus,
input[type=number]:focus,select:focus,
.follow-field:hover{
	box-shadow: 0 0 4px 2px #00000044;
}
.lrc-content:hover,
.lrc-overview:hover {
	box-shadow: 0 0 4px 2px #00000044;
}
.am-dropdown-content,
.lrc-content,
.lrc-overview,
#sync-button,
.txmp-tag,
.follow-field {
	border-radius:3px;
}
.lrc-content, .lrc-overview {
	padding:7px;
}

<?php if($w) { ?>
.pr-player {
	box-shadow: 0 -4px 2px 1px #00000022;
	transition: box-shadow 0.3s;
}
.pr-player:hover {
	box-shadow: 0 -4px 2px 1px #00000025;
}
<?php }else{ ?>
#right-container {
	margin-top: 13px;
}
.song-avatar {
	border-radius: 50%;
	box-shadow: 0 0 4px 2px #00000022;
	transition: box-shadow 0.3s;
}
.song-avatar:hover {
	box-shadow: 0 0 4px 2px #00000044;
}
.am-btn:not(.am-btn-primary):not(.am-btn-secondary):not(.am-btn-warning):not(.am-btn-danger) {
	border: 1px solid #AAA;
}
#preview {
	padding-bottom: 1px;
}
<?php } ?>

/* </style> */
