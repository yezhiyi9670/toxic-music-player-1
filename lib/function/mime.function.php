<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

define("PI",3.14159265358979323846264);

define("FULL_SPACE","\xE3\x80\x80");

// 判断是否仅包含字符
function onlyConsistsOf($haystack,$needle=[]) {
	for($i = 0; $i < strlen($haystack); $i++) {
		if(!in_array($haystack[$i],$needle)) {
			return false;
		}
	}
	return true;
}

// 随机字符串
function randString($len,$charset='0123456789') {
	$ret = '';
	for($i = 0; $i < $len; $i++) {
		$ret .= $charset[mt_rand(0,strlen($charset)-1)];
	}
	return $ret;
}

// 随机UUID
function randomUUID() {
	$res = randString(32,'0123456789abcdef');
	$p1 = substr($res,0,8);
	$p2 = substr($res,8,4);
	$p3 = substr($res,12,4);
	$p4 = substr($res,16,4);
	$p5 = substr($res,20);
	return implode('-', [$p1,$p2,$p3,$p4,$p5]);
}

// 限制数值
function clampLimit($x,$default=0,$step=1,$min=-65536,$max=65535) {
	if(null === $x) {
		return $default;
	}
	$x = floatval($x);
	if($x != $x) {
		return $default;
	}
	if($x < $min) {
		$x = $min;
	}
	if($x > $max) {
		$x = $max;
	}
	$x = round($x / $step) * $step;
	return $x;
}

// 格式化时长
function formatDuration($x) {
	$s = $x % 60;
	if($s < 10) {
		$s = '0' . $s;
	} else {
		$s = '' . $s;
	}
	$m = floor($x / 60);
	if($m < 10) {
		$m = '0' . $m;
	} else {
		$m = '' . $m;
	}
	if($m < 60) {
		return $m . ':' . $s;
	} else {
		$m = floor($x / 60) % 60;
		if($m < 10) {
			$m = '0' . $m;
		} else {
			$m = '' . $m;
		}
		$h = floor($x / 3600);

		return $h . ':' . $m . ':' . $s;
	}
}

// 分析音频码率、时长数据
function analyzeAudio($fn) {
	$result = (new getID3())->analyze($fn);
	if($result['error'] ?? null) {
		return null;
	}
	return [
		'format' => $result['fileformat'],
		'bitrate' => $result['audio']['lossless'] ? -floor($result['audio']['bitrate'] / 1000) : floor($result['audio']['bitrate'] / 1000),
		'time' => intval($result['playtime_seconds'])
	];
}
// “随机”鲜艳色彩（RGB格式）
function hashed_saturate_gradient($s) {
	$rd = new HashedRand();
	$rd->set_seed($s);
	$h1 = floor($rd->rand_float() * 360);
	$h2 = floor($rd->rand_float() * 140 - 70 + $h1) % 360;
	$lm1 = cos(($h1 - 100) * PI / 180) * 0.15 + 0.45;
	$lm2 = cos(($h2 - 100) * PI / 180) * 0.15 + 0.45;
	$c1 = hsl2rgb([
		$h1,
		$rd->rand_float() * 0.2 + 0.8,
		$rd->rand_float() * 0.1 + $lm1
	]);
	$c2 = hsl2rgb([
		$h2,
		$rd->rand_float() * 0.2 + 0.8,
		$rd->rand_float() * 0.1 + $lm2
	]);
	return [$c1,$c2];
}

// 颜色值 -> 光学数值
function color_tolinear($cl) {
	return pow($cl,2.2);
}

// 光学数值 -> 颜色值
function color_tovalue($c1) {
	return max(0,min(255,round(pow($c1,0.454545))));
}

// 二色平均
function two_color_avg($p1,$p2,$ratio = 0.5) {
	return [
		color_tovalue(color_tolinear($p1[0]) * $ratio + color_tolinear($p2[0]) * (1 - $ratio)),
		color_tovalue(color_tolinear($p1[1]) * $ratio + color_tolinear($p2[1]) * (1 - $ratio)),
		color_tovalue(color_tolinear($p1[2]) * $ratio + color_tolinear($p2[2]) * (1 - $ratio))
	];
}
function two_color_avg_hex($c1,$c2,$ratio = 0.5) {
	return '#' . rgb2hex(two_color_avg(hex2rgb($c1),hex2rgb($c2),$ratio));
}

// HEX -> RGB 转换
function hex2rgb($cd) {
	if($cd[0] == '#') {
		$cd = substr($cd,1);
	}
	$hex = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$r = strpos($hex,$cd[0]) * 16 + strpos($hex,$cd[1]);
	$g = strpos($hex,$cd[2]) * 16 + strpos($hex,$cd[3]);
	$b = strpos($hex,$cd[4]) * 16 + strpos($hex,$cd[5]);
	return [$r,$g,$b];
}

// RGB -> HEX 转换
function rgb2hex($col) {
	$hex="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	return
		$hex[intval($col[0] / 16)] .
		$hex[$col[0] % 16] .
		$hex[intval($col[1] / 16)] .
		$hex[$col[1] % 16] .
		$hex[intval($col[2] / 16)] .
		$hex[$col[2] % 16];
}

// HSL -> RGB 转换
// 值域 H[0,360) S[0,1] L[0,1]
// 输出数字信号值整数 [0,256)
function hsl2rgb($col) {
	$h = ($col[0] + 180) % 360 * 1.0;
	$s = $col[1] * 1.0;
	$l = $col[2] * 1.0;

	if($s == 0) {
		return [floor($l*255),floor($l*255),floor($l*255)];
	}

	$q = 0.0;
	if($l < 0.5) {
		$q = $l * (1 + $s);
	} else {
		$q = $l + $s - ($l * $s);
	}

	$p = 2 * $l - $q;

	$hk = $h / 360;

	$t = [
		$hk + 0.33333,
		$hk,
		$hk - 0.33333
	];
	for($i = 0; $i < 3; $i++) {
		if($t[$i] < 0) $t[$i] += 1.0;
		if($t[$i] > 1) $t[$i] -= 1.0;
	}

	$ret = [0,0,0];
	for($i = 0; $i < 3; $i++) {
		if($t[$i] < 0.16667) {
			$ret[$i] = $p + (($q - $p) * 6 * $t[$i]);
		} else if($t[$i] < 0.5) {
			$ret[$i] = $q;
		} else if($t[$i] < 0.66667) {
			$ret[$i] = $p + (($q - $p) * 6 * (0.66667 - $t[$i]));
		} else {
			$ret[$i] = $p;
		}
	}

	return [
		floor($ret[0] * 255),
		floor($ret[1] * 255),
		floor($ret[2] * 255)
	];
}

// 是否有子串
function str_included($str,$arr) {
	foreach($arr as $m) {
		if(strstr($str,$m)) return true;
	}
	return false;
}

// 目录列表
function dir_list($directory) {
	$files = array();
	if(is_dir($directory)) {
		if($files = scandir($directory)) {
			$files = array_slice($files,2);
		}
	}
	return $files;
}

// 含有 option 的 HTTP get 格式。
// 适用于爬虫可能因为缺少某些数据被拦截的时候。
function ex_url_get_contents($url,$option=[]) {
	$option = array_merge($option,['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36']);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$option);
	$output = curl_exec($ch);
	// echo $output;exit();
	curl_close($ch);
	return $output;
}

// 同 preg_match 但是将匹配结果作为数组返回
// 用于避免创建不必要的变量，减少某些地方的代码量
function preg_match_return($rule,$str) {
	$arr = [];
	preg_match($rule,$str,$arr);
	return $arr;
}

// 获得第一个 '_' 后的内容，用于解析出remoteplay的数字ID
function R($id) {
	return substr(strstr($id,'_'),1);
}

// 截除第一个 $f 以及此后的东西。如果不存在，则返回原字符串
function preSubstr($u,$f="/") {
	$x=$u;
	if(strstr($u,$f)){
		$x=substr($u,0,strpos($u,$f));
	}
	return $x;
}

// XML 特殊字符反向替换
function xmlspecial_unescape($str) {
	return str_replace(
		["&apos;","&lt;","&gt;","&quot;","&amp;"],
		["'","<",">",'"','&'],
		$str
	);
}

// -------------------------------- 此上为补加内容 -------------------------------- //

// 获取后缀 MIME 类型
function getMime($ext)
{
	$mime_maps = array(
		'ez' => 'application/andrew-inset',
		'aw' => 'application/applixware',
		'atom' => 'application/atom+xml',
		'atomcat' => 'application/atomcat+xml',
		'atomsvc' => 'application/atomsvc+xml',
		'ccxml' => 'application/ccxml+xml',
		'cu' => 'application/cu-seeme',
		'davmount' => 'application/davmount+xml',
		'dssc' => 'application/dssc+der',
		'xdssc' => 'application/dssc+xml',
		'ecma' => 'application/ecmascript',
		'emma' => 'application/emma+xml',
		'epub' => 'application/epub+zip',
		'pfr' => 'application/font-tdpfr',
		'stk' => 'application/hyperstudio',
		'ipfix' => 'application/ipfix',
		'jar' => 'application/java-archive',
		'ser' => 'application/java-serialized-object',
		'class' => 'application/java-vm',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'lostxml' => 'application/lost+xml',
		'hqx' => 'application/mac-binhex40',
		'cpt' => 'application/mac-compactpro',
		'mrc' => 'application/marc',
		'mb' => 'application/mathematica',
		'ma' => 'application/mathematica',
		'nb' => 'application/mathematica',
		'mathml' => 'application/mathml+xml',
		'mbox' => 'application/mbox',
		'mscml' => 'application/mediaservercontrol+xml',
		'mp4s' => 'application/mp4',
		'dot' => 'application/msword',
		'doc' => 'application/msword',
		'mxf' => 'application/mxf',
		'oda' => 'application/oda',
		'opf' => 'application/oebps-package+xml',
		'ogx' => 'application/ogg',
		'onepkg' => 'application/onenote',
		'onetoc' => 'application/onenote',
		'onetoc2' => 'application/onenote',
		'onetmp' => 'application/onenote',
		'xer' => 'application/patch-ops-error+xml',
		'pdf' => 'application/pdf',
		'pgp' => 'application/pgp-encrypted',
		'sig' => 'application/pgp-signature',
		'asc' => 'application/pgp-signature',
		'prf' => 'application/pics-rules',
		'p10' => 'application/pkcs10',
		'p7c' => 'application/pkcs7-mime',
		'p7m'=> 'application/pkcs7-mime',
		'p7s' => 'application/pkcs7-signature',
		'cer' => 'application/pkix-cert',
		'crl' => 'application/pkix-crl',
		'pkipath' => 'application/pkix-pkipath',
		'pki' => 'application/pkixcmp',
		'pls' => 'application/pls+xml',
		'ps' => 'application/postscript',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'cww' => 'application/prs.cww',
		'rdf' => 'application/rdf+xml',
		'rif' => 'application/reginfo+xml',
		'rnc' => 'application/relax-ng-compact-syntax',
		'rl' => 'application/resource-lists+xml',
		'rld' => 'application/resource-lists-diff+xml',
		'rs' => 'application/rls-services+xml',
		'rsd' => 'application/rsd+xml',
		'rss' => 'application/rss+xml',
		'rtf' => 'application/rtf',
		'sbml' => 'application/sbml+xml',
		'scq' => 'application/scvp-cv-request',
		'scs' => 'application/scvp-cv-response',
		'spq' => 'application/scvp-vp-request',
		'spp' => 'application/scvp-vp-response',
		'sdp' => 'application/sdp',
		'setpay' => 'application/set-payment-initiation',
		'setreg' => 'application/set-registration-initiation',
		'shf' => 'application/shf+xml',
		'smil' => 'application/smil+xml',
		'smi' => 'application/smil+xml',
		'rq' => 'application/sparql-query',
		'srx' => 'application/sparql-results+xml',
		'gram' => 'application/srgs',
		'grxml' => 'application/srgs+xml',
		'ssml' => 'application/ssml+xml',
		'plb' => 'application/vnd.3gpp.pic-bw-large',
		'psb' => 'application/vnd.3gpp.pic-bw-small',
		'pvb' => 'application/vnd.3gpp.pic-bw-var',
		'tcap' => 'application/vnd.3gpp2.tcap',
		'pwn' => 'application/vnd.3m.post-it-notes',
		'aso' => 'application/vnd.accpac.simply.aso',
		'imp' => 'application/vnd.accpac.simply.imp',
		'acu' => 'application/vnd.acucobol',
		'acutc' => 'application/vnd.acucorp',
		'atc' => 'application/vnd.acucorp',
		'air' => 'application/vnd.adobe.air-application-installer-package+zip',
		'xdp' => 'application/vnd.adobe.xdp+xml',
		'xfdf' => 'application/vnd.adobe.xfdf',
		'azf' => 'application/vnd.airzip.filesecure.azf',
		'azs' => 'application/vnd.airzip.filesecure.azs',
		'azw' => 'application/vnd.amazon.ebook',
		'acc' => 'application/vnd.americandynamics.acc',
		'ami' => 'application/vnd.amiga.ami',
		'apk' => 'application/vnd.android.package-archive',
		'cii' => 'application/vnd.anser-web-certificate-issue-initiation',
		'fti' => 'application/vnd.anser-web-funds-transfer-initiation',
		'atx' => 'application/vnd.antix.game-component',
		'mpkg' => 'application/vnd.apple.installer+xml',
		'm3u8' => 'application/vnd.apple.mpegurl',
		'swi' => 'application/vnd.aristanetworks.swi',
		'aep' => 'application/vnd.audiograph',
		'mpm' => 'application/vnd.blueice.multipass',
		'bmi' => 'application/vnd.bmi',
		'rep' => 'application/vnd.businessobjects',
		'cdxml' => 'application/vnd.chemdraw+xml',
		'mmd' => 'application/vnd.chipnuts.karaoke-mmd',
		'cdy' => 'application/vnd.cinderella',
		'cla' => 'application/vnd.claymore',
		'rp9' => 'application/vnd.cloanto.rp9',
		'c4u' => 'application/vnd.clonk.c4group',
		'c4g' => 'application/vnd.clonk.c4group',
		'c4d' => 'application/vnd.clonk.c4group',
		'c4f' => 'application/vnd.clonk.c4group',
		'c4p' => 'application/vnd.clonk.c4group',
		'csp' => 'application/vnd.commonspace',
		'cdbcmsg' => 'application/vnd.contact.cmsg',
		'cmc' => 'application/vnd.cosmocaller',
		'clkx' => 'application/vnd.crick.clicker',
		'clkk' => 'application/vnd.crick.clicker.keyboard',
		'clkp' => 'application/vnd.crick.clicker.palette',
		'clkt' => 'application/vnd.crick.clicker.template',
		'clkw' => 'application/vnd.crick.clicker.wordbank',
		'wbs' => 'application/vnd.criticaltools.wbs+xml',
		'pml' => 'application/vnd.ctc-posml',
		'ppd' => 'application/vnd.cups-ppd',
		'car' => 'application/vnd.curl.car',
		'pcurl' => 'application/vnd.curl.pcurl',
		'rdz' => 'application/vnd.data-vision.rdz',
		'fe_launch' => 'application/vnd.denovo.fcselayout-link',
		'dna' => 'application/vnd.dna',
		'mlp' => 'application/vnd.dolby.mlp',
		'dpg' => 'application/vnd.dpgraph',
		'dfac' => 'application/vnd.dreamfactory',
		'geo' => 'application/vnd.dynageo',
		'mag' => 'application/vnd.ecowin.chart',
		'nml' => 'application/vnd.enliven',
		'esf' => 'application/vnd.epson.esf',
		'msf' => 'application/vnd.epson.msf',
		'qam' => 'application/vnd.epson.quickanime',
		'slt' => 'application/vnd.epson.salt',
		'ssf' => 'application/vnd.epson.ssf',
		'et3' => 'application/vnd.eszigno3+xml',
		'es3' => 'application/vnd.eszigno3+xml',
		'ez2' => 'application/vnd.ezpix-album',
		'ez3' => 'application/vnd.ezpix-package',
		'fdf' => 'application/vnd.fdf',
		'mseed' => 'application/vnd.fdsn.mseed',
		'dataless' => 'application/vnd.fdsn.seed',
		'seed' => 'application/vnd.fdsn.seed',
		'gph' => 'application/vnd.flographit',
		'ftc' => 'application/vnd.fluxtime.clip',
		'book' => 'application/vnd.framemaker',
		'fm' => 'application/vnd.framemaker',
		'frame' => 'application/vnd.framemaker',
		'maker' => 'application/vnd.framemaker',
		'fnc' => 'application/vnd.frogans.fnc',
		'ltf' => 'application/vnd.frogans.ltf',
		'fsc' => 'application/vnd.fsc.weblaunch',
		'oas' => 'application/vnd.fujitsu.oasys',
		'oa2' => 'application/vnd.fujitsu.oasys2',
		'oa3' => 'application/vnd.fujitsu.oasys3',
		'fg5' => 'application/vnd.fujitsu.oasysgp',
		'bh2' => 'application/vnd.fujitsu.oasysprs',
		'ddd' => 'application/vnd.fujixerox.ddd',
		'xdw' => 'application/vnd.fujixerox.docuworks',
		'xbd' => 'application/vnd.fujixerox.docuworks.binder',
		'fzs' => 'application/vnd.fuzzysheet',
		'txd' => 'application/vnd.genomatix.tuxedo',
		'ggb' => 'application/vnd.geogebra.file',
		'ggt' => 'application/vnd.geogebra.tool',
		'gre' => 'application/vnd.geometry-explorer',
		'gex' => 'application/vnd.geometry-explorer',
		'gxt' => 'application/vnd.geonext',
		'g2w' => 'application/vnd.geoplan',
		'g3w' => 'application/vnd.geospace',
		'gmx' => 'application/vnd.gmx',
		'kml' => 'application/vnd.google-earth.kml+xml',
		'kmz' => 'application/vnd.google-earth.kmz',
		'gqs' => 'application/vnd.grafeq',
		'gqf' => 'application/vnd.grafeq',
		'gac' => 'application/vnd.groove-account',
		'ghf' => 'application/vnd.groove-help',
		'gim' => 'application/vnd.groove-identity-message',
		'grv' => 'application/vnd.groove-injector',
		'gtm' => 'application/vnd.groove-tool-message',
		'tpl' => 'application/vnd.groove-tool-template',
		'vcg' => 'application/vnd.groove-vcard',
		'zmm' => 'application/vnd.handheld-entertainment+xml',
		'hbci' => 'application/vnd.hbci',
		'les' => 'application/vnd.hhe.lesson-player',
		'hpgl' => 'application/vnd.hp-hpgl',
		'hpid' => 'application/vnd.hp-hpid',
		'hps' => 'application/vnd.hp-hps',
		'jlt' => 'application/vnd.hp-jlyt',
		'pcl' => 'application/vnd.hp-pcl',
		'pclxl' => 'application/vnd.hp-pclxl',
		'sfd-hdstx'=>'application/vnd.hydrostatix.sof-data',
		'x3d' => 'application/vnd.hzn-3d-crossword',
		'mpy' => 'application/vnd.ibm.minipay',
		'list3820' => 'application/vnd.ibm.modcap',
		'afp' => 'application/vnd.ibm.modcap',
		'listafp' => 'application/vnd.ibm.modcap',
		'irm' => 'application/vnd.ibm.rights-management',
		'sc' => 'application/vnd.ibm.secure-container',
		'icm' => 'application/vnd.iccprofile',
		'icc' => 'application/vnd.iccprofile',
		'igl' => 'application/vnd.igloader',
		'ivp' => 'application/vnd.immervision-ivp',
		'ivu' => 'application/vnd.immervision-ivu',
		'xpx' => 'application/vnd.intercon.formnet',
		'xpw' => 'application/vnd.intercon.formnet',
		'qbo' => 'application/vnd.intu.qbo',
		'qfx' => 'application/vnd.intu.qfx',
		'rcprofile' => 'application/vnd.ipunplugged.rcprofile',
		'irp' => 'application/vnd.irepository.package+xml',
		'xpr' => 'application/vnd.is-xpr',
		'jam' => 'application/vnd.jam',
		'rms' => 'application/vnd.jcp.javame.midlet-rms',
		'jisp' => 'application/vnd.jisp',
		'joda' => 'application/vnd.joost.joda-archive',
		'ktr' => 'application/vnd.kahootz',
		'ktz' => 'application/vnd.kahootz',
		'karbon' => 'application/vnd.kde.karbon',
		'chrt' => 'application/vnd.kde.kchart',
		'kfo' => 'application/vnd.kde.kformula',
		'flw' => 'application/vnd.kde.kivio',
		'kon' => 'application/vnd.kde.kontour',
		'kpt' => 'application/vnd.kde.kpresenter',
		'kpr' => 'application/vnd.kde.kpresenter',
		'ksp' => 'application/vnd.kde.kspread',
		'kwt' => 'application/vnd.kde.kword',
		'kwd' => 'application/vnd.kde.kword',
		'htke' => 'application/vnd.kenameaapp',
		'kia' => 'application/vnd.kidspiration',
		'knp' => 'application/vnd.kinar',
		'kne' => 'application/vnd.kinar',
		'skm' => 'application/vnd.koan',
		'skp' => 'application/vnd.koan',
		'skd' => 'application/vnd.koan',
		'skt' => 'application/vnd.koan',
		'sse' => 'application/vnd.kodak-descriptor',
		'lbd' => 'application/vnd.llamagraphics.life-balance.desktop',
		'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml',
		'123' => 'application/vnd.lotus-1-2-3',
		'apr' => 'application/vnd.lotus-approach',
		'pre' => 'application/vnd.lotus-freelance',
		'nsf' => 'application/vnd.lotus-notes',
		'org' => 'application/vnd.lotus-organizer',
		'scm' => 'application/vnd.lotus-screencam',
		'lwp' => 'application/vnd.lotus-wordpro',
		'portpkg' => 'application/vnd.macports.portpkg',
		'mcd' => 'application/vnd.mcd',
		'mc1' => 'application/vnd.medcalcdata',
		'cdkey' => 'application/vnd.mediastation.cdkey',
		'mwf' => 'application/vnd.mfer',
		'mfm' => 'application/vnd.mfmp',
		'flo' => 'application/vnd.micrografx.flo',
		'igx' => 'application/vnd.micrografx.igx',
		'mif' => 'application/vnd.mif',
		'daf' => 'application/vnd.mobius.daf',
		'dis' => 'application/vnd.mobius.dis',
		'mbk' => 'application/vnd.mobius.mbk',
		'mqy' => 'application/vnd.mobius.mqy',
		'msl' => 'application/vnd.mobius.msl',
		'plc' => 'application/vnd.mobius.plc',
		'txf' => 'application/vnd.mobius.txf',
		'mpn' => 'application/vnd.mophun.application',
		'mpc' => 'application/vnd.mophun.certificate',
		'xul' => 'application/vnd.mozilla.xul+xml',
		'cil' => 'application/vnd.ms-artgalry',
		'cab' => 'application/vnd.ms-cab-compressed',
		'xlw' => 'application/vnd.ms-excel',
		'xls' => 'application/vnd.ms-excel',
		'xlm' => 'application/vnd.ms-excel',
		'xla' => 'application/vnd.ms-excel',
		'xlc' => 'application/vnd.ms-excel',
		'xlt' => 'application/vnd.ms-excel',
		'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12',
		'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
		'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12',
		'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
		'eot' => 'application/vnd.ms-fontobject',
		'chm' => 'application/vnd.ms-htmlhelp',
		'ims' => 'application/vnd.ms-ims',
		'lrm' => 'application/vnd.ms-lrm',
		'cat' => 'application/vnd.ms-pki.seccat',
		'stl' => 'application/vnd.ms-pki.stl',
		'pot' => 'application/vnd.ms-powerpoint',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pps' => 'application/vnd.ms-powerpoint',
		'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
		'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
		'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
		'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
		'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12',
		'mpt' => 'application/vnd.ms-project',
		'mpp' => 'application/vnd.ms-project',
		'docm' => 'application/vnd.ms-word.document.macroenabled.12',
		'dotm' => 'application/vnd.ms-word.template.macroenabled.12',
		'wdb' => 'application/vnd.ms-works',
		'wps' => 'application/vnd.ms-works',
		'wks' => 'application/vnd.ms-works',
		'wcm' => 'application/vnd.ms-works',
		'wpl' => 'application/vnd.ms-wpl',
		'xps' => 'application/vnd.ms-xpsdocument',
		'mseq' => 'application/vnd.mseq',
		'mus' => 'application/vnd.musician',
		'msty' => 'application/vnd.muvee.style',
		'nlu' => 'application/vnd.neurolanguage.nlu',
		'nnd' => 'application/vnd.noblenet-directory',
		'nns' => 'application/vnd.noblenet-sealer',
		'nnw' => 'application/vnd.noblenet-web',
		'ngdat' => 'application/vnd.nokia.n-gage.data',
		'n-gage'=>'application/vnd.nokia.n-gage.symbian.install',
		'rpst' => 'application/vnd.nokia.radio-preset',
		'rpss' => 'application/vnd.nokia.radio-presets',
		'edm' => 'application/vnd.novadigm.edm',
		'edx' => 'application/vnd.novadigm.edx',
		'ext' => 'application/vnd.novadigm.ext',
		'odc' => 'application/vnd.oasis.opendocument.chart',
		'otc' => 'application/vnd.oasis.opendocument.chart-template',
		'odb' => 'application/vnd.oasis.opendocument.database',
		'odf' => 'application/vnd.oasis.opendocument.formula',
		'odft' => 'application/vnd.oasis.opendocument.formula-template',
		'odg' => 'application/vnd.oasis.opendocument.graphics',
		'otg' => 'application/vnd.oasis.opendocument.graphics-template',
		'odi' => 'application/vnd.oasis.opendocument.image',
		'oti' => 'application/vnd.oasis.opendocument.image-template',
		'odp' => 'application/vnd.oasis.opendocument.presentation',
		'otp' => 'application/vnd.oasis.opendocument.presentation-template',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
		'odt' => 'application/vnd.oasis.opendocument.text',
		'otm' => 'application/vnd.oasis.opendocument.text-master',
		'ott' => 'application/vnd.oasis.opendocument.text-template',
		'oth' => 'application/vnd.oasis.opendocument.text-web',
		'xo' => 'application/vnd.olpc-sugar',
		'dd2' => 'application/vnd.oma.dd2+xml',
		'oxt' => 'application/vnd.openofficeorg.extension',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
		'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',

		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'dp' => 'application/vnd.osgi.dp',
		'oprc' => 'application/vnd.palm',
		'pdb' => 'application/vnd.palm',
		'pqa' => 'application/vnd.palm',
		'paw' => 'application/vnd.pawaafile',
		'str' => 'application/vnd.pg.format',
		'ei6' => 'application/vnd.pg.osasli',
		'efif' => 'application/vnd.picsel',
		'wg' => 'application/vnd.pmi.widget',
		'plf' => 'application/vnd.pocketlearn',
		'pbd' => 'application/vnd.powerbuilder6',
		'box' => 'application/vnd.previewsystems.box',
		'mgz' => 'application/vnd.proteus.magazine',
		'qps' => 'application/vnd.publishare-delta-tree',
		'ptid' => 'application/vnd.pvi.ptid1',
		'qxb' => 'application/vnd.quark.quarkxpress',
		'qxd' => 'application/vnd.quark.quarkxpress',
		'qxt' => 'application/vnd.quark.quarkxpress',
		'qwd' => 'application/vnd.quark.quarkxpress',
		'qwt' => 'application/vnd.quark.quarkxpress',
		'qxl' => 'application/vnd.quark.quarkxpress',
		'bed' => 'application/vnd.realvnc.bed',
		'mxl' => 'application/vnd.recordare.musicxml',
		'musicxml' => 'application/vnd.recordare.musicxml+xml',
		'cod' => 'application/vnd.rim.cod',
		'rm' => 'application/vnd.rn-realmedia',
		'link66' => 'application/vnd.route66.link66+xml',
		'st' => 'application/vnd.sailingtracker.track',
		'see' => 'application/vnd.seemail',
		'sema' => 'application/vnd.sema',
		'semd' => 'application/vnd.semd',
		'semf' => 'application/vnd.semf',
		'ifm' => 'application/vnd.shana.informed.formdata',
		'itp' => 'application/vnd.shana.informed.formtemplate',
		'iif' => 'application/vnd.shana.informed.interchange',
		'ipk' => 'application/vnd.shana.informed.package',
		'twds' => 'application/vnd.simtech-mindmapper',
		'twd' => 'application/vnd.simtech-mindmapper',
		'mmf' => 'application/vnd.smaf',
		'teacher' => 'application/vnd.smart.teacher',
		'sdkd' => 'application/vnd.solent.sdkm+xml',
		'sdkm' => 'application/vnd.solent.sdkm+xml',
		'dxp' => 'application/vnd.spotfire.dxp',
		'sfs' => 'application/vnd.spotfire.sfs',
		'sdc' => 'application/vnd.stardivision.calc',
		'sda' => 'application/vnd.stardivision.draw',
		'sdd' => 'application/vnd.stardivision.impress',
		'smf' => 'application/vnd.stardivision.math',
		'sdw' => 'application/vnd.stardivision.writer',
		'vor' => 'application/vnd.stardivision.writer',
		'sgl' => 'application/vnd.stardivision.writer-global',
		'sxc' => 'application/vnd.sun.xml.calc',
		'stc' => 'application/vnd.sun.xml.calc.template',
		'sxd' => 'application/vnd.sun.xml.draw',
		'std' => 'application/vnd.sun.xml.draw.template',
		'sxi' => 'application/vnd.sun.xml.impress',
		'sti' => 'application/vnd.sun.xml.impress.template',
		'sxm' => 'application/vnd.sun.xml.math',
		'sxw' => 'application/vnd.sun.xml.writer',
		'sxg' => 'application/vnd.sun.xml.writer.global',
		'stw' => 'application/vnd.sun.xml.writer.template',
		'susp' => 'application/vnd.sus-calendar',
		'sus' => 'application/vnd.sus-calendar',
		'svd' => 'application/vnd.svd',
		'sisx' => 'application/vnd.symbian.install',
		'sis' => 'application/vnd.symbian.install',
		'xsm' => 'application/vnd.syncml+xml',
		'bdm' => 'application/vnd.syncml.dm+wbxml',
		'xdm' => 'application/vnd.syncml.dm+xml',
		'tao' => 'application/vnd.tao.intent-module-archive',
		'tmo' => 'application/vnd.tmobile-livetv',
		'tpt' => 'application/vnd.trid.tpt',
		'mxs' => 'application/vnd.triscape.mxs',
		'tra' => 'application/vnd.trueapp',
		'ufdl' => 'application/vnd.ufdl',
		'ufd' => 'application/vnd.ufdl',
		'utz' => 'application/vnd.uiq.theme',
		'umj' => 'application/vnd.umajin',
		'unityweb' => 'application/vnd.unity',
		'uoml' => 'application/vnd.uoml+xml',
		'vcx' => 'application/vnd.vcx',
		'vsw' => 'application/vnd.visio	',
		'vsd' => 'application/vnd.visio	',
		'vst' => 'application/vnd.visio	',
		'vss' => 'application/vnd.visio	',
		'vis' => 'application/vnd.visionary',
		'vsf' => 'application/vnd.vsf',
		'wbxml' => 'application/vnd.wap.wbxml',
		'wmlc' => 'application/vnd.wap.wmlc',
		'wmlsc' => 'application/vnd.wap.wmlscriptc',
		'wtb' => 'application/vnd.webturbo',
		'nbp' => 'application/vnd.wolfram.player',
		'wpd' => 'application/vnd.wordperfect',
		'wqd' => 'application/vnd.wqd',
		'stf' => 'application/vnd.wt.stf',
		'xar' => 'application/vnd.xara',
		'xfdl' => 'application/vnd.xfdl',
		'hvd' => 'application/vnd.yamaha.hv-dic',
		'hvs' => 'application/vnd.yamaha.hv-script',
		'hvp' => 'application/vnd.yamaha.hv-voice',
		'osf' => 'application/vnd.yamaha.openscoreformat',
		'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
		'saf' => 'application/vnd.yamaha.smaf-audio',
		'spf' => 'application/vnd.yamaha.smaf-phrase',
		'cmp' => 'application/vnd.yellowriver-custom-menu',
		'zirz' => 'application/vnd.zul',
		'zir' => 'application/vnd.zul',
		'zaz' => 'application/vnd.zzazz.deck+xml',
		'vxml' => 'application/voicexml+xml',
		'hlp' => 'application/winhlp',
		'wsdl' => 'application/wsdl+xml',
		'wspolicy' => 'application/wspolicy+xml',
		'abw' => 'application/x-abiword',
		'ace' => 'application/x-ace-compressed',
		'vox' => 'application/x-authorware-bin',
		'aab' => 'application/x-authorware-bin',
		'x32' => 'application/x-authorware-bin',
		'u32' => 'application/x-authorware-bin',
		'aam' => 'application/x-authorware-map',
		'aas' => 'application/x-authorware-seg',
		'bcpio' => 'application/x-bcpio',
		'torrent' => 'application/x-bittorrent',
		'bz' => 'application/x-bzip',
		'boz' => 'application/x-bzip2',
		'bz2' => 'application/x-bzip2',
		'vcd' => 'application/x-cdlink',
		'chat' => 'application/x-chat',
		'pgn' => 'application/x-chess-pgn',
		'cpio' => 'application/x-cpio',
		'csh' => 'application/x-csh',
		'udeb' => 'application/x-debian-package',
		'deb' => 'application/x-debian-package',

		'swa' => 'application/x-director',
		'dir' => 'application/x-director',
		'dcr' => 'application/x-director',
		'dxr' => 'application/x-director',
		'cst' => 'application/x-director',
		'cct' => 'application/x-director',
		'cxt' => 'application/x-director',
		'w3d' => 'application/x-director',
		'fgd' => 'application/x-director',

		'wad' => 'application/x-doom',
		'ncx' => 'application/x-dtbncx+xml',
		'dtb' => 'application/x-dtbook+xml',
		'res' => 'application/x-dtbresource+xml',
		'dvi' => 'application/x-dvi',
		'bdf' => 'application/x-font-bdf',
		'gsf' => 'application/x-font-ghostscript',
		'psf' => 'application/x-font-linux-psf',
		'otf' => 'application/x-font-otf',
		'pcf' => 'application/x-font-pcf',
		'snf' => 'application/x-font-snf',
		'ttc' => 'application/x-font-ttf',
		'ttf' => 'application/x-font-ttf',
		'afm' => 'application/x-font-type1',
		'pfa' => 'application/x-font-type1',
		'pfb' => 'application/x-font-type1',
		'pfm' => 'application/x-font-type1',
		'spl' => 'application/x-futuresplash',
		'gnumeric' => 'application/x-gnumeric',
		'gtar' => 'application/x-gtar',
		'hdf' => 'application/x-hdf',
		'jnlp' => 'application/x-java-jnlp-file',
		'latex' => 'application/x-latex',
		'mobi' => 'application/x-mobipocket-ebook',
		'prc' => 'application/x-mobipocket-ebook',
		'application' => 'application/x-ms-application',
		'wmd' => 'application/x-ms-wmd',
		'wmz' => 'application/x-ms-wmz',
		'xbap' => 'application/x-ms-xbap',
		'mdb' => 'application/x-msaccess',
		'obd' => 'application/x-msbinder',
		'crd' => 'application/x-mscardfile',
		'clp' => 'application/x-msclip',
		'msi' => 'application/x-msdownload',
		'exe' => 'application/x-msdownload',
		'dll' => 'application/x-msdownload',
		'com' => 'application/x-msdownload',
		'bat' => 'application/x-msdownload',
		'm14' => 'application/x-msmediaview',
		'mvb' => 'application/x-msmediaview',
		'm13' => 'application/x-msmediaview',
		'wmf' => 'application/x-msmetafile',
		'mny' => 'application/x-msmoney',
		'pub' => 'application/x-mspublisher',
		'scd' => 'application/x-msschedule',
		'trm' => 'application/x-msterminal',
		'wri' => 'application/x-mswrite',
		'cdf' => 'application/x-netcdf',
		'nc' => 'application/x-netcdf',
		'pfx' => 'application/x-pkcs12',
		'p12' => 'application/x-pkcs12',
		'spc' => 'application/x-pkcs7-certificates',
		'p7b' => 'application/x-pkcs7-certificates',
		'p7r' => 'application/x-pkcs7-certreqresp',
		'rar' => 'application/x-rar-compressed',
		'sh' => 'application/x-sh',
		'shar' => 'application/x-shar',
		'swf' => 'application/x-shockwave-flash',
		'xap' => 'application/x-silverlight-app',
		'sit' => 'application/x-stuffit',
		'sitx' => 'application/x-stuffitx',
		'sv4cpio' => 'application/x-sv4cpio',
		'sv4crc' => 'application/x-sv4crc',
		'tar' => 'application/x-tar',
		'tcl' => 'application/x-tcl',
		'tex' => 'application/x-tex',
		'tfm' => 'application/x-tex-tfm',
		'texi' => 'application/x-texinfo',
		'texinfo' => 'application/x-texinfo',
		'ustar' => 'application/x-ustar',
		'src' => 'application/x-wais-source',
		'crt' => 'application/x-x509-ca-cert',
		'der' => 'application/x-x509-ca-cert',
		'fig' => 'application/x-xfig',
		'xpi' => 'application/x-xpinstall',
		'xenc' => 'application/xenc+xml',
		'xht' => 'application/xhtml+xml',
		'xhtml' => 'application/xhtml+xml',
		'xsl' => 'application/xml',
		'xml' => 'application/xml',
		'dtd' => 'application/xml-dtd',
		'xop' => 'application/xop+xml',
		'xslt' => 'application/xslt+xml',
		'xspf' => 'application/xspf+xml',
		'xvm' => 'application/xv+xml',
		'mxml' => 'application/xv+xml',
		'xhvml' => 'application/xv+xml',
		'xvml' => 'application/xv+xml',
		'zip' => 'application/zip',
		'adp' => 'audio/adpcm',
		'snd' => 'audio/basic',
		'au' => 'audio/basic',
		'rmi' => 'audio/midi',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'kar' => 'audio/midi',
		'mp4a' => 'audio/mp4',
		'm3a' => 'audio/mpeg',
		'mpga' => 'audio/mpeg',
		'mp2' => 'audio/mpeg',
		'mp2a' => 'audio/mpeg',
		'mp3' => 'audio/mpeg',
		'm2a' => 'audio/mpeg',
		'spx' => 'audio/ogg',
		'oga' => 'audio/ogg',
		'ogg' => 'audio/ogg',
		'eol' => 'audio/vnd.digital-winds',
		'dra' => 'audio/vnd.dra',
		'dts' => 'audio/vnd.dts',
		'dtshd' => 'audio/vnd.dts.hd',
		'lvp' => 'audio/vnd.lucent.voice',
		'pya' => 'audio/vnd.ms-playready.media.pya',
		'ecelp4800' => 'audio/vnd.nuera.ecelp4800',
		'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
		'ecelp9600' => 'audio/vnd.nuera.ecelp9600',
		'aac' => 'audio/x-aac',
		'aifc' => 'audio/x-aiff',
		'aif' => 'audio/x-aiff',
		'aiff' => 'audio/x-aiff',
		'm3u' => 'audio/x-mpegurl',
		'wax' => 'audio/x-ms-wax',
		'wma' => 'audio/x-ms-wma',
		'ra' => 'audio/x-pn-realaudio',
		'ram'=> 'audio/x-pn-realaudio',
		'rmp' => 'audio/x-pn-realaudio-plugin',
		'wav' => 'audio/x-wav',
		'cdx' => 'chemical/x-cdx',
		'cif' => 'chemical/x-cif',
		'cmdf' => 'chemical/x-cmdf',
		'cml' => 'chemical/x-cml',
		'csml' => 'chemical/x-csml',
		'xyz' => 'chemical/x-xyz',
		'bmp' => 'image/bmp',
		'cgm' => 'image/cgm',
		'g3' => 'image/g3fax',
		'gif' => 'image/gif',
		'ief' => 'image/ief',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'btif' => 'image/prs.btif',
		'svgz' => 'image/svg+xml',
		'svg' => 'image/svg+xml',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'psd' => 'image/vnd.adobe.photoshop',
		'djv' => 'image/vnd.djvu',
		'djvu' => 'image/vnd.djvu',
		'dwg' => 'image/vnd.dwg',
		'dxf' => 'image/vnd.dxf',
		'fbs' => 'image/vnd.fastbidsheet',
		'fpx' => 'image/vnd.fpx',
		'fst' => 'image/vnd.fst',
		'mmr' => 'image/vnd.fujixerox.edmics-mmr',
		'rlc' => 'image/vnd.fujixerox.edmics-rlc',
		'mdi' => 'image/vnd.ms-modi',
		'npx' => 'image/vnd.net-fpx',
		'wbmp' => 'image/vnd.wap.wbmp',
		'xif' => 'image/vnd.xiff',
		'ras' => 'image/x-cmu-raster',
		'cmx' => 'image/x-cmx',
		'fh7' => 'image/x-freehand',
		'fh'=> 'image/x-freehand',
		'fhc'=> 'image/x-freehand',
		'fh4'=> 'image/x-freehand',
		'fh5'=> 'image/x-freehand',
		'ico' => 'image/x-icon',
		'pcx' => 'image/x-pcx',
		'pct' => 'image/x-pict',
		'pic' => 'image/x-pict',
		'pnm' => 'image/x-portable-anymap',
		'pbm' => 'image/x-portable-bitmap',
		'pgm' => 'image/x-portable-graymap',
		'ppm' => 'image/x-portable-pixmap',
		'rgb' => 'image/x-rgb',
		'xbm' => 'image/x-xbitmap',
		'xpm' => 'image/x-xpixmap',
		'xwd' => 'image/x-xwindowdump',
		'mime' => 'message/rfc822',
		'eml' => 'message/rfc822',
		'iges' => 'model/iges',
		'igs' => 'model/iges',
		'silo' => 'model/mesh',
		'msh' => 'model/mesh',
		'mesh' => 'model/mesh',
		'dwf' => 'model/vnd.dwf',
		'gdl' => 'model/vnd.gdl',
		'gtw' => 'model/vnd.gtw',
		'mts' => 'model/vnd.mts',
		'vtu' => 'model/vnd.vtu',
		'vrml' => 'model/vrml',
		'wrl' => 'model/vrml',
		'ifb' => 'text/calendar',
		'ics' => 'text/calendar',
		'css' => 'text/css',
		'csv' => 'text/csv',
		'htm' => 'text/html	',
		'html'=> 'text/html	',
		'in' => 'text/plain',
		'txt' => 'text/plain',
		'text' => 'text/plain',
		'conf' => 'text/plain',
		'def' => 'text/plain',
		'list' => 'text/plain',
		'log' => 'text/plain',
		'dsc' => 'text/prs.lines.tag',
		'rtx' => 'text/richtext',
		'sgm' => 'text/sgml',
		'sgml' => 'text/sgml',
		'tsv' => 'text/tab-separated-values',
		'ms' => 'text/troff',
		't' => 'text/troff',
		'tr' => 'text/troff',
		'roff' => 'text/troff',
		'man' => 'text/troff',
		'me' => 'text/troff',
		'urls' => 'text/uri-list',
		'uri' => 'text/uri-list',
		'uris' => 'text/uri-list',
		'curl' => 'text/vnd.curl',
		'dcurl' => 'text/vnd.curl.dcurl',
		'scurl' => 'text/vnd.curl.scurl',
		'mcurl' => 'text/vnd.curl.mcurl',
		'fly' => 'text/vnd.fly',
		'flx' => 'text/vnd.fmi.flexstor',
		'gv' => 'text/vnd.graphviz',
		'3dml' => 'text/vnd.in3d.3dml',
		'spot' => 'text/vnd.in3d.spot',
		'jad' => 'text/vnd.sun.j2me.app-descriptor',
		'wml' => 'text/vnd.wap.wml',
		'wmls' => 'text/vnd.wap.wmlscript',
		'asm' => 'text/x-asm',
		's' => 'text/x-asm',
		'dic' => 'text/x-c',
		'c' => 'text/x-c',
		'cc' => 'text/x-c',
		'cxx' => 'text/x-c',
		'cpp' => 'text/x-c',
		'h' => 'text/x-c',
		'hh' => 'text/x-c',
		'f90' => 'text/x-fortran',
		'f' => 'text/x-fortran',
		'for' => 'text/x-fortran',
		'f77' => 'text/x-fortran',
		'pas' => 'text/x-pascal',
		'p' => 'text/x-pascal',
		'java' => 'text/x-java-source',
		'etx' => 'text/x-setext',
		'uu' => 'text/x-uuencode',
		'vcs' => 'text/x-vcalendar',
		'vcf' => 'text/x-vcard',
		'3gp' => 'video/3gpp',
		'3g2' => 'video/3gpp2',
		'h261' => 'video/h261',
		'h263' => 'video/h263',
		'h264' => 'video/h264',
		'jpgv' => 'video/jpeg',
		'jpgm' => 'video/jpm',
		'jpm'  => 'video/jpm',
		'mjp2' => 'video/mj2',
		'mj2'  => 'video/mj2',
		'mpg4' => 'video/mp4',
		'mp4' => 'video/mp4',
		'mp4v' => 'video/mp4',
		'm2v' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpe' => 'video/mpeg',
		'm1v' => 'video/mpeg',
		'ogv' => 'video/ogg',
		'mov' => 'video/quicktime',
		'qt' => 'video/quicktime',
		'fvt' => 'video/vnd.fvt',
		'm4u' => 'video/vnd.mpegurl',
		'mxu' => 'video/vnd.mpegurl',
		'pyv' => 'video/vnd.ms-playready.media.pyv',
		'viv' => 'video/vnd.vivo',
		'f4v' => 'video/x-f4v',
		'fli' => 'video/x-fli',
		'flv' => 'video/x-flv',
		'm4v' => 'video/x-m4v',
		'asx' => 'video/x-ms-asf','asf' => 'video/x-ms-asf',
		'wm' => 'video/x-ms-wm',
		'wmv' => 'video/x-ms-wmv',
		'wmx' => 'video/x-ms-wmx',
		'wvx' => 'video/x-ms-wvx',
		'avi' => 'video/x-msvideo',
		'movie' => 'video/x-sgi-movie',
		'ice' => 'x-conference/x-cooltalk',
	);
	if(!isset($mime_maps[$ext])) return "text/plain";
	return $mime_maps[$ext];
}

/*
* @link http://kodcloud.com/
* @author warlee | e-mail:kodcloud@qq.com
* @copyright warlee 2014.(Shanghai)Co.,Ltd
* @license http://kodcloud.com/tools/license/license.txt
*/

/**
 * 系统函数：				filesize(),file_exists(),pathinfo(),rname(),unlink(),filemtime(),is_readable(),is_wrieteable();
 * 获取文件详细信息		file_info($fileName)
 * 获取文件夹详细信息		path_info($dir)
 * 递归获取文件夹信息		path_info_more($dir,&$fileCount=0,&$pathCount=0,&$size=0)
 * 获取文件夹下文件列表	path_list($dir)
 * 路径当前文件[夹]名		get_path_this($path)
 * 获取路径父目录			get_path_father($path)
 * 删除文件				del_file($file)
 * 递归删除文件夹			del_dir($dir)
 * 递归复制文件夹			copy_dir($source, $dest)
 * 创建目录				mk_dir($dir, $mode = 0777)
 * 文件大小格式化			size_format($bytes, $precision = 2)
 * 判断是否绝对路径		path_is_absolute( $path )
 * 扩展名的文件类型		ext_type($ext)
 * 文件下载				file_download($file)
 * 文件下载到服务器		file_download_this($from, $fileName)
 * 获取文件(夹)权限		get_mode($file)  //rwx_rwx_rwx [文件名需要系统编码]
 * 上传文件(单个，多个)	upload($fileInput, $path = './');//
 * 获取配置文件项			get_config($file, $ini, $type="string")
 * 修改配置文件项			update_config($file, $ini, $value,$type="string")
 * 写日志到LOG_PATH下		write_log('dd','default|.自建目录.','log|error|warning|debug|info|db')
 */

global $config;
$config = [
	'systemCharset' => 'UTF-8',
	'appCharset' => 'UTF-8'
];

// 传入参数为程序编码时，有传出，则用程序编码，
// 传入参数没有和输出无关时，则传入时处理成系统编码。
function iconv_app($str){
	global $config;
	$result = iconv_to($str,$config['systemCharset'], $config['appCharset']);
	return $result;
}
function iconv_system($str){
	//去除中文空格UTF8; windows下展示异常;过滤文件上传、新建文件等时的文件名
	//文件名已存在含有该字符时，没有办法操作.
	$char_empty = "\xc2\xa0";
	if(strpos($str,$char_empty) !== false){
		$str = str_replace($char_empty," ",$str);
	}

	global $config;
	$result = iconv_to($str,$config['appCharset'], $config['systemCharset']);
	$result = path_filter($result);
	return $result;
}
function iconv_to($str,$from,$to){
	if (strtolower($from) == strtolower($to)){
		return $str;
	}
	if (!function_exists('iconv')){
		return $str;
	}
	//尝试用mb转换；android环境部分问题解决
	if(function_exists('mb_convert_encoding')){
		$result = @mb_convert_encoding($str,$to,$from);
	}else{
		$result = @iconv($from, $to, $str);
	}
	if(strlen($result)==0){
		return $str;
	}
	return $result;
}
function path_filter($path){
	if(strtoupper(substr(PHP_OS, 0,3)) != 'WIN'){
		return $path;
	}
	$notAllow = array('*','?','"','<','>','|');//去除 : D:/
	return str_replace($notAllow,' ', $path);
}


//filesize 解决大于2G 大小问题
//http://stackoverflow.com/questions/5501451/php-x86-how-to-get-filesize-of-2-gb-file-without-external-program
function get_filesize($path){
	$result = false;
	$fp = fopen($path,"r");
	if(! $fp = fopen($path,"r")) return $result;
	if(PHP_INT_SIZE >= 8 ){ //64bit
		$result = (float)(abs(sprintf("%u",@filesize($path))));
	}else{
		if (fseek($fp, 0, SEEK_END) === 0) {
			$result = 0.0;
			$step = 0x7FFFFFFF;
			while ($step > 0) {
				if (fseek($fp, - $step, SEEK_CUR) === 0) {
					$result += floatval($step);
				} else {
					$step >>= 1;
				}
			}
		}else{
			static $iswin;
			if (!isset($iswin)) {
				$iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
			}
			static $exec_works;
			if (!isset($exec_works)) {
				$exec_works = (function_exists('exec') && !ini_get('safe_mode') && @exec('echo EXEC') == 'EXEC');
			}
			if ($iswin && class_exists("COM")) {
				try {
					$fsobj = new COM('Scripting.FileSystemObject');
					$f = $fsobj->GetFile( realpath($path) );
					$size = $f->Size;
				} catch (Exception $e) {
					$size = null;
				}
				if (is_numeric($size)) {
					$result = $size;
				}
			}else if ($exec_works){
				$cmd = ($iswin) ? "for %F in (\"$path\") do @echo %~zF" : "stat -c%s \"$path\"";
				@exec($cmd, $output);
				if (is_array($output) && is_numeric($size = trim(implode("\n", $output)))) {
					$result = $size;
				}
			}else{
				$result = filesize($path);
			}
		}
	}
	fclose($fp);
	return $result;
}

//文件是否存在，区分文件大小写
function file_exists_case( $fileName ){
	if(file_exists($fileName) === false){
		return false;
	}
	$status         = false;
	$directoryName  = dirname( $fileName );
	$fileArray      = glob( $directoryName . '/*', GLOB_NOSORT);
	if ( preg_match( "/\\\|\//", $fileName) ){
		$array    = preg_split("/\\\|\//", $fileName);
		$fileName = $array[ count( $array ) -1 ];
	}
	foreach($fileArray as $file ){
		if(preg_match("/{$fileName}/i", $file)){
			$output = "{$directoryName}/{$fileName}";
			$status = true;
			break;
		}
	}
	return $status;
}


function path_readable($path){
	$result = intval(is_readable($path));
	if($result){
		return $result;
	}
	$mode = get_mode($path);
	if( $mode &&
		strlen($mode) == 18 &&
		substr($mode,-9,1) == 'r'){// -rwx rwx rwx(0777)
		return true;
	}
	return false;
}
function path_writeable($path){
	$result = intval(is_writeable($path));
	if($result){
		return $result;
	}
	$mode = get_mode($path);
	if( $mode &&
		strlen($mode) == 18 &&
		substr($mode,-8,1) == 'w'){// -rwx rwx rwx (0777)
		return true;
	}
	return false;
}

/**
 * 获取文件详细信息
 * 文件名从程序编码转换成系统编码,传入utf8，系统函数需要为gbk
 */
function file_info($path){
	$info = array(
		'name'			=> iconv_app(get_path_this($path)),
		'path'			=> iconv_app($path),
		'ext'			=> get_path_ext($path),
		'type' 			=> 'file',
		'mode'			=> get_mode($path),
		'atime'			=> @fileatime($path), //最后访问时间
		'ctime'			=> @filectime($path), //创建时间
		'mtime'			=> @filemtime($path), //最后修改时间
		'isReadable'	=> path_readable($path),
		'isWriteable'	=> path_writeable($path),
		'size'			=> get_filesize($path)
	);
	return $info;
}
/**
 * 获取文件夹细信息
 */
function folder_info($path){
	$info = array(
		'name'			=> iconv_app(get_path_this($path)),
		'path'			=> iconv_app(rtrim($path,'/').'/'),
		'type' 			=> 'folder',
		'mode'			=> get_mode($path),
		'atime'			=> @fileatime($path), //访问时间
		'ctime'			=> @filectime($path), //创建时间
		'mtime'			=> @filemtime($path), //最后修改时间
		'isReadable'	=> path_readable($path),
		'isWriteable'	=> path_writeable($path)
	);
	return $info;
}


/**
 * 获取一个路径(文件夹&文件) 当前文件[夹]名
 * test/11/ ==>11 test/1.c  ==>1.c
 */
function get_path_this($path){
	$path = str_replace('\\','/', rtrim($path,'/'));
	$pos = strrpos($path,'/');
	if($pos === false){
		return $path;
	}
	return substr($path,$pos+1);
}
/**
 * 获取一个路径(文件夹&文件) 父目录
 * /test/11/==>/test/   /test/1.c ==>/www/test/
 */
function get_path_father($path){
	$path = str_replace('\\','/', rtrim($path,'/'));
	$pos = strrpos($path,'/');
	if($pos === false){
		return $path;
	}
	return substr($path, 0,$pos+1);
}
/**
 * 获取扩展名
 */
function get_path_ext($path){
	$name = get_path_this($path);
	$ext = '';
	if(strstr($name,'.')){
		$ext = substr($name,strrpos($name,'.')+1);
		$ext = strtolower($ext);
	}
	if (strlen($ext)>3 && preg_match("/([\x81-\xfe][\x40-\xfe])/", $ext, $match)) {
		$ext = '';
	}
	return htmlspecialchars($ext);
}



//自动获取不重复文件(夹)名
//如果传入$file_add 则检测存在则自定重命名  a.txt 为a{$file_add}.txt
//$same_file_type  rename,replace,skip,folder_rename
function get_filename_auto($path,$file_add = "",$same_file_type='replace'){
	if (is_dir($path) && $same_file_type!='folder_rename') {//文件夹则忽略
		return $path;
	}
	//重名处理
	if (file_exists($path)) {
		if ($same_file_type=='replace') {
			return $path;
		}else if($same_file_type=='skip'){
			return false;
		}
	}

	$i=1;
	$father = get_path_father($path);
	$name =  get_path_this($path);
	$ext = get_path_ext($name);
	if(is_dir($path)){
		$ext = '';
	}
	if (strlen($ext)>0) {
		$ext='.'.$ext;
		$name = substr($name,0,strlen($name)-strlen($ext));
	}
	while(file_exists($path)){
		if ($file_add != '') {
			$path = $father.$name.$file_add.$ext;
			$file_add.='-';
		}else{
			$path = $father.$name.'('.$i.')'.$ext;
			$i++;
		}
	}
	return $path;
}

/**
 * 获取文件夹详细信息,文件夹属性时调用，包含子文件夹数量，文件数量，总大小
 */
function path_info($path){
	if (!file_exists($path)) return false;
	$pathinfo = _path_info_more($path);//子目录文件大小统计信息
	$folderinfo = folder_info($path);
	return array_merge($pathinfo,$folderinfo);
}

/**
 * 检查名称是否合法
 */
function path_check($path){
	$check = array('/','\\',':','*','?','"','<','>','|');
	$path = rtrim($path,'/');
	$path = get_path_this($path);
	foreach ($check as $v) {
		if (strstr($path,$v)) {
			return false;
		}
	}
	return true;
}

/**
 * 递归获取文件夹信息： 子文件夹数量，文件数量，总大小
 */
function _path_info_more($dir, &$fileCount = 0, &$pathCount = 0, &$size = 0){
	if (!$dh = @opendir($dir)) return array('fileCount'=>0,'folderCount'=>0,'size'=>0);
	while (($file = readdir($dh)) !== false) {
		if ($file =='.' || $file =='..') continue;
		$fullpath = $dir . "/" . $file;
		if (!is_dir($fullpath)) {
			$fileCount ++;
			$size += get_filesize($fullpath);
		} else {
			_path_info_more($fullpath, $fileCount, $pathCount, $size);
			$pathCount ++;
		}
	}
	closedir($dh);
	$pathinfo['fileCount'] = $fileCount;
	$pathinfo['folderCount'] = $pathCount;
	$pathinfo['size'] = $size;
	return $pathinfo;
}


/**
 * 获取多选文件信息,包含子文件夹数量，文件数量，总大小，父目录权限
 */
function path_info_muti($list,$timeType){
	if (count($list) == 1) {
		if ($list[0]['type']=="folder"){
			return path_info($list[0]['path'],$timeType);
		}else{
			return file_info($list[0]['path'],$timeType);
		}
	}
	$pathinfo = array(
		'fileCount'		=> 0,
		'folderCount'	=> 0,
		'size'			=> 0,
		'father_name'	=> '',
		'mod'			=> ''
	);
	foreach ($list as $val){
		if ($val['type'] == 'folder') {
			$pathinfo['folderCount'] ++;
			$temp = path_info($val['path']);
			$pathinfo['folderCount']	+= $temp['folderCount'];
			$pathinfo['fileCount']	+= $temp['fileCount'];
			$pathinfo['size'] 		+= $temp['size'];
		}else{
			$pathinfo['fileCount']++;
			$pathinfo['size'] += get_filesize($val['path']);
		}
	}
	$father_name = get_path_father($list[0]['path']);
	$pathinfo['mode'] = get_mode($father_name);
	return $pathinfo;
}

/**
 * 获取文件夹下列表信息
 * dir 包含结尾/   d:/wwwroot/test/
 * 传入需要读取的文件夹路径,为程序编码
 */
function path_list($dir,$listFile=true,$checkChildren=false){
	$dir = rtrim($dir,'/').'/';
	if (!is_dir($dir) || !($dh = @opendir($dir))){
		return array('folderList'=>array(),'fileList'=>array());
	}
	$folderList = array();$fileList = array();//文件夹与文件
	while (($file = readdir($dh)) !== false) {
		if ($file =='.' || $file =='..' || $file == ".svn") continue;
		$fullpath = $dir . $file;
		if (is_dir($fullpath)) {
			$info = folder_info($fullpath);
			if($checkChildren){
				$info['isParent'] = path_haschildren($fullpath,$listFile);
			}
			$folderList[] = $info;
		} else if($listFile) {//是否列出文件
			$info = file_info($fullpath);
			if($checkChildren) $info['isParent'] = false;
			$fileList[] = $info;
		}
	}
	closedir($dh);
	return array('folderList' => $folderList,'fileList' => $fileList);
}

// 判断文件夹是否含有子内容【区分为文件或者只筛选文件夹才算】
function path_haschildren($dir,$checkFile=false){
	$dir = rtrim($dir,'/').'/';
	if (!$dh = @opendir($dir)) return false;
	while (($file = readdir($dh)) !== false){
		if ($file =='.' || $file =='..') continue;
		$fullpath = $dir.$file;
		if ($checkFile) {//有子目录或者文件都说明有子内容
			if(@is_file($fullpath) || is_dir($fullpath.'/')){
				return true;
			}
		}else{//只检查有没有文件
			if(@is_dir($fullpath.'/')){//解决部分主机报错问题
				return true;
			}
		}
	}
	closedir($dh);
	return false;
}

/**
 * 删除文件 传入参数编码为操作系统编码. win--gbk
 */
function del_file($fullpath){
	if (!@unlink($fullpath)) { // 删除不了，尝试修改文件权限
		@chmod($fullpath, 0777);
		if (!@unlink($fullpath)) {
			return false;
		}
	} else {
		return true;
	}
}

/**
 * 删除文件夹 传入参数编码为操作系统编码. win--gbk
 */
function del_dir($dir){
	if(!file_exists($dir) || !is_dir($dir)) return true;
	if (!$dh = opendir($dir)) return false;
	@set_time_limit(0);
	while (($file = readdir($dh)) !== false) {
		if ($file =='.' || $file =='..') continue;
		$fullpath = $dir . '/' . $file;
		if (!is_dir($fullpath)) {
			if (!unlink($fullpath)) { // 删除不了，尝试修改文件权限
				chmod($fullpath, 0777);
				if (!unlink($fullpath)) {
					return false;
				}
			}
		} else {
			if (!del_dir($fullpath)) {
				chmod($fullpath, 0777);
				if (!del_dir($fullpath)) return false;
			}
		}
	}
	closedir($dh);
	if (rmdir($dir)) {
		return true;
	} else {
		return false;
	}
}


/**
 * 复制文件夹
 * eg:将D:/wwwroot/下面wordpress复制到
 *	D:/wwwroot/www/explorer/0000/del/1/
 * 末尾都不需要加斜杠，复制到地址如果不加源文件夹名，
 * 就会将wordpress下面文件复制到D:/wwwroot/www/explorer/0000/del/1/下面
 * $from = 'D:/wwwroot/wordpress';
 * $to = 'D:/wwwroot/www/explorer/0000/del/1/wordpress';
 */

function copy_dir($source, $dest){
	if (!$dest) return false;
	if (is_dir($source) && $source == substr($dest,0,strlen($source))) return false;//防止父文件夹拷贝到子文件夹，无限递归

	@set_time_limit(0);
	$result = true;
	if (is_file($source)) {
		if ($dest[strlen($dest)-1] == '/') {
			$__dest = $dest . "/" . basename($source);
		} else {
			$__dest = $dest;
		}
		$result = @copy($source,$__dest);
		@chmod($__dest, 0777);
	}else if(is_dir($source)) {
		if ($dest[strlen($dest)-1] == '/') {
			$dest = $dest . basename($source);
		}
		if (!is_dir($dest)) {
			@mkdir($dest,0777);
		}
		if (!$dh = opendir($source)) return false;
		while (($file = readdir($dh)) !== false) {
			if ($file =='.' || $file =='..') continue;
			$result = copy_dir($source . "/" . $file, $dest . "/" . $file);
		}
		closedir($dh);
	}
	return $result;
}

/**
 * 移动文件&文件夹；（同名文件夹则特殊处理）
 * 问题：win下，挂载磁盘移动到系统盘时rmdir导致遍历不完全；
 */
function move_path2($source,$dest,$repeat_add='',$repeat_type='replace'){
	if (!$dest) return false;
	if (is_dir($source) && $source == substr($dest,0,strlen($source))) return false;//防止父文件夹拷贝到子文件夹，无限递归
	@set_time_limit(0);
	if (is_file($source)) {
		return move_file($source,$dest,$repeat_add,$repeat_type);
	}else if(is_dir($source)) {
		if ($dest[strlen($dest)-1] == '/') {
			$dest = $dest . basename($source);
		}
		if (!file_exists($dest)) {
			@mkdir($dest,0777);
		}
		if (!$dh = opendir($source)) return false;
		while (($file = readdir($dh)) !== false) {
			if ($file =='.' || $file =='..') continue;
			move_path($source."/".$file, $dest."/".$file,$repeat_add,$repeat_type);
		}
		closedir($dh);
		return @rmdir($source);
	}
}

function move_file($source,$dest,$repeat_add,$repeat_type){
	if ($dest[strlen($dest)-1] == '/') {
		$dest = $dest . "/" . basename($source);
	}
	if(file_exists($dest)){
		$dest = get_filename_auto($dest,$repeat_add,$repeat_type);//同名文件处理规则
	}
	$result = intval(@rename($source,$dest));
	if (! $result) { // windows部分ing情况处理
		$result = intval(@copy($source,$dest));
		if ($result) {
			@unlink($source);
		}
	}
	return $result;
}
function move_path($source,$dest,$repeat_add='',$repeat_type='replace'){
	if (!$dest || !file_exists($source)) return false;
	if ( is_dir($source) ){
		//防止父文件夹拷贝到子文件夹，无限递归
		if($source == substr($dest,0,strlen($source))){
			return false;
		}
		//地址相同
		if(rtrim($source,'/') == rtrim($dest,'/')){
			return false;
		}
	}

	@set_time_limit(0);
	if(is_file($source)){
		return move_file($source,$dest,$repeat_add,$repeat_type);
	}
	recursion_dir($source,$dirs,$files,-1,0);

	@mkdir($dest);
	foreach($dirs as $f){
		$path = $dest.'/'.substr($f,strlen($source));
		if(!file_exists($path)){
			mk_dir($path);
		}
	}
	$file_success = 0;
	foreach($files as $f){
		$path = $dest.'/'.substr($f,strlen($source));
		$file_success += move_file($f,$path,$repeat_add,$repeat_type);
	}
	foreach($dirs as $f){
		rmdir($f);
	}
	@rmdir($source);
	if($file_success == count($files)){
		del_dir($source);
		return true;
	}
	return false;
}

/**
 * 创建目录
 *
 * @param string $dir
 * @param int $mode
 * @return bool
 */
function mk_dir($dir, $mode = 0777){
	if (!$dir) return false;
	if (is_dir($dir) || @mkdir($dir, $mode)){
		return true;
	}
	if (!mk_dir(dirname($dir), $mode)){
		return false;
	}
	return @mkdir($dir, $mode);
}

/*
* 获取文件&文件夹列表(支持文件夹层级)
* path : 文件夹 $dir ——返回的文件夹array files ——返回的文件array
* $deepest 是否完整递归；$deep 递归层级
*/
function recursion_dir($path,&$dir,&$file,$deepest=-1,$deep=0){
	$path = rtrim($path,'/').'/';
	if (!is_array($file)) $file=array();
	if (!is_array($dir)) $dir=array();
	if (!$dh = opendir($path)) return false;
	while(($val=readdir($dh)) !== false){
		if ($val=='.' || $val=='..') continue;
		$value = strval($path.$val);
		if (is_file($value)){
			$file[] = $value;
		}else if(is_dir($value)){
			$dir[]=$value;
			if ($deepest==-1 || $deep<$deepest){
				recursion_dir($value."/",$dir,$file,$deepest,$deep+1);
			}
		}
	}
	closedir($dh);
	return true;
}

// 安全读取文件，避免并发下读取数据为空
function file_read_safe($file,$timeout = 3){
	if(!$file || !file_exists($file)) return false;
	$fp = @fopen($file, 'r');
	if(!$fp) return false;
	$startTime = microtime(true);
	do{
		$locked = flock($fp, LOCK_SH);//LOCK_EX|LOCK_NB
		if(!$locked){
			usleep(mt_rand(1, 50) * 1000);//1~50ms;
		}
	} while((!$locked) && ((microtime(true) - $startTime) < $timeout ));//设置超时时间
	if($locked && filesize($file) >=0 ){
		$result = @fread($fp, filesize($file));
		flock($fp,LOCK_UN);
		fclose($fp);
		if(filesize($file) == 0){
			return '';
		}
		return $result;
	}else{
		flock($fp,LOCK_UN);fclose($fp);
		return false;
	}
}

// 安全读取文件，避免并发下读取数据为空
function file_wirte_safe($file,$buffer,$timeout=3){
	clearstatcache();
	if(strlen($file) == 0 || !$file || !file_exists($file)) return false;
	$fp = fopen($file,'r+');
	$startTime = microtime(true);
	do{
		$locked = flock($fp, LOCK_EX);//LOCK_EX
		if(!$locked){
			usleep(mt_rand(1, 50) * 1000);//1~50ms;
		}
	} while((!$locked) && ((microtime(true) - $startTime) < $timeout ) );//设置超时时间
	if($locked){
		$tempFile = $file.'.temp';
		$result = file_put_contents($tempFile,$buffer,LOCK_EX);//验证是否还能写入；避免磁盘空间满的情况
		if(!$result || !file_exists($tempFile) ){
			flock($fp,LOCK_UN);fclose($fp);
			return false;
		}
		@unlink($tempFile);

		ftruncate($fp,0);
		rewind($fp);
		$result = fwrite($fp,$buffer);
		flock($fp,LOCK_UN);fclose($fp);
		clearstatcache();
		return $result;
	}else{
		flock($fp,LOCK_UN);fclose($fp);
		return false;
	}
}

/*
 * $search 为包含的字符串
 * is_content 表示是否搜索文件内容;默认不搜索
 * is_case  表示区分大小写,默认不区分
 */
function path_search($path,$search,$is_content=false,$file_ext='',$is_case=false){
	$result = array();
	$result['fileList'] = array();
	$result['folderList'] = array();
	if(!$path) return $result;

	$ext_arr = explode("|",$file_ext);
	recursion_dir($path,$dirs,$files,-1,0);
	$strpos = 'stripos';//是否区分大小写
	if ($is_case) $strpos = 'strpos';
	$result_num = 0;
	$result_num_max = 2000;//搜索文件内容，限制最多匹配条数
	foreach($files as $f){
		if($result_num >= $result_num_max){
			$result['error_info'] = $result_num_max;
			break;
		}

		//若指定了扩展名则只在匹配扩展名文件中搜索
		$ext = get_path_ext($f);
		if($file_ext != '' && !in_array($ext,$ext_arr)){
			continue;
		}

		//搜索内容则不搜索文件名
		if ($is_content) {
			if(!is_text_file($ext)) continue; //在限定中或者不在bin中
			$search_info = file_search($f,$search,$is_case);
			if($search_info !== false){
				$result_num += count($search_info['searchInfo']);
				$result['fileList'][] = $search_info;
			}
		}else{
			$path_this = get_path_this($f);
			if ($strpos($path_this,iconv_system($search)) !== false){//搜索文件名;
				$result['fileList'][] = file_info($f);
				$result_num ++;
			}
		}
	}
	if (!$is_content && $file_ext == '' ) {//没有指定搜索文件内容，且没有限定扩展名，才搜索文件夹
		foreach($dirs as $f){
			$path_this = get_path_this($f);
			if ($strpos($path_this,iconv_system($search)) !== false){
				$result['folderList'][]= array(
					'name'  => iconv_app(get_path_this($f)),
					'path'  => iconv_app($f)
				);
			}
		}
	}
	return $result;
}

// 文件搜索；返回行及关键词附近行
// 优化搜索算法 提高100被性能
function file_search($path,$search,$is_case){
	$strpos = 'stripos';//是否区分大小写
	if ($is_case) $strpos = 'strpos';

	//文本文件 超过40M不再搜索
	if(@filesize($path) >= 1024*1024*40){
		return false;
	}
	$content = file_get_contents($path);
	if( $strpos($content,"\0") > 0 ){// 不是文本文档
		unset($content);
		return false;
	}
	$charset = get_charset($content);
	//搜索关键字为纯英文则直接搜索；含有中文则转为utf8再搜索，为兼容其他文件编码格式
	$notAscii = preg_match("/[\x7f-\xff]/", $search);
	if($notAscii && !in_array($charset,array('utf-8','ascii'))){
		$content = iconv_to($content,$charset,'utf-8');
	}

	//文件没有搜索到目标直接返回
	if ($strpos($content,$search) === false){
		unset($content);
		return false;
	}

	$pose = 0;
	$file_size = strlen($content);
	$arr_search = array(); // 匹配结果所在位置
	while ( $pose !== false) {
		$pose = $strpos($content,$search, $pose);
		if($pose !== false){
			$arr_search[] = $pose;
			$pose ++;
		}else{
			break;
		}
	}

	$arr_line = array();
	$pose = 0;
	while ( $pose !== false) {
		$pose = strpos($content, "\n", $pose);
		if($pose !== false){
			$arr_line[] = $pose;
			$pose ++;
		}else{
			break;
		}
	}
	$arr_line[] = $file_size;//文件只有一行而且没有换行，则构造虚拟换行
	$result = array();//  [2,10,22,45,60]  [20,30,40,50,55]
	$len_search = count($arr_search);
	$len_line 	= count($arr_line);
	for ($i=0,$line=0; $i < $len_search && $line < $len_line; $line++) {
		while ( $arr_search[$i] <= $arr_line[$line]) {
			//行截取字符串
			$cur_pose = $arr_search[$i];
			$from = $line == 0 ? 0:$arr_line[$line-1];
			$to = $arr_line[$line];
			$len_max = 300;
			if( $to - $from >= $len_max){ //长度过长处理
				$from = $cur_pose - 20;
				$from = $from <= 0 ? 0 : $from;
				$to   = $from + $len_max;
				//中文避免截断；（向前 向后找到分隔符后终止）
				$token = array("\r","\n"," ","\t",",","/","#","_","[","]","(",")","+","-","*","/","=","&");
				while (!in_array($content[$from],$token) && $from >= 0) {
					$from -- ;
				}
				while (!in_array($content[$to],$token) && $to <= $file_size) {
					$to ++ ;
				}
			}
			$line_str = substr($content,$from,$to - $from);
			if($strpos($line_str,$search) === false){ //截取乱码避免
				$line_str = $search;
			}

			$result[] = array('line'=>$line+1,'str'=>$line_str);
			if(++$i >= $len_search ){
				break;
			}
		}
	}

	$info = file_info($path);
	$info['searchInfo'] = $result;
	unset($content);
	return $info;
}

/**
 * 修改文件、文件夹权限
 * @param  $path 文件(夹)目录
 * @return :string
 */
function chmod_path($path,$mod){
	if (!isset($mod)) $mod = 0777;
	if (!file_exists($path)) return false;
	if (is_file($path)) return @chmod($path,$mod);
	if (!$dh = @opendir($path)) return false;
	while (($file = readdir($dh)) !== false){
		if ($file =='.' || $file =='..') continue;
		$fullpath = $path . '/' . $file;
		chmod_path($fullpath,$mod);
		@chmod($fullpath,$mod);
	}
	closedir($dh);
	return @chmod($path,$mod);
}

/**
 * 文件大小格式化
 *
 * @param  $ :$bytes, int 文件大小
 * @param  $ :$precision int  保留小数点
 * @return :string
 */
function size_format($bytes, $precision = 2){
	if ($bytes == 0) return "0 B";
	$unit = array(
		'TB' => 1099511627776,  // pow( 1024, 4)
		'GB' => 1073741824,		// pow( 1024, 3)
		'MB' => 1048576,		// pow( 1024, 2)
		'kB' => 1024,			// pow( 1024, 1)
		'B ' => 1,				// pow( 1024, 0)
	);
	foreach ($unit as $un => $mag) {
		if (doubleval($bytes) >= $mag)
			return round($bytes / $mag, $precision).' '.$un;
	}
}

/**
 * 判断路径是不是绝对路径
 * 返回true('/foo/bar','c:\windows').
 *
 * @return 返回true则为绝对路径，否则为相对路径
 */
function path_is_absolute($path){
	if (realpath($path) == $path)// *nux 的绝对路径 /home/my
		return true;
	if (strlen($path) == 0 || $path[0] == '.')
		return false;
	if (preg_match('#^[a-zA-Z]:\\\\#', $path))// windows 的绝对路径 c:\aaa\
		return true;
	return (bool)preg_match('#^[/\\\\]#', $path); //绝对路径 运行 / 和 \绝对路径，其他的则为相对路径
}


function is_text_file($ext){
	$ext_arr = array(
		"txt","textile",'oexe','inc','csv','log','asc','tsv','lnk','url','webloc','meta',"localized",
		"xib","xsd","storyboard","plist","csproj","pch","pbxproj","local","xcscheme","manifest","vbproj",
		"strings",'jshintrc','sublime-project','readme','changes',"changelog",'version','license','changelog',

		"abap","abc","as","asp",'aspx',"ada","adb","htaccess","htgroups","htgroups",
		"htpasswd","asciidoc","adoc","asm","a","ahk","bat","cmd","cpp","c","cc","cxx","h","hh","hpp",
		"ino","c9search_results","cirru","cr","clj","cljs","cbl","cob","coffee","cf","cson","cakefile",
		"cfm","cs","css","curly","d","di","dart","diff","patch","dockerfile","dot","dummy","dummy","e",
		"ge","ejs","ex","exs","elm","erl","hrl","frt","fs","ldr","ftl","gcode","feature",".gitignore",
		"glsl","frag","vert","gbs","go","groovy","haml","hbs","handlebars","tpl","mustache","hs","hx",
		"html","hta","htm","xhtml","eex","html.eex","erb","rhtml","html.erb","ini",'inf',"conf","cfg","prefs","io",
		"jack","jade","java","ji","jl","jq","js","jsm","json","jsp","jsx","latex","ltx","bib",
		"lean","hlean","less","liquid","lisp","ls","logic","lql","lsl","lua","lp","lucene","Makefile","makemakefile",
		"gnumakefile","makefile","ocamlmakefile","make","md","markdown","mask","matlab","mz","mel",
		"mc","mush","mysql","nix","nsi","nsh","m","mm","ml","mli","pas","p","pl","pm","pgsql","php",
		"phtml","shtml","php3","php4","php5","phps","phpt","aw","ctp","module","ps1","praat",
		"praatscript","psc","proc","plg","prolog","properties","proto","py","r","cshtml","rd",
		"rhtml","rst","rb","ru","gemspec","rake","guardfile","rakefile","gemfile","rs","sass",
		"scad","scala","scm","sm","rkt","oak","scheme","scss","sh","bash","bashrc","sjs","smarty",
		"tpl","snippets","soy","space","sql","sqlserver","styl","stylus","svg","swift","tcl","tex",
		"toml","twig","swig","ts","typescript","str","vala","vbs","vb","vm","v","vh",
		"sv","svh","vhd","vhdl","wlk","wpgm","wtest","xml","rdf","rss","wsdl","xslt","atom","mathml",
		"mml","xul","xbl","xaml","xq","yaml","yml",

		"cer","reg","config"
	);
	if(in_array($ext,$ext_arr)){
		return true;
	}else{
		return false;
	}
}

/**
 * 输出、文件下载，断点续传支持
 * 默认以附件方式下载；$download为false时则为输出文件
 * 视频播放拖拽：流媒体服务器
 * 文件缓存：http://blog.csdn.net/eroswang/article/details/8302191
 */
function file_put_out($file,$download=-1,$downFilename=false){

	$error = false;
	$fp=null;
	if (!file_exists($file)){
		$error = 'file not exists';
	}else if (!path_readable($file)){
		$error = 'file not readable';
	}else if (!$fp = @fopen($file, "rb")){
		$error = 'file open error!';
	}
	if($error !== false){
		if($downFilename === false){
			return;
		}else{
			return;
			// show_json($error,false);
		}
	}

	$start= 0;
	$file_size = get_filesize($file);
	$end  = $file_size - 1;
	@ob_end_clean();
	@set_time_limit(0);

	$time = gmdate('D, d M Y H:i:s',filemtime($file));
	$filename = get_path_this($file);
	if($downFilename !== false){
		$filename = $downFilename;
	}

	$mime = get_file_mime(get_path_ext($filename));
	if ($download === -1 && !mime_support($mime)){
		$download = true;
	}
	$headerName = rawurlencode(iconv_app($filename));
	$headerName = '"'.$headerName."\"; filename*=utf-8''".$headerName;
	if ($download) {
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment;filename='.$headerName);
	}else{
		header('Content-Type: '.$mime);
		header('Content-Disposition: inline;filename='.$headerName);
		if(strstr($mime,'text/')){
			//$charset = get_charset(file_get_contents($file));
			header('Content-Type: '.$mime.'; charset=');//避免自动追加utf8导致gbk网页乱码
		}
	}

	//缓存文件
	header('Expires: '.gmdate('D, d M Y H:i:s',time()+3600*24*20).' GMT');
	header('Cache-Pragma: public');
	header('Pragma: public');
	// header('Cache-Control: cache, must-revalidate');
// 	header('Cache-Control: no-store');
	header("Connection: Keep-Alive");
	if (isset($_SERVER['If-Modified-Since']) &&
		(strtotime($_SERVER['If-Modified-Since']) == filemtime($file))) {
		header('304 Not Modified', true, 304);
		exit;
	}
	$etag = '"'.md5($time.$file_size).'"';
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag){
		header("Etag: ".$etag, true, 304);
		exit;
	}
	$filenameOutput = basename($file);
	if($downFilename) {
		$filenameOutput = $downFilename;
	}
	header('Etag: '.$etag);
	header('Last-Modified: '.$time.' GMT');
	header("X-OutFileName: ".$filenameOutput);
	header("X-Powered-By: kodExplorer.");
	header("X-FileSize: ".$file_size);

	//调用webserver下载
	$server = strtolower($_SERVER['SERVER_SOFTWARE']);
	if($server && false){
		if(strstr($server,'nginx')){//nginx
			header('X-Accel-Redirect: '.$file);
		}else if(strstr($server,'apache')){ //apache
			header("X-Sendfile: ".$file);
		}else if(strstr($server,'http') || strstr($server,'litespeed')){//light http
			header( "X-LIGHTTPD-send-file: " . $file);
		}
		return;
	}

	//远程路径不支持断点续传；打开zip内部文件
	if(!file_exists($file)){
		header('HTTP/1.1 200 OK');
		header('Content-Length: '.($end+1));
		return;
	}
	$acceptedRange=false;
	if (isset($_SERVER['HTTP_RANGE'])){
		if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)){
			header('HTTP/1.1 206 Partial Content');
			header("Accept-Ranges: bytes");
			$acceptedRange=true;
			$start	= intval($matches[1]);
			if (!empty($matches[2])){
				$end = intval($matches[2]);
			}
// 			if($end - $start > 96 * 1024) {
// 			    $end = min($end, $start + 96 * 1024);
// 			}
		}
		else header('HTTP/1.1 200 OK');
	}else{
		header('HTTP/1.1 200 OK');
	}
	if(isset($_GET['start'])){//flash video
		$acceptedRange=true;
		$start = intval($_GET['start']);
	}
	header('Content-Length:' . (($end - $start) + 1));
	if (isset($_SERVER['HTTP_RANGE']) || isset($_GET['start'])){
		header("Content-Range: bytes $start-$end/".$file_size);
	}

	//输出文件
	$cur = $start;
	fseek($fp, $start,0);
	while(!feof($fp) && $cur <= $end  && (connection_status() == 0)){
		print fread($fp, min(1024 * 256, ($end - $cur) + 1));
		$cur += 1024 *256;
		flush();
	}
	fclose($fp);
}

/**
 * 远程文件下载到服务器
 * 支持fopen的打开都可以；支持本地、url
 */
function file_download_this($from, $fileName,$headerSize=0){
	@set_time_limit(0);
	$fileTemp = $fileName.'.downloading';
	if ($fp = @fopen ($from, "rb")){
		if(!$downloadFp = @fopen($fileTemp, "wb")){
			return false;
		}
		while(!feof($fp)){
			if(!file_exists($fileTemp)){//删除目标文件；则终止下载
				fclose($downloadFp);
				return false;
			}
			//对于部分fp不结束的通过文件大小判断
			clearstatcache();
			if( $headerSize>0 &&
				$headerSize==get_filesize(iconv_system($fileTemp))
				){
				break;
			}
			fwrite($downloadFp, fread($fp, 1024 * 16 ), 1024 * 16);
		}
		//下载完成，重命名临时文件到目标文件
		fclose($downloadFp);
		fclose($fp);
		if(!@rename($fileTemp,$fileName)){
			unlink($fileName);
			return rename($fileTemp,$fileName);
		}
		return true;
	}else{
		return false;
	}
}

/**
 * 获取文件(夹)权限 rwx_rwx_rwx
 */
function get_mode($file){
	$Mode = @fileperms($file);
	$theMode = ' '.decoct($Mode);
	$theMode = substr($theMode,-4);
	$Owner = array();$Group=array();$World=array();
	if ($Mode &0x1000) $Type = 'p'; // FIFO pipe
	elseif ($Mode &0x2000) $Type = 'c'; // Character special
	elseif ($Mode &0x4000) $Type = 'd'; // Directory
	elseif ($Mode &0x6000) $Type = 'b'; // Block special
	elseif ($Mode &0x8000) $Type = '-'; // Regular
	elseif ($Mode &0xA000) $Type = 'l'; // Symbolic Link
	elseif ($Mode &0xC000) $Type = 's'; // Socket
	else $Type = 'u'; // UNKNOWN
	// Determine les permissions par Groupe
	$Owner['r'] = ($Mode &00400) ? 'r' : '-';
	$Owner['w'] = ($Mode &00200) ? 'w' : '-';
	$Owner['x'] = ($Mode &00100) ? 'x' : '-';
	$Group['r'] = ($Mode &00040) ? 'r' : '-';
	$Group['w'] = ($Mode &00020) ? 'w' : '-';
	$Group['e'] = ($Mode &00010) ? 'x' : '-';
	$World['r'] = ($Mode &00004) ? 'r' : '-';
	$World['w'] = ($Mode &00002) ? 'w' : '-';
	$World['e'] = ($Mode &00001) ? 'x' : '-';
	// Adjuste pour SUID, SGID et sticky bit
	if ($Mode &0x800) $Owner['e'] = ($Owner['e'] == 'x') ? 's' : 'S';
	if ($Mode &0x400) $Group['e'] = ($Group['e'] == 'x') ? 's' : 'S';
	if ($Mode &0x200) $World['e'] = ($World['e'] == 'x') ? 't' : 'T';
	$Mode = $Type.$Owner['r'].$Owner['w'].$Owner['x'].' '.
			$Group['r'].$Group['w'].$Group['e'].' '.
			$World['r'].$World['w'].$World['e'];
	return $Mode.'('.$theMode.')';
}

/**
 * 获取可以上传的最大值
 * return * byte
 */
function get_post_max(){
	$upload = ini_get('upload_max_filesize');
	$upload = $upload==''?ini_get('upload_max_size'):$upload;
	$post = ini_get('post_max_size');
	$upload = intval($upload)*1024*1024*0.8;
	$post = intval($post)*1024*1024*0.8;
	$the_max = $upload<$post?$upload:$post;
	return $the_max==0?1024*1024*0.5:$the_max;//获取不到则500k
}



function path_clear($path){
	$path = str_replace('\\','/',trim($path));
	$path = preg_replace('/\/+/', '/', $path);
	if (strstr($path,'../')) {
		$path = preg_replace('/\/\.+\//', '/', $path);
	}
	return $path;
}
function path_clear_name($path){
	$path = str_replace('\\','/',trim($path));
	$path = str_replace('/','.',trim($path));
	return $path;
}

// 兼容move_uploaded_file 和 流的方式上传
function kod_move_uploaded_file($fromPath,$savePath){
	$tempPath = $savePath.'.parttmp';
	if($fromPath == "base64"){
		@file_put_contents($tempPath,base64_decode($_POST['file']));
	}else if($fromPath == "php://input"){
		$in  = @fopen($fromPath, "rb");
		$out = @fopen($tempPath, "wb");
		if(!$in || !$out) return false;
		while (!feof($in)) {
			fwrite($out, fread($in, 1024*200));
		}
		fclose($in);
		fclose($out);
	}else{
		if(!move_uploaded_file($fromPath,$tempPath)){
			show_json('move uploaded file error!',false);
		}
	}

	$result = rename($tempPath,$savePath);
	chmod_path($savePath,DEFAULT_PERRMISSIONS);
	return $result;
}
function check_upload($error){
	$status = array(
		'UPLOAD_ERR_OK',        //0 没有错误发生，文件上传成功。
		'UPLOAD_ERR_INI_SIZE',  //1 上传的文件超过了php.ini 中 upload_max_filesize 选项限制的值。
		'UPLOAD_ERR_FORM_SIZE', //2 上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
		'UPLOAD_ERR_PARTIAL',   //3 文件只有部分被上传。
		'UPLOAD_ERR_NO_FILE',   //4 没有文件被上传。
		'UPLOAD_UNKNOW',		//5 未定义
		'UPLOAD_ERR_NO_TMP_DIR',//6 找不到临时文件夹。php 4.3.10 和 php 5.0.3 引进。
		'UPLOAD_ERR_CANT_WRITE',//7 文件写入失败。php 5.1.0 引进。
	);
	return $error.':'.$status[$error];
}

//拍照上传
function updload_ios_check($fileName,$in){
	if(!is_wap()) return $fileName;
	$time = strtotime($in['lastModifiedDate']);
	$time = $time ? $time : time();
	$beforeName = strtolower($fileName);
	if($beforeName == "image.jpg" || $beforeName == "image.jpeg"){
		$fileName =  date('Ymd',$time).'_'.$in['size'].'.jpg';
	}else if($beforeName == "capturedvideo.mov"){
		$fileName =  date('Ymd',$time).'_'.$in['size'].'.mov';
	}
	return $fileName;
}

/**
 * 文件上传处理。大文件支持分片上传
 * upload('file','D:/www/');
 *
 * post上传：base64Upload=1;file=base64str;name=filename
 */
function upload($path,$tempPath,$repeatAction='replace'){
	ignore_timeout();
	global $in;
	$fileInput = 'file';
	$fileName = "";
	if (!empty($_FILES)) {
		$fileName = iconv_system(path_clear_name($_FILES[$fileInput]["name"]));
		$uploadFile = $_FILES[$fileInput]["tmp_name"];
		if(!$uploadFile && $_FILES[$fileInput]['error']>0){
			show_json(check_upload($_FILES[$fileInput]['error']),false);
		}
		$fileName = updload_ios_check($fileName,$in);//拍照上传
	}else if (isset($in["name"])) {
		$fileName = iconv_system(path_clear_name($in["name"]));
		$uploadFile = "php://input";
		if(isset($in['base64Upload'])){
			$uploadFile = "base64";
		}
		$fileName = updload_ios_check($fileName,$in);//拍照上传
	}else if( isset($in["check_md5"]) ) {//断点续传检测
		$fileName = iconv_system(path_clear_name($in["name"]));
		$savePath = get_filename_auto($path.$fileName,""); //自动重命名
		return upload_chunk("--check_md5--",$tempPath,$savePath);
	}else{
		show_json('param error',false);
	}

	//正常上传
	$savePath = get_filename_auto($path.$fileName,"",$repeatAction); //自动重命名
	Hook::trigger('uploadFileBefore',$savePath);
	if($savePath === false){
		show_json('upload_exist_skip',false);
	}

	$chunks = isset($in["chunks"]) ? intval($in["chunks"]) : 1;
	if ($chunks > 1) {//并发上传，不一定有前后顺序
		return upload_chunk($uploadFile,$tempPath,$savePath);
	}
	if(kod_move_uploaded_file($uploadFile,$savePath)){
		Hook::trigger('uploadFileAfter',$savePath);
		show_json('upload_success',true,iconv_app(_DIR_OUT($savePath)));
	}else {
		show_json('move_error',false);
	}
}


/**
 * 简易文件hash获取;替代md5_file;
 * md5(文件头6字节+中间6字节+结尾6字节)
 */
function file_hash_simple($file){
	$fileSize    = filesize($file);
	$sliceLength = 6;
	if($fileSize <= $sliceLength){
		$sliceString = file_get_contents($file);
	}else{
		$fp = fopen($file,'r');
		$sliceString = fread($fp,$sliceLength);
		fseek($fp,($fileSize-$sliceLength)/2);
		$sliceString .= fread($fp,$sliceLength);
		fseek($fp,$fileSize-$sliceLength);
		$sliceString .= fread($fp,$sliceLength);
		fclose($fp);
	}
	$hash = $fileSize;
	for ($i=0; $i < strlen($sliceString); $i++) {
		$hash = $hash.",".ord($sliceString[$i]);
	}
	return md5($hash);
}

function upload_chunk($uploadFile,$tempPath,$savePath){
	global $in;
	$chunk = isset($in["chunk"]) ? intval($in["chunk"]) : 0;
	$chunks = isset($in["chunks"]) ? intval($in["chunks"]) : 1;
	$check_md5 = isset($in["check_md5"]) ? $in["check_md5"] : false;

	//if(mt_rand(0, 100) > 10) die("server error".$chunk); //模拟失败
	//文件分块检测是否已上传，已上传则忽略；断点续传
	if($check_md5 !== false){
		$chunk_file_pre = $tempPath.md5($savePath).'.part';
		$chunk_file = $chunk_file_pre.$chunk;
		if( file_exists($chunk_file) && file_hash_simple($chunk_file) == $check_md5){
			$arr = array();
			for($index = 0; $index<$chunks; $index++ ){
				if(file_exists($chunk_file_pre.$index)){
					$arr['part_'.$index] = file_hash_simple($chunk_file_pre.$index);
				}
			}
			show_json('success',true,$arr);
		}else{
			show_json('not_exists',false);
		}
	}

	$tempFilePre = $tempPath.md5($savePath).'.part';
	if(kod_move_uploaded_file($uploadFile, $tempFilePre.$chunk)){
		$done = true;

		//优化分片存在判断；当分片太多时,每个分片都全量判断,会占用服务器资源及影响上传速度;
		$fromIndex    = 0;
		$existMaxFile = $tempFilePre.'.max';//记录连续存在文件的最大序号
		if(file_exists($existMaxFile)){
			$fromIndex = intval(file_get_contents($fromIndex));
		}else{
			file_put_contents($existMaxFile,$fromIndex);
		}
		for($index = $fromIndex; $index<$chunks; $index++ ){
			if (!file_exists($tempFilePre.$index)) {
				if($index-1 > $fromIndex){
					file_put_contents($existMaxFile,$index-1);
				}
				$done = false;
				break;
			}
		}

		if (!$done){
			show_json('upload_success',true);
		}else{
			$savePathTemp = $tempFilePre.mtime();
			if(!$out = fopen($savePathTemp, "wb")){
				show_json('no_permission_write',false);
			}
			if (!flock($out, LOCK_EX)) {
				show_json('lock dist move error',false);
			}else{
				for( $index = 0; $index < $chunks; $index++ ) {
					$chunk_file = $tempFilePre.$index;
					if (!$fp_in = @fopen($chunk_file,"rb")){//并发情况下另一个访问时文件已删除
						flock($out, LOCK_UN);
						fclose($out);
						unlink($savePathTemp);
						show_json('open chunk error! cur='.$chunk.';index='.$index,false);
					}
					while (!feof($fp_in)) {
						fwrite($out, fread($fp_in,1024*200));
					}
					fclose($fp_in);
					unlink($chunk_file);
				}
				flock($out, LOCK_UN);
				fclose($out);
			}
		}
		unlink($existMaxFile);
		$res = rename($savePathTemp,$savePath);
		if(!$res){
			unlink($savePath);
			$res = rename($savePathTemp,$savePath);
			if(!$res){
				show_json('move(rename) dist file error!',false);
			}
		}
		Hook::trigger('uploadFileAfter',$savePath);
		show_json('upload_success',true,iconv_app(_DIR_OUT($savePath)));
	}else {
		show_json('move_error',false);
	}
}


/**
 * 写日志
 * @param string $log   日志信息
 * @param string $type  日志类型 [system|app|...]
 * @param string $level 日志级别
 * @return boolean
 */
function write_log($log, $type = 'default', $level = 'log'){
	if(!defined('LOG_PATH')){
		return;
	}
	list($usec, $sec) = explode(' ', microtime());
	$now_time = date('[H:i:s.').substr($usec,2,3).'] ';
	$target   = LOG_PATH . strtolower($type) . '/';
	mk_dir($target);
	if (!path_writeable($target)){
		exit('path can not write!');
	}
	$ext = '.php';//.php .log;
	$target .= date('Y_m_d').'__'.$level.$ext;
	//检测日志文件大小, 超过配置大小则重命名
	if (file_exists($target) && get_filesize($target) >= 1024*1024*10) {
		$fileName = substr(basename($target),0,strrpos(basename($target),$ext)).date('H:i:s').$ext;
		rename($target, dirname($target) .'/'. $fileName);
	}
	if(!file_exists($target)){
		error_log("<?php exit;?>\n", 3,$target);
	}

	if(is_object($log) || is_array($log)){
		$log = json_encode_force($log);
	}
	clearstatcache();
	return error_log("$now_time $log\n", 3, $target);
}

/**
 * 列树
 * dir_list_tree($dir,&$dst)
 */
function dir_list_tree($dir,&$dst){
	$dst["success"]="OK";
	while(substr($dir,strlen($dir)-1,1)=="/") $dir=substr($dir,0,strlen($dir)-1);
	//$dst["absolute"]=$dir;
	$dst["name"]=basename2($dir);
	$dst["type"]="folder";
	if(!file_exists($dir)){
		$dst["success"]="notExists";
		return;
	}
	$dst["mtime"]=filemtime($dir);
	if(!is_dir($dir)){
		$dst["type"]="file";
		$dst["size"]=filesize($dir);
		return;
	}
	if(!$dh=opendir($dir))
	{
		$dst["success"]="deny";
		return;
	}
	@set_time_limit(0);
	$dst["list"]=array();
	$i=0;
	while (($file = readdir($dh)) !== false) {
		if ($file =='.' || $file =='..') continue;
		$fullpath = $dir . '/' . $file;
		dir_list_tree($fullpath,$dst["list"][$i]);
		$i++;
	}
	closedir($dh);
}
/*
* @product RojExplorer
* @author Luojie
* @copyright rojcms team 2017.
*/

function htmlspecial($str){
	if($str == null) {
		return '';
	}
	return str_replace(
		array('&','<','>','"',"'"),
		array('&amp;','&lt;','&gt;','&quot;','&#039;'),
		$str
	);
}

/**
 * JS in HTML 特殊字符转换
 */
function jsspecial($str) {
	return str_replace(
		['<', '>', '`', '$'],
		["\\x3c", "\\x3e", "\\x60", "\\x24"],
		addslashes($str)
	);
}

function htmlspecial3($str){
	if($str == null) {
		return '';
	}
	return str_replace(
		array('&','<','>'),
		array('&amp;','&lt;','&gt;'),
		$str
	);
}
function htmlspecial2($str){
	if($str == null) {
		return '';
	}
	return str_replace(
		array('&','<','>'),
		array('&amp;','&lt;','&gt;'),
		$str
	);
}
function htmlspecial_decode($str){
	return str_replace(
		array('&lt;','&gt;','&quot;','&#039;','&amp;'),
		array('<','>','"',"'","&"),
		$str
	);
}


/**
 * client ip address
 *
 * @param boolean $s_type ip类型[ip|long]
 * @return string $ip
 */
function get_client_ip($b_ip = true){
	$arr_ip_header = array(
		"HTTP_CLIENT_IP",
		"HTTP_X_FORWARDED_FOR",
		"REMOTE_ADDR",
		"HTTP_CDN_SRC_IP",
		"HTTP_PROXY_CLIENT_IP",
		"HTTP_WL_PROXY_CLIENT_IP"
	);
	$client_ip = 'unknown';
	foreach ($arr_ip_header as $key) {
		if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != "unknown") {
			$client_ip = $_SERVER[$key];
			break;
		}
	}
	if ($pos = strpos($client_ip,',')){
		$client_ip = substr($client_ip,$pos+1);
	}
	return $client_ip;
}

function get_url_link($url){
	if(!$url) return "";
	$res = parse_url($url);
	$port = (empty($res["port"]) || $res["port"] == '80')?'':':'.$res["port"];
	return $res['scheme']."://".$res["host"].$port.$res['path'];
}
function get_url_domain($url){
	if(!$url) return "";
	$res = parse_url($url);
	return $res["host"];
}

function get_host() {
	$protocol = (!empty($_SERVER['HTTPS'])
				 && $_SERVER['HTTPS'] !== 'off'
				 || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';

	if( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		strlen($_SERVER['HTTP_X_FORWARDED_PROTO']) > 0 ){
		$protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://';
	}
	$url_host = $_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']=='80' ? '' : ':'.$_SERVER['SERVER_PORT']);
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $url_host;
	$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $host;//proxy
	return $protocol.$host;
}
// current request url
function this_url(){
	$url = get_host().$_SERVER['REQUEST_URI'];
	return $url;
}
function reset_path($str){
	return str_replace('\\','/',$str);
}
function get_webroot($app_path=''){
	$index='index.php';
	$self_file  = reset_path($_SERVER['SCRIPT_NAME']);
	if($app_path == ''){
		$index_path = reset_path($_SERVER['SCRIPT_FILENAME']);
		$app_path = substr($index_path,0,strrpos($index_path,'/'));
		$index = substr($index_path,1+strrpos($index_path,'/'));
	}
	$fixval = $app_path;
	$webRoot = str_replace($index,'',$app_path.$index).'/';
	if(strrpos(strtolower($webRoot),strtolower(str_replace($index,'',$self_file)))){//解决我家主机不兼容问题
		$webRoot = str_replace(strtolower(str_replace($index,'',$self_file)),'',strtolower($webRoot));
	}
	if (substr($webRoot,-(strlen($index)+1)) == $index.'/') {
	//解决部分主机不兼容问题
		$webRoot = reset_path($_SERVER['DOCUMENT_ROOT']).'/';
	}


	return $webRoot;
}
function ua_has($str){
	if(!isset($_SERVER['HTTP_USER_AGENT'])){
		return false;
	}
	if(strpos($_SERVER['HTTP_USER_AGENT'],$str) ){
		return true;
	}
	return false;
}
function is_wap(){
	if(($_GET['wap'] ?? '')=='force-phone') return 1;
	if(($_GET['wap'] ?? '')=='force-pc') return 0;
	if(!isset($_SERVER['HTTP_USER_AGENT'])){
		return false;
	}
	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i',
		strtolower($_SERVER['HTTP_USER_AGENT']))){
		return true;
	}
	if((isset($_SERVER['HTTP_ACCEPT'])) &&
		(strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false)){
		return true;
	}
	return false;
}
function is_real_wap(){
	if(!isset($_SERVER['HTTP_USER_AGENT'])){
		return false;
	}
	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i',
		strtolower($_SERVER['HTTP_USER_AGENT']))){
		return true;
	}
	if((isset($_SERVER['HTTP_ACCEPT'])) &&
		(strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false)){
		return true;
	}
	return false;
}

function parse_headers($raw_headers){
	$headers = array();
	$key = '';
	foreach (explode("\n", $raw_headers) as $h) {
		$h = explode(':', $h, 2);
		if (isset($h[1])) {
			if ( ! isset($headers[$h[0]])) {
				$headers[$h[0]] = trim($h[1]);
			} elseif (is_array($headers[$h[0]])) {
				$headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])) );
			} else {
				$headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])) );
			}
			$key = $h[0];
		} else {
			if (substr($h[0], 0, 1) === "\t") {
				$headers[$key] .= "\r\n\t" . trim($h[0]);
			} elseif ( ! $key) {
				$headers[0] = trim($h[0]);
			}
			trim($h[0]);
		}
	}
	return $headers;
}

function escape($str) {
	preg_match_all ( "/[\xc2-\xdf][\x80-\xbf]+|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}|[\x01-\x7f]+/e", $str, $r );
	//匹配utf-8字符，
	$str = $r [0];
	$l = count ( $str );
	for($i = 0; $i < $l; $i ++) {
		$value = ord ( $str [$i] [0] );
		if ($value < 223) {
			$str [$i] = rawurlencode ( utf8_decode ( $str [$i] ) );
		//先将utf8编码转换为ISO-8859-1编码的单字节字符，urlencode单字节字符.
		//utf8_decode()的作用相当于iconv("UTF-8","CP1252",$v)。
		} else {
			$str [$i] = "%u" . strtoupper ( bin2hex ( iconv ( "UTF-8", "UCS-2", $str [$i] ) ) );
		}
	}
	return join ( "", $str );
}
function unescape($str) {
	$ret = '';
	$len = strlen ( $str );
	for($i = 0; $i < $len; $i ++) {
		if ($str [$i] == '%' && $str [$i + 1] == 'u') {
			$val = hexdec ( substr ( $str, $i + 2, 4 ) );
			if ($val < 0x7f)
				$ret .= chr ( $val );
			else if ($val < 0x800)
				$ret .= chr ( 0xc0 | ($val >> 6) ) . chr ( 0x80 | ($val & 0x3f) );
			else
				$ret .= chr ( 0xe0 | ($val >> 12) ) . chr ( 0x80 | (($val >> 6) & 0x3f) ) . chr ( 0x80 | ($val & 0x3f) );
			$i += 5;
		} else if ($str [$i] == '%') {
			$ret .= urldecode ( substr ( $str, $i, 3 ) );
			$i += 2;
		} else
			$ret .= $str [$i];
	}
	return $ret;
}

//多人同时上传同一个文件；或上传到多个服务;
$curlCurrentFile = false;
function curl_progress_bind($file,$uuid='',$download=false){
	if(!$GLOBALS['curlCurrentFile']){
		$cacheFile = TEMP_PATH.'/curlProgress/'.md5($file.$uuid).'.log';
		mk_dir(get_path_father($cacheFile));
		@touch($cacheFile);
		if(!file_exists($cacheFile)){
			return;
		}
		$GLOBALS['curlCurrentFile'] = array(
			'path'		 => $file,
			'uuid'		 => $uuid,
			'time'		 => 0,
			'setNum'	 => 0,
			'cacheFile'	 => $cacheFile,
			'download' 	 => $download
		);
	}
	curl_progress_set(false,0,0,0,0);
}
function curl_progress_set(){
	$fileInfo = $GLOBALS['curlCurrentFile'];
	$file = $fileInfo['path'];
	$cacheFile = $fileInfo['cacheFile'];
	if( !is_array($fileInfo) ||
		mtime() - $fileInfo['time'] <= 0.3){//每300ms做一次记录
		return;
	}
	//进度文件被删除则终止传输;
	clearstatcache();
	if( !file_exists($cacheFile) ||
		!file_exists($file) ){
		exit;
	}

	$GLOBALS['curlCurrentFile']['time'] = mtime();
	$GLOBALS['curlCurrentFile']['setNum'] += 1;
	$args = func_get_args();
	if (is_resource($args[0])) {// php 5.5
		array_shift($args);
	}
	$downTotal = $args[0];
	$downSize = $args[1];
	$upTotal = $args[2];
	$upSize = $args[3];

	//默认上传
	$size = @filesize($file);
	$sizeSuccess = $upSize;
	if($fileInfo['download']){
		$size = $downTotal;
		$sizeSuccess = $downSize;
	}
	$json = array(
		'name'			=> substr(rawurlencode(get_path_this($file)),-10),
		'taskUuid'		=> $fileInfo['uuid'],
		'type'		 	=> $fileInfo['download']?'fileDownload':'fileUpload',
		'timeStart' 	=> time(),

		'sizeTotal'		=> $size,
		'sizeSuccess'	=> $sizeSuccess,
		'progress'	 	=> 0,
		'timeUse'	 	=> 0,
		'timeNeed'		=> 0,
		'speed'			=> 0,
		'logList'		=> array()
	);
	//write_log(array($args,$size,$sizeSuccess),'ttt');
	if(time() - filemtime($cacheFile) <= 10){//10s内才处理;同一个文件
		$data = @json_decode(file_get_contents($cacheFile),true);
		$json = $data?$data:$json;
	}else{
		del_file($cacheFile);
		touch($cacheFile);
	}

	//更新数据
	$logList = &$json['logList'];
	if(count($logList) >=10 ){
		$logList = array_slice($logList,-10);
	}

	$current = array('time'=>time(),'sizeSuccess'=>$sizeSuccess);
	if(count($logList) == 0){
		$logList[] = $current;
	}else{
		$last = $logList[count($logList)-1];
		if(time() == $last['time']){
			$logList[count($logList)-1] = $current;
		}else{
			$logList[] = $current;
		}
	}

	//计算速度
	$first = $logList[0];
	$last  = $logList[count($logList)-1];
	$time  = $last['time'] - $first['time'];
	$speed = $time?($last['sizeSuccess'] - $first['sizeSuccess'])/$time : 0;
	if($speed <0 || $speed>500*1024*1024){
		$speed = 0;
	}
	$timeNeed = $speed ? ($size - $sizeSuccess)/$speed:0;
	$progress = 0;
	if($size != 0 ){
		$progress  = ($sizeSuccess>=$size)?1:$sizeSuccess/$size;
	}
	$json['sizeTotal']  	= $size;
	$json['sizeSuccess']	= $sizeSuccess;
	$json['progress'] 		= $progress;
	$json['timeUse']  		= time() - $json['timeStart'];
	$json['timeNeed'] 		= intval($timeNeed);
	$json['speed'] = intval($speed);
	file_put_contents($cacheFile,json_encode($json));
}
function curl_progress_get($file,$uuid=''){
	$cacheFile = TEMP_PATH.'/curlProgress/'.md5($file.$uuid).'.log';
	if(!file_exists($cacheFile) || $file == ''){
		return -1;
	}
	$data = @json_decode(file_get_contents($cacheFile),true);
	if(is_array($data)){
		unset($data['logList']);
		return $data;
	}
	return -3;
}

// https://segmentfault.com/a/1190000000725185
// http://blog.csdn.net/havedream_one/article/details/52585331
// php7.1 curl上传中文路径文件失败问题？【暂时通过重命名方式解决】
function url_request($url,$method='GET',$data=false,$headers=false,$options=false,$json=false,$timeout=3600){
	ignore_timeout();
	$ch = curl_init();
	$upload = false;
	if(is_array($data)){//上传检测并兼容
		foreach($data as $key => &$value){
			if(!is_string($value) || substr($value,0,1) !== "@"){
				continue;
			}
			$upload = true;
			$path = ltrim($value,'@');
			$filename = iconv_app(get_path_this($path));
			$mime = get_file_mime(get_path_ext($filename));
			if(isset($data['curlUploadName'])){//自定义上传文件名;临时参数
				$filename = $data['curlUploadName'];
				unset($data['curlUploadName']);
			}
			if (class_exists('\CURLFile')){
				$value = new CURLFile(realpath($path),$mime,$filename);
			}else{
				$value = "@".realpath($path).";type=".$mime.";filename=".$filename;
			}
			//有update且method为PUT
			if($method == 'PUT'){
				curl_setopt($ch, CURLOPT_PUT,1);
				curl_setopt($ch, CURLOPT_INFILE,@fopen($path,'r'));
				curl_setopt($ch, CURLOPT_INFILESIZE,@filesize($path));
			}

			//上传进度记录并处理
			curl_progress_bind($path);
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION,'curl_progress_set');
		}
	}
	if($upload){
		if (class_exists('\CURLFile')){
			curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
		} else {
			if (defined('CURLOPT_SAFE_UPLOAD')) {
				curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
			}
		}
	}

	// post数组或拼接的参数；不同方式服务器兼容性有所差异
	// http://blog.csdn.net/havedream_one/article/details/52585331
	if ($data && is_array($headers) && $method != 'DOWNLOAD' &&
		in_array('Content-Type: application/x-www-form-urlencoded',$headers)) {
		$data = http_build_query($data);
	}
	if($method == 'GET' && $data){
		if(strstr($url,'?')){
			$url = $url.'&'.$data;
		}else{
			$url = $url.'?'.$data;
		}
	}
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_REFERER,get_url_link($url));
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.94 Safari/537.36');
	if($headers){
		if(is_string($headers)){
			$headers = array($headers);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}

	switch ($method) {
		case 'GET':
			curl_setopt($ch,CURLOPT_HTTPGET,1);
			break;
		case 'DOWNLOAD':
			//远程下载到指定文件；进度条
			$downTemp = $data.'.'.rand_string(5);
			$fp = fopen ($downTemp,'w+');
			curl_progress_bind($downTemp,'',true);//下载进度
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION,'curl_progress_set');

			curl_setopt($ch,CURLOPT_HTTPGET,1);
			curl_setopt($ch, CURLOPT_HEADER,0);//不输出头
			curl_setopt($ch, CURLOPT_FILE, $fp);
			//CURLOPT_RETURNTRANSFER 必须放在CURLOPT_FILE前面;否则出问题
			break;
		case 'HEAD':
			curl_setopt($ch, CURLOPT_NOBODY, true);
			break;
		case 'POST':
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			break;
		case 'OPTIONS':
		case 'PATCH':
		case 'DELETE':
		case 'PUT':
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$method);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			break;
		default:break;
	}

	if(!empty($options)){
		curl_setopt_array($ch, $options);
	}
	$response = curl_exec($ch);
	$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
	$response_info = curl_getinfo($ch);
	$http_body   = substr($response, $header_size);
	$http_header = substr($response, 0, $header_size);
	$http_header = parse_headers($http_header);
	if(is_array($http_header)){
		$http_header['roj_add_request_url'] = $url;
	}
	//error
	if($response_info['http_code'] == 0){
		$error_message = curl_error($ch);
		if (! empty($error_message)) {
			$error_message = "API call to $url failed;$error_message";
		} else {
			$error_message = "API call to $url failed;maybe network error!";
		}
		return array(
			'data'		=> $error_message,
			'code'		=> 0,
			'header'	=> $response_info,
		);
	}

	curl_close($ch);
	if(is_array($GLOBALS['curlCurrentFile'])){
		@unlink($GLOBALS['curlCurrentFile']['cacheFile']);
	}
	$success = $response_info['http_code'] >= 200 && $response_info['http_code'] <= 299;
	if( $json && $success){
		$data = @json_decode($http_body,true);
		if (json_last_error() == 0) { // string
			$http_body = $data;
		}
	}
	if($method == 'DOWNLOAD'){
		@fclose($fp);
		@clearstatcache();
		if($success){
			move_path($downTemp,$data);
		}
		@unlink($downTemp);
	}

	$return = array(
		'data'		=> $http_body,
		'status'	=> $success,
		'code'		=> $response_info['http_code'],
		'header'	=> $http_header,
	);
	return $return;
}


function get_headers_curl($url,$timeout=30,$depth=0,&$headers=array()){
	if(!function_exists('curl_init')){
		return false;
	}
	if ($depth >= 10) return false;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER,true);
	curl_setopt($ch, CURLOPT_NOBODY,true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_REFERER,get_url_link($url));
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.94 Safari/537.36');

	$res = curl_exec($ch);
	$res = explode("\r\n", $res);

	$location = false;
	foreach ($res as $line) {
		list($key, $val) = explode(": ", $line, 2);
		$the_key = trim($key);
		if($the_key == 'Location' || $the_key == 'location'){
			$the_key = 'Location';
			$location = trim($val);
		}
		if( strlen($the_key) == 0 &&
			strlen(trim($val)) == 0  ){
			continue;
		}
		if( substr($the_key,0,4) == 'HTTP' &&
			strlen(trim($val)) == 0  ){
			$headers[] = $the_key;
			continue;
		}

		if(!isset($headers[$the_key])){
			$headers[$the_key] = trim($val);
		}else{
			if(is_string($headers[$the_key])){
				$temp = $headers[$the_key];
				$headers[$the_key] = array($temp);
			}
			$headers[$the_key][] = trim($val);
		}
	}
	if($location !== false){
		$depth++;
		get_headers_curl($location,$timeout,$depth,$headers);
	}
	return count($headers)==0?false:$headers;
}

// 防止CSRF 攻击;curl,file_get_contents前检测url;
function request_url_safe($url){
	$link = trim(strtolower($url));
	$link = str_replace('\\','/',$link);
	while (strstr($link,'../')) {
		$link = str_replace('../', '/', $link);
	}
	if( substr($link,0,6) != "ftp://" &&
		substr($link,0,7) != "http://" &&
		substr($link,0,8) != "https://" ){
		return false;
	}
	return true;
}

// url header data
function url_header($url){
	$name = '';$length=0;
	$header = get_headers_curl($url);//curl优先
	if(is_array($header)){
		$header['ACTION_BY'] = 'get_headers_curl';
	}else{
		$header = @get_headers($url,true);
	}

	if (!$header) return false;
	if(isset($header['Content-Length'])){
		if(is_array($header['Content-Length'])){
			$length = array_pop($header['Content-Length']);
		}else{
			$length = $header['Content-Length'];
		}
	}

	//301跳转
	$fileUrl = $url;
	$location = 'Location';
	if(!isset($header['Location']) &&
		isset($header['location'])){
		$location = 'location';
	}
	if(isset($header[$location])){
		if(is_string($header[$location])){
			$fileUrl = $header[$location];
		}else if(is_array($header[$location])  && count($header[$location])>0 ){
			$fileUrl = $header[$location][count($header[$location])-1];
		}
	}

	if(isset($header['Content-Disposition'])){
		if(is_array($header['Content-Disposition'])){
			$dis = array_pop($header['Content-Disposition']);
		}else{
			$dis = $header['Content-Disposition'];
		}
		$i = strpos($dis,"filename=");
		if($i!== false){
			$name = substr($dis,$i+9);
			$j = strpos($name,"; ");//多个参数，
			if($j!== false){
				$name = substr($name,0,$j);
			}
			$name = trim($name,'"');
		}
	}
	if(isset($header['X-OutFileName'])){
		$name = $header['X-OutFileName'];
	}
	if(!$name){
		$name = get_path_this($fileUrl);
		if (stripos($name,'?')) $name = substr($name,0,stripos($name,'?'));
		if (!$name) $name = 'index.html';

		$firstName = get_path_this($url);
		if( get_path_ext($firstName) == get_path_ext($name) ){
			$name = $firstName;
		}
	}
	$name = rawurldecode($name);
	$name = str_replace(array('/','\\'),'-',$name);//safe;
	$supportRange = isset($header["Accept-Ranges"])?true:false;
	if(!request_url_safe($fileUrl)){
		$fileUrl = "";
	}
	$result = array(
		'url' 		=> $fileUrl,
		'length' 	=> $length,
		'name' 		=> trim($name,'"'),
		'supportRange' =>$supportRange && ($length!=0),
		'all'		=> $header,
	);
	if(!function_exists('curl_init')){
		$result['supportRange'] = false;
	}
	//debug_out($url,$header,$result);
	return $result;
}


// check url if can use
function check_url($url){
	$array = get_headers($url,true);
	$error = array('/404/','/403/','/500/');
	foreach ($error as $value) {
		if (preg_match($value, $array[0])) {
			return false;
		}
	}
	return true;
}

// refer URL
function refer_url(){
	return isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
}

function select_var($array){
	if (!is_array($array)) return -1;
	ksort($array);
	$chosen = -1;
	foreach ($array as $k => $v) {
		if (isset($v)) {
			$chosen = $v;
			break;
		}
	}
	return $chosen;
}

/**
 * 解析url获得url参数
 * @param $query
 * @return array array
 */
function parse_url_query($url){
	$arr = parse_url($url);
	$queryParts = explode('&',$arr['query']);
	$params = array();
	foreach ($queryParts as $param) {
		$item = explode('=', $param);
		$params[$item[0]] = $item[1];
	}
	return $params;
}

function stripslashes_deep($value){
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : (isset($value) ? stripslashes($value) : null);
	return $value;
}

function parse_url_route(){
	$param = str_replace($_SERVER['SCRIPT_NAME'],"",$_SERVER['PHP_SELF']);
	if($param && substr($param,0,1) == '/'){
		$arr = explode('&',$param);
		$arr[0] = ltrim($arr[0],'/');
		foreach ($arr as  $cell) {
			$cell = explode('=',$cell);
			if(is_array($cell)){
				if(!isset($cell[1])){
					$cell[1] = '';
				}
				$_GET[$cell[0]] = $cell[1];
				$_REQUEST[$cell[0]] = $cell[1];
			}
		}
	}
}


/**
 * GET/POST数据统一入口
 * 将GET和POST的数据进行过滤，去掉非法字符以及hacker code，返回一个数组
 * 注意如果GET和POST有相同的Key，POST优先
 *
 * @return array $_GET和$_POST数据过滤处理后的值
 */
function parse_incoming(){
	parse_url_route();
	global $_GET, $_POST,$_COOKIE;

	$_COOKIE = stripslashes_deep($_COOKIE);
	$_GET	 = stripslashes_deep($_GET);
	$_POST	 = stripslashes_deep($_POST);
	$return = array();
	$return = array_merge($_GET,$_POST);
	$remote = array_get_index($return,0);
	$remote = explode('/',trim($remote[0],'/'));
	$return['URLremote'] = $remote;
	return $return;
}

function db_escape($str) {
	$str = addslashes($str);
	$str = str_replace(array('_', '%'),array('\\_', '\\%'), $str);
	return $str;
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * in('id',0); 获取id参数 自动判断get或者post
 * in('post.name','','htmlspecialchars'); 获取$_POST['name']
 * in('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @return mixed
 */
function in($name,$default='',$filter=null) {
	$default_filter = 'htmlspecialchars,db_escape';
	if(strpos($name,'.')) { // 指定参数来源
		list($method,$name) = explode('.',$name,2);
	}else{ // 默认为自动判断
		$method = 'request';
	}
	switch(strtolower($method)) {
		case 'get'     :   $input =& $_GET;break;
		case 'post'    :   $input =& $_POST;break;
		case 'request' :   $input =& $_REQUEST;   break;

		case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
		case 'session' :   $input =& $_SESSION;   break;
		case 'cookie'  :   $input =& $_COOKIE;    break;
		case 'server'  :   $input =& $_SERVER;    break;
		// case 'globals' :   $input =& $GLOBALS;    break;
		default:return NULL;
	}
	$filters = isset($filter)?$filter:$default_filter;
	if($filters) {
		$filters = explode(',',$filters);
	}
	if(empty($name)) { // 获取全部变量
		$data = $input;
		foreach($filters as $filter){
			$data = array_map($filter,$data); // 参数过滤
		}
	}elseif(isset($input[$name])) { // 取值操作
		$data =	$input[$name];
		foreach($filters as $filter){
			if(function_exists($filter)) {
				$data = is_array($data)?array_map($filter,$data):$filter($data); // 参数过滤
			}else{
				$data = filter_var($data,is_int($filter)?$filter:filter_id($filter));
				if(false === $data) {
					return isset($default)?$default:NULL;
				}
			}
		}
	}else{ // 变量默认值
		$data = isset($default)?$default:NULL;
	}
	return $data;
}


function url2absolute($index_url, $preg_url){
	if (preg_match('/[a-zA-Z]*\:\/\//', $preg_url)) return $preg_url;
	preg_match('/([a-zA-Z]*\:\/\/.*)\//', $index_url, $match);
	$index_url_temp = $match[1];

	foreach(explode('/', $preg_url) as $key => $var) {
		if ($key == 0 && $var == '') {
			preg_match('/([a-zA-Z]*\:\/\/[^\/]*)\//', $index_url, $match);
			$index_url_temp = $match[1] . $preg_url;
			break;
		}
		if ($var == '..') {
			preg_match('/([a-zA-Z]*\:\/\/.*)\//', $index_url_temp, $match);
			$index_url_temp = $match[1];
		} elseif ($var != '.') $index_url_temp .= '/' . $var;
	}
	return $index_url_temp;
}

// 输出js
function exec_js($js){
	echo "<script language='JavaScript'>\n" . $js . "</script>\n";
}
// 禁止缓存
function no_cache(){
	header("Cache-Control:no-cache\r\n");
	header("Cache-Control:no-cache\r\n");
	header("Expires:0\r\n");
}
// 生成javascript转向
function go_url($url, $msg = ''){
	header("Content-type: text/html; charset=utf-8\r\n");
	echo "<script type='text/javascript'>\n";
	echo "window.location.href='$url';";
	echo "</script>\n";
	exit;
}

function send_http_status($i_status, $s_message = ''){
	$a_status = array(
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',
		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy', // 306 is deprecated but reserved
		307 => 'Temporary Redirect',
		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
		);

	if (array_key_exists($i_status, $a_status)) {
		header('HTTP/1.1 ' . $i_status . ' ' . $a_status[$i_status]);
	}
	if ($s_message) {
		echo $s_message;
		exit();
	}
}

//是否是windows
function client_is_windows(){
	static $is_windows;
	if(!is_array($is_windows)){
		$is_windows = array(0);
		$os = get_os();
		if(strstr($os,'Windows')){
			$is_windows = array(1);
		}
	}
	return $is_windows[0];
}

// 获取操作系统信息 TODO
function get_os (){
	$agent = $_SERVER['HTTP_USER_AGENT'];
	$preg_find = array(
		"Windows 95"	=>array('win','95'),
		"Windows ME"	=>array('win 9x','4.90'),
		"Windows 98"	=>array('win','98'),
		"Windows 2000"	=>array('win','nt 5.0',),
		"Windows XP"	=>array('win','nt 5.1'),
		"Windows Vista"	=>array('win','nt 6.0'),
		"Windows 7"		=>array('win','nt 6.1'),
		"Windows 32"	=>array('win','32'),
		"Windows NT"	=>array('win','nt'),
		"Mac OS"		=>array('Mac OS'),
		"Linux"			=>array('linux'),
		"Unix"			=>array('unix'),
		"SunOS"			=>array('sun','os'),
		"IBM OS/2"		=>array('ibm','os'),
		"Macintosh"		=>array('Mac','PC'),
		"PowerPC"		=>array('PowerPC'),
		"AIX"			=>array('AIX'),
		"HPUX"			=>array('HPUX'),
		"NetBSD"		=>array('NetBSD'),
		"BSD"			=>array('BSD'),
		"OSF1"			=>array('OSF1'),
		"IRIX"			=>array('IRIX'),
		"FreeBSD"		=>array('FreeBSD'),
	);

	$os='';
	foreach ($preg_find as $key => $value) {
		if(count($value)==1 && stripos($agent,$value[0])){
			$os=$key;break;
		}else if(count($value)==2
				 && stripos($agent,$value[0])
				 && stripos($agent,$value[1])
				 ){
			$os=$key;break;
		}
	}
	if ($os=='') {$os = "Unknown"; }
	return $os;
}

// 浏览器是否直接打开
function mime_support($mime){
	$arr_start = array(
		"text/",
		"image/",
		"audio/",
		"video/",
		"message/",
	);
	$arr_mime = array(
		"application/hta",
		"application/javascript",
		"application/json",
		"application/x-latex",
		"application/pdf",
		"application/x-shockwave-flash",
		"application/x-tex",
		"application/x-texinfo"
	);
	if(in_array($mime,$arr_mime)){
		return true;
	}
	foreach ($arr_start as $val) {
		if(substr($mime,0,strlen($val)) == $val){
			return true;
		}
	}
	return false;
}

//根据扩展名获取mime
function get_file_mime($ext){

	//代码 或文本浏览器输出
	$text = array(/*'oexe','inc','inf','csv','log','asc','tsv'*/);
	$code = array(/*"abap","abc","as","ada","adb","htgroups","htpasswd","conf","htaccess","htgroups",
				"htpasswd","asciidoc","asm","ahk","bat","cmd","c9search_results","cpp","c","cc","cxx","h","hh","hpp",
				"cirru","cr","clj","cljs","CBL","COB","coffee","cf","cson","Cakefile","cfm","cs","css","curly","d",
				"di","dart","diff","patch","Dockerfile","dot","dummy","dummy","e","ejs","ex","exs","elm","erl",
				"hrl","frt","fs","ldr","ftl","gcode","feature",".gitignore","glsl","frag","vert","go","groovy",
				"haml","hbs","handlebars","tpl","mustache","hs","hx","html","htm","xhtml","erb","rhtml","ini",
				"cfg","prefs","io","jack","jade","java","js","jsm","json","jq","jsp","jsx","jl","tex","latex",
				"ltx","bib","lean","hlean","less","liquid","lisp","ls","logic","lql","lsl","lua","lp","lucene",
				"Makefile","GNUmakefile","makefile","OCamlMakefile","make","md","markdown","mask","matlab",
				"mel","mc","mush","mysql","nc","nix","m","mm","ml","mli","pas","p","pl","pm","pgsql","php","phtml",
				"ps1","praat","praatscript","psc","proc","plg","prolog","properties","proto","py","r","Rd",
				"Rhtml","rb","ru","gemspec","rake","Guardfile","Rakefile","Gemfile","rs","sass","scad","scala",
				"scm","rkt","scss","sh","bash",".bashrc","sjs","smarty","tpl","snippets","soy","space","sql",
				"styl","stylus","svg","tcl","tex","txt","textile","toml","twig","ts","typescript","str","vala",
				"vbs","vb","vm","v","vh","sv","svh","vhd","vhdl","xml","rdf","rss","log",
				"wsdl","xslt","atom","mathml","mml","xul","xbl","xaml","xq","yaml","yml","htm",
				"xib","storyboard","plist","csproj"*/);

	if(in_array($ext,$text) || in_array($ext,$code)){
		return "text/plain";
	}else{
		return getMime($ext);
	}

}
