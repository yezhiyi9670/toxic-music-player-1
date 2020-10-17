<?php
header("Content-Type: text/javascript");
$w=($_GET["w"]=="1");
error_reporting(E_ALL & (~E_NOTICE));
header("Cache-Control: public max-age=1296000");
header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>

function autofit(){
    //LyricArea autofit
    $('#lyricfile').css("height",
        ($('.txmp-page-left').height()-52-$('#toolbox').height()<?php if($w) echo "+64" ?>)
    .toString()+"px");
    $('#lyricfile').css("width",
        ($('.txmp-page-left').width())
    .toString()+"px");
    
    $('#lyricfile').setTextareaCount({
	  	width: "30px",
		bgColor: "rgba(240,240,240,0.7)",
		color: "#777",
		display: "block",
	});
    //Android autofit keyboard bug. Disable autofit if wap.
}

<?php if(!$w){ ?>
window.onresize=autofit;
<?php } ?>

//Strict autofit
//setInterval(autofit,500);

