"mod PlayerApp";

// This is experimental, and may get reverted to v127e

/* TODO[WMSDFCL/User:4]: 考虑对象封装，减少全局变量 */

var A;                               // 当前播放的音频标签
var B;                               // 后备音频标签
var loaded = false;                  // 音频是否已加载
var isNormal = false;                // 是否正常状态
var isErrored = false;               // 是否出错
var B_canPlay = 0;                   // B 是否加载进入可播放状态（0=默认，1=可，2=不可）
var isBeginPlay = 0;                 // 加载后是否不读取断电记忆

var ct_eps = 0.0001;                 // 点击歌词行定位时的微调量（同时影响间奏显示）
var lrct=[];                         // 歌词时间前缀查找表
var lrnt=[];                         // 歌词时间后缀查找表

var S = true;                        // 自动滚动开关
var SPEED = 1.0;                     // 变速倍率
var highspeed_unlocked = false;      // 本次会话是否使用过不安全的设置

var preRead_threads = 0;             // 预读取已占用的线程数
var Cache = {};                      // 切换数据缓存

var curr_idx = 0;                    // 列表中当前歌曲的索引
var nxt_idx = 0;                     // 列表中下一个歌曲的索引
var list_idx = {};                   // ID -> 列表索引查找表

var updated_lrcpos = [null,null];    // 已应用至 HTML 的高亮位置

/**
 * 补全字符串前缀
 */
function strprefix(str,pre="0",len=2) {
	while(str.length<len) str = pre+str;
	return str;
}

/**
 * 补全小数位
 */
function rnum(num,len=2) {
	num=num.toString();
	if(num.indexOf('.')==-1) num+='.';

	while(num.length-num.indexOf('.')-1<len) num+='0';
	while(num.length-num.indexOf('.')-1>len) num=num.substring(0,num.length-1);

	return num;
}

/**
 * 时间格式输出
 */
function ftime(t) {
	return strprefix(Math.floor(t/60).toString())+":"+strprefix(Math.floor(t%60).toString());
}

/**
 * 自动滚动工具
 */
(() => {
	var ease_function = (x) => {
		return -((x - 1) ** 2) + 1;
	};
	var scroller_interval_T = null;
	window.scrollto = function(v,s,d=200) {
		if(scroller_interval_T) {
			removeFrameFunc(scroller_interval_T);
			scroller_interval_T = null;
		}

		if(v < 0) v = 0;

		var ele=$(s)[0];
		var old=ele.scrollTop;
		// 检验极值
		ele.scrollTop = 1610612736;
		var mx = ele.scrollTop;
		ele.scrollTop = old;
		if(v < 0) v = 0;
		if(v > mx) v = mx;
		if(old==v) return;

		var E = v - old;
		var last_t = -1;
		var init_t = -1;
		scroller_interval_T = function(timer){
			if(init_t == -1) {
				init_t = last_t = timer;
				return;
			}
			curr_t = timer - init_t;
			prev_t = last_t - init_t;
			last_t = timer;
			if(curr_t > d) {
				curr_t = d;
			}
			var D = E * (ease_function(curr_t / d) - ease_function(prev_t / d));
			ele.scrollTop += D;
			if(curr_t >= d) {
				removeFrameFunc(scroller_interval_T);
				scroller_interval_T = null;
			}
		};
		registerFrameFunc(scroller_interval_T);
	}
	var scroller_interval_L = null;
	window.scrollto_left = function(v,s,d=300) {
		if(scroller_interval_L) {
			removeFrameFunc(scroller_interval_L);
			scroller_interval_L = null;
		}

		if(v < 0) v = 0;

		var ele=$(s)[0];
		var old=ele.scrollLeft;
		// 检验极值
		ele.scrollLeft = 1610612736;
		var mx = ele.scrollLeft;
		ele.scrollLeft = old;
		if(v < 0) v = 0;
		if(v > mx) v = mx;
		if(old==v) return;

		var E = v - old;
		var last_t = -1;
		var init_t = -1;
		scroller_interval_L = function(timer){
			if(init_t == -1) {
				init_t = last_t = timer;
				return;
			}
			curr_t = timer - init_t;
			prev_t = last_t - init_t;
			last_t = timer;
			if(curr_t > d) {
				curr_t = d;
			}
			var D = E * (ease_function(curr_t / d) - ease_function(prev_t / d));
			ele.scrollLeft += D;
			if(curr_t >= d) {
				removeFrameFunc(scroller_interval_L);
				scroller_interval_L = null;
			}
		};
		registerFrameFunc(scroller_interval_L);
	}
})();

/**
 * 对换 A、B 音频组件
 */
window.swap_audio = function() {
	// 对换加载成功动作
	A.oncanplay = [B.oncanplay, B.oncanplay = A.oncanplay][0];
	// 对换报错动作
	A.onerror = [B.onerror, B.onerror = A.onerror][0];
	// 对换终止行为
	A.onended = [B.onended, B.onended = A.onended][0];

	// 对换播放设置
	A.volume = [B.volume, B.volume = A.volume][0];
	A.playbackRate = [B.playbackRate, B.playbackRate = A.playbackRate][0];
	if(A.preservesPitch !== undefined) {
		A.preservesPitch = [B.preservesPitch, B.preservesPitch = A.preservesPitch][0];
	}
	A.currentTime = [B.currentTime, B.currentTime = A.currentTime][0];

	// 销毁原副音频加载状态
	A.src = '';

	// 对换名称
	A = [B, B = A][0];

	if(B_canPlay == 1) {
		// 主音频初始化执行
		if(A.oncanplay) A.oncanplay();
	} else if(B_canPlay == 2) {
		// 加载未成功，销毁加载状态并重试
		let tmp = A.src;
		A.src = '';
		A.src = tmp;
		B.load();
	}
	B_canPlay = 0;
}

/**
 * 更新歌词行高亮
 */
var highlight_lyric = function(st) {
	var f = A.currentTime;
	f = Math.floor(f*10);
	// 取出原歌词时间
	f = lrct[f];
	// 取出位置
	var hl_node = data['timestamps'][f];
	// 位置相同，不处理
	if(!st && hl_node[0] == updated_lrcpos[0] && hl_node[1] == updated_lrcpos[1]) {
		;
	} else {
		// 复制赋值
		updated_lrcpos = hl_node.slice(0);

		if(f>=0 && hl_node[0] >= -1){
			// 有高亮
			if(!$('#lrc-'+hl_node[1]).hasClass('lrc-active') || st) {
				// 取消激活
				$('.para.para-active').removeClass('para-active');
				$('.lrc-item.lrc-active').removeClass('lrc-active');
				$('.lo-item.lo-active').removeClass('lo-active');
				// 激活对应
				$('#lrc-'+hl_node[1]).addClass('lrc-active');
				$('#para-'+hl_node[0]).addClass('para-active');
				if(hl_node[0] != -1) {
					// 前奏初始标志不激活
					$('#lo-'+hl_node[0]).addClass('lo-active');
				}

				// 自动滚动
				if(S) {
					var $hl_lrc = $('#lrc-'+hl_node[1]);
					var $hl_lo = $('#lo-'+hl_node[0]);

					// 歌词正文
					var lrcline_height = (removePX($hl_lrc.css('font-size')) * 1.2) * (1 - 0.025 + 2.8256 - 2.0);
					scrollto(
						+ $('.lrc-content')[0].scrollTop
						+ $hl_lrc.offset().top
						- $('.lrc-content').offset().top
						- lrcline_height * (G.is_wap ? 1.2 : 3.4)
						- (G.is_wap ? 52 : 0)
					,".lrc-content");

					// 概览区也要
					scrollto_left(
						+ $('.lrc-overview')[0].scrollLeft
						+ $hl_lo.offset().left
						- $('.lrc-overview').offset().left
						- 41
					,".lrc-overview");
				}
			}
		} else {
			// 无高亮
			if($('.lrc-active').length || st) {
				// 取消激活
				$('.para.para-active').removeClass('para-active');
				$('.lrc-item.lrc-active').removeClass('lrc-active');
				$('.lo-item.lo-active').removeClass('lo-active');

				// 自动滚动
				if(S) {
					var $lastline = $('.lrc-content__wrapperin .para:last-child .lrc-item:last-child');
					scrollto(
						$('.lrc-content')[0].scrollTop+
						$lastline.offset().top-
						$('.lrc-content').offset().top+
						$lastline.height()-
						$('.lrc-content').height()/2
					,".lrc-content");
				}
			}
		}
	}
	$('.lrc-interval-item,.lrc-wap-title').each(function(idx){
		var lrcline=$(this);
		var thistime=lrcline[0].getAttribute('data-time');
		var nxttime=lrnt[thistime]/10;
		thistime /= 10;
		var remain_limit = Math.floor(nxttime - thistime - ct_eps);
		var interval_len = remain_limit;
		var remain = Math.floor(nxttime - A.currentTime);
		if(remain < 0) remain = 0;
		if(remain_limit > 7) remain_limit = 7;
		if(lrcline.hasClass('lrc-interval-item')) {
			var txt = "";
			// 间奏类型
			if(lrcline.hasClass('lrc-break')) {
				txt = LNG('player.lyric.break') + ' &rsaquo;' + " &nbsp;";
			} else {
				txt = LNG('player.lyric.empty') + ' &rsaquo;' + " &nbsp;";
			}
			// 激活状态间奏
			if(lrcline.hasClass('lrc-active')) {
				for(var i=1;i<=remain_limit;i++) {
					if(i>1) txt += ' ';
					if(i>remain) txt += LNG('player.lyric.break.zero');
					else txt += LNG('player.lyric.break.one');
				}
				txt += ' (' + remain + ')';
			}
			// 非激活状态间奏
			else {
				if(thistime==0) txt='&gt; ' + LNG('player.lyric.restart') + ' &lt;';
				else if(lrcline.hasClass('lrc-break')) txt='- - - - - - -';
				else txt='- - -';
			}
			$('.lrc-text',lrcline).html(txt);
		} else {
			if(lrcline.hasClass('lrc-active') && remain_limit >= 2 && remain < interval_len) {
				$('.lrc-wap-title-singer',lrcline).text(listMeta[curr_idx]['S'] + "\u3000\u3000\u3000");
				$('.lrc-wap-title-singer',lrcline).html($('.lrc-wap-title-singer',lrcline).html()
					+ '<span style="color:#888">' + "前奏剩余 " + remain + "s" + "</span>");
			} else {
				$('.lrc-wap-title-singer',lrcline).text(listMeta[curr_idx]['S']);
			}
		}
	});
};

/**
 * 全局初始化
 */
$('document').ready(function(){
	// 初始化大标题下拉
	$(function() {
		$('.title-dropdown-father').dropdown();
	});

	// 获取音频标签
	A = $('#audio_1')[0];
	B = $('#audio_2')[0];

	// 加载音频
	A.load();

	// 歌词缩放值（移动端不适用）
	if(!G.is_wap) {
		if(!storeData('player.zoom') && !G.is_wap) {
			storeData('player.zoom', 1.15);
		}
		if(storeData('player.zoom')) $('.lrc-content').css('font-size',(storeData('player.zoom')*15)+'px');
	}

	// “保持音调不变”是否受支持
	if(A.preservesPitch === undefined) {
		$('.speed-preserve-pitch').parent().addClass('am-disabled');
	}

	// 在线保存替换标题
	if(isCloudSave) document.title=titleformat.replace('%{list_name}',cloudData['title'] + '/' + data['meta']['N']);

	// 音频错误处理
	A.onerror = function() {
		if(true != listMeta[list_idx[song_id]].cant_play) {
			modal_alert(LNG('ui.error'),LNG('player.alert.audio.fail',A.error.code));
		}
		isErrored = true;
		$('.song-status').hide();
		$('.song-status-errored').show();
	}
	B.onerror = function() {
		B_canPlay = 2;
	}

	// 挂加载函数
	A.oncanplay = playInit;
	B.oncanplay = function() {
		B_canPlay = 1;
	}

	for(var i=0;i<list.length;i++) {
		list_idx[list[i]] = i;
	}
	if(!isList) {
		// 非列表：切歌按钮改为刷新
		$('#skip-ico').removeClass('fa-arrow-right');
		$('#skip-ico').addClass('fa-rotate-right');
		$('#skip-button').css({'padding-top':'6px','padding-left':'3px'});
	}

	// 歌词高亮&进度显示&播放按钮【刻函数】
	window.tick_func = function(){
		// 非活跃状态无需执行
		if(!isWindowActive()) return;
		if(!isNormal) return;

		// 标记时间位置
		document.querySelector('#total-len').innerHTML=ftime(A.duration);
		document.querySelector('#elasped').innerHTML=
			ftime(A.currentTime);
		document.querySelector('#remain').innerHTML=
			ftime(Math.floor(A.duration)-Math.floor(A.currentTime));
		document.querySelector('#accurate').innerHTML=
			rnum(A.currentTime);
		document.querySelector('.player-processbar-i').style.width=
			(A.currentTime*100/A.duration).toString()+"%";

		// 标记播放按钮
		var X = $('#play-button');
		if(A.paused) {
			if(X.hasClass('fa-pause-circle')) {
				X.addClass("fa-play-circle");
				X.removeClass("fa-pause-circle");
			}
		} else {
			if(X.hasClass('fa-play-circle')) {
				X.removeClass("fa-play-circle");
				X.addClass("fa-pause-circle");
			}
		}

		// 歌词高亮
		highlight_lyric(false);
	};
	// 刻函数与进度存储设置
	setInterval(tick_func,50);
	setInterval(function() {
		if(isNormal && !A.paused) {
			storeData('player.play_stat.' + list[curr_idx] + '.proc', A.currentTime);
		}
	},1500);
	$(document).on('data_enter', function() {
		// 调整为活跃状态后立即更新元素
		tick_func();
	});

	// 加载进度提示
	var update_load_process = function(){
		// 非活跃状态不执行
		if(!isWindowActive()) return;

		var b = A.buffered;
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
	};
	setInterval(update_load_process,1000);
	$(document).on('data_enter', function() {
		update_load_process();
	});

	// 设定进度调节函数
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

	// 绑定进度调节
	document.querySelector('.player-processbar').onmousedown=process;
	document.querySelector('.player-processbar').onmousewheel=processwheel;

	// 显示播放列表
	showSongList();
	// 播放初始化
	playPreInit();

	// 快速跳转命令
	var str = location.href.substr(location.href.lastIndexOf('#'));
	if(str.match(/^\#id\-(\w+)$/)) {
		var target_id = str.match(/^\#id\-(\w+)$/)[1];
		if(target_id != song_id && list_idx[target_id] !== undefined && listMeta[list_idx[target_id]] != null) changeTo(target_id);
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
		txt += '<li style="border-top: 1px solid #00000022;';
		// 右侧色彩条显示
		if(song_id == list[i]) {
			// 一定不是无效歌曲
			txt += 'border-right: 11px solid #' + listMeta[i]['A'];
		} else {
			if(listMeta[i]) {
				txt += 'border-right: 5px dashed #' + listMeta[i]['A'];
			} else {
				txt += 'border-right: 5px dashed #00000077';
			}
		}
		txt += '" class="song-list-item songlist-status-none" id="song-list-item-' + list[i] + '"';
		if(song_id == list[i]) {
			txt += ' data-current-play';
		}
		txt += ' data-id="'+list[i]+'"';
		txt += '';
		txt+='>';

		txt+='<a'+(listMeta[i]==null ? '' : ' onclick="changeTo(\''+list[i]+'\')"')+' style="'+(song_id==list[i]? 'font-weight:700;':'')+((listMeta[i]==null || listMeta[i].cant_play == true) ? 'opacity:0.6;' : '')+'">';
		txt+=listName[i];
		txt+='</a>';

		txt+='</li>';
	}
	obj.innerHTML=txt;


	if(isCloudSave) for(var i=0;i<list.length;i++) {
		$('#list-rating-' + list[i]).html(myRating[list[i]]);
	}

	if(isCloudSave) for(var i=0;i<list.length;i++) {
		$('#list-id-' + list[i]).html(fa_icon('hashtag') + myIds[list[i]]);
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
		isNormal = false;
		isErrored = false;
		$('.song-status').hide();
		$('.song-status-loading').show();
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
		if(!await modal_confirm_p(LNG('player.alert.paydl'),LNG('player.alert.paydl.tips'),LNG('ui.nohack'),LNG('ui.yeshack'))) {
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

// 自动滚动开关
function roll_toggle(st)
{
	if(!st){
		S=false;
		$('#sync-button').removeClass('float-btn-active');
	}
	else {
		S=true;
		$('#sync-button').addClass('float-btn-active');
	}
}

// 确认危险变速
async function confirmUnsafeSpeed() {
	if(await modal_confirm_p(LNG('player.alert.unsafe_speed'),LNG('player.alert.unsafe_speed.tips'),LNG('ui.cancel'),LNG('ui.yeshack'))) {
		highspeed_unlocked = true;
		return true;
	}
	return false;
}
// 变速开关
async function setPlayRate(st) {
	if(!highspeed_unlocked && (st < 0.7 || st > 1.5) && false === A.preservesPitch) {
		if(!await confirmUnsafeSpeed()) return;
	}

	$('.speed-choice').parent().removeClass('am-active');
	var found_choice = false;
	$('.speed-choice').each(function() {
		if(this.innerText == decimalRps(st,1,2) + 'x') {
			$(this).parent().addClass('am-active');
			found_choice = true;
		}
	});
	if(!found_choice) {
		$('.speed-choice-custom').parent().addClass('am-active');
		$('.speed-choice-custom').text(LNG('player.menu.speed.custom') + LNG('punc.colon') + decimalRps(st,1,2));
	} else {
		$('.speed-choice-custom').text(LNG('player.menu.speed.custom'));
	}

	if(st != 1.0) {
		$('#speed-button').addClass('float-btn-active');
	} else {
		$('#speed-button').removeClass('float-btn-active');
	}
	if(st < 1) {
		$('#speed-ico').removeClass('fa-forward');
		$('#speed-ico').addClass('fa-backward');
		$('#speed-button').css('padding-left','0px');
	} else {
		$('#speed-ico').removeClass('fa-backward');
		$('#speed-ico').addClass('fa-forward');
		$('#speed-button').css('padding-left','');
	}

	SPEED = st;
	A.playbackRate = st;
}
async function setPlayRateCustom() {
	var txt = await modal_prompt_p(LNG('player.alert.cspeed'),LNG('player.alert.cspeed.tips'),SPEED,'number');
	if(!txt) return;
	if(1 * txt != txt) return;
	txt = 1 * txt;
	txt = Math.round(100 * txt ) / 100;
	txt = Math.max(0.1,Math.min(16,txt));
	setPlayRate(txt);
}

// 音调恒常
async function togglePreservePitch() {
	// 不支持
	if(A.preservesPitch === undefined) {
		return;
	}
	// 正常切换
	if(A.preservesPitch) {
		if(!highspeed_unlocked && (SPEED < 0.7 || SPEED > 1.5)) {
			if(!await confirmUnsafeSpeed()) return;
		}
		A.preservesPitch = false;
		$('.speed-preserve-pitch').text(LNG('player.menu.pitch.off'));
	} else {
		A.preservesPitch = true;
		$('.speed-preserve-pitch').text(LNG('player.menu.pitch.on'));
	}
}

// 音量开关
function setVolume(st) {
	$('.volume-choice').parent().removeClass('am-active');
	var found_choice = false;
	$('.volume-choice').each(function() {
		if(this.innerText == decimalRps(st,2,2)) {
			$(this).parent().addClass('am-active');
			found_choice = true;
		}
	});
	if(!found_choice) {
		$('.volume-choice-custom').parent().addClass('am-active');
		$('.volume-choice-custom').text(LNG('player.menu.volume.custom') + LNG('punc.colon') + decimalRps(st,2,2));
	} else {
		$('.volume-choice-custom').text(LNG('player.menu.volume.custom'));
	}

	if(st != 0) {
		$('#volume-button').addClass('float-btn-active');
	} else {
		$('#volume-button').removeClass('float-btn-active');
	}
	if(st <= 0.5) {
		$('#volume-ico').removeClass('fa-volume-up');
		$('#volume-ico').addClass('fa-volume-down');
	} else {
		$('#volume-ico').removeClass('fa-volume-down');
		$('#volume-ico').addClass('fa-volume-up');
	}

	storeData('player.volume', st);
	A.volume = st;
}
async function setVolumeCustom() {
	var txt = await modal_prompt_p(LNG('player.alert.volume'),LNG('player.alert.volume.tips'),A.volume,'number');
	if(!txt) return;
	if(1 * txt != txt) return;
	txt = 1 * txt;
	txt = Math.max(0,Math.min(1,txt));
	setVolume(txt);
}

function scrollToPara(x)
{
	var d=$('#para-'+x);
	var c=$('.lrc-content');
	scrollto(
		+ c[0].scrollTop
		+ d.offset().top
		- c.offset().top
		- 16
		- (G.is_wap ? 37 : 0)
	,".lrc-content");
}

function showPlaytimes() {
	var data_begin = getPlaytimesList('begin');
	var data_finish = getPlaytimesList('finish');
	for(var i=0;i<list.length;i++) {
		$('#list-playtimes-'+list[i]).html(
			(data_begin[list[i]] ?? 0) + '/' + (data_finish[list[i]] ?? 0)
		);
	}
}

function addPlaytimes(id,st='begin') {
	var num_id = 'player.play_stat.' + id + '.' + st + '.count';
	var date_id = 'player.play_stat.' + id + '.' + st + '.count_date';

	if(storeData(date_id) != curr_date_str) {
		storeData(date_id, curr_date_str);
		storeData(num_id, 0);
	}
	storeData(num_id,storeData(num_id) + 1);

	showPlaytimes();
}

function getPlaytimes(id,st='begin') {
	var num_id = 'player.play_stat.' + id + '.' + st + '.count';
	var date_id = 'player.play_stat.' + id + '.' + st + '.count_date';

	if(storeData(date_id) != curr_date_str) {
		storeData(date_id, curr_date_str);
		storeData(num_id, 0);
	}
	return storeData(num_id);
}

function getPlaytimesList(st = 'begin') {
	var data = storeData('player.play_stat');
	var retl = {};

	for(let id in data) {
		if(!data[id][st] || data[id][st]['count_date'] != curr_date_str) {
			retl[id] = 0;
		} else {
			retl[id] = data[id][st]['count'];
		}
	}

	return retl;
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

// 每首歌元数据加载后立刻执行
function playPreInit() {
	if(isCloudSave) {
		if(G.username != cloudUser) {
			$('.edit-label').html(LNG('player.list.edit.he'));
			if(G.username == '') {
				$('.edit-label').parent().parent().remove();
			}
		}
	}

	// 显示歌曲详情签
	$('.menu-curr-display').html(listName[curr_idx]);
	$('.menu-curr-display > .addition-cmt > .txmp-tag.tag-blue-g').remove();
	$('.menu-curr-display > .addition-cmt > .txmp-tag.tag-purple-g').remove();
	$('.menu-curr-display > .addition-cmt').append($('<span class="txmp-tag tag-cyan-g">'+fa_icon('pencil')+escapeXml(listMeta[curr_idx]['LA'])+' | '+escapeXml(listMeta[curr_idx]['MA'])+'</span>'+'<span class="txmp-tag tag-orange-g">'+fa_icon('book')+escapeXml(listMeta[curr_idx]['C'])+'</span>'));

	updated_lrcpos = [null,null];

	// 不可播放？
	if(listMeta[list_idx[song_id]].cant_play == true) {
		$('.right-second-row').hide();
		$('.download-button').parent().hide();
	} else {
		$('.right-second-row').show();
	}

	if(isList) {
		nxt_idx=0;
		var nxtList = [];
		if(isCloudSave) {
			if(!cloudData['transform']['constraints2']) {
				cloudData['transform']['constraints2'] = {};
				cloudData['transform']['constraints2']['comparator'] = '>';
				cloudData['transform']['constraints2']['multiplier'] = 0;
				cloudData['transform']['constraints2']['delta'] = -1;
			}
		}
		for(var i=0;i<list.length;i++) {
			// 判断是否可选择&标记颜色
			$('#song-list-item-' + list[i]).removeClass('songlist-status-none');
			if(listMeta[i] != null && listMeta[i].cant_play != true && (!isCloudSave || validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints']) && validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints2']))) {
				if(isCloudSave) {
					$('#song-list-item-' + list[i]).addClass('songlist-status-allow');
				} else {
					$('#song-list-item-' + list[i]).addClass('songlist-status-none');
				}
				nxtList[nxtList.length] = list[i];
			} else {
				$('#song-list-item-' + list[i]).addClass('songlist-status-deny');
			}
		}
		// 非云歌单不可能发生此情况，因为入口点必为可访问项目。
		if(nxtList.length == 0) {
			var action = cloudData['transform']['termination'];
			if(action == 'end') nxt_idx = false;
			else if(action == 'loop') nxt_idx = curr_idx;
			else {
				// 重新构造
				nxtList = [];
				for(let i=0;i<list.length;i++) {
					if(listMeta[i] != null && listMeta[i].cant_play != true) {
						nxtList[nxtList.length] = list[i];
					}
				}
				var sel = nxtList[Math.floor(Math.random() * nxtList.length)];
				nxt_idx = list_idx[sel];
			}
		}
		else if(isRand) {
			var sel = nxtList[Math.floor(Math.random() * nxtList.length)];
			nxt_idx = list_idx[sel];
		} else {
			nxt_idx = -1;
			// 顺序查找可用项目
			for(var i=(curr_idx+1) % list.length;1;) {
				if(listMeta[i] != null && listMeta[i].cant_play != true && (!isCloudSave || validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints']) && validate_constraints(myRating[song_id], myRating[list[i]], cloudData['transform']['constraints2']))) {
					nxt_idx = i;
					// console.log('Select ',list[i]);
					break;
				}
				i = (i + 1) % list.length;
			}
		}

		if(nxt_idx === false) {
			AK_next = function(){addPlaytimes(song_id,'finish');};
		} else {
			AK_next = function(){
				addPlaytimes(song_id,'finish');
				curr_idx=nxt_idx;
				isBeginPlay = 1;
				changeTo(list[nxt_idx],true);
			}
		}

		RP_next = function(){
			addPlaytimes(song_id,'finish');
			isBeginPlay = 1;
			changeTo(list[curr_idx],true);
		};

		if($('#repeat-button').hasClass('fa-repeat')) A.onended = RP_next;
		else A.onended = AK_next;

		// 允许插队
		if(nxt_idx !== false /*&& preRead_threads < 3*/) {
			preRead_threads++;
			preCache(list[nxt_idx],5,function(e) {
				// 准备下一首音频
				try{
					B.src = JSON.parse(e[1]).src1;
				} catch(_err) {}
			}); //自动预读5次，尽量防止切换时出现故障
		}
	}
	else {
		A.onended=function() {
			addPlaytimes(song_id,'finish');
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

	// 标记同步按钮状态
	roll_toggle(S);
	// 标记倍速状态
	setPlayRate(SPEED);
	// 标记音量状态
	if(!storeData('player.volume')) {
		storeData('player.volume', 1);
	}
	setVolume(storeData('player.volume'));
}

// 每首歌之前的预处理工作。
function playInit(){
	loaded = true;

	// 获取断电记忆
	var startPlayTime = storeData('player.play_stat.' + song_id + '.proc');
	if(2 != isBeginPlay && startPlayTime < A.duration-2.5) {
		A.currentTime = startPlayTime;
	} else {
		A.currentTime = 0;
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
	// 反向处理
	for(var i=Math.floor(72000);i>=0;i--) {
		lrnt[i]=current;
		if(data['timestamps'][i]) current=i;
	}
	A.oncanplay = null;

	addPlaytimes(song_id,'begin');

	$('.song-status').hide();
	isNormal=true;
	tick_func();
	$('.song-status-process').show();

	//设定歌词
	var F=document.querySelectorAll('.lrc-text');
	for(i=0;i<F.length;i++)
	{
		if(F[i].getAttribute('data-time')<=1610612735) {
			(function() {
				var clicked_time = -998244353;
				$(F[i]).on('click', function(e){
					if(!G.is_real_wap || new Date().getTime() - clicked_time < 600) {
						A.currentTime = this.getAttribute("data-time")/10 + ct_eps;
						if(!A.paused) {
							storeData('player.play_stat.' + list[curr_idx] + '.proc', A.currentTime);
						}
						roll_toggle(true);
						highlight_lyric(1);
						return false;
					}
					clicked_time = new Date().getTime();
					e.preventDefault();
				});
			})();
		}
		//F[i].title="右键跳到本句歌词："+ftime(F[i].getAttribute("data-time"));
	}

	A.setAttribute('data-rplay','no');

	clean_server_trash();
}

setInterval(function(){
	var nxt_idx=Math.floor(Math.random()*list.length);
	var i=0;
	while(i<64 && Cache[list[nxt_idx]]) {
		nxt_idx=Math.floor(Math.random()*list.length);i++;
	}
	if(i>=64) return;
	if(preRead_threads >= 2)return;
	preRead_threads++;
	preCache(list[nxt_idx],3);
},4000); //随机预读歌曲元数据。最多读取3次。

function switchContent(e,dst_song,flag=false) {
	var a=$('.lrc-content__wrapperin')[0];
	var b=$('.right-first-row')[0];
	var d=$('.right-third-row')[0];
	var f=$('.lrc-overview')[0];
	var obj=$('.song-title')[0];

	// var isItalic=(obj.style.fontStyle=='italic');

	a.innerHTML = e[2];
	b.innerHTML = e[3];
	d.innerHTML = e[5]+e[4];
	f.innerHTML = e[6];
	var is_same = (song_id == dst_song);
	song_id = dst_song;
	for(var i=0;i<list.length;i++) {
		if(list[i]==song_id) curr_idx=i;
	}
	obj=$('.song-title')[0]; //被改动，重新选择

	$(function() {
		$('.title-dropdown-father').dropdown();
	});

	showSongList();
	$('#filter-terms').val('');

	data=JSON.parse(e[0]);
	var metadata=JSON.parse(e[1]);

	baseurl=metadata.baseurl;
	src1=metadata.src1;
	src2=metadata.src2;

	$('#mainColoredCss')[0].href=metadata.main_colored_css;
	$('#playerColoredCss')[0].href=metadata.player_colored_css;

	if(1 == isBeginPlay) {
		isBeginPlay = 2;
	} else {
		isBeginPlay = 0;
	}
	if(!is_same) {
		isNormal = false;
		isErrored = false;
		A.oncanplay = playInit;
		// 验证 B 状态
		if(B.src != metadata.src1) {
			B.src = metadata.src1;
			B.load();
			B_canPlay = 0;
		}
		// 对换
		swap_audio();
	} else {
		if(!isNormal || isErrored) {
			isNormal = false;
			isErrored = false;
			A.oncanplay = playInit;
			A.load();
		} else {
			isNormal = true;
			// storeData('player.play_stat.' + song_id + '.proc', 0);
			playInit();
		}
	}

	if(flag) A.play();
	if(flag) A.setAttribute('data-rplay','yes');

	document.title=metadata.title;
	if(isCloudSave) document.title=titleformat.replace('%{list_name}',cloudData['title'] + '/' + data['meta']['N']);

	if(storeData('player.zoom')) $('.lrc-content').css('font-size',(storeData('player.zoom')*15)+'px');

	location.href='#id-'+dst_song;
}

function preCache(dst_song,remain,success_cb) {
	if(remain==0) {preRead_threads--;return;} //重试次数用完则放弃
	if(!Cache[dst_song]) {
		$.ajax({
			async:true,
			timeout:20000,
			dataType:"text",
			url:home+dst_song+"/switch-all?wap="+(G.is_wap ? 'force-phone' : 'force-pc')+(G.is_iframe ? '&iframe' : ''),
			error:function(res) {
				preCache(dst_song,remain-1,success_cb); //预读gugugu？重试
				return;
			},
			success:function(res) {
				var e=res.split("\n--------TxmpSwitchDataBoundary--------\n");
				if(e.length!=7) {
					preCache(dst_song,remain-1);
					return;
				}
				Cache[dst_song]=e;
				console.log(LNG('player.debug.preread_success',dst_song));
				preRead_threads--;

				if(success_cb) success_cb(e);
			},
		});
	} else {
		if(success_cb) success_cb(Cache[dst_song]);
	}
}

function changeTo(dst_song,flag=false,no_cache=false) {
	var al=modal_loading(LNG('player.alert.switch'),LNG('player.alert.switch.tips'));
	if(!A.paused) A.pause();

	setTimeout(function(){

		//及时获取
		if(!Cache[dst_song] || no_cache) {
			$.ajax({
				async:true,
				timeout:30000,
				dataType:"text",
				url:home+dst_song+"/switch-all?wap="+(G.is_wap ? 'force-phone' : 'force-pc')+(G.is_iframe ? '&iframe' : ''),
				error:function(res) {
					modal_alert(LNG('ui.error'),LNG('player.alert.switch.fail'));
					close_modal(al);
					return;
				},
				success:function(res) {
					var e=res.split("\n--------TxmpSwitchDataBoundary--------\n");
					if(e.length!=7) {
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
		}
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

async function setZoom() {
	var txt=await modal_prompt_p(LNG('player.alert.zoom'),LNG('player.alert.zoom.tips'),storeData('player.zoom'));
	if(!txt) return;
	if(1 * txt != txt) return;
	txt = 1 * txt;
	txt = Math.max(0.8,Math.min(5,txt));
	storeData('player.zoom',txt);
	$('.lrc-content').css('font-size',(txt*15)+'px');
	return false;
}

function filterPlaylistByQuery(txt) {
	var qrs = txt.split('+');
	for(var i=0;i<qrs.length;i++) {
		qrs[i] = qrs[i].trim();
	}
	if(txt == '') {
		qrs = [];
	}

	var $lst = $('.song-list-item');
	$lst.each(function(idx) {
		var $ele = $($lst[idx]);

		// I:0274
		var id = $ele.attr('data-id');

		// X:0
		var idx = list_idx[id];

		if(listMeta[idx] == null) {
			if(qrs.length == 0) $ele.css('display','block');
			else $ele.css('display','none');
			return;
		}

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
		// $('#right-menu').css('display','none');
		// $('#right-menu').css('visibility','hidden');
	},0);
	$('#right-menu').css({marginRight:'calc(' + (G.is_wap ? '-100%' : '-480px') + ' - 24px)'});
	$('#right-menu').css('display','none');
}

function rmenu_show() {
	$('#right-menu').css('display','block');
	$('#right-menu').css('visibility','visible');
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
