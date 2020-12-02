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
			if(await modal_confirm_p(LNG('list.alert.norp'),LNG('list.alert.norp.tips'),LNG('ui.cancel'),LNG('ui.continue'))) {
				window.open(G.basic_url + $(this).attr('data-id'));
			}
			return false;
		}

		return true;
	});
}

// Firefox对onmousewheel的支持修复。
(function()
{

if (navigator.userAgent.toLowerCase().indexOf('firefox')>=0)
{
	//firefox支持onmousewheel
	addEventListener('DOMMouseScroll',function(e)
	{
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
		if (onmousewheel)
		{
			if(e.preventDefault)e.preventDefault();
			e.returnValue=false;    //禁止页面滚动

			if ( typeof xxTarget.onmousewheel!='function' )
			{
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
});
