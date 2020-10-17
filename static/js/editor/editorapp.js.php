<?php
header("Content-Type: text/javascript");
$w=($_GET["w"]=="1");
error_reporting(E_ALL & (~E_NOTICE));
header("Cache-Control: public max-age=1296000");
header("Last-Modified: " . gmdate('D, d M Y H:i:s',filemtime(__FILE__)));
?>

/* <script> */

"EditorApp BEGIN";

function insertText(obj,str) {
	if (document.selection) {
		var sel = document.selection.createRange();
		sel.text = str;
	} else if (typeof obj.selectionStart === 'number' && typeof obj.selectionEnd === 'number') {
		var startPos = obj.selectionStart,
			endPos = obj.selectionEnd,
			cursorPos = startPos,
			tmpStr = obj.value;
		obj.value = tmpStr.substring(0, startPos) + str + tmpStr.substring(endPos, tmpStr.length);
		cursorPos += str.length;
		obj.selectionStart = obj.selectionEnd = cursorPos;
	} else {
		obj.value += str;
	}
}

function editor_ftime() {
	var obj=$('#lyricfile')[0];
	var t=$('#preview')[0].contentWindow.$('#accurate')[0].innerHTML;
	t=Math.round(t*10)/10;
	t=Math.floor(t*10)/10;
	if(Math.abs(t-Math.floor(t))<0.01) t=t.toString()+".0";
	else t=t.toString();
	insertText(obj,t);
	var lchar=obj.value[obj.selectionStart];
	if(lchar!=' ' && lchar!=']') insertText(obj," ");

	var np=obj.value.indexOf('__FTIME__');
	if(np!=-1) {
		obj.selectionStart=np;
		obj.selectionEnd=np+9;
	}

	$(obj).keydown();
	<?php if(!$w){ ?>obj.focus();<?php } ?>
}

function editor_etime() {
	var obj=$('#lyricfile')[0];

	t='-';
	insertText(obj,t);
	var lchar=obj.value[obj.selectionStart];
	if(lchar!=' ' && lchar!=']') insertText(obj," ");

	var np=obj.value.indexOf('__FTIME__');
	if(np!=-1) {
		obj.selectionStart=np;
		obj.selectionEnd=np+9;
	}

	$(obj).keydown();
	<?php if(!$w){ ?>obj.focus();<?php } ?>
}

function editor_entag(txt) {
	var obj=$('#lyricfile')[0];
	var s=obj.selectionStart;
	var e=obj.selectionEnd;
	obj.selectionStart=obj.selectionEnd=e;
	insertText(obj,'[/'+txt+']');
	obj.selectionStart=obj.selectionEnd=s;
	insertText(obj,'['+txt+']');
	$(obj).keydown();
	obj.focus();
}

function editor_sni(x){
	var sni={
		"info": `[Info]
N  歌曲名
S  歌手
C  专辑
LA 作词
MA 作曲
A  主题色
O  原页面地址`,
		"para": "[Para @ID AC 名称]",
		"hidden": "[Hidden @ID AC 名称]",
		"reuse": "[Reuse @UID __FTIME__]",
		"similar": "[Similar @ID @UID __FTIME__ AC 名称]",
		"line": "L __FTIME__ 内容",
		"mid": `[Para -- 间奏]
L __FTIME__ - - - - - - -`,
	};
	insertText(document.getElementById('lyricfile'),sni[x]);
	document.getElementById('lyricfile').focus();
}

function editor_nl(){
	var obj=$('#lyricfile')[0];
	var str=obj.value.replace(/\r\n/g,"\n");
	var lns=str.split("\n");
	for(var i=0;i<lns.length;i++) {
		if(lns[i]=='') {
			if(i!=lns.length-1 && lns[i+1][0]!='[') {
				lns.splice(i,1);
				i--;
			}
		}
	}
	str="";
	for(var i=0;i<lns.length;i++)
	{
		if(i) str+="\n";
		str+=lns[i];
	}
	obj.value=str;
	$(obj).keydown();
}

function rmsym(str) {
	str=str.replace(/\,/g," ");
	str=str.replace(/，/g," ");
	str=str.replace(/\./g,"");
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
	var obj=$('#lyricfile')[0];
	var str=obj.value.replace(/\r\n/g,"\n");
	var lns=str.split("\n");
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
	obj.value=str;
	$(obj).keydown();
}

function editor_addl(){
	var obj=$('#lyricfile')[0];
	var str=obj.value.replace(/\r\n/g,"\n");
	var lns=str.split("\n");
	var allh=['A ','S ','N ','LA ','MA ','C ','L ','//','##','O ','TAG ','VAL ','MK ','FI '];
	var tagname='';
	for(var i=0;i<lns.length;i++) {
		var lns0=lns[i];
		lns[i]=lns[i].trim();
		if(lns[i][0]=='[' && lns[i][lns[i].length-1]==']') {
			tagname=lns[i].substring(1).split()[0];
		}
		else {
			if(tagname && tagname!="Comment")
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
				if(!ok) lns0="L __FTIME__ "+lns0;
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
	obj.value=str;
	$(obj).keydown();
}

function editor_cleartime(t) {
	var obj=$('#lyricfile')[0];
	var str=obj.value.replace(/\r\n/g,"\n");

	str=str.replace(/\bL (\d+)\.(\d+)\b/g,'L '+t);
	str=str.replace(/\bL (\d+)-(\d+).(\d+)\b/g,'L '+t);
	str=str.replace(/\bL (\d+)-(\d+)\b/g,'L '+t);
	str=str.replace(/\bL (\d+)\b/g,'L '+t);

	obj.value=str;
	$(obj).keydown();
}

function editor_formatheading(){
	var obj=$('#lyricfile')[0];
	var str=obj.value.replace(/\r\n/g,"\n");
	var lns=str.split("\n");
	var allh=['A ','S ','N ','LA ','MA ','C ','L ','//','##','O ','TAG ','VAL ','MK ','FI '];
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
	obj.value=str;
	$(obj).keydown();
}

$('document').ready(function(){
	autofit();

	setInterval(function(){
		var x=$('#thefile')[0];
		var ft=$('#ftype')[0];
		if(x.files.length!==0) {
			ft.value=x.files[0].type;
		}
		else ft.value="x/empty";
	},200);
	setInterval(function(){
		var x=$('#theback')[0];
		var ft=$('#back-ftype')[0];
		if(x.files.length!==0) {
			ft.value=x.files[0].type;
		}
		else ft.value="x/empty";
	},200);

	$('.txmp-page-right')[0].scrollTop=37777;

	$('#input-time-button').click(function() {
		$ele = $('#input-time-button');
		if($ele.hasClass('am-btn-warning')) $ele.removeClass('am-btn-warning');
		else $ele.addClass('am-btn-warning');
		document.getElementById('lyricfile').focus();
	});
	$('#lyricfile').keydown(function(e){
		console.log(e);
		if(!e) return true;

		if(e.ctrlKey == 1 && (49 <= e.which && e.which <= 55)) {
			editor_entag(String.fromCharCode(e.which));
			return false;
		}

		if(!$('#input-time-button').hasClass('am-btn-warning')) return true;
		if(e.which == 32 || e.which == 13) {editor_ftime();return false;}
		else if(e.which == 191) {editor_etime();return false;}
		return true;
	});
});

/* </script> */
