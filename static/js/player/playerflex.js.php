<?php
header("Content-Type: text/javascript");
$w=($_GET["w"]=="1");
error_reporting(E_ALL & (~E_NOTICE));
header("Cache-Control: public max-age=1296000");
header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>

/*<script>*/

function autofit(){
	//ProcessBar
	$('.player-processbar').css("width",
		($('.right-second-row').width()-$('#play-button').width()-$('#repeat-button').width()-<?php echo $w?"18":"24" ?>)
	.toString()+"px");

	//LyricArea
	$('.lrc-content').css("height",
		(((
			navigator.userAgent.toLowerCase().indexOf('firefox')>=0?
				$('html').height()-52:$('body').height()
		)-$('header').height())-$('.lrc-overview').height()-16-18-18<?php echo $w?"-60":"" ?>)
	.toString()+"px");

	//SyncButton
	$('#sync-button').css("top",
		($('.lrc-content').offset().top + $('.lrc-content').height() + 20 - $('#sync-button').height() - 8)
	.toString()+"px");
	$('#sync-button').css("left",
		($('.lrc-content').offset().left + 14 )
	.toString()+"px");
}

window.onresize=autofit;

window.onload=autofit;

//Strict autofit
setInterval(autofit,1500);

/*</script>*/
