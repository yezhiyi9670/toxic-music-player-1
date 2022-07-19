"mod MakerApp";

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

	// 显示内容
	if(isList) {
		var obj=$('.maker-list')[0];
		var tpl=obj.innerHTML;
		obj.innerHTML="";
		for(var i=0;i<list.length;i++) {
			// obj.innerHTML+=tpl;
			$(obj).append($(tpl));
		}
		for(var i=0;i<obj.children.length;i++) {
			var b=obj.children[i];
			b.children[0].style.color='#'+(listColor[i] ?? '000');
			if(listColor[i] == null) b.children[0].style.opacity='0.6';
			b.children[1].children[0].style.color='#'+(listColor[i] ?? '000');
			b.setAttribute('data-id',list[i]);
			if(listColor[i] == null) {
				b.setAttribute('data-invalid','yes');
			}
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
				scrollto_ele(g,'body');
			}
		} else if(k == 115) {
			var g = $('.list-focus')[0].nextElementSibling;
			if(g) {
				_focus_to(g);
				scrollto_ele(g,'body');
			}
		} else if(k == 97) {
			var g = $('.list-focus')[0].children[1].children[1].children[3].children[0];
			g.click();
			scrollto_ele($('.list-focus')[0],'body');
		} else if(k == 101) {
			var g = $('.list-focus')[0].children[1].children[1].children[1].children[0];
			g.click();
			scrollto_ele($('.list-focus')[0],'body');
		} else if(k == 100) {
			var g = $('.list-focus')[0].children[1].children[1].children[2].children[0];
			g.click();
			scrollto_ele($('.list-focus')[0],'body');
		} else if(k == 114) {
			var g = $('.list-focus')[0].children[1].children[1].children[4].children[0];
			g.click();
			scrollto_ele($('.list-focus')[0],'body');
		} else if(k == 122) {
			var g = $('.list-focus')[0].children[1].children[1].children[5].children[0];
			g.click();
			scrollto_ele($('.list-focus')[0],'body');
		} else if(k == 120) {
			var g = $('.list-focus')[0].children[1].children[1].children[6].children[0];
			g.click();
			scrollto_ele($('.list-focus')[0],'body');
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
	$('.tooltip-box input[type=checkbox]').on('input',generateUrl);
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
	if(!$('.list-submit')[0].hasAttribute('disabled')) {
		mark_changed();
	}
	genUrlTime = new Date().getTime();
}

function scrollto_ele(v,s) {
	scrollto(
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
			$('span#list-id-' + id).html(fa_icon('hashtag') + myIds[id] + '<span></span>');

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
	if(isCloudSave) $('#g-url')[0].value=home+'playlist/'+G.username+'/'+cloudId;

	var datalen=txt.length;
	if(isCloudSave) {
		if(isCsv) datalen = buildCloudCsv().length;
		else datalen = JSON.stringify(buildCloudObject()).length;
	}

	// 长度显示
	var lenlimit=2048;
	if(isCloudSave) lenlimit = cloudLenLimit;
	$('.list-len-show').html(datalen+'/'+lenlimit);
	if(datalen>lenlimit) {
		$('.list-submit')[0].setAttribute('disabled','disabled');
		if(!$('.op-btn').hasClass('am-disabled')) $('.op-btn').addClass('am-disabled');
	}
	else {
		$('.list-submit')[0].removeAttribute('disabled');
		$('.op-btn').removeClass('am-disabled');
	}

	// 数量显示
	amount=idList.length;

	// 检测是否有无效歌曲
	if($('[data-id][data-invalid=yes]').length == 0) {
		$('.btn-clear-invalid').remove();
	}
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
		await modal_alert_p(LNG('led.alert.last'),LNG('led.alert.last.tips'));
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

// 清除失效歌曲
async function clearInvalid() {
	var invalid_count = $('[data-id][data-invalid=yes]').length;
	var sel = await modal_confirm_p(LNG('led.alert.clear'),LNG('led.alert.clear.tips',invalid_count));
	if(!sel) return;
	var valid_count = $('[data-id]:not([data-invalid=yes])').length;
	// 不可以清空整个列表
	var first = (valid_count == 0);
	var clear_count = 0;
	$('[data-id][data-invalid=yes]').each(function() {
		if(first) {
			first = false;
		} else {
			$(this).remove();
			clear_count += 1;
		}
	});
	generateUrl();
	Toast.make_toast_text(LNG('led.toast.clear', clear_count));
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
	var txt = await modal_prompt_p(LNG('led.alert.change_weight'),LNG('led.alert.change_weight.tips'),myRating[id]);
	if(!txt) {_focus_to(t);return;}
	txt = 1*txt;
	if(txt >= -45000 && txt <= 45000 && Math.floor(txt) === Math.ceil(txt)) {
		myRating[id] = txt;
		generateUrl();
	}
	else {
		Toast.make_toast_text(LNG('led.toast.invalid_weight'));
	}
	_focus_to(t);
}

async function setCanonical(t,id) {
	if(!id) t=t.parentElement.parentElement.parentElement.parentElement;
	if(!id) id = t.getAttribute('data-id');
	var txt = await modal_prompt_p(LNG('led.alert.change_id'),LNG('led.alert.change_id.tips',id),myIds[id]);
	if(!txt) {_focus_to(t);return;}
	if(txt.match(/^(\w+)$/)) {
		myIds[id] = txt;
		generateUrl();
	}
	else {
		Toast.make_toast_text(LNG('led.toast.invalid_id'));
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
	wnd.ready_for_search_check(); // 激活“准备速查”

	var T=setInterval(function(){
		wnd.ready_for_search_check(); // 防止因窗口未加载失败
		var inList=wnd.$('.song-item');
		for(var i=0;i<inList.length;i++) {
			var b=inList[i];
			//console.log(b);
			if(b.getAttribute('data-select')=='yes'){
				clearInterval(T);
				// 选中，修改
				t.removeAttribute('data-invalid');
				t.children[0].style.opacity = '1';
				t.children[0].style.color = b.style.color;
				t.children[1].children[0].style.color=b.style.color;
				t.children[0].children[0].innerHTML=b.children[0].innerHTML;
				if(t.children[0].children[2] === undefined) {
					t.children[0].innerHTML += '<br /><span class="addition-cmt"></span>';
				}
				if(!isCloudSave) t.children[0].children[2].innerHTML=b.children[2].innerHTML;
				else t.children[0].children[2].innerHTML=b.children[2].innerHTML + '<span class="txmp-tag tag-purple-g">' + fa_icon('asterisk') +'<span id="list-rating-' + b.children[0].getAttribute('data-id') + '"></span>';
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
			$(b.children[0]).off('click');
			$(b.children[0]).on('click',function(e){
				this.parentElement.style.border="1px solid #000000";
				this.parentElement.setAttribute("data-select",'yes');
				// 防止错误地打开歌曲播放页。v127a-pre10 修复。
				e.stopPropagation();
				e.preventDefault();
			});
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

	var ret = G.csv_version + "\n" + ($('#cloudPublic')[0].checked ? 'T' : 'F') + ",";
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
	var wnd = modal_loading(LNG('led.alert.export.loading'));
	$.ajax({
		async: true,
		url: xurl,
		dataType: 'text',
		method: 'GET',
		error: async function(e) {
			close_modal(wnd);
			await modal_alert_p(LNG('led.alert.export.fail'),LNG('led.alert.export.fail.tips'));
		},
		success: async function(e) {
			close_modal(wnd);
			await modal_promptarea_p(LNG('led.alert.export.show'),LNG('led.alert.export.show.tips'),e);
		}
	});
}

var is_changed = false;
function mark_changed() {
	is_changed = true;
	$('.list-submit').text(LNG('led.action.save'));
}

async function openUrl(ffflag = 0,doOpen = 1) {
	if(isCloudSave) {
		if(ffflag == 0 && doOpen == 1 && !is_changed) {
			location.href = home + 'playlist/' + G.username + '/' + cloudId;
			return;
		}
		var listdata = buildCloudCsv();
		var wnd = modal_loading(LNG('ui.wait'),LNG('led.alert.save'));
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
				'csrf-token-name': G.csrf_s1,
				'csrf-token-value': G.csrf_s2,
			},
			error: function(e) {
				close_modal(wnd);
				console.error(LNG('led.debug.save.error'),e);
				modal_alert(LNG('ui.error'),LNG('led.alert.save.error'));
			},
			success: async function(e) {
				if(e && e.substr(0,1) != '+') {
					close_modal(wnd);
					console.error(LNG('led.debug.save.error'),e);
					modal_alert(LNG('ui.error'), LNG('led.alert.save.error') + LNG('punc.colon') + e);
				} else if(ffflag == 0) {
					is_changed = false;
					Toast.make_toast_text(LNG('led.toast.saved'));
					$('.list-submit').text(LNG('led.action.open'));
					if(doOpen != 0) {
						close_modal(wnd);
						if(doOpen == 2) {
							if(!isCsv) {
								display_raw(home + 'playlist/' + G.username + '/' + cloudId + '?raw');
							} else {
								var type = await modal_confirm_p(
									LNG('led.alert.export'),
									LNG('led.alert.export.tips'),
									LNG('led.exporttype.classic'),
									LNG('led.exporttype.csv')
								);
								if(type) {
									display_raw(home + 'playlist/' + G.username + '/' + cloudId + '?raw');
								} else {
									display_raw(home + 'playlist/' + G.username + '/' + cloudId + '?raw&json');
								}
							}
							return;
						}
					}
				} else if(ffflag == -1) {
					if(doOpen != 0) location.href = home + 'user';
				} else {
					if(doOpen != 0) location.href = home + 'list-maker/' + e.substr(1);
				}
			}
		});
	} else if(doOpen != 0) {
		location.href=$('#g-url')[0].value;
	}
}

/* TODO[WMSDFCL/User:4]: 清理重复代码 */
async function editRaw() {
	conf = await modal_confirm_by_input(LNG('led.alert.editraw.action'),LNG('led.alert.editraw.prompt'),
					'' + Math.floor(9000 * Math.random() + 1000));
	if(!conf) return;
	var wnd2 = modal_loading(LNG('led.alert.export.loading'));
	var xurl = home + 'playlist/' + G.username + '/' + cloudId + '?raw';
	$.ajax({
		async: true,
		url: xurl,
		dataType: 'text',
		method: 'GET',
		error: async function(e) {
			close_modal(wnd2);
			await modal_alert_p(LNG('led.alert.export.fail'),LNG('led.alert.export.fail.tips'));
		},
		success: async function(e) {
			while(true) {
				close_modal(wnd2);
				var res;
				res = await modal_promptarea_p(LNG('led.alert.editraw'),LNG('led.alert.editraw.tips'),e);
				e = res;
				if(!res) return;
				var isCsv = (res.indexOf('{') == -1);
				if(!isCsv) {
					try{
						res = JSON.parse(res);
					} catch(e) {
						modal_alert(LNG('ui.error'),LNG('led.alert.import.fail.tips'));
						return;
					}
					if(typeof(res) != 'object') {
						modal_alert(LNG('ui.error'),LNG('led.alert.import.fail.tips'));
						return;
					}
				}

				var list = res;
				var is_success = await new Promise((resolve,reject) => {
					var wnd = modal_loading(LNG('ui.wait'),LNG('led.alert.import.loading'));
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
							'csrf-token-name': G.csrf_s1,
							'csrf-token-value': G.csrf_s2,
						},
						error: function(e) {
							close_modal(wnd);
							resolve(2);
						},
						success: function(e) {
							if(e && e.substr(0,1) != '+') {
								close_modal(wnd);
								resolve(e);
							} else {
								resolve(0);
							}
						}
					});
				});
				if(2 === is_success) {
					console.error(LNG('led.debug.save.error'));
					await modal_alert_p(LNG('ui.error'),LNG('led.alert.save.error'));
				} else if(0 === is_success) {
					history.go(0);
					break;
				} else {
					console.error(LNG('led.debug.save.error'),is_success);
					await modal_alert_p(LNG('ui.error'),LNG('led.alert.save.error') + LNG('punc.colon') + is_success);
				}
			}
		}
	});
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

function internal_cloudsave() {
	if(!G.username) {
		modal_alert(LNG('ui.error'),LNG('led.alert.login_only'));
		return;
	}

	// return;
	var wnd = modal_loading(LNG('ui.wait'),LNG('led.alert.save'));

	var ret = G.csv_version + "\nF,5peg5qCH6aKY\n";

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
	ret += "-\n";

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
			'csrf-token-name': G.csrf_s1,
			'csrf-token-value': G.csrf_s2,
		},
		error: function(e) {
			close_modal(wnd);
			console.error(LNG('led.debug.save.error'),e);
			modal_alert(LNG('ui.error'),LNG('led.alert.save.error'));
		},
		success: function(e) {
			if(e && e.substr(0,1) != '+') {
				close_modal(wnd);
				console.error(LNG('led.debug.save.error'),e);
				modal_alert(LNG('ui.error'), LNG('led.alert.save.error') + LNG('punc.colon') + e);
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
		await modal_alert_p(LNG('led.alert.needmark'),LNG('led.alert.needmark.tips'));
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
		res = await modal_confirm_by_input(LNG('led.alert.import.action'),LNG('led.alert.import.prompt'),cloudData['title']);
		if(!res) return;
	}
	res = await modal_promptarea_p(LNG('led.alert.import'),LNG('led.alert.import.tips'));
	if(!res) return;
	var isCsv = (res.indexOf('{') == -1);
	if(!isCsv) {
		try{
			res = JSON.parse(res);
		} catch(e) {
			modal_alert(LNG('ui.error'),LNG('led.alert.import.fail.tips'));
			return;
		}
		if(typeof(res) != 'object') {
			modal_alert(LNG('ui.error'),LNG('led.alert.import.fail.tips'));
			return;
		}
	}

	var list = res;
	var wnd = modal_loading(LNG('ui.wait'),LNG('led.alert.import.loading'));
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
			'csrf-token-name': G.csrf_s1,
			'csrf-token-value': G.csrf_s2,
		},
		error: function(e) {
			close_modal(wnd);
			console.error(LNG('led.debug.save.error'),e);
			modal_alert(LNG('ui.error'),LNG('led.alert.save.error'));
		},
		success: function(e) {
			if(e && e.substr(0,1) != '+') {
				close_modal(wnd);
				console.error(LNG('led.debug.save.error'),e);
				modal_alert(LNG('ui.error'),LNG('led.alert.save.error') + LNG('punc.colon') + e);
			} else {
				if(flag) history.go(0);
				else {
					window.open(location.href = home + 'list-maker/' + e.substr(1));
				}
			}
		}
	});
}

window.onbeforeunload = function() {
	if(is_changed) {
		return LNG('led.not_saved');
	}
	e.preventDefault();
	return false;
};

/* </script> */
