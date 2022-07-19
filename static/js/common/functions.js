"mod CommonFunctions";

// 语言函数
function LNG(key = "") {
	if(!LNG_array[key]) return key;
	var str = LNG_array[key];
	for(let i=1;i<arguments.length;i++) {
		str = str.replace(new RegExp("\\$\\{" + (i-1) + "\\}",'g'),arguments[i]);
	}
	return str;
}

// 切换用户语言
function switchLanguage(lang) {
	document.cookie = G.app_prefix+"-user-language=" + lang + "; expires=" + new Date(+new Date() + 86400*365) + "; path=/";

	history.go(0);
}

// 替换XML
function escapeXml(unsafe) {
	return unsafe.replace(/[<>&'"]/g, function (c) {
		switch (c) {
			case '<': return '&lt;';
			case '>': return '&gt;';
			case '&': return '&amp;';
			case '\'': return '&apos;';
			case '"': return '&quot;';
		}
	});
}

// 强行重载 iframe（破坏性）
function reloadIframe($lst) {
	$lst.each(function(i) {
		this.outerHTML = this.outerHTML;
	});
}

// localStorage 本地存储值
// - data: 设置
// - undefined: 查询
// - null: 删除
function storeData(key='',val) {
	// 判断模式
	var mode = 'set';
	if(val === undefined) {
		mode = 'get';
	} else if(val === null) {
		mode = 'remove';
	}

	// 取出数据
	var data = localStorage[G.app_prefix + '-local-data'];
	try {
		data = JSON.parse(data);
	} catch(_e) {
		data = {};
	}
	if(typeof(data) != 'object') {
		data = {};
	}

	// 寻址
	key = key.split('.');
	if(key.length == 1 && key[0] == '') {
		if(mode == 'get') {
			return data;
		} else if(mode == 'set') {
			return null;
		} else {
			delete localStorage[G.app_prefix + '-local-data'];
			return undefined;
		}
	}
	var curr = data;
	for(let i=0;i<key.length-1;i++) {
		var nxt = curr[key[i]];
		if(typeof(nxt) != 'object' || nxt === null) {
			// 正在设置且此处无内容
			if(mode == 'set' && nxt === undefined) {
				// 创建，然后重新赋值
				curr[key[i]] = {};
				nxt = curr[key[i]];
			} else {
				// 否则失败
				return null;
			}
		}
		curr = nxt;
	}
	// 末端寻址
	if(mode == 'set') {
		curr[key[key.length-1]] = val;
		localStorage[G.app_prefix + '-local-data'] = JSON.stringify(data);
		return val;
	} else if(mode == 'get') {
		return curr[key[key.length-1]];
	} else if(mode == 'remove') {
		// 删除值
		delete curr[key[key.length-1]];
		localStorage[G.app_prefix + '-local-data'] = JSON.stringify(data);
		return undefined;
	}
}

// 获取行/列位置
function getLinedPosition(str,pos) {
	var lnd = 0;
	str = str.replace(/\r\n/g,"\n");
	while(str.indexOf("\n") != -1) {
		var delta = str.indexOf("\n");
		str = str.substr(delta+1);
		if(pos < delta + 1) break;
		pos -= delta + 1;
		lnd++;
	}
	return {line:lnd,ch:pos};
}

// 比较行/列位置
function compLinedPosition(x,y) {
	if(x.line < y.line) return -1;
	if(x.line > y.line) return 1;
	if(x.ch < y.ch) return -1;
	if(x.ch > y.ch) return 1;
	return 0;
}
// 行/列取小
function minLinedPosition(x,y) {
	if(compLinedPosition(x,y) == -1) {
		return x;
	}
	return y;
}
// 行/列取大
function maxLinedPosition(x,y) {
	if(compLinedPosition(x,y) != -1) {
		return x;
	}
	return y;
}

// 获取线性位置
function getLinearPosition(str,lch) {
	var lnd = lch.line;
	var pos = lch.ch;

	str = str.replace(/\r\n/g,"\n");
	while(lnd-- && str.indexOf("\n") != -1) {
		var delta = str.indexOf("\n");
		str = str.substr(delta+1);
		pos += delta + 1;
	}
	return pos;
}

// fa-icon
function fa_icon($id,$mleft='05',$mright=3) {
	return '<span class="fa fa-' + $id + '" style="margin-left:.' + $mleft + 'em;margin-right:.' + $mright + 'em">' + '</span>';
}

// 查找结果取小
function minIndex(x,y) {
	if(x == -1) return y;
	if(y == -1) return x;
	if(x < y) return x;
	return y;
}

// 小数表示
function decimalRps(x,minP=1,maxP=2) {
	x = x.toString();
	var currP = 0;
	if(x.indexOf('.') == -1) {
		x = x + '.';
	} else {
		currP = x.length - x.indexOf('.') - 1;
	}
	while(currP > maxP && x[x.length - 1] != '.') {
		x = x.substr(0, x.length - 1);
		currP--;
	}
	while(currP > minP && x[x.length - 1] == '0') {
		x = x.substr(0, x.length - 1);
		currP--;
	}
	while(currP < minP) {
		x = x + '0';
		currP++;
	}
	if(x[x.length - 1] == '.') {
		x = x.substr(0, x.length - 1);
	}
	return x;
}

// 去 px
function removePX(x) {
	if(x.substr(-2) != 'px') {
		return NaN;
	}
	return 1 * x.substr(0,x.length - 2);
}

// 隐藏提示 ?msg=...
function F_HideNotice(_selector="#head-notice"){
	history.replaceState({} ,document.title ,location.href.substring(0,location.href.indexOf('?')));
	$(_selector).css('display','none');
}

// 折叠菜单改变可见性
async function toggleVisible(eventEle,_selector="#notes",displayState='block',warn=undefined){
	var txt=eventEle.innerHTML.trim();
	if($(_selector).css('display')=='none') {
		if(warn !== undefined) {
			if(!await modal_confirm_p(LNG('ui.nonpro_stop'),warn,'&#x3000;&#x3000;&#x3000;&#x3000;返回&#x3000;&#x3000;&#x3000;&#x3000;',LNG('ui.nonpro_confirm'))) return;
		}
		$(_selector).css('display',displayState);
		if(txt[0]=='▶') txt='▼'+txt.substr(1);
	}
	else {
		$(_selector).css('display','none');
		if(txt[0]=='▼') txt='▶'+txt.substr(1);
	}
	eventEle.innerHTML=txt;
}

// 取文件basename
function basename(txt) {
	var i=txt.lastIndexOf('/');
	if(i!=-1) txt=txt.substr(i+1);
	var i=txt.indexOf('.');
	if(i!=-1) txt=txt.substr(0,i);
	return txt;
}

// 区间随机
function mt_rand(lower,upper) {
	return lower+Math.floor((upper-lower+1)*Math.random());
}

// 双数组随机排序
function random_shuffle(s1,s2) {
	if(s1.length!=s2.length) return;
	if(s1.length<2) return;

	for(var i=1;i<s1.length;i++) {
		var j=mt_rand(i,s1.length-1);
		var tmp=s1[j];
		s1[j]=s1[i];
		s1[i]=tmp;
		tmp=s2[j];
		s2[j]=s2[i];
		s2[i]=tmp;
	}

	return {
		first:s1,
		second:s2
	};
}

// 设置页面次级标题
function set_section_name(str) {
	$('.header-title-section-name').text(str);
}

// 试图阻止用户点击“付费播放”的 RemotePlay 项目
function handle_rp_item() {
	$('.song-item-rp a').each(function() {
		if($('.tag-rplim-payplay',$(this.parentElement)).length || location.search.indexOf('iframe') != -1) {
			$(this).attr('href','javascript:;');
			$(this).removeAttr('target');
		}
	});
	$('.song-item-rp a').on('click',async function() {
		if($('.tag-rplim-payplay',$(this.parentElement)).length && location.search.indexOf('iframe') == -1) {
			if(await modal_confirm_p(LNG('list.alert.norp'),LNG(G.can_pay_play ? 'list.alert.norp.tips.true' : 'list.alert.norp.tips.false'),LNG('ui.nohack'),LNG('ui.yeshack'))) {
				window.open(G.basic_url + $(this).attr('data-id'));
			}
			return false;
		}

		return true;
	});
}

// Firefox对onmousewheel的支持修复。
(function() {
	if(navigator.userAgent.toLowerCase().indexOf('firefox')>=0) {
		//firefox支持onmousewheel
		addEventListener('DOMMouseScroll',function(e) {
			//console.log('iakioi');
			var xxTarget=e.target;
			var onmousewheel = xxTarget.onmousewheel;
			var akioi=0;
			while(!onmousewheel) {
				xxTarget = xxTarget.parentElement;
				//console.log(xxTarget);
				akioi++;
				if(akioi>10) break;
				if(!xxTarget) break;
				onmousewheel = xxTarget.onmousewheel;
			}
			//console.log(onmousewheel);
			if (onmousewheel) {
				if(e.preventDefault)e.preventDefault();
				e.returnValue=false;    //禁止页面滚动

				if (typeof xxTarget.onmousewheel!='function') {
					//将onmousewheel转换成function
					eval('window._tmpFun = function(event){'+onmousewheel+'}');
					xxTarget.onmousewheel = window._tmpFun;
					window._tmpFun = null;
				}
				// 不直接执行是因为若onmousewheel(e)运行时间较长的话，会导致锁定滚动失效，使用setTimeout可避免
				setTimeout(function(){
					//console.log("Executed");
					e.wheelDelta = e.detail * (-40); // Firefox取值不同。
					//console.log(e);
					xxTarget.onmousewheel(e);
				},1);
			}
		},false);
	}
})();

$('document').ready(function(){
	if(!G.username) {
		$('.login-only').addClass('am-disabled');
	}
	setInterval(function() {
		$('.dynamic-spin').each(function() {
			var $ele = $(this);
			var delta = $ele.attr('data-spin-delta') * 1;
			if(!delta) delta = 3;
			var status = $ele.attr('data-spin-status') * 1;
			if(!status) status = 0;
			
			status += delta;
			status %= 360;
			$ele.attr('data-spin-status',status);
			$ele.css('transform','rotateZ('+status+'deg)');
		});
	},7);

	$('input[type=text],textarea').attr('spellcheck',false);
});

// resize 函数
function setResizeFunc(func) {
	var last_resize_time = +new Date();
	window.onresize = function() {
		var trigger_time = +new Date();
		setTimeout(function() {
			if(trigger_time <= last_resize_time) return;
			func();
		},20);
	}
}

(() => {
	var is_hidden = false;

	// 网页显示与隐藏触发
	$(document).ready(function() {
		function c() {
			if(document[a]) {
				is_hidden = true;
				$(document).trigger('data_leave');
			} else {
				is_hidden = false;
				$(document).trigger('data_enter');
			}
		}
		var a, b, d = document.title;
		"undefined" != typeof document.hidden ? (a = "hidden", b = "visibilitychange") : "undefined" != typeof document.mozHidden ? (a = "mozHidden", b = "mozvisibilitychange") : "undefined" != typeof document.webkitHidden && (a = "webkitHidden", b = "webkitvisibilitychange"); "undefined" == typeof document.addEventListener && "undefined" == typeof document[a] || document.addEventListener(b, c, !1);
	});

	// 网页显示状态
	// * 仅供性能优化使用。可能将非 active 状态误报为 active。
	window.isWindowActive = function() {
		return !is_hidden;
	}
})();

/**
 * 帧函数注册工具
 */
(() => {
	var frame_funcs = {};
	var frame_func_id = 0;
	window.registerFrameFunc = (func) => {
		frame_func_id += 1;
		frame_funcs['s' + frame_func_id] = func;
		return 's' + frame_func_id;
	};
	window.removeFrameFunc = (id) => {
		if(frame_funcs[id]) {
			delete frame_funcs[id];
		}
	};
	var runFrameFunc = (timer) => {
		for(let id in frame_funcs) {
			frame_funcs[id](timer);
		}
		requestAnimationFrame(runFrameFunc);
	}
	requestAnimationFrame(runFrameFunc);
})();

// 自动调节
$(() => {
	if(autofit) {
		autofit();
	}
});
