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
	border-radius:0;
}
input[type=text], input[type=password], input[type=select],
input[type=number],select {
	outline:none;
	border:1.5px dashed <?php echo $color3 ?>;
}
input[type=text]:focus,
input[type=password]:focus,
input[type=select]:focus,
input[type=number]:focus,
select:focus {
	border:1.5px dashed <?php echo $color ?>;
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
	box-shadow: none;
}
.am-btn {
	transition: background-color 0.1s;
}


.lrc-content,
.lrc-overview {
	border: 1.5px dashed #bbbbbb;
}
.lrc-content, .lrc-overview {
	padding:7px;
}

<?php if($w) { ?>
.pr-player {
	border-top: 1.5px dashed #bbbbbb;
}
<?php }else{ ?>
#right-container {
	margin-top: 13px;
}
.song-avatar {
	border-radius: 50%;
}
.am-btn:not(.am-btn-primary):not(.am-btn-secondary):not(.am-btn-warning):not(.am-btn-danger) {
	border: 1px solid #AAA;
}
#preview {
	padding-bottom: 1px;
}
<?php } ?>

.txmp-header {
	transition: background-color 1.5s;
}
a {
	transition: color .25s;
}
.lrc-item,.para-title {
	transition: color .3s;
}
.para {
	transition: background-color .3s;
}

.maker-list>li {
	transition: background-color .3s;
}

<?php if(!$w) { ?>
.am-list-news {
	max-width:900px;
	padding: 16px 24px;
	margin-left: auto;
	margin-right: auto;
	box-shadow: 0 0 4px 2px #00000033;
	transition: box-shadow .3s;
}
.am-list-news:focus {
	box-shadow: 0 0 4px 2px #00000055;
}
<?php } ?>

.player-processbar-i {
	transition: background-color .25s, width .2s;
}

/* </style> */
