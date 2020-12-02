"mod PlayerFlex";

function autofit(){
	//ProcessBar
	$('.player-processbar').css("width",
		($('.right-second-row').width()-$('#play-button').width()-$('#repeat-button').width()-(G.is_wap ? 18 : 24))
	.toString()+"px");

	//LyricArea
	$('.lrc-content').css("height",
		(((
			navigator.userAgent.toLowerCase().indexOf('firefox')>=0?
				$('html').height()-52:$('body').height()
		)-$('header').height())-$('.lrc-overview').height()-16-18-18+(G.is_wap?-60:0))
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

setInterval(autofit,1500);
