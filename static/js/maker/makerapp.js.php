<?php
header("Content-Type: text/javascript");
$w=($_GET["w"]=="1");
error_reporting(E_ALL & (~E_NOTICE));
header("Cache-Control: public max-age=1296000");
header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>

 /* <script> */
"MakerApp BEGIN";

var markItem = null;
var myRating = [];
var myIds = [];
var shouldGenerateUrl = false;
var genUrlTime = 0;

var scroller_sqrt = [];
$('document').ready(function() {
	for(var i=0;i<=110;i++) scroller_sqrt[i] = Math.pow(i,0.72);

	$('#g-url').on('input',generateUrl);

	// setInterval(generateUrl,<?php echo $w ? '2000': '1000'; ?>);

	if(isRand) $('#isRand')[0].checked="checked";
	else $('#isRand')[0].removeAttribute("checked");

	if(isRandShuffle) $('#isRandShuffle')[0].checked="checked";
	else $('#isRandShuffle')[0].removeAttribute("checked");

	if(isList) {
		var obj=$('.maker-list')[0];
		var tpl=obj.innerHTML;
		obj.innerHTML="";
		for(var i=0;i<list.length;i++) {
			obj.innerHTML+=tpl;
		}
		for(var i=0;i<obj.children.length;i++) {
			var b=obj.children[i];
			b.children[0].style.color='#'+listColor[i];
			b.children[1].children[0].style.color='#'+listColor[i];
			b.setAttribute('data-id',list[i]);
			b.children[0].innerHTML=listName[i];
		}
	}

	if(isCloudSave) {
		for(var i=0;i<cloudData['playlist'].length;i++) {
			myRating[cloudData['playlist'][i]['id']] = cloudData['playlist'][i]['rating'];
		}
		for(var i=0;i<cloudData['playlist'].length;i++) {
			myIds[cloudData['playlist'][i]['id']] =
				cloudData['playlist'][i]['canonical']?
					cloudData['playlist'][i]['canonical']
					:cloudData['playlist'][i]['id'];
		}
	}

	//从File Manager > v447d 来的链接（准备保存歌单）
	(function(){

		var ref=document.referrer;

		//https://fake.com/path/to/file/manager/index.php?appcallback/go&url=txmp&filename=/音乐集/TXMP歌单.oexe
		if(ref.indexOf('?appcallback/go&')!=-1) {

			var fmbase=ref.substr(0,ref.indexOf('?'));
			var fname=decodeURIComponent(ref.substr(ref.indexOf('&filename=')+10));

			var randid=(Math.floor(Math.random()*1000)).toString();
			var currloc=location.href;
			if(currloc.indexOf('?')==-1) {
				currloc+='?fmid='+randid;
			}
			else {
				currloc+='&fmid='+randid;
			}

			localStorage['fm-save-base-'+randid]=fmbase;
			localStorage['fm-save-loc-'+randid]=fname;
			history.replaceState({},document.title,currloc);

			isFmSave=true;
			fmRandId=randid;
		}

		if(isFmSave) {
			console.log('该链接来自RojExplorer。已准备利用云盘便捷地制作、保存歌单。'+"\n"+'RojExplorer 主网址：'+localStorage['fm-save-base-'+fmRandId]+"\n"+'保存目标：'+localStorage['fm-save-loc-'+fmRandId]);
		}

	})();

	generateUrl();

	$('.am-list-news').on('keypress',function(e) {
		console.log('Key Press ',e.which);
		var k = e.which;
		// if(!$('.list-focus').length) return;
		if(k == 32) {
			var g = $('.list-focus')[0].children[0];
			$(g).dblclick();
			e.preventDefault();
		} else if(k == 119) {
			var g = $('.list-focus')[0].previousElementSibling;
			if(g) {
				_focus_to(g);
				scroll_to(g,'body');
			}
		} else if(k == 115) {
			var g = $('.list-focus')[0].nextElementSibling;
			if(g) {
				_focus_to(g);
				scroll_to(g,'body');
			}
		} else if(k == 97) {
			var g = $('.list-focus')[0].children[1].children[1].children[3].children[0];
			g.click();
			scroll_to($('.list-focus')[0],'body');
		} else if(k == 101) {
			var g = $('.list-focus')[0].children[1].children[1].children[1].children[0];
			g.click();
			scroll_to($('.list-focus')[0],'body');
		} else if(k == 100) {
			var g = $('.list-focus')[0].children[1].children[1].children[2].children[0];
			g.click();
			scroll_to($('.list-focus')[0],'body');
		} else if(k == 114) {
			var g = $('.list-focus')[0].children[1].children[1].children[4].children[0];
			g.click();
			scroll_to($('.list-focus')[0],'body');
		} else if(k == 122) {
			var g = $('.list-focus')[0].children[1].children[1].children[5].children[0];
			g.click();
			scroll_to($('.list-focus')[0],'body');
		} else if(k == 120) {
			var g = $('.list-focus')[0].children[1].children[1].children[6].children[0];
			g.click();
			scroll_to($('.list-focus')[0],'body');
		} else if(k == 99) {
			var g = $('.list-focus')[0].children[1].children[1].children[7].children[0];
			g.click();
			e.preventDefault();
		} else if(k == 118) {
			var g = $('.list-focus')[0].children[0].children[2].children[0];
			g.click();
			e.preventDefault();
		}
	});

	$('.tooltip-box input[type=text]').on('input',generateUrl);
	$('.tooltip-box input[type=number]').on('input',generateUrl);
	$('.tooltip-box select').on('input',generateUrl);

	setInterval(function(){
		if(shouldGenerateUrl && new Date().getTime() - genUrlTime >= 200) {
			shouldGenerateUrl = false;
			_generateUrl();
		}
	},200);
});

function generateUrl() {
	shouldGenerateUrl = true;
	genUrlTime = new Date().getTime();
}

var scroller_interval = -1;
function _scroll_to(v,s,d=<?php echo $w ? '12' : '28' ?>) {
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
	},<?php echo $w ? '20' : '10' ?>);
}

function scroll_to(v,s) {
	_scroll_to(
		// $(s)[0].scrollTop+
		$(v).offset().top-
		$(s).offset().top+
		$(v).height()/2-
		$(s).height()/2
	,s);
}

var amount=0;
function _generateUrl() {
	$('.am-list-date').dropdown();
	var txt=home;
	var idList=[];
	var obj=$('.maker-list')[0];
	for(var i=0;i<obj.children.length;i++) {
		var b=obj.children[i];
		var id=b.getAttribute('data-id');
		b.setAttribute('data-order',i);
		if(id=="undefined") continue;
		if(id==undefined) continue;
		idList[idList.length]=id;
		if(isCloudSave) {
			$('#list-rating-' + id).html(myRating[id]);

			// A hacky way to fix the bug (?) of jquery.
			$('span#list-id-' + id).html(myIds[id] + '<span></span>');

			if(id != myIds[id]) {
				var idEle = $('span#list-id-' + id);
				idEle.removeClass('tag-default');
				idEle.addClass('tag-canonical');
			} else {
				var idEle = $('span#list-id-' + id);
				idEle.removeClass('tag-canonical');
				idEle.addClass('tag-default');
			}

			if(!$('#list-rating-' + id).parent().hasClass('action-linked')) {
				$('#list-rating-' + id).parent().addClass('action-linked');
				$('#list-rating-' + id).parent().click(function(){
					rate(this.children[0]);
					return false;
				});
			}

			if(!$('#list-id-' + id).hasClass('action-linked')) {
				$('#list-id-' + id).addClass('action-linked');
				$('#list-id-' + id).click(function(){
					setCanonical(this.children[0]);
					return false;
				});
			}
		}
	}
	if(idList.length) txt+=idList[0]+"";
	if(idList.length>1) txt+="?list=";
	for(var i=1;i<idList.length;i++) {
		if(i!=1) txt+='|';
		txt+=idList[i];
	}
	if(idList.length>1 && $('#isRand')[0].checked) txt+='&randList';
	if(idList.length>1 && $('#isRandShuffle')[0].checked) txt+='&randShuffle';
	if($('#isIframe')[0].checked) txt+='&iframe';

	$('#g-url')[0].value=txt;
	if(isCloudSave) $('#g-url')[0].value=home+'playlist/'+G_username+'/'+cloudId;

	var datalen=txt.length;
	if(isCloudSave) {
		if(isCsv) datalen = buildCloudCsv().length;
		else datalen = JSON.stringify(buildCloudObject()).length;
	}
	var lenlimit=2048;
	if(isCloudSave) lenlimit = 51200;
	$('.list-len-show').html(datalen+'/'+lenlimit);
	if(datalen>lenlimit) {
		$('.list-submit')[0].setAttribute('disabled','disabled');
		if(!$('.op-btn').hasClass('am-disabled')) $('.op-btn').addClass('am-disabled');
	}
	else {
		$('.list-submit')[0].removeAttribute('disabled');
		$('.op-btn').removeClass('am-disabled');
	}

	if(isFmSave) {
		$('.opened-file')[0].innerHTML=escapeXml(localStorage['fm-save-loc-'+fmRandId]);
		$('.opened-file-basename')[0].innerHTML=escapeXml(basename(localStorage['fm-save-loc-'+fmRandId]));
		$('.fm-base')[0].innerHTML=escapeXml(localStorage['fm-save-base-'+fmRandId]);
	}
	amount=idList.length;
}

function _focus_to(t) {
	var st = document.body.scrollTop;
	$('.list-focus').removeClass('list-focus');
	$(t).addClass('list-focus');
	$('.am-list-news').focus();
	document.body.scrollTop = st;
}

function focus_to(t) {
	t=t.parentElement.parentElement.parentElement.parentElement;
	_focus_to(t);
}

async function removeItem(t) {
	t=t.parentElement.parentElement.parentElement.parentElement;
	if($('.maker-list>.am-list-item-dated:not(.item-removing)').length<2) {
		await modal_alert_p("提示","不可以删除最后一项");
		_focus_to(t);
		return;
	}
	var g = t.previousElementSibling;
	if(!g) {
		g = t.nextElementSibling;
	}
	$(t).addClass('item-removing');
	$(t).css('background-color','rgba(255,200,200,1)');
	setTimeout(function(){$(t).remove();generateUrl();},300);
	_focus_to(g);
}

function highlight(t) {
	$(t).css('background-color','rgba(255,255,100,1)');
	setTimeout(function(){$(t).css('background-color','rgba(255,255,16,0)');},300);
}

function duplicate(t) {
	t = t.parentElement.parentElement.parentElement.parentElement;
	var id = $(t).attr('data-id');
	$(t).addClass('dup-tmp');
	t.outerHTML = t.outerHTML + t.outerHTML;

	setTimeout(function() {
		$('span#list-id-' + id).each(function(){
			$(this).addClass('action-linked');
			$(this).click(function(){
				setCanonical(this.children[0]);
				return false;
			});
		});
		$('span#list-rating-' + id).each(function(){
			var ele = this.parentElement;
			$(ele).addClass('action-linked');
			$(ele).click(function(){
				rate(this.children[0]);
				return false;
			});
		});
		generateUrl();
	},2);
	_focus_to($('.dup-tmp')[1]);
	$('.dup-tmp').removeClass('dup-tmp');
}

async function rate(t,id) {
	if(!id) t=t.parentElement.parentElement.parentElement.parentElement;
	if(!id) id = t.getAttribute('data-id');
	var txt = await modal_prompt_p('更改评分','输入新的评分',myRating[id]);
	if(!txt) {_focus_to(t);return;}
	txt = 1*txt;
	if(txt >= 0 && txt <= 45000 && Math.floor(txt) === Math.ceil(txt)) {
		myRating[id] = txt;
		generateUrl();
	}
	else {
		await modal_alert_p('评分错误','评分必须是 0 到 45000 之间的整数。');
	}
	_focus_to(t);
}

async function setCanonical(t,id) {
	if(!id) t=t.parentElement.parentElement.parentElement.parentElement;
	if(!id) id = t.getAttribute('data-id');
	var txt = await modal_prompt_p('修改编号','输入自定义编号（原有值：'+id+'）',myIds[id]);
	if(!txt) {_focus_to(t);return;}
	if(txt.match(/^(\w+)$/)) {
		myIds[id] = txt;
		generateUrl();
	}
	else {
		await modal_alert_p('编号错误','编号只能包含字母、数字和下划线');
	}
	_focus_to(t);
}

function move(t,x) {
	t=t.parentElement.parentElement.parentElement.parentElement;
	t=$(t);
	if(x==1) {
		if(t.next()) {
			t.insertAfter(t.next());
		}
	}
	else {
		if(t.prev()) {
			t.insertBefore(t.prev());
		}
	}
	highlight(t[0]);
	generateUrl();
	_focus_to(t);
}

function selectItem(t) {
	t=t.parentElement;
	var obj=$('#selector')[0];
	obj.style.display="block";
	var wnd=obj.children[0].contentWindow;

	var T=setInterval(function(){
		var inList=wnd.$('.song-item');
		for(var i=0;i<inList.length;i++) {
			var b=inList[i];
			//console.log(b);
			if(b.getAttribute('data-select')=='yes'){
				clearInterval(T);
				t.children[0].style.color=b.style.color;
				t.children[1].children[0].style.color=b.style.color;
				t.children[0].children[0].innerHTML=b.children[0].innerHTML;
				if(!isCloudSave) t.children[0].children[2].innerHTML=b.children[2].innerHTML;
				else t.children[0].children[2].innerHTML=b.children[2].innerHTML<?php if($w) echo " + '<br>'" ?> + '<span class="txmp-tag tag-purple-g">评分：<span id="list-rating-' + b.children[0].getAttribute('data-id') + '"></span>';
				t.setAttribute('data-id',b.children[0].getAttribute('data-id'));
				obj.style.display="none";

				if(isCloudSave) myIds[b.children[0].getAttribute('data-id')] = b.children[0].getAttribute('data-id');

				var id = b.children[0].getAttribute('data-id');
				if(myRating[id] === undefined) {
					myRating[id] = 0;
				}

				highlight(t);
				_focus_to(t);
				generateUrl();
			}
		}
		for(var i=0;i<inList.length;i++) {
			var b=inList[i];
			//console.log(b);
			b.children[0].onclick=function(){
				this.parentElement.style.border="1px solid #000000";
				this.parentElement.setAttribute("data-select",'yes');
				return false;
			}
			if(b.children[0].getAttribute('data-id')==t.getAttribute('data-id'))
				b.style.backgroundColor="#EEEEEE";
			else {
				b.style.backgroundColor="transparent";
			}
			b.style.border="none";
			b.removeAttribute('data-select');
		}
	},500);

	$('#button-cancel')[0].onclick=function() {
		obj.style.display="none";
		clearInterval(T);
		highlight(t);
		_focus_to(t);
		generateUrl();
	}
}

function buildCloudObject() {
	var idList=[];
	var obj=$('.maker-list')[0];
	for(var i=0;i<obj.children.length;i++) {
		var b=obj.children[i];
		var id=b.getAttribute('data-id');
		if(id=="undefined") continue;
		if(id==undefined) continue;
		idList[idList.length]=id;
	}

	var list = {
		'public': $('#cloudPublic')[0].checked,
		'title': $('#cloudTitle').val(),
		'playlist': [

		],
		'transform': {
			'pick': ($('#cloudIsRand')[0].checked)?'rand':'next', // 循环选择下一个，最近的可选项
			'random_shuffle': ($('#cloudRandShuffle')[0].checked),
			'constraints': { // 约束：$ >= 0
				'comparator': $('#cloudConstComparator').val(),
				'multiplier': $('#cloudConstMultiplier').val(),
				'delta': $('#cloudConstDelta').val(),
			},
			'constraints2': { // 约束：$ >= 0
				'comparator': $('#cloudConstComparator2').val(),
				'multiplier': $('#cloudConstMultiplier2').val(),
				'delta': $('#cloudConstDelta2').val(),
			},
			/*
				无法选择下一个时的处理方式：
					end: 终止播放
					loop: 进行单曲循环
					all: 将所有视为候选项，以便跳出困境
			*/
			'termination': $('#cloudTermination').val(),
		}
	};

	for(var i=0;i<idList.length;i++) {
		var id = idList[i];
		list['playlist'][list['playlist'].length] = {
			'id': id,
			'rating': myRating[id],
			'canonical': myIds[id],
		};
	}

	return list;
}

function buildCloudCsv() {
	var idList=[];
	var obj=$('.maker-list')[0];
	for(var i=0;i<obj.children.length;i++) {
		var b=obj.children[i];
		var id=b.getAttribute('data-id');
		if(id=="undefined") continue;
		if(id==undefined) continue;
		idList[idList.length]=id;
	}

	var ret = G_csv_version + "\n" + ($('#cloudPublic')[0].checked ? 'T' : 'F') + ",";
	ret += Base64.encode($('#cloudTitle').val()) + "\n";
	for(var i=0;i<idList.length;i++) {
		var id = idList[i];
		ret += id + ',' + myRating[id] + ',' + myIds[id] + "\n";
	}
	ret += "-\n";

	ret += (($('#cloudIsRand')[0].checked)?'R':'N') + ',';
	ret += ($('#cloudRandShuffle')[0].checked ? 'T' : 'F') + ',';
	/*
		无法选择下一个时的处理方式：
			F: 终止播放
			L: 进行单曲循环
			A: 将所有视为候选项，以便跳出困境
	*/
	if($('#cloudTermination').val() == 'end') {
		ret += "F\n";
	} else if($('#cloudTermination').val() == 'loop') {
		ret += "L\n";
	} else {
		ret += "A\n";
	}
	ret += $('#cloudConstComparator').val() + ',';
	ret += $('#cloudConstMultiplier').val() + ',';
	ret += $('#cloudConstDelta').val() + "\n";
	ret += $('#cloudConstComparator2').val() + ',';
	ret += $('#cloudConstMultiplier2').val() + ',';
	ret += $('#cloudConstDelta2').val() + "\n";

	return ret;
}

async function display_raw(xurl) {
	var wnd = modal_loading('正在显示');
	$.ajax({
		async: true,
		url: xurl,
		dataType: 'text',
		method: 'GET',
		error: async function(e) {
			close_modal(wnd);
			await modal_alert_p('错误','导出结果加载失败');
		},
		success: async function(e) {
			close_modal(wnd);
			await modal_promptarea_p('导出结果','请复制下面的文本',e);
		}
	});
}

async function openUrl(ffflag = 0,doOpen = 1) {
	if(isCloudSave) {
		var listdata = buildCloudCsv();
		var wnd = modal_loading('请稍候','正在保存');
		$.ajax({
			async: true,
			url: home + 'playlist/save-list/'+((ffflag != 1) ? cloudId : 0),
			dataType: 'text',
			method: 'POST',
			data: {
				'str': listdata,
				'isCsv': 'yes',
				'delete': ffflag == -1,
				'isSubmit': 'yes',
				'isAjax': 'yes',
				'csrf-token-name': G_csrf_s1,
				'csrf-token-value': G_csrf_s2,
			},
			error: function(e) {
				close_modal(wnd);
				console.error('发生错误：',e);
				console.info('访问网址：',home + 'playlist/save-list/0');
				modal_alert('出问题了','无法操作');
			},
			success: async function(e) {
				if(e && e.substr(0,1) != '+') {
					close_modal(wnd);
					console.error('发生错误：',e);
					modal_alert('无法操作',e);
				} else if(ffflag == 0) {
					if(doOpen != 0) {
						if(doOpen == 2) {
							close_modal(wnd);
							if(!isCsv) {
								display_raw(home + 'playlist/' + G_username + '/' + cloudId + '?raw');
							} else {
								var type = await modal_confirm_p(
									'导出歌单',
									'你想要以什么格式导出？' + "<br>" +
									'传统格式与旧版本兼容，且适合手动编辑歌单。' + "<br>" +
									'CSV 格式文件较小，且可以快速导入至新版本。',
									'传统',
									'CSV'
								);
								if(type) {
									display_raw(home + 'playlist/' + G_username + '/' + cloudId + '?raw');
								} else {
									display_raw(home + 'playlist/' + G_username + '/' + cloudId + '?raw&json');
								}
							}
							return;
						}
						else location.href = home + 'playlist/' + G_username + '/' + cloudId;
					}
				} else if(ffflag == -1) {
					if(doOpen != 0) location.href = home + 'user';
				} else {
					if(doOpen != 0) location.href = home + 'list-maker/' + e.substr(1);
				}
			}
		});
	}
	else if(isFmSave) {

		var al=modal_loading('稍等','正在同步到RojExplorer...<br>1/3 获得服务器许可');
		var fmbase=localStorage['fm-save-base-'+fmRandId];
		var fname=localStorage['fm-save-loc-'+fmRandId];

		var successFunc=function (){
			modal_alert("成功","同步完成");
			close_modal(al);
			if(doOpen != 0) location.href=$('#g-url')[0].value+'&fmid='+fmRandId;
		};

		//console.log(fmbase+'?appcallback/accessToken');
		$.ajax({
			timeout:9000,
			url:fmbase+'?appcallback/accessToken',
			dataType:'text',
			method:'GET',
			xhrFields: {
				withCredentials: true
			},
			error:function(res) {
				modal_alert("出问题了","同步失败：许可字符串获取失败<br>（RojExplorer没有登录/RojExplorer未与本站对接）");
				close_modal(al);
			},
			success:function(res) {
				if(res.length>128) {
					modal_alert("出问题了","同步失败：许可字符串获取失败<br>（RojExplorer没有登录/RojExplorer未与本站对接）");
					close_modal(al);
					return;
				}
				var token=res;
				//console.log(token);
				close_modal(al);
				al=modal_loading('稍等','正在同步到RojExplorer...<br>2/3 读取目标文件');
				$.ajax({
					timeout:9000,
					url:fmbase+'?explorer/fileProxy&accessToken='+encodeURIComponent(token)+'&path='+encodeURIComponent(fname),
					dataType:'text',
					method:'GET',
					xhrFields: {
						withCredentials: true
					},
					error:function(res) {
						modal_alert("出问题了","同步失败：目标文件读取失败");
						close_modal(al);
					},
					success: function(res){
						try{res=JSON.parse(res)}catch(err){res=undefined};
						if(!res || !res.type) {
							res={
								"type": "app",
								"content": "",
								"icon": "https://ak-ioi.com/favicon.ico",
								"width": "80%",
								"height": "90%",
								"simple": 0,
								"resize": 1,
								"undefined": 0
							};
						}
						res.content="var url=encodeURIComponent('"+$('#g-url')[0].value+"');\nlocalStorage['callback-go-txmp']=url;\nwindow.open('?appcallback/go&url=txmp&filename='+encodeURIComponent(theFile));";
						close_modal(al);
						al=modal_loading('稍等','正在同步到RojExplorer...<br>3/3 保存目标文件');
						$.ajax({
							timeout:9000,
							url:fmbase+'?explorer/fileSave&accessToken='+encodeURIComponent(token)+'&path='+encodeURIComponent(fname)+'&type=filesave',
							dataType:'json',
							method:'POST',
							xhrFields: {
								withCredentials: true
							},
							data: {
								filecontent: JSON.stringify(res)
							},
							error:function(res) {
								modal_alert("出问题了","同步失败：目标文件读取失败");
								close_modal(al);
							},
							success:function(res) {
								successFunc();
							}

						});
					}
				});
			}
		});

	}
	else if(doOpen != 0) location.href=$('#g-url')[0].value;
}

function internal_conv() {
	var txt=home;
	var idList=[];
	var obj=$('.maker-list')[0];
	for(var i=0;i<obj.children.length;i++) {
		var b=obj.children[i];
		var id=b.getAttribute('data-id');
		b.setAttribute('data-order',i);
		if(id=="undefined") continue;
		if(id==undefined) continue;
		idList[idList.length]=id;
		if(isCloudSave) {
			$('#list-rating-' + id).html(myRating[id]);
			if(!$('#list-rating-' + id).parent().hasClass('action-linked')) {
				$('#list-rating-' + id).parent().addClass('action-linked');
				$('#list-rating-' + id).parent().click(function(){
					rate(this.children[0]);
					return false;
				});
			}
		}
	}
	txt += 'list-maker';
	txt+="?list=";
	for(var i=0;i<idList.length;i++) {
		if(i!=0) txt+='|';
		txt+=idList[i];
	}

	window.open(txt);
}

async function cloud_unsave() {
	if(!await modal_confirm_p('取消云保存','确定要取消云保存？\n该歌单将不再在RojExplorer中保存（文件不删除）')) return;
	isFmSave=false;
	fmRandId=0;
	$('.cloudsave-enabled').css('display','none');
	$('.cloudsave-disabled').css('display','inline-block');
	$('.list-submit').html('打开');
	var loc=location.href;
	loc=loc.replace(/\&fmid\=(\d+)/g,'');
	loc=loc.replace(/\?fmid\=(\d+)/g,'?');
	//console.log(loc);
	history.replaceState({},document.title,loc);

	generateUrl();
}

async function cloud_ensave() {
	var txt='';
	if(!localStorage['txmp-maker-save-fmbase']) localStorage['txmp-maker-save-fmbase']='https://wmsdf.cf/cloud/';
	txt=await modal_prompt_p('云保存初次设置','请输入RojExplorer网盘网址<br>（必须带上 https:// 等前缀）',localStorage['txmp-maker-save-fmbase']);
	if(!txt) return;
	localStorage['txmp-maker-save-fmbase']=txt;

	isFmSave=true;
	fmRandId=Math.floor(Math.random()*1000);

	localStorage['fm-save-base-'+fmRandId]=txt;
	localStorage['fm-save-loc-'+fmRandId]='/新建歌单.oexe';

	$('.cloudsave-enabled').css('display','inline-block');
	$('.cloudsave-disabled').css('display','none');
	$('.list-submit').html('保存并打开');
	$('.type-form-content').css('display','none');

	var loc=location.href;
	if(loc.indexOf('?')) loc+='&fmid='+fmRandId;
	else loc+='?fmid='+fmRandId;

	history.replaceState({},document.title,loc);

	generateUrl();
}

function internal_cloudsave() {
	if(!G_username) {
		modal_alert('错误','此功能仅限以登录用户使用');
		return;
	}

	// return;
	var wnd = modal_loading('请稍候','正在操作...');

	var ret = G_csv_version + "\nF,5peg5qCH6aKY\n";

	var idList=[];
	var obj=$('.maker-list')[0];
	for(var i=0;i<obj.children.length;i++) {
		var b=obj.children[i];
		var id=b.getAttribute('data-id');
		b.setAttribute('data-order',i);
		if(id=="undefined") continue;
		if(id==undefined) continue;
		idList[idList.length]=id;
		ret += id + ',0,' + id + "\n";
	}

	ret += "N,F,L" + "\n";
	ret += ">=,0.5,0" + "\n";
	ret += ">,0,-1" + "\n";

	// -- 发送消息到服务端，进行保存 --
	$.ajax({
		async: true,
		url: home + 'playlist/save-list/0',
		dataType: 'text',
		method: 'POST',
		data: {
			'str': ret,
			'isCsv': 'yes',
			'isSubmit': 'yes',
			'isAjax': 'yes',
			'csrf-token-name': G_csrf_s1,
			'csrf-token-value': G_csrf_s2,
		},
		error: function(e) {
			close_modal(wnd);
			console.error('发生错误：',e);
			console.info('访问网址：',home + 'playlist/save-list/0');
			modal_alert('出问题了','无法保存');
		},
		success: function(e) {
			if(e && e.substr(0,1) != '+') {
				close_modal(wnd);
				console.error('发生错误：',e);
				modal_alert('无法保存',e);
			}
			else {
				location.href = home+'list-maker/'+e.substr(1);
			}
		}
	});

	generateUrl();
}

function mark(t) {
	t=t.parentElement.parentElement.parentElement.parentElement;
	if(markItem) {
		$(markItem).css('background-color','rgba(255,255,16,0)');
	}
	if(markItem==t) {markItem=null;return;}
	$(t).css('background-color','rgba(170,230,255,1)');
	markItem=t;

	generateUrl();
}

async function moveto(t) {
	t=t.parentElement.parentElement.parentElement.parentElement;
	if(!markItem) {
		await modal_alert_p("提示","请先标记要移动的内容。");
		_focus_to(t);
		return;
	}

	$(markItem).insertBefore(t);

	$(markItem).css('background-color','rgba(255,255,16,0)');
	highlight(markItem);
	markItem=null;

	generateUrl();
}

async function importData(flag = true) {
	var res;
	if(flag) {
		res = await modal_confirm_by_input('导入数据以覆盖此歌单<br>此歌单的内容将失去且难以恢复','此歌单的原名',cloudData['title']);
		if(!res) return;
	}
	res = await modal_promptarea_p('导入歌单','输入需要导入的数据');
	if(!res) return;
	var isCsv = (res.indexOf('{') == -1);
	if(!isCsv) {
		try{
			res = JSON.parse(res);
		} catch(e) {
			modal_alert('无法导入','数据无法解析，请检查完整性！');
			return;
		}
		if(typeof(res) != 'object') {
			modal_alert('无法导入','数据无法解析，请检查完整性！');
			return;
		}
	}

	var list = res;
	var wnd = modal_loading('请稍候','正在导入');
	$.ajax({
		async: true,
		url: home + 'playlist/save-list/'+(isCloudSave ? cloudId : 0),
		dataType: 'text',
		method: 'POST',
		data: {
			'str': (isCsv ? res : JSON.stringify(list)),
			'isCsv': (isCsv ? 'yes' : 'no'),
			'delete': false,
			'isSubmit': 'yes',
			'isAjax': 'yes',
			'csrf-token-name': G_csrf_s1,
			'csrf-token-value': G_csrf_s2,
		},
		error: function(e) {
			close_modal(wnd);
			console.error('发生错误：',e);
			console.info('访问网址：',home + 'playlist/save-list/0');
			modal_alert('出问题了','无法操作');
		},
		success: function(e) {
			if(e && e.substr(0,1) != '+') {
				close_modal(wnd);
				console.error('发生错误：',e);
				modal_alert('无法操作',e);
			} else {
				if(flag) history.go(0);
				else {
					window.open(location.href = home + 'list-maker/' + e.substr(1));
				}
			}
		}
	});
}

/* </script> */
