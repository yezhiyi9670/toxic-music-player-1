<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 寻找 NBTTagCompound[] 中 $v[$key] == $value 的一个值，并返回其值。
function findKVInArray($s,$key,$value)
{
	if(!is_array($s)) return '';
	foreach($s as $k=>$v)
	{
		if($key=="")
		{
			if($s[$k]==$value) return $s[$k];
		}
		else
		{
			if(isset($s[$k][$key]) && $s[$k][$key]==$value) return $s[$k];
		}
	}
	return NULL;
}


// 转换字符串为浮点数
function strToFloat($num) {
	$dotPos = strrpos($num, '.');
	$commaPos = strrpos($num, ',');
	$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
		((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

	if (!$sep) {
		return floatval(preg_replace("/[^0-9]/", "", $num));
	}

	return floatval(
		preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
		preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
	);
}

// 给出描述时间的文本，返回时间的实际值（单位为 0.1秒）。返回的格式是字符串。
// 如果是未填写的值或者空白值，返回 1610612736 以表示该行为注释行。
function strToCurrtime($str)
{
	if($str=='-' || $str=='__FTIME__' || $str=='NULL') return '1610612736';
	$t2=explode("-",$str);
	if(count($t2)==1){
		$t2[1]=$t2[0];
		$t2[0]="0";
	}
	if(count($t2)!=2) return '1610612736';
	$egg=strToFloat($t2[0])*60.0+strToFloat($t2[1]);
	$egg=floatval(intval($egg*10));
	return strval($egg);
}

// 改变段标的最后一个数字为下标
function fmtParaMark($x) {
	$x = htmlspecial3($x);
	if(is_numeric($x[strlen($x)-1])) {
		return substr($x,0,strlen($x)-1).'<sub>'.$x[strlen($x)-1].'</sub>';
	}
	return $x;
}

// 用空格连接 $arr 的 $s 到 $e 项以形成连续文本（注意不包含 $e）
// 用于还原被拆分的不定项参数。
function concat_arguments($arr,$s=0,$e=-1)
{
	if(!is_array($arr)) return '';
	$egg="";
	if($e==-1) $e=count($arr);
	for($i=$s;$i<$e;$i++)
	{
		if($i!=$s) $egg.=" ";
		$egg.=$arr[$i];
	}
	return $egg;
}

// 将标签替换为HTML格式，以供网页端输出
function replaceHtMark($str) {
	return str_replace([
		'[U]','[/U]',
		'[S]','[/S]',
		'[1]','[/1]',
		'[2]','[/2]',
		'[3]','[/3]',
		'[4]','[/4]',
		'[5]','[/5]',
		'[6]','[/6]',
		'[7]','[/7]',
		'[R]','[/R]'
	],[
		'<span style="text-decoration:underline">','</span>',
		'<span style="text-decoration:line-through">','</span>',
		'<span class="active-color-1">','</span>',
		'<span class="active-color-2">','</span>',
		'<span class="active-color-3">','</span>',
		'<span class="active-color-4">','</span>',
		'<span class="active-color-5">','</span>',
		'<span class="active-color-6">','</span>',
		'<span class="active-color-7">','</span>',
		'<span class="reverse-sound">','</span>'
	],htmlspecial3($str));
}

// 二位十六进制转换数（用于处理颜色代码）
function h2d($f) {
	$hex="0123456789ABCDEF";
	return stripos($hex,$f[0])*16+stripos($hex,$f[1]);
}
function d2h($f) {
	$hex="0123456789ABCDEF";
	return $hex[$f/16].$hex[$f%16];
}

// 将颜色略微调暗
function darkenColorHex($c,$minus=10) {
	$hex="0123456789ABCDEF";
	$r=h2d(substr($c,0,2));
	$g=h2d(substr($c,2,2));
	$b=h2d(substr($c,4,2));
	$r-=$minus;
	$g-=$minus;
	$b-=$minus;
	if($r<0) $r=0; if($g<0) $g=0; if($b<0) $b=0;
	return d2h($r).d2h($g).d2h($b);
}

// 返回主代码文件的源代码
// 会处理remoteplay，并返回爬虫程序生成的源代码。
function getLyricFile($u) {
	if(isKuwoId($u)) {
		global $akCrawler;
		global $akCrawlerInfo;
		remoteEncache($u,'K');
		return $akCrawler[$u]->thefile();
	}
	else if(!isValidMusic($u,false)) {
		return "";
	}
	else return file_get_contents(FILES.$u."/lyric.txt");
}

// 构造一个编译错误的JSON
function errorJson($t,$e,$c) {
	return encode_data(array(
		"error" => [[$t,$e]],
		"meta" => array(
			"N" => LNG('comp.system_error'),
			"S" => '--',
			"LA" => '--',
			"MA" => '--',
			"C" => '--',
			"O" => '',
			"A" => MAIN_COLOR,
			"X" => darkenColorHex(MAIN_COLOR),
			"G1" => GC_COLOR_1,
			"G2" => GC_COLOR_2,
		),
		"lyrics" => array(array(
			"id"=>0,
			"ac"=>"error",
			"n"=>LNG('comp.system_error'),
			"display"=>false,
			"title"=>false,
			"in"=>array(array(
				"id"=>0,
				"ts"=>1610612736,
				"c"=>$c,
			)),
		)),
		"timestamps" => array(),
	));
}

function f_json_encode($data) {
	return json_encode($data,JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE);
}

// 编译器主体代码
// 如果 $parseTags 为 false 将不会把标签转换成HTML代码。
function parseCmpLyric($u,$parseTags = true,$debug = false) {
	if(!$debug) {
		if($GLOBALS['compiler_cache'][$u][intval($parseTags)]) {
			return $GLOBALS['compiler_cache'][$u][intval($parseTags)];
		}
	}

	// FS Cache
	if(_CT('compiled_cache') && !kuwo_classify_id($u) && $parseTags == true && $debug == false) {
		$cache_fn = FILES . $u . '/compiled.json';
		if(file_exists($cache_fn)) {
			if(filemtime($cache_fn) >= filemtime(FILES . $u . '/lyric.txt') && (!file_exists(getPicturePath(FILES . $u . '/avatar')) || filemtime($cache_fn) >= filemtime(getPicturePath(FILES . $u . '/avatar'))) && filemtime($cache_fn) >= filemtime(__FILE__)) {
				return file_get_contents($cache_fn);
			}
		}
	}

	$msg = '';

	// 文件不存在
	if(!isValidMusic($u,false)){
		$ft = LNG('comp.errorw.file_tan90',0);
		$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
		$e = errorJson("Fatal","LyricNotExist",$ft);
		if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
		else return [
			"message" => $msg,
			"source" => null,
			"ld" => null,
			"cd" => null,
			"gd" => null,
			"final" => null
		];
	}

	// 行拆
	$srccode = getLyricFile($u);
	$txt=explode("\n",str_replace("\r\n","\n",$srccode));

	// 主体部分
	try {
		// 删除注释、多余空格，然后拆分为行，行内按空格拆分
		$zlj=array();
		$k=0;
		foreach($txt as $lineid => $v)
		{
			$v=trim($v);
			if($v && (strlen($v)<2 || substr($v,0,2)!="//") && (strlen($v)<2 || substr($v,0,2)!="##") && (strlen($v)<1 || substr($v,0,1)!="!"))
			{
				// 删除行末注释
				if(strstr($v,'##')) {
					$comment_pos = strpos($v, '##');
					if($comment_pos < strlen($v) - 2) { // 防止破坏某些音乐网站中“不标准”的分段记号“//”
						$v = substr($v,0,$comment_pos);
						$v = trim($v); // 再次裁剪头尾，因为移除注释后，注释前可以有大量空格。
					}
				}


				$zlj[$k]=array("__line" => ($lineid + 1));
				if($v[0]=='[')
				{
					if($v[strlen($v)-1]!=']')
					{
						$ft = LNG('comp.errorw.ambig_heading',$v['__line']);
						$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
						$e = errorJson(
							'Fatal','IncompleteHeading',
							$ft
						);
						if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
						else return [
							"message" => $msg,
							"source" => $srccode,
							"ld" => $txt,
							"cd" => $zlj,
							"gd" => null,
							"final" => null
						];
					}
					$v=substr($v,1,strlen($v)-2);
					$zlj[$k]['type']='Para';
					$vi=explode(" ",$v);
					foreach($vi as $k2=>$v2)
					{
						if(!$v2) continue;
						if($k2){
							$zlj[$k]['arg'][]=$v2;
						}
						else
						{
							$allow_heading=array('Info','Para','Hidden','Comment','Reuse','Similar');
							// Info 歌曲信息
							  // N <NAME>
							  // S <SINGER>
							  // C <COLLECTION>
							  // LA <LYRIC_AUTHOR>
							  // MA <MUSIC_AUTHOR>
							// Para [@<ID>] <AC> <UFN> 段落
							  // L <TIME> <THING>
							// Hidden [@<ID>] <AC> <UFN> 不在概览中显示的段落
							  // L <TIME> <THING>
							// Comment 注释段，编译时整个忽略
							  // *
							// Reuse @<USING_ID> <BEGIN_TIME> 重用整段
							// Similar [@<ID>] @<USING_ID> <BEGIN_TIME> <AC> <UFN> 时间差（节奏）与某一段相同的段，但是歌词不同。该段内L命令的时间参数写“-”即可。
							  // L - <THING>
							if(!in_array($v2,$allow_heading)) {
								$ft = LNG('comp.errorw.undef_header',$v['__line'],concat_arguments($allow_heading,0));
								$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
								$e = errorJson(
									'Fatal','WrongHeading',
									$ft
								);
								if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
								else return [
									"message" => $msg,
									"source" => $srccode,
									"ld" => $txt,
									"cd" => $zlj,
									"gd" => null,
									"final" => null
								];
							}
							$zlj[$k]['cmd']=$v2;
							$zlj[$k]['arg']=array();
						}
					}
				}
				else
				{
					$zlj[$k]['type']='Command';
					$vi=explode(" ",$v);
					foreach($vi as $k2=>$v2)
					{
						if(!$v2) continue;
						if($k2) $zlj[$k]['arg'][]=$v2;
						else $zlj[$k]['cmd']=$v2;
					}
				}
				$k++;
			}
		}

		// 按段头分组，并删除 Comment 段。
		$currcmid=-1;
		$iscommented = false;
		$hasInfo = false;
		$am=array();
		foreach($zlj as $k=>$v)
		{
			if(!$k && $v['type']!="Para") {
				$ft = LNG('comp.errorw.not_started',$v['__line']);
				$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
				$e = errorJson(
					'Fatal','IncompleteBeginning',
					$ft
				);
				if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
				else return [
					"message" => $msg,
					"source" => $srccode,
					"ld" => $txt,
					"cd" => $zlj,
					"gd" => $am,
					"final" => null
				];
			}
			if($v['type']=="Para")
			{
				$currcmid++;
				$iscommented = false;
				if($v['cmd'] == 'Info') {
					if($hasInfo) {
						$msg .= '<strong>' . LNG('comp.error.warn') . COLON . LNG('comp.errorw.mult_info',$v['__line']) . "\n";
					}
					$hasInfo = true;
				}
				$am[$currcmid]['type']=$v['cmd'];
				$am[$currcmid]['arg']=$v['arg'];
				$am[$currcmid]['__line']=$v['__line'];
				$am[$currcmid]['cmds']=array();
				if($v['cmd']=='Comment') {$currcmid-=1;$iscommented = true;}
			}
			else if(!$iscommented)
			{
				$v['type']=$v['cmd'];
				unset($v['cmd']);
				$am[$currcmid]['cmds'][]=$v;
			}
		}

		if(!$hasInfo) {
			$msg .= '<strong>' . LNG('comp.error.warn') . COLON . LNG('comp.errorw.no_info',0);
		}

		// 解析出 meta 部分
		$meta=array();
		$xinfo=findKVInArray($am,"type","Info")['cmds'];
		@$meta['meta']['N']=concat_arguments(findKVInArray($xinfo,"type","N")['arg']); // 标题
		@$meta['meta']['S']=concat_arguments(findKVInArray($xinfo,"type","S")['arg']); // 演唱
		if(
			strstr($meta['meta']['S'],'&') ||
			strstr($meta['meta']['S'],'|') ||
			strstr($meta['meta']['S'],',') ||
			strstr($meta['meta']['S'],'、')
		) {
			$msg .= '<strong>' . LNG('comp.error.unstd') . COLON . '</strong>' . LNG('comp.errorw.authorsep',findKVInArray($xinfo,"type","S")['__line']) . "\n";
		}
		@$meta['meta']['LA']=concat_arguments(findKVInArray($xinfo,"type","LA")['arg']); // 作词
		if(
			strstr($meta['meta']['LA'],'&') ||
			strstr($meta['meta']['LA'],'|') ||
			strstr($meta['meta']['LA'],',') ||
			strstr($meta['meta']['LA'],'、')
		) {
			$msg .= '<strong>' . LNG('comp.error.unstd') . COLON . '</strong>' . LNG('comp.errorw.authorsep',findKVInArray($xinfo,"type","LA")['__line']) . "\n";
		}
		@$meta['meta']['MA']=concat_arguments(findKVInArray($xinfo,"type","MA")['arg']); // 作曲
		if(
			strstr($meta['meta']['MA'],'&') ||
			strstr($meta['meta']['MA'],'|') ||
			strstr($meta['meta']['MA'],',') ||
			strstr($meta['meta']['MA'],'、')
		) {
			$msg .= '<strong>' . LNG('comp.error.unstd') . COLON . '</strong>' . LNG('comp.errorw.authorsep',findKVInArray($xinfo,"type","MA")['__line']) . "\n";
		}
		@$meta['meta']['C']=concat_arguments(findKVInArray($xinfo,"type","C")['arg']); // 分类
		@$meta['meta']['A']=concat_arguments(findKVInArray($xinfo,"type","A")['arg']); // 主颜色
		@$meta['meta']['G1']=concat_arguments(findKVInArray($xinfo,"type","G1")['arg']); // 渐变色1
		@$meta['meta']['G2']=concat_arguments(findKVInArray($xinfo,"type","G2")['arg']); // 渐变色2
		@$meta['meta']['O']=concat_arguments(findKVInArray($xinfo,"type","O")['arg']); // 原网页的网址
		@$meta['meta']['P']=concat_arguments(findKVInArray($xinfo,"type","P")['arg']); // 摘要图片网址
		if(!$meta['meta']['P'] || $meta['meta']['P'] == '-') {
			$meta['meta']['P'] = $u . '/avatar';
			if(!file_exists(getPicturePath(FILES . $u . '/avatar'))) {
				$meta['meta']['P'] = '';
			}
		}
		if(!$meta['meta']['N']) {
			$ft = LNG('comp.errorw.no_title',findKVInArray($am,"type","Info")['__line']);
			$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
			$e = errorJson(
				'Fatal','NoSongTitle',
				$ft
			);
			if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
			else return [
				"message" => $msg,
				"source" => $srccode,
				"ld" => $txt,
				"cd" => $zlj,
				"gd" => $am,
				"final" => $meta
			];
		}

		if($meta['meta']['A']==NULL) {
			if($meta['meta']['G1']==NULL) {
				$meta['meta']['A']=MAIN_COLOR;
			} else {
				$meta['meta']['A'] = $meta['meta']['G1'];
			}
		}
		if($meta['meta']['G1']==NULL) $meta['meta']['G1']='NULL';
		if($meta['meta']['G2']==NULL) $meta['meta']['G2']='NULL';

		if($meta['meta']['A'][0]=='#')$meta['meta']['A']=substr($meta['meta']['A'],1);
		if($meta['meta']['G1'][0]=='#')$meta['meta']['G1']=substr($meta['meta']['G1'],1);
		if($meta['meta']['G2'][0]=='#')$meta['meta']['G2']=substr($meta['meta']['G2'],1);
		$meta['meta']['X']=darkenColorHex($meta['meta']['A']);

		// 为段和句标号；按照播放时间指定当前句与段编号；名称、内容类参数空格重组；段标号sub格式处理；歌词内容 Markup 处理；段落重用符号处理(Reuse,Similar)
		$lastTime = -5;
		$meta['lyrics']=array();
		$meta['timestamps']=array();
		$meta['timestamps']['0']=[-1,-1];
		$pid=-1;
		$rid=-1;
		$k=0;
		$reuses=array();
		foreach($am as $v)
		{
			if($v['type']=='Reuse')
			{
				$pid++;
				if($v['arg'][0][0]!='@') {
					$ft = LNG('comp.errorw.id_invalid',$v['__line']);
					$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
					$e = errorJson('Fatal','ReuseIdIllegal',$ft);
					if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
					else return [
						"message" => $msg,
						"source" => $srccode,
						"ld" => $txt,
						"cd" => $zlj,
						"gd" => $am,
						"final" => $meta
					];
				}
				if(!isset($reuses[$v['arg'][0]])) {
					$ft = LNG('comp.errorw.id_tan90',$v['__line']);
					$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
					$e = errorJson('Fatal','ReuseIdNotExist',$ft);
					if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
					else return [
						"message" => $msg,
						"source" => $srccode,
						"ld" => $txt,
						"cd" => $zlj,
						"gd" => $am,
						"final" => $meta
					];
				}
				$reuseitem=$reuses[$v['arg'][0]];
				$timealt=strToCurrtime($v['arg'][1])-$meta['lyrics'][$reuseitem]['in'][0]['ts'];
				$meta['lyrics'][$pid]=$meta['lyrics'][$reuseitem];
				$meta['lyrics'][$pid]['id']=$pid;
				$meta['lyrics'][$pid]['display']=$meta['lyrics'][$reuseitem]['display'];
				$meta['lyrics'][$pid]['title']=$meta['lyrics'][$reuseitem]['title'];
				foreach($meta['lyrics'][$pid]['in'] as $f=>$h)
				{
					$rid++;
					$meta['lyrics'][$pid]['in'][$f]['id']=$rid;
					$meta['lyrics'][$pid]['in'][$f]['ts']+=$timealt;
					$meta['timestamps'][$meta['lyrics'][$pid]['in'][$f]['ts']]=array($pid,$rid);
					if($lastTime == $meta['lyrics'][$pid]['in'][$f]['ts']) {
						$msg .= '<strong>' . LNG('comp.error.warn') . COLON . '</strong>' . LNG('comp.errorw.sametime',$v['__line']) . "\n";
					}
					if($meta['lyrics'][$pid]['in'][$f]['ts'] < 1610612736) $lastTime = $meta['lyrics'][$pid]['in'][$f]['ts'];
				}
			}
			if($v['type']=='Similar') {
				$pid++;
				$meta['lyrics'][$pid]=array();
				$meta['lyrics'][$pid]["id"]=$pid;
				$acstart=0;
				if($v['arg'][0][0]!='@') {
					$ft = LNG('comp.errorw.id_invalid',$v['__line']);
					$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
					$e = errorJson('Fatal','SimilarIdIllegal',$ft);
					if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
					else return [
						"message" => $msg,
						"source" => $srccode,
						"ld" => $txt,
						"cd" => $zlj,
						"gd" => $am,
						"final" => $meta
					];
				}
				if($v['arg'][1][0]=='@')
				{
					$acstart++;
					$reuses[$v['arg'][0]]=$pid;
				}
				if(!isset($reuses[$v['arg'][$acstart]])) {
					$ft = LNG('comp.errorw.id_tan90',$v['__line']);
					$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
					$e = errorJson('Fatal','SimilarIdNotExist',$ft);
					if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
					else return [
						"message" => $msg,
						"source" => $srccode,
						"ld" => $txt,
						"cd" => $zlj,
						"gd" => $am,
						"final" => $meta
					];
				}
				$reuseitem=$reuses[$v['arg'][$acstart]];
				$timealt=strToCurrtime($v['arg'][$acstart+1])-$meta['lyrics'][$reuseitem]['in'][0]['ts'];
				$meta['lyrics'][$pid]["ac"]=fmtParaMark($v['arg'][$acstart+2]);
				$meta['lyrics'][$pid]['n']=concat_arguments($v['arg'],$acstart+3);
				if(!$meta['lyrics'][$pid]["n"]) $meta['lyrics'][$pid]["n"]="";
				$meta['lyrics'][$pid]['display']=$meta['lyrics'][$reuseitem]['display'];
				$meta['lyrics'][$pid]['title']=$meta['lyrics'][$reuseitem]['title'];
				$k=-1;
				$meta['lyrics'][$pid]['in']=array();
				if(count($v['cmds'])>count($meta['lyrics'][$reuseitem]['in'])) {
					$ft = LNG('comp.errorw.similar_exceeded',$v['__line']);
					$msg .= '<strong>' . LNG('comp.error.fatal') . COLON . '</strong>'.$ft."\n";
					$e = errorJson('Fatal','SimilarLarger',$ft);
					if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
					else return [
						"message" => $msg,
						"source" => $srccode,
						"ld" => $txt,
						"cd" => $zlj,
						"gd" => $am,
						"final" => $meta
					];
				}
				foreach($v['cmds'] as $h)
				{
					if($h['type']=='L')
					{
						$rid++;
						$k++;
						$meta['lyrics'][$pid]['in'][$k]['id']=$rid;
						$c=count($h['arg']);
						$meta['lyrics'][$pid]['in'][$k]['ts']=$meta['lyrics'][$reuseitem]['in'][$k]['ts']+$timealt;
						$meta['timestamps'][$meta['lyrics'][$pid]['in'][$k]['ts']]=array($pid,$rid);
						$meta['lyrics'][$pid]['in'][$k]['c']="";
						$str = concat_arguments($h['arg'],1);
						if($lastTime == $meta['lyrics'][$pid]['in'][$k]['ts']) {
							$msg .= '<strong>' . LNG('comp.error.warn') . COLON . '</strong>' . LNG('comp.errorw.sametime',$h['__line']) . "\n";
						}
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < 1610612736) $lastTime = $meta['lyrics'][$pid]['in'][$k]['ts'];
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < 1610612736 && str_included($str,[
							',','，','.','。','"','“','”','‘','’','?','？','!','！','：','；',';','~','`',
							'+','{','}','<','>'
						])) {
							$msg .= '<strong>' . LNG('comp.error.unstd') . '</strong>' . LNG('comp.errorw.punc',$h['__line__']) . "\n";
						}
						if($parseTags) $meta['lyrics'][$pid]['in'][$k]['c']=replaceHtMark(concat_arguments($h['arg'],1));
						else $meta['lyrics'][$pid]['in'][$k]['c']=(concat_arguments($h['arg'],1));
					}
				}
			}
			if($v['type']=='Para' || $v['type']=='Hidden')
			{
				$pid++;
				$meta['lyrics'][$pid]=array();
				$meta['lyrics'][$pid]["id"]=$pid;
				$acstart=1;
				if($v['arg'][0][0]=='@')
				{
					$acstart=2;
					$reuses[$v['arg'][0]]=$pid;
				}
				$meta['lyrics'][$pid]["ac"]=fmtParaMark($v['arg'][$acstart-1]);
				$meta['lyrics'][$pid]['n']=concat_arguments($v['arg'],$acstart);
				if(!$meta['lyrics'][$pid]["n"]) $meta['lyrics'][$pid]["n"]="";
				if($v['type']=="Para") $meta['lyrics'][$pid]['display']=true;
				else $meta['lyrics'][$pid]['display']=false;
				$meta['lyrics'][$pid]['title']=true;
				if($v['arg'][$acstart-1]=='__') {
					$meta['lyrics'][$pid]['title']=false;
					$meta['lyrics'][$pid]['display']=false;
				}
				$k=-1;
				$meta['lyrics'][$pid]['in']=array();
				foreach($v['cmds'] as $h)
				{
					if($h['type']=='L')
					{
						$rid++;
						$k++;
						$meta['lyrics'][$pid]['in'][$k]['id']=$rid;
						$c=count($h['arg']);
						$meta['lyrics'][$pid]['in'][$k]['ts']=strToCurrtime($h['arg'][0]);
						$meta['timestamps'][strToCurrtime($h['arg'][0])]=array($pid,$rid);
						$meta['lyrics'][$pid]['in'][$k]['c']="";
						$str = concat_arguments($h['arg'],1);
						if($lastTime == $meta['lyrics'][$pid]['in'][$k]['ts']) {
							$msg .= '<strong>' . LNG('comp.error.warn') . COLON . '</strong>' . LNG('comp.errorw.sametime',$h['__line']) . "\n";
						}
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < 1610612736) $lastTime = $meta['lyrics'][$pid]['in'][$k]['ts'];
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < 1610612736 && str_included($str,[
							',','，','.','。','"','“','”','‘','’','?','？','!','！','：','；',';','~','`',
							'+','{','}','<','>'
						])) {
							$msg .= '<strong>' . LNG('comp.error.unstd') . COLON . '</strong>' . LNG('comp.errorw.punc',$h['__line']) . "\n";
						}
						if($parseTags) $meta['lyrics'][$pid]['in'][$k]['c']=replaceHtMark(concat_arguments($h['arg'],1));
						else $meta['lyrics'][$pid]['in'][$k]['c']=(concat_arguments($h['arg'],1));
					}
				}
			}
		}

		$meta['lyrics']=array_merge([[
			'id'=> -1,
			'ac'=> '--',
			'n' => 'pre',
			'display'=>false,
			'title'=>false,
			'in'=> [[
				'id'=>-1,
				'ts'=>"0",
				'c' =>'- - - - - - -'
			]]
		]],$meta['lyrics']);

		$msg .= LNG('comp.success') . "\n";

		// FS Cache
		if(_CT('compiled_cache') && !kuwo_classify_id($u) && $parseTags == true && $debug == false) {
			$cache_fn = FILES . $u . '/compiled.json';
			file_put_contents($cache_fn,encode_data($meta,true));
		}

		if(!$debug) return encode_data($meta);
		else {
			return [
				"message" => $msg,
				"source" => $srccode,
				"ld" => $txt,
				"cd" => $zlj,
				"gd" => $am,
				"final" => $meta
			];
		}
	} catch(Exception $exception) {
		$ft = LNG('comp.uke');
		$msg .= '<strong>' . LNG('comp.error.uke') . COLON . '</strong>'.$ft."\n";
		$e = errorJson('Fatal','UnknownError',$ft);
		if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = $e;
		else return [
			"message" => $msg,
			"source" => $srccode,
			"ld" => null,
			"cd" => null,
			"gd" => null,
			"final" => null
		];
	}
}

function prefixToLength($str,$len=3,$prfx=' ') {
	$ret = $str;
	while(strlen($ret) < $len) $ret = $prfx.$ret;
	return $ret;
}

function addLineNumbers($str) {
	$txt=explode("\n",str_replace("\r\n","\n",$str));
	$r = '';

	foreach($txt as $k => $v) {
		$r .= prefixToLength(strval($k+1))."| ";
		$r .= $v;
		$r .= "\n";
	}
	return $r;
}

// 将标签替换标准lrc中的符号
function replaceLrcMark($str) {
	return str_replace([
		'[U]','[/U]',
		'[S]','[/S]',
		'[1]','[/1]',
		'[2]','[/2]',
		'[3]','[/3]',
		'[4]','[/4]',
		'[5]','[/5]',
		'[6]','[/6]',
		'[7]','[/7]',
		'[R]','[/R]'
	],[
		'','',
		'','',
		' (1)','',
		' (2)','',
		' (3)','',
		' (4)','',
		' (5)','',
		' (6)','',
		' (7)','',
		'<','>'
	],$str);
}

function lrcTimestamp($val) {
	$i = strval(floor($val / 600));
	$s = strval(floor($val % 600 / 10));
	while(strlen($i) < 2) $i = '0' . $i;
	while(strlen($s) < 2) $s = '0' . $s;
	$g = strval($val % 10);
	return $i . ':' . $s . '.' . $g . '00';
}

function fmtDataForLrc($data) {
	$timelist = [];
	foreach($data['lyrics'] as $i=>&$para) {
		if($para['id'] == -1) {
			$para['in'][0]['ts'] = "-1";
			continue;
		}
		foreach($para['in'] as $j=>&$item) {
			if($para['ac'] == '--') {
				$item['c'] = '(Music)';
			}
			$timelist[] = [intval($item['ts']),$i,$j];
			if($item['ts'] == 0) {
				$item['ts'] = 5;
			}
		}
	}
	$j = 0;
	$prevtime = 0;
	for($i=0;$i<count($timelist);) {
		if($timelist[$i][0] < 1610612736) {
			$prevtime = $timelist[$i][0];
			$i++;
			continue;
		}
		$nexttime = -1;
		$j = $i;
		while($j < count($timelist) && $timelist[$j][0] >= 1610612736) {
			$j++;
		}
		if($j < count($timelist)) {
			$nexttime = $timelist[$j][0];
		}
		$sz = ($j - $i + 1);
		for($k=$i;$k<$j;$k++) {
			$idx = $k - $i + 1;
			$ridx = $j - $k;
			$ts = -1;
			if($nexttime == -1) {
				$ts = $prevtime + $idx * 10;
			} else if($sz * 7 > $nexttime - $prevtime) {
				$ts = floatval($nexttime - $prevtime) / $sz * $idx + $prevtime;
			} else {
				$ts = $nexttime - $ridx * 7;
			}
			$data['lyrics'][$timelist[$k][1]]['in'][$timelist[$k][2]]['ts'] = strval($ts);
		}
		$i = $j;
	}
	return $data;
}

function buildMinifiedLrc($data) {
	$data = fmtDataForLrc($data);
	$contmap = [];

	$ret = '';

	$ret .= '[ti:' . $data['meta']['N'] . ']' . "\n";
	$ret .= '[ar:' . $data['meta']['S'] . ']' . "\n";
	$ret .= '[al:' . $data['meta']['C'] . ']' . "\n";
	$ret .= '[by:' . _CT('app_name') . ']' . "\n";
	$ret .= '[offset:0]' . "\n";
	$ret .= '[00:00.000]' . $data['meta']['N'] . ' - ' . $data['meta']['S'] . "\n";

	foreach($data['lyrics'] as $para) {
		foreach($para['in'] as $item) {
			if($item['ts'] > 0) {
				$cont = trim(replaceLrcMark($item['c']));
				if(!isset($contmap[$cont])) {
					$contmap[$cont] = [];
				}
				$contmap[$cont][] = intval($item['ts']);
			}
		}
	}

	foreach($contmap as $cont => $tsl) {
		foreach($tsl as $ts) {
			$ret .= '[' . lrcTimestamp($ts) . ']';
		}
		$ret .= $cont . "\n";
	}

	return $ret;
}

function buildExtendedLrc($data) {
	$data = fmtDataForLrc($data);

	$ret = '';

	$ret .= '[ti:' . $data['meta']['N'] . ']' . "\n";
	$ret .= '[ar:' . $data['meta']['S'] . ']' . "\n";
	$ret .= '[txmp_la:' . $data['meta']['LA'] . ']' . "\n";
	$ret .= '[txmp_ma:' . $data['meta']['MA'] . ']' . "\n";
	$ret .= '[al:' . $data['meta']['C'] . ']' . "\n";
	$ret .= '[by:' . _CT('app_name') . ']' . "\n";
	$ret .= '[offset:0]' . "\n";
	$ret .= '[00:00.000]' . $data['meta']['N'] . ' - ' . $data['meta']['S'] . "\n";

	foreach($data['lyrics'] as $para) {
		if($para['id'] == -1) continue;
		$ret .= '[txmp_para:' . $para['ac'] . ' ' . $para['n'] . ']' . "\n";
		foreach($para['in'] as $item) {
			if($item['ts'] > 0) {
				$cont = trim(replaceLrcMark($item['c']));
				$ret .= '[' . lrcTimestamp($item['ts']) . ']' . $cont . "\n";
			}
		}
	}

	return $ret;
}

// 解析歌单的CSV存储格式
function plcsv_decode($str) {
	$str = str_replace(["\r\n","\r"],["\n","\n"],$str);
	$lines = explode("\n",$str);

	$ver = trim($lines[0]);
	if(strval(intval($ver)) != $ver) {
		return null;
	}

	$meta_t = $lines[1];
	$meta_t = explode(',',trim($meta_t));

	if(count($meta_t) != 2) {
		return null;
	}

	$ret = [
		'public' => (trim($meta_t[0]) == 'T'),
		'title' => base64_decode(trim($meta_t[1])),
		'playlist' => [],
		'transform' => [
			'pick' => '',
			'random_shuffle' => false,
			'constraints' => [
				'comparator' => '',
				'multiplier' => '',
				'delta' => ''
			],
			'constraints2' => [
				'comparator' => '',
				'multiplier' => '',
				'delta' => ''
			],
			'termination' => ''
		],
	];

	$isTerminated = -1;
	$item_cnt = 0;
	for($i=2;$i<count($lines);$i++) {
		if(trim($lines[$i]) == '-') {
			$isTerminated = $i;
			break;
		}
		$item_t = explode(',',trim($lines[$i]));
		if(count($item_t) != 3) {
			return null;
		}
		$ret['playlist'][$item_cnt] = [
			'id' => trim($item_t[0]),
			'rating' => intval(trim($item_t[1])),
			'canonical' => trim($item_t[2]),
		];
		$item_cnt++;
	}

	if($isTerminated == -1) {
		return null;
	}

	if(count($lines) <= $isTerminated+3) {
		return null;
	}

	$tr_t = explode(',',trim($lines[$isTerminated+1]));
	$c1_t = explode(',',trim($lines[$isTerminated+2]));
	$c2_t = explode(',',trim($lines[$isTerminated+3]));

	if(count($tr_t) != 3) {
		return null;
	}
	if(trim($tr_t[0]) == 'R') {
		$ret['transform']['pick'] = 'rand';
	} else if(trim($tr_t[0]) == 'N') {
		$ret['transform']['pick'] = 'next';
	} else {
		return null;
	}
	if(trim($tr_t[1]) == 'T') {
		$ret['transform']['random_shuffle'] = true;
	} else if(trim($tr_t[1]) == 'F') {
		$ret['transform']['random_shuffle'] = false;
	} else {
		return null;
	}
	if(trim($tr_t[2]) == 'F') {
		$ret['transform']['termination'] = 'end';
	} else if(trim($tr_t[2]) == 'L') {
		$ret['transform']['termination'] = 'loop';
	} else if(trim($tr_t[2]) == 'A') {
		$ret['transform']['termination'] = 'all';
	} else {
		return null;
	}

	$ret['transform']['constraints']['comparator'] = trim($c1_t[0]);
	$ret['transform']['constraints']['multiplier'] = trim($c1_t[1]);
	$ret['transform']['constraints']['delta'] = trim($c1_t[2]);
	$ret['transform']['constraints2']['comparator'] = trim($c2_t[0]);
	$ret['transform']['constraints2']['multiplier'] = trim($c2_t[1]);
	$ret['transform']['constraints2']['delta'] = trim($c2_t[2]);

	for($i = $isTerminated + 4; $i < count($lines); $i++) {
		if(trim($lines[$i]) != '') {
			return null;
		}
	}

	return $ret;
}

function hasPlaylist($user,$id) {
	return uauth_has_data($user,'playlist',$id.'.json') || uauth_has_data($user,'playlist',$id.'.csv');
}

function readPlaylistData($user,$id,$raw = false) {
	if($user == '__syscall') {
		global $__syscall;
		return $__syscall -> fetchData('playlist',$id . '.json');
	}

	$type = '';
	$fid = '';
	if(file_exists(USER_DATA.$user.'/playlist/'.$id.'.json')) {
		$type = 'json';
		$fid = USER_DATA.$user.'/playlist/'.$id;
	} else if(file_exists(USER_DATA.$user.'/playlist/'.$id.'.csv')) {
		$type = 'csv';
		$fid = USER_DATA.$user.'/playlist/'.$id.'-csv';
	}
	if($type == '') {
		return null;
	}
	wait_file($fid);
	$fkd = file_get_contents(USER_DATA.$user.'/playlist/'.$id.'.'.$type);
	if($raw) return $fkd;

	if($type == 'json') {
		return json_decode($fkd,true);
	} else {
		return plcsv_decode($fkd);
	}
}
