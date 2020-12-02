<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

/* TODO[yezhiyi9670]: Translate generated document. */
/* 由于生成结果不能翻译，此页面暂不翻译。 */

function exitWithError($t) {
	header('HTTP/1.1 400 Bad Request');
	echo $t;
	echo '<script>setTimeout(function(){history.go(-1);},3000);</script>';
	exit;
}

//输入的字体
$font=$_REQUEST['font'];
if(!$font) $font="微软雅黑";

//判断是否组合文档，建表
$isList=isset($_REQUEST['list']);
if($isList) {
	$list=explode('|',$_REQUEST['list']);
	if($_REQUEST['list']=='') $list=[];
	$list=array_merge(array(cid()),$list);
	$canList = explode('|',$_REQUEST['customname']);
	if(count($canList) != count($list)) {
		exitWithError('自定义编号列表长度与歌单不符');
	}
	foreach($list as $item) {
		_checkPermission('music/getdoc',$item);
	}
	foreach($canList as $item) {
		if(!preg_match('/^(\w+)$/',$item)) {
			exitWithError('自定义编号只能包含字母、数字和下划线');
		}
	}

	global $canName;
	$canName = [];
	for($i=0;$i<count($list);$i++) {
		$canName[$list[$i]] = $canList[$i];
	}
}
else {
	global $canName;
	$canName = [];
	$canName[cid()] = cid();
}

//组合文档
if($isList) {
	//Source：根据文件的mtime判断是否要重新生成缓存。组合文档不会显式缓存，故任取一个文件即可。
	//显然，index.php只有每次软件更新时才会改，其mtime一般都比缓存文件小
	$source=BASIC_PATH."/index.php";
	//Dest：缓存到的文件名
	$dest=REMOTE_CACHE.$_REQUEST['cacheid']."__ListGen.xml";
	//Super：如果存在，直接输出这个文件（在隐式缓存的组合文档模式下没有太大意义）
	$super=REMOTE_CACHE.$_REQUEST['cacheid']."__ConstListGen.xml";
	//Cacheid指定文件名。如果出现'..'等内容，可能会 套出/破坏 系统的其他文件。此处判定以防止这种情况。
	if(!preg_match('/^(\w+)$/',$_REQUEST['cacheid'])){
		exitWithError("cacheid输入非法。（请不要瞎搞）");
	}
}
//单曲文档
else {
	//kuwoCrawler文档（缓存目录同爬虫缓存目录）
	if(isKuwoId(cid())) {
		global $akCrawler;
		remoteEncache(cid(),'K');
		if($akCrawler[cid()]->success==false) exit;
		$source=REMOTE_CACHE.R(cid()).".json";
		$dest=REMOTE_CACHE.R(cid())."__WordGen.xml";
		$super=REMOTE_CACHE.R(cid())."__ConstWordGen.xml";
	}
	//内置文档（缓存目录根据歌曲目录专开）
	else
	{
		$source=FILES.preSubstr($_REQUEST["_lnk"])."/lyric.txt";
		$dest=FILES.preSubstr($_REQUEST["_lnk"])."/lyric.xml";
		$super=FILES.preSubstr($_REQUEST["_lnk"])."/super.xml";
	}
}

$alwaysGen=0; //【仅供调试使用】如果设为1，则每次访问时都会重新生成缓存。播放器使用频率高或生成组合文档时，这种模式都会出锅。

//判定是否有Super文件。
if(file_exists($super))
{
	file_put_out($super,1);
	exit;
}

//打印内容到缓存文件
function _E($dest,$data)
{
	file_put_contents($dest,$data,FILE_APPEND);
}
//读取模板，并进行文本替换，然后返回
function _T($id,$rps=array(),$rpd=array())
{
	return str_replace($rps,$rpd,file_get_contents(RAW.'lyric-xml-'.$id.'.txt'));
}
//【不完美功能】将HTML标签转换为Word标签（先闭后开式，不完美）
function _W($s,$tms,$tmd,$flag = false)
{
	return str_replace($tms,$tmd,$flag ? htmlspecial3($s) : $s);
}
//计算段长度（不等于in参数的长度，因为段长度忽略无效行以及注释行）
function _N($a) {
	$ret=0;
	foreach ($a as $v) {
		if($v['ts']<=1610612735) $ret++;
	}
	return "".$ret;
}
//打印一个歌曲的文档到缓存   [single_document:id]
//在生成歌词本时，最后一个部分的格式与其他略有不同，要判断
function _SINGLE($dest,$isfirst=true,$islast=true) {
	$W_1=array(
		'[U]',              // [U]
		'[/U]',                                               // [/?]
		'[S]',           // [S]
		'[/S]',
		'<sub>',                                                 // <sub> in para-ac
		'</sub>',                                                // </sub> in para-ac
		'<sup>',
		'</sup>',
		'[1]','[/1]',
		'[2]','[/2]',
		'[3]','[/3]',
		'[4]','[/4]',
		'[5]','[/5]',
		'[6]','[/6]',
		'[7]','[/7]',
		'[R]','[/R]',
		'&nbsp;'                                                 // Microsoft word 兼容性问题
	);
	$W_2=array(
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:u w:val="single"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:strike/><w:dstrike w:val="off"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:vertAlign w:val="subscript"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:vertAlign w:val="superscript"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		'</w:t></w:r><w:r><w:rPr><w:rFonts w:hint="fareast"/><w:lang w:val="EN-US" w:fareast="ZH-CN"/></w:rPr><w:t>',
		
		'[1]','',
		'[2]','',
		'[12]','',
		'[3]','',
		'[13]','',
		'[23]','',
		'[123]','',
		
		'&lt;','&gt;',
		
		'&#0032;'
	);

	$c=json_decode(parseCmpLyric(preSubstr($_GET['_lnk']),false),true);

	//预处理【分段方式】区域的内容
	$paraov="";
	foreach($c['lyrics'] as $v)
	{
		if($v['display']){
			if($paraov) $paraov.=" ";
			$paraov.=_W($v['ac'],$W_1,$W_2);
		}
	}

	//预处理全部段长度（排序依据：第一次出现的位置）
	$paralen_used=array();
	$paralen="";
	foreach($c['lyrics'] as $v)
	{
		if(substr($v['ac'],strlen($v['ac'])-6)==='</sub>' || preg_match('/^[A-Z]+$/',$v['ac'][strlen($v['ac'])-1]))
		{
			if(!isset($paralen_used[_N($v['in'])]) && _N($v['in'])!=0){
				if(strlen($paralen)>0) $paralen.='/';
				$paralen.=_N($v['in']);
				$paralen_used[_N($v['in'])]="yes";
			}
		}
	}


	global $canName;

	//打印单曲标题（包含【详细信息】和【分段方式】）
	_E($dest,_T('1-5',array(
		'%{G}','%{N}','%{LA}','%{MA}','%{S}','%{C}','%{LO}','%{PL}','@fontname','%{newline}'
	),array(
		htmlspecial3($canName[preSubstr($_GET['_lnk'])]),
		htmlspecial3($c['meta']['N']),
		htmlspecial3($c['meta']['LA']),
		htmlspecial3($c['meta']['MA']),
		htmlspecial3($c['meta']['S']),
		htmlspecial3($c['meta']['C']),
		_W($paraov,$W_1,$W_2),
		$paralen,
		'"'.htmlspecial3($font).'"',
		!$isfirst?'<w:r><w:br w:type="page"/></w:r>':'',
	)));

	//打印歌词主体
	foreach($c['lyrics'] as $p)
	{
		//Writes para header
		_E($dest,_T(2,array(
			'%{xid}','%{c}','@fontname',
		),array(
			'11',_W('['.htmlspecial3($p['n']).' '.$p['ac'].']',$W_1,$W_2),'"'.htmlspecial3($font).'"'
		)));
		foreach($p['in'] as $v)
		{
			//Writes line
			_E($dest,_T(2,array(
				'%{xid}','%{c}','@fontname'
			),array(
				($v['ts']<=1610612735 ? '15':'18'),_W($v['c'],$W_1,$W_2,true),'"'.htmlspecial3($font).'"',
			)));
		}
	}

	//打印结束标签等
	//  模板<3-5>：sect格式标签是表示结束的，用于歌词本最后一项以及单曲文档
	if($islast) _E($dest,_T('3-5'));
	//  模板<3-6>：sect格式标签表示后面还有内容，用于歌词本非最后一项
	else _E($dest,_T('3-6'));
}

//生成主程序
if($alwaysGen || !file_exists($dest) || filemtime($source) > filemtime($dest) || filemtime(__FILE__) > filemtime($dest)){

	//清空缓存文件，以免重复写入
	file_put_contents($dest,"");

	//打印全文档开始标签
	_E($dest,_T(1,array(
		'%{author}','@fontname',
	),array(
		_CT('app_name'),
		'"'.htmlspecial3($font).'"',
	)));

	//对于单曲：只输出一个单曲内容
	if(!$isList){
		_SINGLE($dest);
	}
	//对于歌词本：那就复杂了
	else {
		//输出封面和CIP数据
		_E($dest,_T('H',array(
			'%{name}',
			'%{subname}',
			'%{author}',
			'%{press}',
			'%{city}',
			'%{isbn}',
			'%{cipname}',
			'%{cipauthor}',
			'%{cipcate}',
			'%{cipgb}',
			'%{year}',
			'%{number}',
			'%{ititle}',
			'%{isubtitle}',
			'%{address}',
			'%{website}',
			'%{version}',
			'%{printpapers}',
			'%{price}',
			'@fontname',
		),array(
			htmlspecial3($_REQUEST['name']),
			htmlspecial3($_REQUEST['subname']),
			htmlspecial3($_REQUEST['author']),
			htmlspecial3($_REQUEST['press']),
			htmlspecial3($_REQUEST['city']),
			htmlspecial3($_REQUEST['isbn']),
			htmlspecial3($_REQUEST['cipname']),
			htmlspecial3($_REQUEST['cipauthor']),
			htmlspecial3($_REQUEST['cipcate']),
			htmlspecial3($_REQUEST['cipgb']),
			htmlspecial3($_REQUEST['year']),
			htmlspecial3($_REQUEST['number']),
			htmlspecial3($_REQUEST['ititle']),
			htmlspecial3($_REQUEST['isubtitle']),
			htmlspecial3($_REQUEST['address']),
			htmlspecial3($_REQUEST['website']),
			htmlspecial3($_REQUEST['version']),
			htmlspecial3($_REQUEST['printpapers']),
			htmlspecial3($_REQUEST['price']),
			'"'.htmlspecial3($font).'"',
		)));

		//打印【包含的歌曲】
		_E($dest,_T('S',array(
			'%{name}',
			'%{author}',
			'@fontname',
		),array(
			htmlspecial3($_REQUEST['name']),
			htmlspecial3($_REQUEST['author']),
			'"'.htmlspecial3($font).'"',
		)));
		foreach($list as $item) {
			$_GET['_lnk']=$item;
			if(_checkPermission('music/getdoc',$item)) { //权限不允许的，自动忽略
				$c=json_decode(parseCmpLyric($item),true);
				_E($dest,_T('M',array(
					'%{G}',
					'%{N}',
					'%{S}',
					'@fontname',
				),array(
					htmlspecial3($canName[$item]),
					htmlspecial3($c['meta']['N']),
					htmlspecial3($c['meta']['S']),
					'"'.htmlspecial3($font).'"',
				)));
			}
		}
		_E($dest,_T('E',array(
			'@fontname',
		),array(
			'"'.htmlspecial3($font).'"',
		)));

		//打印每个单曲文档
		foreach($list as $item) {
			$_GET['_lnk']=$item;
			if(_checkPermission('music/getdoc',$item)) //权限不允许的，自动忽略
				_SINGLE($dest,/*$item===$list[0]*/false,$item===$list[count($list)-1]);
		}
	}

	//打印全文档结束标签
	_E($dest,_T(3));
}
//单曲很小，为了方便字体设置，不缓存，一次性输出（不可断点续传）
if(!$isList) {
	$c=json_decode(parseCmpLyric(preSubstr($_GET['_lnk'])),true);
	header('Content-Disposition: filename='.preSubstr($_GET['_lnk'])." ".$c['meta']['N'].'.doc');
	header('Content-Type: application/x-doc');
	echo str_replace('@fontname','"'.htmlspecial3($font).'"',file_get_contents($dest));
}
//组合文档可能大到24MB，需缓存，模拟Apache输出（可断点续传）
else {
	file_put_out($dest,1,$_REQUEST['name'].'.doc');
}
