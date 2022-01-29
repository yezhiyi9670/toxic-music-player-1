"mod EditorFlex";

function autofit(){
	//LyricArea autofit
	// $('#lyricfile').css("height",
	// 	($('.txmp-page-left').height()-52-$('#toolbox').height()+(G.is_wap?64:0))
	// .toString()+"px");
	// $('#lyricfile').css("width",
	// 	($('.txmp-page-left').width())
	// .toString()+"px");
	lyricEditor.setSize(
		$('.txmp-page-left').width(),
		$('.txmp-page-left').height()-52-$('#toolbox').height()+(G.is_wap?128:0)
	);
	//Android autofit keyboard bug. Disable autofit if wap.
}

if(!G.is_wap) {
	setResizeFunc(autofit);
}
