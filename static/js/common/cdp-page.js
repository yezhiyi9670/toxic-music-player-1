var pagecount = 0;
$('.cdp-page').each(function(){
	$(this).attr('data-cdp-id',++pagecount);
	if(!cdp_nav_gettext) {
		$('#cdp-header-nav').append($(`
			<li class="cdp-nav-item" id="cdp-nav-${pagecount}"><a onclick="turn_page(${pagecount} - currpage)">${pagecount}：${$(this).attr('data-cdp-name')}</a></li>
		`));
	} else {
		$('#cdp-header-nav').append($(`
			<li class="cdp-nav-item" id="cdp-nav-${pagecount}"><a onclick="turn_page(${pagecount} - currpage)">${pagecount}：${cdp_nav_gettext(this)}</a></li>
		`));
	}
});
var currpage = 1;
$('.cdp-page').css('display','none');
$('[data-cdp-id='+pagecount+']').css('display','block');

$('#page-now').text(currpage);
$('#page-tot').text(pagecount);
$('#page-name').text($('[data-cdp-id='+pagecount+']').attr('data-cdp-name'));

function turn_page(p) {
	var g = currpage + p;
	if(g < 1 || g > pagecount) return;
	currpage = g;
	$('.cdp-page').css('display','none');
	$('[data-cdp-id='+currpage+']').css('display','block');
	
	$('#page-now').text(currpage);
	$('#page-tot').text(pagecount);
	$('#page-name').text($('[data-cdp-id='+currpage+']').attr('data-cdp-name'));

	$('#cdp-header-text').width($('body').width() - 132);

	$('.cdp-nav-item').children().css('background-color','');
	$('#cdp-nav-'+currpage).children().css('background-color','#DDD');
}

function prev_page() {
	turn_page(-1);
}

function next_page() {
	turn_page(1);
}

setInterval(function(){
	$('#cdp-header-text').width($('body').width() - 132);
},500);
