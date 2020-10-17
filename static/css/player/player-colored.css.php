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
	$colorG1="";
	$colorG2="";
	$w=($_GET["w"]=="1");
	if(isset($_GET['A']) && $_GET['A'][0]=='X' && strlen($_GET['A'])==7) $color='#'.substr($_GET['A'],1);
	if(isset($_GET['S']) && $_GET['S'][0]=='X' && strlen($_GET['S'])==7) $color4='#'.substr($_GET['S'],1);
	if(isset($_GET['G1']) && $_GET['G1'][0]=='X' && strlen($_GET['G1'])==7) $colorG1='#'.substr($_GET['G1'],1);
	if(isset($_GET['G2']) && $_GET['G2'][0]=='X' && strlen($_GET['G2'])==7) $colorG2='#'.substr($_GET['G2'],1);
	header("Cache-Control: public max-age=86400");
	header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>
/*<style>*/
.lrc-active{
	color:<?php echo $color ?>;
}
<?php if($colorG1 != "" && $colorG2 != ""){ ?>
.player-icon:hover{
	color:<?php echo $colorG1 ?>;
}
.player-icon,
.player-icon:focus,
.player-icon:visited{
	color:<?php echo $colorG2 ?>;
}
.player-processbar:hover>.player-processbar-i{
	background-color:<?php echo $colorG1 ?>;
}
.player-processbar-i{
	background-color:<?php echo $colorG2 ?>;
}
<?php } else { ?>
.player-icon:hover{
	color:<?php echo $color ?>;
}
.player-icon,
.player-icon:focus,
.player-icon:visited{
	color:<?php echo $color4 ?>;
}
.player-processbar-i{
	background-color:<?php echo $color ?>;
}
<?php } ?>

/* 人声倒放 */
.lrc-active .reverse-sound-o {
  background-color: <?php echo $color ?>;
}
.lrc-active .reverse-sound-i {
  background-color: <?php echo $color ?>;
}
/*</style>*/
