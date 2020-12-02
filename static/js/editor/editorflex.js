"mod EditorFlex";

function autofit(){
    //LyricArea autofit
    $('#lyricfile').css("height",
        ($('.txmp-page-left').height()-52-$('#toolbox').height()+(G.is_wap?64:0))
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

if(!G.is_wap) {
    window.onresize=autofit;
}
