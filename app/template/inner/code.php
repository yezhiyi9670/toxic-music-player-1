<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title="<?php echo addslashes(GCM()['N']) ?> > 源代码 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
</script>
<style>
div.codeblock{
	display: block;
	padding: 1rem;
	margin: 1rem 0;
	font-size: 1.3rem;
	line-height: 1.6;
	word-break: break-all;
	word-wrap: break-word;
	color: #555;
	background-color: #f8f8f8;
	border: 1px solid #dedede;
	white-space: pre-wrap;
}
div.codeblock {
	font-family: Monaco,Menlo,Consolas,"Courier New",FontAwesome,monospace;
}
</style>
<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/cdp-page.css" />

<div class="cdp-header">
	<a style="width:32px;font-size:19px;padding-right:32px;" onclick="prev_page()"><i class="fa fa-chevron-left"></i></a>
	<div id="cdp-header-text" style="display:inline-block;width:128px;font-size:19px;text-align:center">
		<span class="am-dropdown" data-am-dropdown>
			<a style="color:#000" class="am-dropdown-toggle">页面 <span id="page-now">?</span>/<span id="page-tot">?</span>：<span id="page-name">未定义页面</span></a>
			<ul class="am-dropdown-content" id="cdp-header-nav" style="overflow:auto;font-size:15px;max-height:500px;" onclick="$('.am-dropdown').dropdown('close')">
				<li class="am-dropdown-header">快速导航</li>
				<!---->
			</ul>
		</span>
	</div>
	<a style="width:32px;font-size:19px;padding-left:32px;" onclick="next_page()"><i class="fa fa-chevron-right cl-g-2"></i></a>
</div>

<?php 
$str = parseCmpLyric(preSubstr($_GET["_lnk"],"/"));
$data = json_decode($str,true);
$rdata = json_decode(parseCmpLyric(cid(),false),true);
$dbg = parseCmpLyric(preSubstr($_GET["_lnk"],"/"),true,true);
?>
<div class="cdp-page" data-cdp-name="常规文件">
	<div class="txmp-page-left">
		<h3>源文件</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(getLyricFile(preSubstr($_GET["_lnk"],"/")));
		?></div>
		<p class="footnote">
			API 方法：URL 末尾添加 <code>?raw</code> 可直接获取源文件。<br />
			例如：<code>GET <?php echo BASIC_URL . cid() . '/code?raw' ?></code>
		</p>
	</div>
	<div class="txmp-page-right">
		<h3>本文档编译后结果</h3>
		<div class="codeblock"><?php
			echo htmlspecial2($str);
		?></div>
		<p class="footnote">
			API 方法：从页面 <code>switch-all</code> 中可获得歌曲的全部元数据。<br />
			例如：<code>GET <?php echo BASIC_URL . cid() . '/switch-all' ?></code>
		</p>
	</div>
</div>

<div class="cdp-page" data-cdp-name="标准歌词文件 (LRC)">
	<div class="txmp-page-left">
		<h3>最小化歌词文件</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(buildMinifiedLrc($rdata));
		?></div>
		<p class="footnote">
			舍弃了不被大多数平台支持的段标，且用最小化的方式输出。<br />
			API 方法：URL 末尾添加 <code>?raw&lrc=minified</code> 可直接获取这份文件。<br />
			例如：<code>GET <?php echo BASIC_URL . cid() . '/code?raw&lrc=minified' ?></code>
		</p>
	</div>
	<div class="txmp-page-right">
		<h3>扩展歌词文件</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(buildExtendedLrc($rdata));
		?></div>
		<p class="footnote">
			完整地表达歌词中的绝大多数信息。<br />
			API 方法：URL 末尾添加 <code>?raw&lrc=fancy</code> 可直接获取这份文件。<br />
			例如：<code>GET <?php echo BASIC_URL . cid() . '/code?raw&lrc=fancy' ?></code>
		</p>
	</div>
</div>

<div class="cdp-page" data-cdp-name="编译信息">
	<div class="txmp-page-left">
		<h3>源文件</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(addLineNumbers(getLyricFile(preSubstr($_GET["_lnk"],"/"))));
		?></div>
	</div>
	<div class="txmp-page-right">
		<h3>编译信息</h3>
		<div class="codeblock"><?php
			if($dbg['message']) echo $dbg['message'];
			else echo "编译器没有输出任何信息。\n";
		?></div>
	</div>
</div>

<div class="cdp-page" data-cdp-name="编译/行拆">
	<div class="txmp-page-left">
		<h3>源文件</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(addLineNumbers($dbg['source']));
		?></div>
	</div>
	<div class="txmp-page-right">
		<h3>行拆表</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['ld']));
		?></div>
	</div>
</div>

<div class="cdp-page" data-cdp-name="编译/指令拆分">
	<div class="txmp-page-left">
		<h3>行拆表</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['ld']));
		?></div>
	</div>
	<div class="txmp-page-right">
		<h3>指令拆分</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['cd']));
		?></div>
	</div>
</div>

<div class="cdp-page" data-cdp-name="编译/指令分组">
	<div class="txmp-page-left">
		<h3>指令拆分</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['cd']));
		?></div>
	</div>
	<div class="txmp-page-right">
		<h3>指令分组</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['gd']));
		?></div>
	</div>
</div>

<div class="cdp-page" data-cdp-name="编译/最终编译">
	<div class="txmp-page-left">
		<h3>指令分组</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['gd']));
		?></div>
	</div>
	<div class="txmp-page-right">
		<h3>最终结果</h3>
		<div class="codeblock"><?php
			echo htmlspecial2(f_json_encode($dbg['final']));
		?></div>
	</div>
</div>

<?php if(isKuwoId(cid())) { ?>
<div class="cdp-page" data-cdp-name="爬虫缓存">
	<div class="txmp-page-left">
		<h3>爬虫缓存</h3>
		<div class="codeblock"><?php
			global $akCrawler;
			remoteEncache(cid(),'K');
			echo htmlspecial2($akCrawler[cid()]->cacheContent());
		?></div>
	</div>
	<div class="txmp-page-right">
		<h3>爬虫信息</h3>
		<div class="codeblock"><?php
			echo "抓取自：酷我音乐\n";
			echo "爬虫程序：akCrawler (by yezhiyi)\n";
			echo "参见：".GCM()['O']."\n";
			$akCrawler[cid()]->printAddition();
		?></div>
		<p class="footnote">
			爬虫缓存到期时间：<?php 
				echo date('Y/m/d H:i:s',$akCrawler[cid()]->cacheTime()+_CT('cache_expire')+_CT('timezone'));
			?>&nbsp;&nbsp;&nbsp;&nbsp;<a style="color:#999999" onclick="code_refresh_cache()"><span class="fa fa-refresh force-refresh-logo"></span>&nbsp;立即刷新</a>
		</p>
	</div>
</div>
<?php } ?>

<script src="<?php echo BASIC_URL ?>static/js/common/cdp-page.js"></script>
<script>
function code_refresh_cache() {
	if($('.force-refresh-logo').hasClass('fa-spin')) return;
	var url = '<?php echo BASIC_URL ?>' + '<?php echo cid() ?>' + '/refresh-cache';
	console.log(url);
	$('.force-refresh-logo').addClass('fa-spin');
	
	$.ajax({
		async: true,
		method: 'GET',
		url: url,
		timeout: 10000,
		success: function(){
			history.go(0);
		},
		error: function() {
			$('.force-refresh-logo').removeClass('fa-spin');
		},
	});
}
setInterval(function(){
	$('#cdp-header-text').width($('body').width() - 132);
},500);
</script>
