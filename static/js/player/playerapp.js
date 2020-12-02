"mod PlayerApp";

var Cache={};
var isNormal=false;
var curr=0;
var preRead_threads = 0;
var preRead_count = 0;

function strprefix(str,pre="0",len=2)
{
	while(str.length<len) str=pre+str;
	return str;
}

function rnum(num,len=2)
{
	num=num.toString();
	if(num.indexOf('.')==-1) num+='.';

	while(num.length-num.indexOf('.')-1<len) num+='0';
	while(num.length-num.indexOf('.')-1>len) num=num.substring(0,num.length-1);

	return num;
}

function ftime(t)
{
	return strprefix(Math.floor(t/60).toString())+":"+strprefix(Math.floor(t%60).toString());
}

var scroller_interval=-1;
var scroller_sqrt = [];
function scrollto(v,s,d=(G.is_wap ? 12 : 28))
{
	if(scroller_interval != -1) {
		clearInterval(scroller_interval);
		scroller_interval = -1;
	}

	if(v < 0) v = 0;

	var ele=$(s)[0];
	var old=ele.scrollTop;
	if(old==v) return;

	var E=(v-old)/scroller_sqrt[d];
	var I=0;
	scroller_interval = setInterval(function(){
		E = (v-ele.scrollTop)/scroller_sqrt[d-I];
		ele.scrollTop+=E;
		I++;
		if(I>=d || (ele.scrollTop-v)/E >=0) {
			clearInterval(scroller_interval);
			scroller_interval = -1;
		}
	},G.is_wap ? 20 : 10);
}

var A;
var loaded=false;
var S=true;
var lrct=[];
var lrnt=[];
var highlight_lyric=function(st){
	var f=A.currentTime;
	//console.log("S",f);
	f=Math.floor(f*10);
	f=lrct[f];
	//console.log("E",f);
	if(f>=0){
		if(!$('#lrc-'+data['timestamps'][f][1]).hasClass('lrc-active') || st)
		{
			var i;
			var m=document.querySelectorAll('.para.para-active');
			for(i=0;i<m.length;i++) $(m[i]).removeClass('para-active');
			m=document.querySelectorAll('.lrc-item.lrc-active');
			for(i=0;i<m.length;i++) $(m[i]).removeClass('lrc-active');
			m=document.querySelectorAll('.lo-item.lo-active');
			for(i=0;i<m.length;i++) $(m[i]).removeClass('lo-active');
			$('#lrc-'+data['timestamps'][f][1]).addClass('lrc-active');
			$('#para-'+data['timestamps'][f][0]).addClass('para-active');
			$('#lo-'+data['timestamps'][f][0]).addClass('lo-active');

			if(S)
			{
				scrollto(
					$('.lrc-content')[0].scrollTop+
					$('#lrc-'+data['timestamps'][f][1]).offset().top-
					$('.lrc-content').offset().top+
					$('#lrc-'+data['timestamps'][f][1]).height()/2-
					$('.lrc-content').height()/2
				,".lrc-content");
			}
		}
	}
	else{
		if($('.lrc-active').length || st){
			var i;
			var m=document.querySelectorAll('.para.para-active');
			for(i=0;i<m.length;i++) $(m[i]).removeClass('para-active');
			m=document.querySelectorAll('.lrc-item.lrc-active');
			for(i=0;i<m.length;i++) $(m[i]).removeClass('lrc-active');
			m=document.querySelectorAll('.lo-item.lo-active');
			for(i=0;i<m.length;i++) $(m[i]).removeClass('lo-active');
			if(S) scrollto(0,".lrc-content");
		}
	}
	$('.lrc-interval-item').each(function(idx){
		var lrcline=$($('.lrc-interval-item')[idx]);
		if(lrcline.hasClass('lrc-interval-item')) {
			var thistime=lrcline[0].getAttribute('data-time');
			var nxttime=lrnt[thistime]/10;
			thistime/=10;
			var remain=Math.floor(nxttime-A.currentTime);
			if(remain<0) remain=0;
			if(remain>10) remain=10;
			var txt="";
			if(lrcline.hasClass('lrc-active')) {
				for(var i=1;i<=10;i++) {
					if(i>1) txt+=' ';
					if(i>remain) txt+='○';
					else txt+='●';
				}
				lrcline.css({'font-family':LNG('player.font.simsun')});
			}
			else {
				if(thistime==0) txt='&gt; ' + LNG('player.lyric.restart') + ' &lt;';
				else txt='- - - - - - -';
				lrcline.css({'font-family':''});
			}
			lrcline.children().html(txt);
		}
	});
};

var A_is_txt_loading=false;
var list_idx = {};
$('document').ready(function(){
	for(var i=0;i<=110;i++) scroller_sqrt[i] = Math.pow(i,0.72);

	$(function() {
		$('.title-dropdown-father').dropdown();
	});

	A=document.querySelector('#audio');
	//A.src=yp_src;
	A.load();
	//A.loop="loop";

	if(!localStorage[G.app_prefix+'-zoom'] && !G.is_wap) {
		localStorage[G.app_prefix+'-zoom'] = '1.2';
	}

	if(localStorage[G.app_prefix+'-volume']) A.volume=localStorage[G.app_prefix+'-volume'];
	if(localStorage[G.app_prefix+'-zoom']) $('.lrc-content').css('font-size',(localStorage[G.app_prefix+'-zoom']*15)+'px');

	if(isCloudSave) document.title=titleformat.replace('%{list_name}',data['meta']['N']+' < '+cloudData['title']);

	A.onerror=function(){
		if(A_is_txt_loading) return; //文字地址加载中，忽略出错信息

		modal_alert(LNG('ui.error'),LNG('player.alert.audio.fail',A.error.code));
		if(A.error.code!=3) return;

		// gugugu? 无脑Skip
		var nxt=0;
		if(isRand) nxt=Math.floor(Math.random()*list.length);
		else nxt=(curr+1)%list.length;

		curr=nxt;
		localStorage[G.app_prefix+'-procsave-'+list[nxt]]=0;
		changeTo(list[nxt],true);
	}


	A.oncanplay=playInit;

	if(isList) {
		for(var i=0;i<list.length;i++) {
			list_idx[list[i]] = i;
		}
	}

	//歌词高亮&进度显示&播放按钮
	//精确度：0.1s
	var update_f=function(){
		//modal_alert('fake',A.src.substr(A.src.length-4));
		if(A.src.substr(A.src.length-4) == '.url') { //震惊，是TXT url
			if(A_is_txt_loading) return; //正在加载，则不发起新的加载进程
			A_is_txt_loading = true;
			$.ajax({
				async:true,
				timeout:20000,
				dataType:'text',
				url: A.src,
				error: function(e) {
					A_is_txt_loading = false;
				},
				success: function(e) {
					A_is_txt_loading = false;
					if(e.length < 7) { // 长度小于'http://'，肯定咕
						//
					}
					else {
						var flag= (A.getAttribute('data-rplay')=='yes');
						A.src=e;
						if(flag) A.play();
					}
				}
			});
		}

		if(!isNormal) return;

		document.querySelector('#total-len').innerHTML=ftime(A.duration);
		document.querySelector('#elasped').innerHTML=
			ftime(A.currentTime);
		document.querySelector('#remain').innerHTML=
			ftime(Math.floor(A.duration)-Math.floor(A.currentTime));
		document.querySelector('#accurate').innerHTML=
			rnum(A.currentTime);
		document.querySelector('.player-processbar-i').style.width=
			(A.currentTime*100/A.duration).toString()+"%";

		var X=$('#play-button');
		if(A.paused)
		{
			X.addClass("fa-play-circle");
			X.removeClass("fa-pause-circle");
			$('.song-avatar img').removeClass('dynamic-spin');
		}
		else
		{
			X.removeClass("fa-play-circle");
			X.addClass("fa-pause-circle");
			$('.song-avatar img').addClass('dynamic-spin');
		}

		localStorage[G.app_prefix+'-procsave-'+song_id]=A.currentTime;

		//歌词高亮
		//精确度：0.1s * 10
		// Fix lyric-position related issue
		autofit();
		highlight_lyric(false);
	};
	setInterval(update_f,100);

	setInterval(function(){
		var b=A.buffered;
		for(var i=0;i<1024;i++)
		{
			$('#psbo-'+i).remove();
			if(i>=b.length) continue;
			var $n=$(`
			<div class="player-processbar-o" id="psbo-${i}" style="width:${
				(b.end(i)-b.start(i))*100/A.duration
			}%;left:${
				(b.start(i))*100/A.duration
			}%">
			</div>
			`);
			$('.player-processbar').append($n);
		}
	},1000);


	//设定进度调节函数
	var process=function(e){
		var realx = e.pageX - this.getBoundingClientRect().left;
		var xmp = realx / $(this).width();
		var t = A.duration*xmp;
		A.currentTime=t;
	};
	var processwheel=function(e){
		var t=A.currentTime;
		t+=-e.wheelDelta/120;
		if(t>A.duration) t=A.duration;
		if(t<0) t=0;
		A.currentTime=t;
		return false;
	}


	//设定进度调节
	document.querySelector('.player-processbar').onmousedown=process;
	document.querySelector('.player-processbar').onmousewheel=processwheel;


	showSongList();
	playPreInit();

	var str = location.href.substr(location.href.lastIndexOf('#'));

	if(str.match(/^\#id\-(\w+)$/)) {
		changeTo(str.match(/^\#id\-(\w+)$/)[1]);
	}

	// 显示菜单按钮
	$('#right-fold-menu').css('display','inline-block');

	// 设置菜单标签行为
	$('.rmenu-tab').click(function(){
		var tag = $(this).attr('data-tab');
		$('.rmenu-tab').removeClass('rmenu-tab-active');
		$('.rmenu-tab-' + tag).addClass('rmenu-tab-active');
		$('.rmenu-content').css('display','none');
		$('.rmenu-content-'+tag).css('display','block');
	});
});

function showSongList() {
	var obj=$('#rmenu-list-showbox')[0];
	var txt='';
	for(var i=0;i<list.length;i++) {
		txt+='<li style="border-top:1px dotted #CCC" class="song-list-item" id="song-list-item-' + list[i] + '"';
		if(song_id == list[i]) {
			txt += ' data-current-play';
		}
		txt += ' data-id="'+list[i]+'"';
		txt += '';
		txt+='>';

		txt+='<a  onclick="changeTo(\''+list[i]+'\')"'+(song_id==list[i]? ' style="font-weight:700;"':'')+'>';
		txt+=listName[i];
		txt+='</a>';

		txt+='</li>';
	}
	obj.innerHTML=txt;


	if(isCloudSave) for(var i=0;i<list.length;i++) {
		$('#list-rating-' + list[i]).html(myRating[list[i]]);
	}

	if(isCloudSave) for(var i=0;i<list.length;i++) {
		$('#list-id-' + list[i]).html(myIds[list[i]]);
		if(list[i] != myIds[list[i]]) {
			var idEle = $('#list-id-' + list[i]);
			idEle.removeClass('tag-default');
			idEle.addClass('tag-canonical');
		}
	}

	setTimeout(function(){
		showPlaytimes();
	},1);
}


function trackSwitch() {
	var obj=$('.song-title')[0];
	if(obj.getAttribute("data-back")!="yes") {
		modal_alert(LNG('player.alert.no_bg'),LNG('player.alert.no_bg.tips'));
	}
	else {
		var T=A.currentTime;
		var H=A.paused;
		A.oncanplay=playInit;
		isNormal=false;
		if(obj.style.fontStyle=="italic") {
			obj.style.fontStyle="";
			A.src=src1;
		}
		else {
			obj.style.fontStyle="italic";
			A.src=src2;
		}
		A.load();
		if(H) A.pause();
		else A.play();
	}
}

async function downloadAudio(url) {
	if($('.download-button .tag-rplim-paydl').length) {
		if(!await modal_confirm_p(LNG('player.alert.paydl'),LNG('player.alert.paydl.tips'),LNG('ui.cancel'),LNG('ui.continue'))) {
			return;
		}
	}
	window.open(url);
}

function play_click(x)
{
	if(!loaded) A.load();
	else if(A.paused){
		A.play();
		x.removeClass("fa-play-circle");
		x.addClass("fa-pause-circle");
	}
	else{
		A.pause();
		x.addClass("fa-play-circle");
		x.removeClass("fa-pause-circle");
	}
}

function rep_click(x)
{
	if(!x.hasClass('fa-repeat')){
		x.removeClass("fa-circle-o");
		x.addClass("fa-repeat");
		if(isList) A.onended = RP_next;
		else A.loop = 'loop';
	}
	else{
		x.addClass("fa-circle-o");
		x.removeClass("fa-repeat");
		if(isList) A.onended = AK_next;
		else A.removeAttribute('loop');
	}
}

function stop_click(x)
{
	A.pause();
	x=$('#play-button');
	x.addClass("fa-play-circle");
	x.removeClass("fa-pause-circle");
	A.load();
}

function roll_toggle(st)
{
	if(!st){
		S=false;
		$('#sync-ico').removeClass("fa-location-arrow");
		$('#sync-ico').addClass("fa-map-marker");
	}
	else {
		S=true;
		$('#sync-ico').addClass("fa-location-arrow");
		$('#sync-ico').removeClass("fa-map-marker");
	}
}

function scrollToPara(x)
{
	var d=$('#para-'+x);
	var c=$('.lrc-content');
	scrollto(
		c[0].scrollTop+
		d.offset().top-
		c.offset().top-
		16
	,".lrc-content");
}

function showPlaytimes() {
	for(var i=0;i<list.length;i++) {
		$('#list-playtimes-'+list[i]).html(
			getPlaytimes('begin-'+list[i]) + '/' + getPlaytimes('finish-'+list[i])
		);
	}
}

function addPlaytimes(id) {
	var num_id = G.app_prefix+'-playtimes-'+id;
	var date_id = G.app_prefix+'-playdate-'+id;

	if(localStorage[date_id] != curr_date_str) {
		localStorage[date_id] = curr_date_str;
		localStorage[num_id] = 0;
	}
	localStorage[num_id]++;

	showPlaytimes();
}

function getPlaytimes(id) {
	var num_id = G.app_prefix+'-playtimes-'+id;
	var date_id = G.app_prefix+'-playdate-'+id;

	if(localStorage[date_id] != curr_date_str) {
		localStorage[date_id] = curr_date_str;
		localStorage[num_id] = 0;
	}
	return localStorage[num_id];
}

function validate_constraints(a,b,cs) {
	var x = a * cs['multiplier'] + (1 * cs['delta']);
	// console.log("Evaluated = ",x);
	var cp = cs['comparator'];
	if(cp == '<') return b<x;
	if(cp == '>') return b>x;
	if(cp == '<=') return b<=x;
	if(cp == '>=') return b>=x;
	if(cp == '!=') return b!=x;
}

var AK_next;
var CS_next;

function playPreInit() {
	if(isCloudSave) {
		if(G.username != cloudUser) {
			$('.edit-label').html(LNG('player.list.edit.he'));
			if(G.username == '') {
				$('.edit-label').parent().parent().remove();
			}
		}
	}

	$('.menu-curr-display').html(listName[curr]);
	$('.menu-curr-display > .addition-cmt > .txmp-tag.tag-blue-g').remove();
	$('.menu-curr-display > .addition-cmt > .txmp-tag.tag-purple-g').remove();

	if(isList) {
		var nxt=0;
		if(!isCloudSave) {
			if(isRand) nxt=Math.floor(Math.random()*list.length);
			else nxt=(curr+1)%list.length;
		} else {
			var nxtList = [];
			if(!cloudData['transform']['constraints2']) {
				cloudData['transform']['constraints2'] = {};
				cloudData['transform']['constraints2']['comparator'] = '>';
				cloudData['transform']['constraints2']['multiplier'] = 0;
				cloudData['transform']['constraints2']['delta'] = -1;
			}
			for(var i=0;i<list.length;i++) {
				if(validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints']) && validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints2'])) {
					$('#song-list-item-' + list[i]).css('background-color', '#DFD');
					nxtList[nxtList.length] = list[i];
				} else {
					$('#song-list-item-' + list[i]).css('background-color', '#FDD');
				}
			}
			if(nxtList.length == 0) {
				var action = cloudData['transform']['termination'];
				if(action == 'end') nxt = false;
				else if(action == 'loop') nxt = curr;
				else nxt=Math.floor(Math.random()*list.length);
			}
			else if(isRand) {
				var sel = nxtList[Math.floor(Math.random() * nxtList.length)];
				nxt = -1;
				for(var i=0;i<list.length;i++) {
					if(sel == list[i]) {
						nxt = i;
						break;
					}
				}
			} else {
				nxt = -1;
				// console.log('iakioi');
				for(var i=(curr+1) % list.length;1;) {
					if(validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints']) && validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints2'])) {
						nxt = i;
						// console.log('Select ',list[i]);
						break;
					}
					i = (i + 1) % list.length;
				}
			}
		}

		if(nxt === false) {
			AK_next = function(){addPlaytimes('finish-'+song_id);};
		} else {
			AK_next = function(){
				addPlaytimes('finish-'+song_id);
				curr=nxt;
				localStorage[G.app_prefix+'-procsave-'+list[nxt]]=0;
				changeTo(list[nxt],true);
			}
		}

		RP_next = function(){
			addPlaytimes('finish-'+song_id);
			localStorage[G.app_prefix+'-procsave-'+list[curr]]=0;
			changeTo(list[curr],true);
		};

		if($('#repeat-button').hasClass('fa-repeat')) A.onended = RP_next;
		else A.onended = AK_next;

		if(nxt !== false && preRead_threads < 3) {
			preRead_threads++;
			preCache(list[nxt],5); //自动预读5次，尽量防止切换时出现故障
		}
	}
	else {
		A.onended=function() {
			addPlaytimes('finish-'+song_id);
		}
	}

	var transform_lyric_html = function() {
		$('.reverse-sound').each(function(i){
			this.outerHTML = `
			<ruby>
				${this.innerHTML}
				<rt>
					<div class="reverse-sound-i"></div>
					<div class="reverse-sound-o"></div>
				</rt>
			</ruby>
			`;
		});
	};

	transform_lyric_html();
}

function playInit(){
	loaded=true;

	if(localStorage[G.app_prefix+'-procsave-'+song_id]<A.duration-1) {
		A.currentTime=localStorage[G.app_prefix+'-procsave-'+song_id];
	}

	setTimeout(function(){highlight_lyric(true)},600);

	//歌词高亮预处理
	var current=-1;
	for(var i=0;i<=Math.floor(72000);i++)
	{
		if(data['timestamps'][i]) current=i;
		lrct[i]=current;
	}
	current=Math.floor(A.duration*10);
	for(var i=Math.floor(72000);i>=0;i--) {
		lrnt[i]=current;
		if(data['timestamps'][i]) current=i;
	}
	A.oncanplay=null;

	addPlaytimes('begin-'+song_id);


	isNormal=true;

	//设定歌词
	var F=document.querySelectorAll('.lrc-text');
	for(i=0;i<F.length;i++)
	{
		if(F[i].getAttribute('data-time')<=1610612735) F[i].onclick=function(e){
			A.currentTime=this.getAttribute("data-time")/10 + 0.0001;
			roll_toggle(true);
			return false;
		}
		//F[i].title="右键跳到本句歌词："+ftime(F[i].getAttribute("data-time"));
	}

	A.setAttribute('data-rplay','no');
}

setInterval(function(){
	var nxt=Math.floor(Math.random()*list.length);
	var i=0;
	while(i<64 && Cache[list[nxt]]) {
		nxt=Math.floor(Math.random()*list.length);i++;
	}
	if(i>=64) return;
	if(preRead_threads >= 1)return;
	preRead_threads++;
	preCache(list[nxt],3);
},4000); //随机预读歌曲元数据。最多读取3次。

function switchContent(e,dst_song,flag=false) {
	var a=$('.lrc-area')[0];
	var b=$('.right-first-row')[0];
	var d=$('.right-third-row')[0];
	// var c=$('.right-third-row-x')[0];
	var obj=$('.song-title')[0];

	var isItalic=(obj.style.fontStyle=='italic');

	a.innerHTML=e[2];
	b.innerHTML=e[3];
	// c.innerHTML=e[4];
	d.innerHTML=e[5]+e[4];
	song_id=dst_song;
	for(var i=0;i<list.length;i++) {
		if(list[i]==song_id) curr=i;
	}
	obj=$('.song-title')[0]; //被改动，重新选择

	$(function() {
		$('.title-dropdown-father').dropdown();
	});

	showSongList();
	$('#filter-terms').val('');

	data=JSON.parse(e[0]);
	//console.log("出锅点：",e[1]);
	var metadata=JSON.parse(e[1]);

	baseurl=metadata.baseurl;
	src1=metadata.src1;
	src2=metadata.src2;

	$('#mainColoredCss')[0].href=metadata.main_colored_css;
	$('#playerColoredCss')[0].href=metadata.player_colored_css;

	isNormal=false;

	A.oncanplay=playInit;
	A.src=metadata.src1;
	A.load();
	//A.outerHTML=A.outerHTML;
	//A=document.querySelector('#media');
	if(flag) A.play();
	if(flag) A.setAttribute('data-rplay','yes');
	//playInit();

	document.title=metadata.title;
	if(isCloudSave) document.title=titleformat.replace('%{list_name}',data['meta']['N']+' < '+cloudData['title']);

	if(localStorage[G.app_prefix+'-zoom']) $('.lrc-content').css('font-size',(localStorage[G.app_prefix+'-zoom']*15)+'px');

	location.href='#id-'+dst_song;
}

function preCache(dst_song,remain) {
	if(remain==0) {preRead_threads--;return;} //重试次数用完则放弃
	if(!Cache[dst_song]) $.ajax({
		async:true,
		timeout:20000,
		dataType:"text",
		url:home+dst_song+"/switch-all?wap="+"<?php echo $w?'force-phone':'force-pc' ?>",
		error:function(res) {
			preCache(dst_song,remain-1); //预读gugugu？重试
			return;
		},
		success:function(res) {
			var e=res.split("\n--------TxmpSwitchDataBoundary--------\n");
			if(e.length!=6) {
				preCache(dst_song,remain-1);
				return;
			}
			Cache[dst_song]=e;
			console.log(LNG('player.debug.preread_success',dst_song));
			preRead_threads--;
		},
	});
}

function changeTo(dst_song,flag=false) {
	var al=modal_loading(LNG('player.alert.switch'),LNG('player.alert.switch.tips'));

	setTimeout(function(){

		//及时获取
		if(!Cache[dst_song]) $.ajax({
			async:true,
			timeout:30000,
			dataType:"text",
			url:home+dst_song+"/switch-all?wap="+"<?php echo $w?'force-phone':'force-pc' ?>",
			error:function(res) {
				modal_alert(LNG('ui.error'),LNG('player.alert.switch.fail'));
				close_modal(al);
				return;
			},
			success:function(res) {
				var e=res.split("\n--------TxmpSwitchDataBoundary--------\n");
				if(e.length!=6) {
					modal_alert(LNG('ui.error'),LNG('player.alert.switch.illegal'));
					close_modal(al);
					return;
				}
				close_modal(al);
				Cache[dst_song]=e;
				switchContent(e,dst_song,flag);
				playPreInit();
			},
		});
		else {
			close_modal(al);
			switchContent(Cache[dst_song],dst_song,flag);
			//Cache[dst_song]=null;
			playPreInit();
		}

	},1);
}



async function listEdit() {
	if(!await modal_confirm_p(LNG('player.alert.edit'),LNG('player.alert.edit.tips'))) return;
	if(!isCloudSave) {
		var txt=
			home+'list-maker?list=';
		for(var i=0;i<ordlist.length;i++) {
			if(i!=0) txt+='|';
			txt+=ordlist[i];
		}
		if(isRand) txt+='&randList';
		if(isRandShuffle) txt+='&randShuffle';
	} else {
		if(G.username == cloudUser) {
			var txt = home + 'list-maker/' + cloudId;
		} else {
			var fetch_raw_data = () => new Promise((resolve,reject) => {
				$.ajax({
					async: true,
					timeout: 10000,
					url: '?raw',
					method: 'GET',
					dataType: 'text',
					error: function(e) {
						resolve(undefined);
					},
					success: function(e) {
						resolve(e);
					}
				});
			});

			var str = '';
			var wnd = modal_loading(LNG('ui.wait'),LNG('player.alert.he.tips'));
			if(!isCsv) str = JSON.stringify(cloudData);
			else str = await fetch_raw_data();
			if(str === undefined) {
				await modal_alert_p(LNG('ui.error'),LNG('player.alert.he.fail'));
				return;
			}
			$.ajax({
				async: true,
				timeout: 10000,
				url: home + 'playlist/save-list/0',
				data: {
					'str': str,
					'isCsv': (isCsv ? 'yes' : 'no'),
					'isSubmit': 'yes',
					'isAjax': 'yes',
					'csrf-token-name': G.csrf_s1,
					'csrf-token-value': G.csrf_s2,
				},
				method: 'POST',
				dataType: 'text',
				error: function(e) {
					console.error("Error: ",e);
					close_modal(wnd);
					modal_alert(LNG('ui.error'),LNG('player.alert.he.fail'));
				},
				success: function(e) {
					console.log(e);
					if(e && e[0] != '+') {
						close_modal(wnd);
						modal_alert(LNG('ui.error'),e);
					} else {
						var id = e.substr(1);
						location.href = home + 'list-maker/' + id;
					}
				}
			});
			return;
		}
	}
	location.href=txt;
}

function listPrint() {
	var txt=home+ordlist[0]+'/docs';
	if(!isCloudSave) {
		txt+='?list=';
		for(var i=1;i<ordlist.length;i++) {
			if(i!=1) txt+='|';
			txt+=ordlist[i];
		}
	} else {
		txt = home + 'playlist/gen-docs/' + cloudUser + '/' + cloudId;
	}
	window.open(txt);
}

function switchNext() {
	if(isList) A.onended();
	else location.reload();
}

async function setVolume() {
	var txt=await modal_prompt_p(LNG('player.alert.volume'),LNG('player.alert.volume.tips'),A.volume);
	if(!txt) return;
	localStorage[G.app_prefix+'-volume']=txt;
	A.volume=txt;
}

async function setZoom() {
	var txt=await modal_prompt_p(LNG('player.alert.zoom'),LNG('player.alert.zoom.tips'),localStorage[G.app_prefix+'-zoom']);
	if(!txt) return;
	localStorage[G.app_prefix+'-zoom']=txt;
	$('.lrc-content').css('font-size',(txt*15)+'px');
	return false;
}

function filterPlaylistByQuery(txt) {
	var qrs = txt.split('+');
	for(var i=0;i<qrs.length;i++) {
		qrs[i] = qrs[i].trim();
	}

	$lst = $('.song-list-item');
	$lst.each(function(idx) {
		$ele = $($lst[idx]);

		// I:0274
		var id = $ele.attr('data-id');

		// X:0
		var idx = list_idx[id];

		// N:Sample
		var name = listMeta[idx]['N'];

		// S:Undefined
		var singer = listMeta[idx]['S'];

		// C:--
		var collection = listMeta[idx]['C'];

		// A:--
		// LA:--
		var lyricAuthor = listMeta[idx]['LA'];
		// MA:--
		var musicAuthor = listMeta[idx]['MA'];

		// M:3714  mark == 3714
		// ML:3714  mark <= 3714
		// MG:3714  mark >= 3714
		var mark = myRating[id];

		// Postprocess id
		id = myIds[id];

		// Count: 8
		var inf = [id,idx,name,singer,collection,lyricAuthor,musicAuthor,mark];

		var vl_all = function(a,b) {
			for(var i=0;i<=6;i++) {
				if(i!=1) {
					if(("" + a[i]).indexOf(b) != -1) return true;
				}
			}
			return false;
		}

		var vl_qr = function(a,b) {
			// console.log(a,b);

			var tp = b.indexOf(':');
			if(tp == -1) {
				return vl_all(a,b);
			}

			var str = b.substr(tp+1);
			tp = b.substr(0,tp);

			if(tp == 'I') {
				return a[0] == str;
			} else if(tp == 'X') {
				return ("" + a[1]) == str;
			} else if(tp == 'N') {
				return a[2].indexOf(str) != -1;
			} else if(tp == 'S') {
				return a[3].indexOf(str) != -1;
			} else if(tp == 'C') {
				return a[4].indexOf(str) != -1;
			} else if(tp == 'LA') {
				return a[5].indexOf(str) != -1;
			} else if(tp == 'MA') {
				return a[6].indexOf(str) != -1;
			} else if(tp == 'A') {
				return a[5].indexOf(str) != -1 || a[6].indexOf(str) != -1;
			} else if(tp == 'M') {
				return ("" + a[7]) == str;
			} else if(tp == 'ML') {
				return a[7] <= str;
			} else if(tp == 'MG') {
				return a[7] >= str;
			} else return vl_all(a,b);
		}

		var flag = true;
		for(var i=0;i<qrs.length;i++) {
			if(!vl_qr(inf,qrs[i])) {flag = false;break;}
		}

		if(flag) $ele.css('display','block');
		else $ele.css('display','none');
	});
}

function doFilter() {
	filterPlaylistByQuery($('#filter-terms').val());
}

function removeFilter() {
	$('#filter-terms').val('');
	doFilter();
}


function rmenu_hide() {
	$('#right-menu-overlay').css('opacity','0');
	setTimeout(function(){
		$('#right-menu-overlay').css('display','none');
		$('#right-menu').css('display','none');
	},0);
	$('#right-menu').css({marginRight:'calc(' + (G.is_wap ? '-100%' : '-480px') + ' - 8px)'});
}

function rmenu_show() {
	$('#right-menu').css('display','block');
	$('#right-menu-overlay').css('display','block');
	$('#right-menu-overlay').css('opacity','1');
	$('#right-menu').css({marginRight:'0'});
}

function rmenu_toggle(id,btn) {
	var obj = $('.rmenu-collapse-' + id);
	if(obj.css('display') == 'none') {
		obj.css('display','block');
		$(btn.children[0]).removeClass('fa-plus');
		$(btn.children[0]).addClass('fa-minus');
	}
	else {
		obj.css('display','none');
		$(btn.children[0]).removeClass('fa-minus');
		$(btn.children[0]).addClass('fa-plus');
	}
}
