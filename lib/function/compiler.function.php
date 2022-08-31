<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

define('TS_IS_COMMENT', 1610612736);

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
// 如果是未填写的值或者空白值，返回 TS_IS_COMMENT 以表示该行为注释行。
function strToCurrtime($str,$delta = 0)
{
	if($str=='-' || $str=='__FTIME__' || $str=='__LT__' || $str=='NULL') return TS_IS_COMMENT - $delta;
	$t2=explode("-",$str);
	if(count($t2)==1){
		$t2[1]=$t2[0];
		$t2[0]="0";
	}
	if(count($t2)!=2) return TS_IS_COMMENT - $delta;
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
	$int = null;
	if(isset($arr['arg'])) {
		if(isset($arr['interval'])) {
			$int = $arr['interval'];
		}
		$arr = $arr['arg'];
	}
	if(!is_array($arr)) return '';
	$egg="";
	if($e==-1) $e=count($arr);
	for($i=$s;$i<$e;$i++)
	{
		if($i!=$s) {
			$cnt = (null != $int ? $int[$i] : 1);
			for($j = 0; $j < $cnt; $j++) {
				$egg .= ' ';
			}
		}
		$egg.=$arr[$i];
	}
	$egg = str_replace('  ',FULL_SPACE,$egg);
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
function errorJson() {
	return encode_data(array(
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
			"type"=>"lyrics",
			"n"=>LNG('comp.system_error'),
			"display"=>false,
			"title"=>false,
			"in"=>array(array(
				"id"=>0,
				"ts"=>TS_IS_COMMENT,
				"c"=>LNG('ui.error'),
			)),
		)),
		"timestamps" => array(),
	));
}

function f_json_encode($data) {
	return json_encode($data,JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE);
}

// 追加错误信息
function cmpi_ADD_ERROR(&$info,$level,$id,$line,...$args) {
	$flag = true;
	if($level[0] == '!') {
		$level = substr($level,1);
		$flag = false;
	}
	$info .= '<strong>' . LNG('comp.error.' . $level) . '</strong>' . COLON;
	$info .= LNG('comp.errorw.' . $id, $line, ...$args);
	if($flag) $info .= "\n";
}
function cmpi_ADD_ERROR_P(&$info,$level,$id,$line,...$args) {
	// cmpi_ADD_ERROR($info,$level,$id,$line,...$args);
	if(!is_array($info)) {
		$info = [];
	}
	$info[] = [
		'level' => $level,
		'id' => $id,
		'line' => $line,
		'args' => $args
	];
}

// 追加成功信息
function cmpi_ADD_SUCCESS(&$info) {
	if(!is_array($info)) $info .= LNG('comp.success') . "\n";
}

// 错误信息预检
function cmpi_level_exists($level) {
	return defaultKeyExists('comp.error.' . $level);
}
function cmpi_id_exists($id) {
	return defaultKeyExists('comp.errorw.' . $id);
}
function cmpi_invoke($ADD_ERROR, $excluded, &$info, $level, $id, $line, ...$args) {
	if(!cmpi_level_exists($level)) {
		$ADD_ERROR($info, 'error', 'invoke_type_tan90', $line, $level);
		return false;
	}
	if(!cmpi_id_exists($id)) {
		$ADD_ERROR($info, 'error', 'invoke_id_tan90', $line, $id);
		return false;
	}
	if(in_array($id, $excluded)) {
		return false;
	}
	$ADD_ERROR($info, $level, $id, $line, ...$args);
}

// 参数拆分
function cmp_split_args($line, $lineid, $is_heading, &$allow_heading, $ADD_ERROR, $supressed, &$err_info) {
	$strmap1 = [];
	$strmap2 = [];
	$str_sequence = [];
	$in_str = -1;
	for($i = 0; $i < strlen($line) - 1; $i++) {
		if($in_str == -1 && substr($line, $i, 2) == '<[') {
			$in_str = $i;
			$i += 2 - 1;
		}
		if($in_str != -1 && substr($line, $i, 2) == ']>') {
			$str_sequence[] = [$in_str, $i + 2];
			$in_str = -1;
			$i += 2 - 1;
		}
	}
	if($in_str != -1) {
		$str_sequence[] = [$in_str, strlen($line) + 2];
		$line .= ']>';
		cmpi_invoke($ADD_ERROR, $supressed, $err_info, 'error', 'unclosed_string', $lineid + 1);
	}
	for($i = count($str_sequence) - 1; $i >= 0; $i--) {
		$rid = 'CDATASTR[' . randString(10, '0123456789abcdef') . '|' . strval(count($strmap1)) . ']';
		$strmap1[] = $rid;
		$strmap2[] = substr($line, $str_sequence[$i][0] + 2, $str_sequence[$i][1] - $str_sequence[$i][0] - 4);
		$line = substr($line, 0, $str_sequence[$i][0]) . $rid . substr($line, $str_sequence[$i][1]);
	}
	
	$line = trim($line);
	$vi = explode(' ', $line);
	$space_count = 0;
	$ret = [];
	foreach($vi as $k2=>$v2) {
		// 删除空参数
		if(!$v2) {
			$space_count++;
			continue;
		}
		$v2 = str_replace($strmap1, $strmap2, $v2);
		if($k2) {
			$ret['arg'][] = $v2;
			$ret['interval'][] = $space_count;
			$space_count = 1;
		} else {
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
			if($is_heading && !in_array($v2,$allow_heading)) {
				cmpi_invoke($ADD_ERROR, $supressed, $err_info,'warn','undef_header',$lineid+1,concat_arguments($allow_heading,0));
			}
			$ret['cmd']=$v2;
			$ret['arg']=array();
			$ret['interval']=array();
			$space_count = 1;
		}
	}
	return $ret;
}

// 编译器主体代码
// 如果 $parseTags 为 false 将不会把标签转换成HTML代码。
function parseCmpLyric($u,$parseTags = true, $debug = false,$ADD_ERROR='cmpi_ADD_ERROR') {
	if(!$debug) {
		if($GLOBALS['compiler_cache'][$u][intval($parseTags)]) {
			return $GLOBALS['compiler_cache'][$u][intval($parseTags)];
		}
	}

	$punc_list = [
		',','，','。','"','“','”','‘','’','?','？','!','！','；',';','`',
		'+','{','}','<','>'
	];
	$allow_heading = array('Info','Para','Hidden','Comment','Reuse','Similar','Split','Final');

	// 文件系统内缓存
	if(_CT('compiled_cache') && !kuwo_classify_id($u) && $parseTags == true && $debug == false) {
		$cache_fn = FILES . $u . '/compiled.json';
		if(file_exists($cache_fn)) {
			if(filemtime($cache_fn) >= filemtime(FILES . $u . '/lyric.txt') && (!file_exists(getPicturePath(FILES . $u . '/avatar')) || filemtime($cache_fn) >= filemtime(getPicturePath(FILES . $u . '/avatar'))) && filemtime($cache_fn) >= filemtime(__FILE__)) {
				return file_get_contents($cache_fn);
			}
		}
	}

	$msg = '';
	$supressed = [];

	// 文件不存在
	if(!isValidMusic($u,false)){
		cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','file_tan90',0);
		$e = errorJson();
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
	$txt = explode("\n",str_replace("\r\n","\n",$srccode));

	// 识别版本号
	$ver_num = '';
	$tmp = $txt[0];
	if(substr($tmp,0,8) == '!dataver') {
		$tmp = substr($tmp,8);
		$ch = $tmp[0];
		if(strlen($tmp) != 0 && trim($ch) != $ch) {
			$tmp = trim(substr($tmp,1));
			$ver_num = $tmp;
		}
	}
	if($ver_num == '') {
		cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','no_ver',0);
	} else if(!preg_match('/^(\d+)$/',$ver_num)) {
		cmpi_invoke($ADD_ERROR, $supressed, $msg,'error','illegal_ver',1);
	} else if(intval($ver_num) < intval(DATAVER)) {
		cmpi_invoke($ADD_ERROR, $supressed, $msg,'notice','low_ver',1,$ver_num,DATAVER);
	} else if(intval($ver_num) > intval(DATAVER)) {
		cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','high_ver',1,$ver_num,DATAVER);
	}

	// 主体部分
	try {
		// 删除注释、多余空格，然后拆分为行，行内按空格拆分；错误触发与删除
		$zlj=array();
		$k=0;
		foreach($txt as $lineid => $v)
		{
			$v=trim($v);
			if($v && (strlen($v)<2 || substr($v,0,2)!="//") && (strlen($v)<2 || substr($v,0,2)!="##") && (strlen($v)<1 || substr($v,0,1)!="!")) {
				// 删除行末注释
				if(strstr($v,'##')) {
					$comment_pos = strpos($v, '##');
					if($comment_pos < strlen($v) - 2) { // 防止破坏某些音乐网站中“不标准”的分段记号“//”
						$v = substr($v,0,$comment_pos);
						$v = trim($v); // 再次裁剪头尾，因为移除注释后，注释前可以有大量空格。
					}
				}


				$zlj[$k]=array("__line" => ($lineid + 1));
				if($v[0]=='[') {
					if($v[strlen($v)-1]!=']') {
						cmpi_invoke($ADD_ERROR, $supressed, $msg,'error','ambig_heading',$lineid + 1);
					} else {
						$v = trim(substr($v,1,strlen($v)-2));
						if(!$v) $v = 'Null';
						$zlj[$k] = cmp_split_args($v, $lineid, true, $allow_heading, $ADD_ERROR, $supressed, $msg);
						$zlj[$k]['type'] = 'Para';
					}
				}
				else {
					$zlj[$k] = cmp_split_args($v, $lineid, false, $allow_heading, $ADD_ERROR, $supressed, $msg);
					$zlj[$k]['type']='Command';
					// 错误触发
					if($zlj[$k]['cmd'] == '~supress') {
						if(!isset($zlj[$k]['arg'][0])) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg, 'warn', 'invalid_argument', $lineid + 1);
						} else {
							$supress_id = $zlj[$k]['arg'][0];
							if(cmpi_id_exists($supress_id)) {
								$supressed[] = $supress_id;
							}
						}
					}
					if($zlj[$k]['cmd'] == '~invoke') {
						if(!isset($zlj[$k]['arg'][1])) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg, 'warn', 'invalid_argument', $lineid + 1);
						}
						$invoke_level = $zlj[$k]['arg'][0];
						$invoke_id = $zlj[$k]['arg'][1];
						$args = array_slice($zlj[$k]['arg'], 2);
						cmpi_invoke($ADD_ERROR, [], $msg, $invoke_level, $invoke_id, $lineid + 1, ...$args);
						if($invoke_level == 'fatal') {
							if(!$debug) return $GLOBALS['compiler_cache'][$u][intval($parseTags)] = errorJson();
							else return [
								"message" => $msg,
								"source" => $srccode,
								"ld" => $txt,
								"cd" => $zlj,
								"gd" => null,
								"final" => null
							];
						}
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
				cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','not_started',$v['__line']);
				$e = errorJson();
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
						cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','mult_info',$v['__line']);
					}
					$hasInfo = true;
				}
				$am[$currcmid]['type']=$v['cmd'];
				$am[$currcmid]['arg']=$v['arg'];
				$am[$currcmid]['interval']=$v['interval'];
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
			cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','no_info',0);
		}

		// 解析出 meta 部分
		$meta=array();
		$xinfo=findKVInArray($am,"type","Info")['cmds'];
		@$meta['meta']['N']=concat_arguments(findKVInArray($xinfo,"type","N")); // 标题
		@$meta['meta']['S']=concat_arguments(findKVInArray($xinfo,"type","S")); // 演唱
		if(
			strstr($meta['meta']['S'],'&') ||
			strstr($meta['meta']['S'],'|') ||
			strstr($meta['meta']['S'],',') ||
			strstr($meta['meta']['S'],'、')
		) {
			cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','authorsep',findKVInArray($xinfo,"type","S")['__line']);
		}
		@$meta['meta']['LA']=concat_arguments(findKVInArray($xinfo,"type","LA")); // 作词
		if(
			strstr($meta['meta']['LA'],'&') ||
			strstr($meta['meta']['LA'],'|') ||
			strstr($meta['meta']['LA'],',') ||
			strstr($meta['meta']['LA'],'、')
		) {
			cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','authorsep',findKVInArray($xinfo,"type","LA")['__line']);
		}
		@$meta['meta']['MA']=concat_arguments(findKVInArray($xinfo,"type","MA")); // 作曲
		if(
			strstr($meta['meta']['MA'],'&') ||
			strstr($meta['meta']['MA'],'|') ||
			strstr($meta['meta']['MA'],',') ||
			strstr($meta['meta']['MA'],'、')
		) {
			cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','authorsep',findKVInArray($xinfo,"type","MA")['__line']);
		}
		@$meta['meta']['C']=concat_arguments(findKVInArray($xinfo,"type","C")); // 分类
		@$meta['meta']['A']=concat_arguments(findKVInArray($xinfo,"type","A")); // 主颜色
		@$meta['meta']['G1']=concat_arguments(findKVInArray($xinfo,"type","G1")['arg']); // 渐变色1
		@$meta['meta']['G2']=concat_arguments(findKVInArray($xinfo,"type","G2")['arg']); // 渐变色2
		@$meta['meta']['O']=concat_arguments(findKVInArray($xinfo,"type","O")); // 原网页的网址
		@$meta['meta']['P']=concat_arguments(findKVInArray($xinfo,"type","P")); // 摘要图片网址
		@$time_delta=floor(floatval(concat_arguments(findKVInArray($xinfo,"type","D"))) * 10); // 偏移量
		if($time_delta != $time_delta) {
			$time_delta = 0;
		}
		if(!$meta['meta']['P'] || $meta['meta']['P'] == '-') {
			$meta['meta']['P'] = $u . '/avatar';
			if(!file_exists(getPicturePath(FILES . $u . '/avatar'))) {
				$meta['meta']['P'] = '';
			}
		}
		if(!$meta['meta']['N']) {
			cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','no_title',findKVInArray($am,"type","Info")['__line']);
			$e = errorJson();
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
		$maxTime = -5;
		$finalized = false;
		$meta['lyrics']=array();
		$meta['timestamps']=array();
		$meta['timestamps']['0']=[-1,-1];
		$pid=-1;
		$rid=-1;
		$k=0;
		$reuses=array();
		foreach($am as $v)
		{
			if(in_array($v['type'],['Reuse','Similar','Para','Hidden'])) {
				if($finalized) {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','fake_final',$v['__line']);
				}
			}
			// 全复用
			if($v['type']=='Reuse')
			{
				$pid++;
				if($v['arg'][0][0]!='@') {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','id_invalid',$v['__line']);
					$e = errorJson();
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
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','id_tan90',$v['__line']);
					$e = errorJson();
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
				if($v['arg'][1] == '__FTIME__' || $v['arg'][1] == '__LT__') {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','ftime',$v['__line']);
				}
				$timealt=strToCurrtime($v['arg'][1],$time_delta)+$time_delta-$meta['lyrics'][$reuseitem]['in'][0]['ts'];
				// 此处为复制
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
					if($lastTime == $meta['lyrics'][$pid]['in'][$f]['ts'] && $lastTime < TS_IS_COMMENT) {
						cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','sametime',$v['__line']);
					}
					if($meta['lyrics'][$pid]['in'][$f]['ts'] < TS_IS_COMMENT) {
						$lastTime = $meta['lyrics'][$pid]['in'][$f]['ts'];
						$maxTime = max($maxTime, $meta['lyrics'][$pid]['in'][$f]['ts']);
					}
				}
			}
			// 时长复用
			if($v['type']=='Similar') {
				$pid++;
				$meta['lyrics'][$pid]=array();
				$meta['lyrics'][$pid]["id"]=$pid;
				$acstart=0;
				if($v['arg'][0][0]!='@') {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','id_invalid',$v['__line']);
					$e = errorJson();
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
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','id_tan90',$v['__line']);
					$e = errorJson();
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
				if($v['arg'][$acstart+1] == '__FTIME__' || $v['arg'][$acstart+1] == '__LT__') {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','ftime',$v['__line']);
				}
				$timealt=strToCurrtime($v['arg'][$acstart+1],$time_delta)+$time_delta-$meta['lyrics'][$reuseitem]['in'][0]['ts'];
				$meta['lyrics'][$pid]["ac"]=fmtParaMark($v['arg'][$acstart+2]);
				$meta['lyrics'][$pid]['n']=concat_arguments($v,$acstart+3);
				// 标题全角空格禁止
				if(str_included($meta['lyrics'][$pid]['n'],[FULL_SPACE])) {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','fullspace_h',$v['__line']);
				}
				if(!$meta['lyrics'][$pid]["n"]) $meta['lyrics'][$pid]["n"]="";
				$meta['lyrics'][$pid]['display']=$meta['lyrics'][$reuseitem]['display'];
				$meta['lyrics'][$pid]['title']=$meta['lyrics'][$reuseitem]['title'];
				$meta['lyrics'][$pid]["type"] = 'lyrics';
				$k=-1;
				$meta['lyrics'][$pid]['in']=array();
				if(count($v['cmds'])>count($meta['lyrics'][$reuseitem]['in'])) {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','similar_exceeded',$v['__line']);
					$e = errorJson();
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
						if($h['arg'][0] == '__FTIME__' || $h['arg'][0] ==  '__LT__') {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','ftime',$h['__line']);
						}
						$meta['timestamps'][$meta['lyrics'][$pid]['in'][$k]['ts']]=array($pid,$rid);
						$meta['lyrics'][$pid]['in'][$k]['c']="";
						if($lastTime == $meta['lyrics'][$pid]['in'][$k]['ts'] && $lastTime < TS_IS_COMMENT) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','sametime',$h['__line']);
						}
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < TS_IS_COMMENT) {
							$lastTime = $meta['lyrics'][$pid]['in'][$k]['ts'];
							$maxTime = max($maxTime, $meta['lyrics'][$pid]['in'][$k]['ts']);
						}
						$str = concat_arguments($h['arg'],1);
						// 禁止角色标示
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < TS_IS_COMMENT) {
							$is_role = false;
							$role = '';
							if(mb_substr($str,mb_strlen($str)-1) == '：') {
								$role = trim(mb_substr($str,0,mb_strlen($str)-1));
								$is_role = true;
							} else if(mb_substr($str,mb_strlen($str)-1) == '】' && mb_substr($str,0,1) == '【') {
								$role = trim(mb_substr($str,1,mb_strlen($str)-2));
								$is_role = true;
							}
							if($is_role && ($role == '合' || strpos($meta['meta']['S'],$role) !== false)) {
								cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','role',$h['__line']);
							}
						}
						// 全角空格禁止
						if(str_included($str,[FULL_SPACE])) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','fullspace',$h['__line']);
						}
						// 不规范间奏标记
						if(onlyConsistsOf($str,['-'])) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','wronginterval',$h['__line']);
						}
						$ret_str = concat_arguments($h,1);
						if($parseTags) $meta['lyrics'][$pid]['in'][$k]['c']=replaceHtMark($ret_str);
						else $meta['lyrics'][$pid]['in'][$k]['c']=($ret_str);
					}
				}
			}
			// 常规用法
			if($v['type']=='Para' || $v['type']=='Hidden')
			{
				$pid++;
				$meta['lyrics'][$pid]=array();
				$meta['lyrics'][$pid]["id"]=$pid;
				$meta['lyrics'][$pid]['type'] = 'lyrics';
				$acstart=1;
				if($v['arg'][0][0]=='@')
				{
					$acstart=2;
					$reuses[$v['arg'][0]]=$pid;
				}
				$meta['lyrics'][$pid]["ac"]=fmtParaMark($v['arg'][$acstart-1]);
				$meta['lyrics'][$pid]['n']=concat_arguments($v,$acstart);
				if(!$meta['lyrics'][$pid]["n"]) $meta['lyrics'][$pid]["n"]="";
				// 标题全角空格禁止
				if(str_included($meta['lyrics'][$pid]['n'],[FULL_SPACE])) {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','fullspace_h',$v['__line']);
				}
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
						if($h['arg'][0] == '__FTIME__' || $h['arg'][0] == '__LT__') {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','ftime',$h['__line']);
						}
						$meta['lyrics'][$pid]['in'][$k]['ts']=strToCurrtime($h['arg'][0],$time_delta) + $time_delta;
						$meta['timestamps'][strToCurrtime($h['arg'][0],$time_delta)+$time_delta]=array($pid,$rid);
						$meta['lyrics'][$pid]['in'][$k]['c']="";
						if($lastTime == $meta['lyrics'][$pid]['in'][$k]['ts'] && $lastTime < TS_IS_COMMENT) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','sametime',$h['__line']);
						}
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < TS_IS_COMMENT) {
							$lastTime = $meta['lyrics'][$pid]['in'][$k]['ts'];
							$maxTime = max($maxTime, $meta['lyrics'][$pid]['in'][$k]['ts']);
						}
						$str = concat_arguments($h['arg'],1);
						// 禁止角色标示
						if($meta['lyrics'][$pid]['in'][$k]['ts'] < TS_IS_COMMENT) {
							$is_role = false;
							$role = '';
							if(mb_substr($str,mb_strlen($str)-1) == '：') {
								$role = trim(mb_substr($str,0,mb_strlen($str)-1));
								$is_role = true;
							} else if(mb_substr($str,mb_strlen($str)-1) == '】' && mb_substr($str,0,1) == '【') {
								$role = trim(mb_substr($str,1,mb_strlen($str)-2));
								$is_role = true;
							}
							if($is_role && ($role == '合' || strpos($meta['meta']['S'],$role) !== false)) {
								cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','role',$h['__line']);
							}
						}
						// 全角空格禁止
						if(str_included($str,[FULL_SPACE])) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','fullspace',$h['__line']);
						}
						// 不规范间奏标记
						if(onlyConsistsOf($str,['-'])) {
							cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','wronginterval',$h['__line']);
						}
						$ret_str = concat_arguments($h,1);
						if($parseTags) $meta['lyrics'][$pid]['in'][$k]['c']=replaceHtMark($ret_str);
						else $meta['lyrics'][$pid]['in'][$k]['c']=($ret_str);
					}
				}
			}
			// 分片符
			if($v['type'] == 'Split') {
				$pid++;
				$meta['lyrics'][$pid] = array();
				$meta['lyrics'][$pid]['id'] = $pid;
				$meta['lyrics'][$pid]['type'] = 'split';
				$meta['lyrics'][$pid]['display'] = true;
			}
			// 终结标志
			if($v['type'] == 'Final') {
				if($finalized) {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'error','dup_final',$v['__line']);
				}
				$finalized = true;

				$ts = strToCurrtime($v['arg'][0],$time_delta) + $time_delta;
				if($ts <= $maxTime) {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'warn','early_final',$v['__line']);
				}
				$pid++;
				if($v['arg'][0] == '__FTIME__' || $v['arg'][0] == '__LT__') {
					cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','ftime',$v['__line']);
				}
				$meta['lyrics'][$pid] = array();
				$meta['lyrics'][$pid]['id'] = $pid;
				$meta['lyrics'][$pid]['type'] = 'final';
				$meta['lyrics'][$pid]['ts'] = $ts;
				$meta['lyrics'][$pid]['display'] = false;

				$meta['timestamps'][$ts]=array(-2,-2);
			}
		}

		if(!$finalized) {
			cmpi_invoke($ADD_ERROR, $supressed, $msg,'unstd','no_final',count($txt));
		}

		$meta['lyrics']=array_merge([[
			'id'=> -1,
			'type'=>'lyrics',
			'ac'=> '--',
			'n' => 'pre',
			'premark' => true,
			'display'=>true,
			'title'=>false,
			'in'=> [[
				'id'=>-1,
				'ts'=>"0",
				'c' =>'- - - - - - -'
			]]
		]],$meta['lyrics']);
		
		if($ADD_ERROR == 'cmpi_ADD_ERROR_P' && !is_array($msg)) {
			$msg = [];
		}
		cmpi_ADD_SUCCESS($msg);

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
		cmpi_invoke($ADD_ERROR, $supressed, $msg,'fatal','uke',0);
		$e = errorJson();
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

/**
 * 计数
 */
function countCompileIssue($msglist, $unaccept = []) {
	$ret = 0;
	foreach($msglist as $e) {
		if(in_array($e['level'],$unaccept)) continue;
		$ret++;
	}
	return $ret;
}
/**
 * 获取格式化后的编译信息（含原内容的）
 * * 可以用于非编译场合，如代码标记
 */
function getCompileIssueMsg($fn, $cont, $msglist, $window = 1, $unaccept = []) {
	$cont = explode("\n",str_replace("\r\n","\n",$cont));
	$nline = count($cont);

	// 将错误信息按行编排
	$lined = [];
	foreach($msglist as $e) {
		if(in_array($e['level'],$unaccept)) continue;
		if(!isset($lined[$e['line']])) {
			$lined[$e['line']] = [];
		}
		$lined[$e['line']][] = $e;
	}

	$partition = [];
	// 行号为 0 的另当别论
	$part_id = 0;
	if(isset($lined[0])) {
		$partition = [[0,0]];
		$part_id = 1;
	}
	$in = false;
	foreach($cont as $_lineid => $linecont) {
		$lineid = $_lineid + 1;
		$should_display = false;

		// 遍历窗口内元素
		for($i = max($lineid - $window,1); $i <= min($lineid + $window, $nline); $i++) {
			// 需要展示
			if(isset($lined[$i])) {
				$should_display = true;
				break;
			}
		}

		if($in == false) {
			// 开启新块
			if($should_display) {
				$partition[$part_id] = [$lineid, $lineid];
				$in = true;
			}
		} else {
			// 扩充块
			if($should_display) {
				$partition[$part_id][1] = $lineid;
			}
			// 离开块
			else {
				$part_id++;
				$in = false;
			}
		}
	}

	// 按块输出
	$msg = '';
	foreach($partition as $rng) {
		// 标题
		$msg .= '<strong class="text-highlight"> ' . $fn . ' @ ';
		if($rng[0] == $rng[1]) {
			$msg .= 'L' . $rng[0];
		} else {
			$msg .= 'L' . $rng[0] . '-L' . $rng[1];
		}
		$msg .= " </strong>\n";
		
		$msg .= '<table>';
		for($i = $rng[0]; $i <= $rng[1]; $i++) {
			// 输出内容
			$display_text = '';
			if($i != 0) {
				$display_text = htmlspecial2($cont[$i - 1]);
				if(strlen($display_text) == 0) {
					$display_text = '&nbsp;';
				}
			} else {
				$display_text = '~';
			}
			$msg .= '<tr><td>'.$i.'&gt;&nbsp;</td><td><span class="text-lowlight">' . $display_text . "</span></td></tr>\n";

			// 输出错误信息
			if(isset($lined[$i])) {
				foreach($lined[$i] as $e) {
					// 不隐藏
					if(!isset($e['hidden'])) {
						$msg .= '<tr>';
						$msg .= '<td>';
						$msg .= '<strong>·</strong>&nbsp;';
						$msg .= '</td>';
						// 纯文本输出
						if(isset($e['text'])) {
							$msg .= '<td>';
							if(isset($e['text_html'])) {
								$msg .= $e['text'];
							} else {
								$msg .= htmlspecial2($e['text']);
							}
							$msg .= '</td>';
						}
						// 错误格式输出
						else {
							$msg .= '<td>';
							$msg .= '<span class="text-comp-'.$e['level'].'">';
							cmpi_ADD_ERROR($msg,'!' . $e['level'],$e['id'],$e['line'],...$e['args']);
							$msg .= '</span>';
							$msg .= '</td>';
						}
						$msg .= "</tr>\n";
					}
				}
			}
		}
		$msg .= '</table>';
	}

	// 没有输出任何东西
	if(count($partition) == 0) {
		$msg .= '<strong class="text-highlight">';
		$msg .= ' ' . LNG('code.compinfo.null') . ' ';
		$msg .= '</strong>';
	}

	return $msg;
}

// 歌词行是否应该是间奏
function isIntervalContent($p) {
	$ptr = 0;
	$cnt = 0;
	for(;$ptr+1 < strlen($p); $ptr += 2) {
		if($p[$ptr] != '-' || $p[$ptr+1] != ' ') {
			return -1;
		}
		$cnt++;
	}
	if($p[$ptr] != '-') {
		return -1;
	}
	$cnt++;
	if($cnt == 1) {
		return -1;
	}
	return $cnt;
}

function getCompileIssueMsg2($item,$msglist,$window = 1) {
	$ft = getLyricFile($item);
	return getCompileIssueMsg($item . '/lyric.txt', $ft, $msglist, $window);
}

function prefixToLength($str,$len=3,$prfx=' ') {
	$ret = $str;
	while(strlen($ret) < $len) $ret = $prfx.$ret;
	return $ret;
}

function addLineNumbers($str) {
	$txt=explode("\n",str_replace("\r\n","\n",$str));
	$r = '';

	$r .= '<table>';
	foreach($txt as $k => $v) {
		$r .= '<tr>';

		$r .= '<td>';
		$r .= strval($k + 1)."|&nbsp;";
		$r .= '</td>';

		$r .= '<td>';
		$r .= ($v != '' ? htmlspecial2($v) : '&nbsp;');
		$r .= '</td>';

		$r .= "</tr>";
	}
	$r .= '</table>';
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

function lrcTimestamp($val, $tweak) {
	$val = max(0, $val + intval(round($GLOBALS['lrcopt']['delta'] * 10)));
	$i = strval(floor($val / 600));
	$s = strval(floor($val % 600 / 10));
	while(strlen($i) < 2) $i = '0' . $i;
	while(strlen($s) < 2) $s = '0' . $s;
	$g = strval($val % 10);

	if($tweak < 0) {
		$tweak = 0;
	} else if($tweak > 99) {
		$tweak = 99;
	}
	$tweak = sprintf("%02d", intval($tweak));
	
	return $i . ':' . $s . '.' . $g . $tweak;
}

function fmtDataForLrc($data) {
	// 找出最后一段的属性
	$lastParaId = '';
	$hasFinal = false;
	foreach($data['lyrics'] as $i=>&$para) {
		if($para['type'] == 'final') {
			$hasFinal = true;
		} else {
			$lastParaId = $para['id'];
		}
	}
	// 在每段最后添加一个空行，以注释形式。
	foreach($data['lyrics'] as $i=>&$para) {
		if(!$hasFinal && $lastParaId == $para['id']) continue;
		if($para['type'] != 'final' && !$para['premark']) {
			$para['in'][] = [
				'id' => -3,
				'ts' => TS_IS_COMMENT,
				'c' => ''
			];
		}
	}
	// 统计时刻，并处理间奏
	$timelist = [];
	foreach($data['lyrics'] as $i=>&$para) {
		if($para['type'] == 'lyrics') {
			// 初始配位段
			if($para['id'] == -1) {
				$para['in'][0]['ts'] = "-1";
				continue;
			}
			// 正常段落
			foreach($para['in'] as $j=>&$item) {
				// 填充间奏
				if(isIntervalContent($item['c']) > 4) {
					$item['c'] = '- Break -';
				}
				// 统计时刻
				$timelist[] = [intval($item['ts']),$i,$j];
				if($item['ts'] <= 5) {
					$item['ts'] = 5;
				} else {
					$ela = intval(round($GLOBALS['lrcopt']['precision'] * 10));
					$item['ts'] = intval(floor($item['ts'] / $ela) * $ela);
				}
			}
		} else if($para['type'] == 'final') {
			$timelist[] = [intval($para['ts']),$i,0];
			// 添加空白结尾标记
			$para = [
				'id' => -2,
				'type' => 'lyrics',
				'ac' => '__E',
				'display' => false,
				'title' => true,
				'ts' => $para['ts'],
				'in' => [
					[
						'id' => -2,
						'ts' => $para['ts'],
						'c' => '- End -'
					]
				]
			];
		}
	}

	// 设置间奏
	$cmt_size = intval(round($GLOBALS['lrcopt']['comment'] * 10));
	$j = 0;
	$prevtime = 0;
	for($i=0;$i<count($timelist);) {
		if($timelist[$i][0] < TS_IS_COMMENT) {
			$prevtime = $timelist[$i][0];
			$i++;
			continue;
		}
		$nexttime = -1;
		$j = $i;
		while($j < count($timelist) && $timelist[$j][0] >= TS_IS_COMMENT) {
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
			} else if($sz * $cmt_size > $nexttime - $prevtime) {
				$ts = floatval($nexttime - $prevtime) / $sz * $idx + $prevtime;
			} else {
				$ts = $nexttime - $ridx * $cmt_size;
			}
			$data['lyrics'][$timelist[$k][1]]['in'][$timelist[$k][2]]['ts'] = strval($ts);
		}
		$i = $j;
	}

	// 调整歌词行切入时间防止重叠
	$tweak = 0;
	$prevTs = -1;
	foreach($data['lyrics'] as $i=>&$para) {
		// 对任意段落生效
		foreach($para['in'] as $j=>&$item) {
			if($item['ts'] == $prevTs) {
				$tweak += 1;
			} else {
				$tweak = 0;
			}
			$prevTs = $item['ts'];

			$item['tweak'] = $tweak;
		}
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
		if($para['type'] == 'lyrics') {
			foreach($para['in'] as $item) {
				if($item['ts'] > 0) {
					$cont = trim(replaceLrcMark($item['c']));
					if(!isset($contmap[$cont])) {
						$contmap[$cont] = [];
					}
					$contmap[$cont][] = [intval($item['ts']), intval($item['tweak'])];
				}
			}
		}
	}

	foreach($contmap as $cont => $tsl) {
		foreach($tsl as $ts) {
			$ret .= '[' . lrcTimestamp($ts[0], $ts[1]) . ']';
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
		if($para['type'] == 'lyrics' && $para['ac'] == '__E') {
			$ret .= '[txmp_final:' . lrcTimestamp($para['ts'], 0) . ']' . "\n";
		}
		if($para['type'] == 'lyrics') {
			if($para['ac'] != '__E') {
				$ret .= '[txmp_para:' . strip_tags($para['ac']) . ' ' . $para['n'] . ']' . "\n";
			}
			foreach($para['in'] as $item) {
				if($item['ts'] > 0) {
					$cont = trim(replaceLrcMark($item['c']));
					$ret .= '[' . lrcTimestamp($item['ts'], $item['tweak']) . ']' . $cont . "\n";
				}
			}
		} else if($para['type'] == 'split') {
			$ret .= '[txmp_split:0]' . "\n";
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
