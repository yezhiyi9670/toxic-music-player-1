"mod PlayerFlex";

function autofit(){
	let isFirefox = $('body').height() == 0;
	let headerDefault = 0;
	if(!isFirefox) {
		headerDefault = 48.67;
	}

	// 进度条宽度
	$('.player-processbar').css("width",
		($('.right-second-row').width()-$('#play-button').width()-$('#repeat-button').width()-(G.is_wap ? 18 : 24))
	.toString()+"px");

	// 歌词区域
	$('.lrc-content').css("height",
		(((
			isFirefox ?
				$('html').height()-52:$('body').height()
		)-($('header').height() ?? headerDefault))-$('.lrc-overview').height()-16-18-18+(G.is_wap?20:-4))
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

	// 移动端封面页
	if(G.is_wap) {
		let totalwidth = $('.txmp-coverpage-pic-container').width();
		let totalheight =
			(isFirefox ? $('html').height() - 52 : $('body').height())
			- ($('header').height() ?? headerDefault);
		console.log(isFirefox ? $('html').height() - 52 : $('body').height());
		let ch_height = 160 + 64;
		let pad = 0;
		let remain = totalheight - ch_height - totalwidth;
		let $flex = $('.txmp-coverpage-flex');
		if(remain < 0) {
			$flex.css('height', 0);
			$('.txmp-coverpage-pic')
				.css('height', remain + totalwidth - 16)
				.css('width', remain + totalwidth - 16);
		} else {
			remain -= pad;
			$('.txmp-coverpage-pic')
				.css('height', totalwidth - 16)
				.css('width', totalwidth - 16);
			if(remain < 0) {
				remain = 0;
			}
			$($flex[0]).css('height', (remain / 3 * 1) + 'px');
			$($flex[1]).css('height', (remain / 3 * 1) + 'px');
			$($flex[2]).css('height', (remain / 3 * 1) + 'px');
			$($flex[3]).css('height', (remain / 3 * 0) + 'px');
		}
	}
}

setResizeFunc(autofit);

// setInterval(autofit,1500);
