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

/* <style> */

a,
a:hover,
a:focus,
a:visited{
	color:<?php echo $color ?>;
}

.txmp-header {
	background-color:<?php echo $color ?>;
	color:<?php echo $color2 ?>;
}
<?php if($colorG1 != "" && $colorG2 != ""){ ?>
.txmp-header {
	background-image:linear-gradient(135deg,<?php echo $colorG1 ?>,<?php echo $colorG2 ?>);
}
.cl-g-2 {
	color:<?php echo $colorG2 ?>;
}
.bcl-g-2 {
	background-color:<?php echo $colorG2 ?>;
}
<?php } ?>

body ::selection {
	background-color:<?php echo $color ?>55;
}

body ::-moz-selection {
	background-color:<?php echo $color ?>55;
}

.am-list-news:focus h2 {
	color:<?php echo $color ?>;
}

<?php if($colorG1 != "" && $colorG2 != "") { ?>
.am-list-news:focus .list-focus {
	background-image: linear-gradient(135deg,<?php echo $colorG1 ?>33,<?php echo $colorG2 ?>33);
}
<?php } else { ?>
.am-list-news:focus .list-focus {
	background-image: linear-gradient(135deg,<?php echo $color ?>22,<?php echo $color ?>33);
}
<?php } ?>

/* ----- PATCHING ----- */
.am-modal-btn {
	color:<?php echo $color ?>;
}

/* </style> */
