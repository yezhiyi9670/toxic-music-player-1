"mod EditorApp";

var has_pending_changes = false;

function insertText(obj,str) {
	obj.replaceSelection(str);
}

function editor_ftime() {
	var obj = lyricEditor;
	// 计算
	var t = $('.preview-play')[0].contentWindow.A.currentTime;
	t = Math.round(t*10)/10;
	t = Math.floor(t*10)/10;
	if(Math.abs(t-Math.floor(t)) < 0.01) t = t.toString()+".0";
	else t = t.toString();

	// 插入
	insertText(obj,t);

	// 双击选择机制已经被改变，不再需要补全。

	// 取出最后位置
	var txt = obj.getValue();
	var sel_list = obj.listSelections();
	var lastpos = 0;
	for(let i=0;i<sel_list.length;i++) {
		lastpos = Math.max(lastpos,getLinearPosition(txt,sel_list[i].anchor),getLinearPosition(txt,sel_list[i].head));
	}
	var np = minIndex(txt.indexOf('__FTIME__',lastpos),txt.indexOf('__LT__',lastpos));
	if(np != -1) {
		var selectL = np;
		var selectR = 0;
		if(txt[np+2] == 'F') {
			selectR = np+9;
		} else {
			selectR = np+6;
		}
		console.log(selectL,selectR,txt.substring(selectL,selectR));
		obj.setSelection(getLinedPosition(txt,selectL),getLinedPosition(txt,selectR));
	}

	if(!G.is_wap) {
		obj.focus();
	}

	has_pending_changes = true;
}

function editor_etime() {
	var obj = lyricEditor;
	// 计算
	var t = '-';

	// 插入
	insertText(obj,t);

	// 双击选择机制已经被改变，不再需要补全。

	// 取出最后位置
	var txt = obj.getValue();
	var sel_list = obj.listSelections();
	var lastpos = 0;
	for(let i=0;i<sel_list.length;i++) {
		lastpos = Math.max(lastpos,getLinearPosition(txt,sel_list[i].anchor),getLinearPosition(txt,sel_list[i].head));
	}
	var np = minIndex(txt.indexOf('__FTIME__',lastpos),txt.indexOf('__LT__',lastpos));
	if(np != -1) {
		var selectL = np;
		var selectR = 0;
		if(txt[np+2] == 'F') {
			selectR = np+9;
		} else {
			selectR = np+6;
		}
		console.log(selectL,selectR,txt.substring(selectL,selectR));
		obj.setSelection(getLinedPosition(txt,selectL),getLinedPosition(txt,selectR));
	}

	if(!G.is_wap) {
		obj.focus();
	}

	has_pending_changes = true;
}

function editor_entag(txt) {
	var obj = lyricEditor;
	var sel_list = obj.listSelections();
	var lastpos = 0;
	for(let i=sel_list.length-1;i>=0;i--) {
		var anchor = minLinedPosition(sel_list[i].anchor,sel_list[i].head);
		var head = maxLinedPosition(sel_list[i].anchor,sel_list[i].head);
		// 末端插入
		obj.setSelection(head,head);
		insertText(obj,'[/' + txt + ']');
		// 头端插入
		obj.setSelection(anchor,anchor);
		insertText(obj,'[' + txt + ']');
	}

	obj.focus();

	has_pending_changes = true;
}

function editor_sni(x){
	var sni={
		"info": `[Info]
N  <Title>
S  <Singer>
C  <Cate>
LA <Lyric Author>
MA <Music Author>
G1 <Gradiant C1>
G2 <Gradiant C2>
O  <Origin>
P  -`,
		"para": "[Para @ID AC <Name>]",
		"hidden": "[Hidden @ID AC <Name>]",
		"reuse": "[Reuse @UID __LT__]",
		"similar": "[Similar @ID @UID __LT__ AC <Name>]",
		"line": "L __LT__ <Content>",
		"mid": `[Para -- <Name>]
L __LT__ - - - - - - -`,
		"split": `[Split]`,
		"final": `[Final __LT__]`
	};
	insertText(lyricEditor,sni[x]);
	lyricEditor.focus();

	has_pending_changes = true;
}

function editor_nl(){
	var obj = lyricEditor;
	var scp = obj.getScrollInfo();
	var sep = obj.listSelections()[0];
	var str = obj.getValue().replace(/\r\n/g,"\n");
	var lns=str.split("\n");
	var tagname='';
	for(var i=0;i<lns.length;i++) {
		var sp = lns[i].trim();
		if(sp[0] == '[' && sp[sp.length-1]==']') {
			tagname = sp.substring(1,sp.length-1).split(' ')[0];
		}
		else if(tagname != 'Comment') {
			if(lns[i]=='') {
				if(i!=lns.length-1 && lns[i+1][0]!='[') {
					lns.splice(i,1);
					i--;
				}
			}
		}
	}
	str="";
	for(var i=0;i<lns.length;i++)
	{
		if(i) str+="\n";
		str+=lns[i];
	}
	obj.setValue(str);

	obj.setSelection(sep.anchor,sep.head);
	obj.scrollTo(scp.left,scp.top);

	has_pending_changes = true;
}

function rmsym(str) {
	str=str.replace(/\,/g," ");
	str=str.replace(/，/g," ");
	str=str.replace(/。/g,"");
	str=str.replace(/\;/g," ");
	str=str.replace(/；/g," ");
	str=str.replace(/\:/g," ");
	str=str.replace(/：/g," ");
	str=str.replace(/\!/g,"");
	str=str.replace(/！/g,"");
	str=str.replace(/\?/g,"");
	str=str.replace(/？/g,"");
	return str;
}

function NextWhite(str,start,flag) {
	var whitespace=[' ','\t'];
	var strstart=start;
	while(true) {
		if(strstart>str.length) {
			return -1;
		}
		if((whitespace.indexOf(str[strstart])==-1) ^ flag) {
			break;
		}
		strstart++;
	}
	return strstart;
}

function editor_rmsym() {
	var obj = lyricEditor;
	var scp = obj.getScrollInfo();
	var sep = obj.listSelections()[0];
	var str = obj.getValue().replace(/\r\n/g,"\n");
	var lns = str.split("\n");
	var lrch=['L'];

	var tagname='';
	for(var i=0;i<lns.length;i++) {
		var curr=0;
		curr=NextWhite(lns[i],curr,false);
		if(curr==-1 || lns[i][curr]!='L') continue;
		curr=NextWhite(lns[i],curr,true);
		if(curr==-1) continue;
		curr=NextWhite(lns[i],curr,false);
		if(curr==-1) continue;
		curr=NextWhite(lns[i],curr,true);
		if(curr==-1) continue;
		curr=NextWhite(lns[i],curr,false);
		if(curr==-1) continue;
		lns[i]=lns[i].substring(0,curr)+rmsym(lns[i].substring(curr));
	}
	str="";
	for(var i=0;i<lns.length;i++)
	{
		if(i) str+="\n";
		str+=lns[i];
	}
	
	obj.setValue(str);
	
	obj.setSelection(sep.anchor,sep.head);
	obj.scrollTo(scp.left,scp.top);

	has_pending_changes = true;
}

function editor_addl(){
	var obj=lyricEditor;
	var scp = obj.getScrollInfo();
	var sep = obj.listSelections()[0];
	var str=obj.getValue().replace(/\r\n/g,"\n");
	var lns=str.split("\n");
	var allh=['A ','S ','N ','LA ','MA ','C ','L ','//','##','O ','TAG ','VAL ','MK ','FI ','G1 ','G2 ','P ','D '];
	var tagname='';
	for(var i=0;i<lns.length;i++) {
		var lns0=lns[i];
		lns[i]=lns[i].trim();
		if(lns[i][0]=='[' && lns[i][lns[i].length-1]==']') {
			tagname=lns[i].substring(1,lns[i].length-1).split(' ')[0];
		}
		else {
			if(tagname == 'Para' || tagname == 'Similar' || tagname == 'Hidden')
			{
				var ok=0;
				if(lns[i]=='') ok=1;
				for(var j=0;j<allh.length;j++)
				{
					if(lns[i].substring(0,allh[j].length)==allh[j])
					{
						ok=1;
					}
				}
				if(!ok) lns0="L __LT__ "+lns0;
			}
		}
		lns[i]=lns0;
	}
	str="";
	for(var i=0;i<lns.length;i++)
	{
		if(i) str+="\n";
		str+=lns[i];
	}
	obj.setValue(str);
	obj.setSelection(sep.anchor,sep.head);
	obj.scrollTo(scp.left,scp.top);

	has_pending_changes = true;
}

function editor_fixtime() {
	var obj = lyricEditor;
	var scp = obj.getScrollInfo();
	var sep = obj.listSelections()[0];
	var str = obj.getValue().replace(/\r\n/g,"\n");

	str = str.replace(/\bL __FTIME__\b/g,'L __LT__');

	obj.setValue(str);
	obj.setSelection(sep.anchor,sep.head);
	obj.scrollTo(scp.left,scp.top);

	has_pending_changes = true;
}

function editor_cleartime(t) {
	var obj = lyricEditor;
	var scp = obj.getScrollInfo();
	var sep = obj.listSelections()[0];
	var str = obj.getValue().replace(/\r\n/g,"\n");

	str=str.replace(/\bL (\d+)\.(\d+)\b/g,'L '+t);
	str=str.replace(/\bL (\d+)-(\d+).(\d+)\b/g,'L '+t);
	str=str.replace(/\bL (\d+)-(\d+)\b/g,'L '+t);
	str=str.replace(/\bL (\d+)\b/g,'L '+t);

	obj.setValue(str);
	obj.setSelection(sep.anchor,sep.head);
	obj.scrollTo(scp.left,scp.top);

	has_pending_changes = true;
}

function editor_formatheading(){
	var obj = lyricEditor;
	var scp = obj.getScrollInfo();
	var sep = obj.listSelections()[0];
	var str = obj.getValue().replace(/\r\n/g,"\n");
	var lns=str.split("\n");
	var allh=['A ','S ','N ','LA ','MA ','C ','L ','//','##','O ','TAG ','VAL ','MK ','FI ','G1 ','G2 ','P ','D '];
	var tagname='';
	for(var i=0;i<lns.length;i++) {
		var lns0=lns[i];
		lns[i]=lns[i].trim();

		var whitespace=lns0.substring(0,NextWhite(lns0,0,false));

		if(lns[i][0]=='[' && lns[i][lns[i].length-1]==']') {
			lns[i]=lns[i].substring(1,lns[i].length-1);
			var xxx=lns[i].split(' ');
			if(xxx[0].match(/^(\w+)$/)) {
				lns[i]=whitespace+'['+lns[i]+']';
				continue;
			}
			lns[i]="";
			lns[i]="Para "+xxx[xxx.length-1];
			for(var j=0;j<xxx.length-1;j++) {
				lns[i]+=' '+xxx[j];
			}
			lns[i]='['+lns[i]+']';
		}

		lns[i]=whitespace+lns[i];
	}
	str="";
	for(var i=0;i<lns.length;i++)
	{
		if(i) str+="\n";
		str+=lns[i];
	}
	
	obj.setValue(str);
	obj.setSelection(sep.anchor,sep.head);
	obj.scrollTo(scp.left,scp.top);

	has_pending_changes = true;
}

$('document').ready(function(){
	// 启动 CodeMirror
	window.lyricEditor = CodeMirror.fromTextArea($('#lyricfile')[0],{
		lineNumbers:true,
		lineWrapping:true,
		lineSeparator:"\n",
		configureMouse:function(cm,repeat,e) {
			if(repeat == 'double') {
				return {
					unit: function(cm,pos) {
						var txt = cm.getValue();
						pos = getLinearPosition(txt,pos);

						function isDelimeter(t) {
							if(t.trim() != t) {
								return true;
							}
							if('`#$%^&*()=+[{]}\\|;:\'",<>/?，。、？：；“”‘’（）【】《》·￥…「」'.indexOf(t) != -1) {
								return true;
							}
							return false;
						}

						var lt = pos;
						var rt = pos;
						while(lt > 0 && !isDelimeter(txt[lt-1])) {
							lt--;
						}
						while(rt < txt.length - 1 && !isDelimeter(txt[rt+1])) {
							rt++;
						}

						return {
							from: getLinedPosition(txt,lt),
							to: getLinedPosition(txt,rt+1)
						};
					}
				};
			}
			return {};
		}
	});
	// $('#lyricfile').val('');

	autofit();

	$('.txmp-page-right')[0].scrollTop=37777;

	$('#input-time-button').click(function() {
		$ele = $('#input-time-button');
		if($ele.hasClass('am-btn-warning')) $ele.removeClass('am-btn-warning');
		else $ele.addClass('am-btn-warning');
		lyricEditor.focus();
	});
	lyricEditor.on('changes',function(cm,delta) {
		$('#lyricfile').val(lyricEditor.getValue());
		has_pending_changes = true;
	});
	lyricEditor.on('keydown',function(cm,e){
		if(!e) return true;

		if(e.ctrlKey == 1 && (49 <= e.which && e.which <= 55)) {
			editor_entag(String.fromCharCode(e.which));
			e.preventDefault();e.stopPropagation();return false;
		}

		if(!$('#input-time-button').hasClass('am-btn-warning')) return true;
		if(e.which == 32 || e.which == 13) {editor_ftime();e.preventDefault();e.stopPropagation();return false;}
		else if(e.which == 191) {editor_etime();e.preventDefault();e.stopPropagation();return false;}
		return true;
	});

	// AJAX 提交
	$('.submit-btn').on('click',async function() {
		var modal_id = modal_loading(LNG('ui.wait'),LNG('editor.alert.save.tips'));
		$('#lyricfile').val(lyricEditor.getValue());
		var data_arr = $('form').serialize();
		// $('#lyricfile').val('');
		$.ajax({
			url: 'edit',
			method: 'POST',
			dataType: 'text',
			data: data_arr,
			timeout: 9000,
			success: async function(e) {
				close_modal(modal_id);
				if(e.substr(0,1) != '+') {
					modal_alert(LNG('ui.error'),LNG('editor.alert.save.fail') + e.substr(1));
				} else {
					// Success
					var new_data = JSON.parse(e.substr(1));
					var flag = (
						new_data['meta']['N'] == current_data['meta']['N'] &&
						new_data['meta']['A'] == current_data['meta']['A'] &&
						new_data['meta']['G1'] == current_data['meta']['G1'] &&
						new_data['meta']['G2'] == current_data['meta']['G2']
					);
					has_pending_changes = false;
					if(flag) {
						// 局部刷新
						// $('#preview')[0].contentWindow.location.reload();
						var frame = $('.preview-play')[0];
						try {
							frame.contentWindow.changeTo(frame.contentWindow.song_id,false,true);
						} catch(_e) {
							reloadIframe($(frame));
						}
						reloadIframe($('.preview-info'));
						current_data = new_data;
					} else {
						history.go(0);
					}
				}
			},
			error: async function(e) {
				close_modal(modal_id);
				modal_alert(LNG('ui.error'),LNG('ui.uke'));
			}
		});
	});
});

function togglePreviewPage() {
	var $btn = $('.btn-page-toggle');
	var $f1 = $('.preview-play');
	var $f2 = $('.preview-info');

	if($f1.css('display') == 'none') {
		$btn.text(LNG('editor.submit.page.player'));
		$f1.show();
		$f2.hide();
	} else {
		$btn.text(LNG('editor.submit.page.comp'));
		$f1.hide();
		$f2.show();
	}
}

window.onbeforeunload = function() {
	if(has_pending_changes) {
		return LNG('editor.not_saved');
	}
	e.preventDefault();
	return false;
};

/* </script> */
