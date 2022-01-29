"mod PlayerFlex";

function autofit(){
	// 进度条宽度
	$('.player-processbar').css("width",
		($('.right-second-row').width()-$('#play-button').width()-$('#repeat-button').width()-(G.is_wap ? 18 : 24))
	.toString()+"px");

	// 歌词区域
	$('.lrc-content').css("height",
		(((
			navigator.userAgent.toLowerCase().indexOf('firefox')>=0?
				$('html').height()-52:$('body').height()
		)-($('header').height() ?? 48.67))-$('.lrc-overview').height()-16-18-18+(G.is_wap?20:-4))
	.toString()+"px");

	// 控制按钮
	if(!G.is_wap) {
		$('.lyric-controls').css("top",
			($('.lrc-content').offset().top + $('.lrc-content').height() + 20 - 36 + 4)
		.toString()+"px");
		$('.lyric-controls').css("left",
			($('.lrc-content').offset().left + $('.lrc-content').width() - 36 + 22 )
		.toString()+"px");
	} else {
		$('.lyric-controls').css("top",
			($('.txmp-page-right').offset().top - 36 -32)
		.toString()+"px");
		$('.lyric-controls').css("right",
			(20)
		.toString()+"px");
	}
}

setResizeFunc(autofit);

window.onload = autofit;

// setInterval(autofit,1500);
