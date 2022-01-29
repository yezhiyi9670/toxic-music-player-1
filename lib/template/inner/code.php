<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title="<?php echo LNGk('code.title') ?> ‹ <?php echo addslashes(GCM()['N']) ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	set_section_name(LNG('code.title'));
</script>
<?php
	load_css('css/common/cdp-page');
?>

<div class="cdp-header">
	<a style="width:32px;font-size:19px;padding-right:32px;" onclick="prev_page()"><i class="fa fa-chevron-left"></i></a>
	<div id="cdp-header-text" style="display:inline-block;width:128px;font-size:19px;text-align:center">
		<span class="am-dropdown" data-am-dropdown>
			<a style="color:#000" class="am-dropdown-toggle"><?php LNGe('page.cdp.pagedesc') ?> <span id="page-now">?</span>/<span id="page-tot">?</span><?php echo COLON ?><span id="page-name"><?php LNGe('page.cdp.pagename.null') ?></span></a>
			<ul class="am-dropdown-content" id="cdp-header-nav" style="overflow:auto;font-size:15px;max-height:500px;" onclick="$('.am-dropdown').dropdown('close')">
				<li class="am-dropdown-header"><?php LNGe('page.cdp.nav') ?></li>
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
$dbg = parseCmpLyric(preSubstr($_GET["_lnk"],"/"),true,true,'cmpi_ADD_ERROR_P');
?>
<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.generic') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.source') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(getLyricFile(preSubstr($_GET["_lnk"],"/")));
		?></div>
		<p class="footnote">
			<?php echo LNG('code.api.source') ?><br />
			<?php LNGe('code.api.forexample') ?><code>GET <?php echo BASIC_URL . cid() . '/code?raw' ?></code>
		</p>
	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.compiled') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(encode_data(json_decode($str)));
		?></div>
		<p class="footnote">
			<?php echo LNG('code.api.compiled') ?><br />
			<?php LNGe('code.api.forexample') ?><code>GET <?php echo BASIC_URL . cid() . '/raw' ?></code>
		</p>
	</div>
</div>

<?php
	$GLOBALS['lrcopt'] = [
		'delta' => clampLimit($_GET['delta'],0,0.1), // 偏移量
		'comment' => clampLimit($_GET['comment'],0.7,0.1,0,65535), // 注释展示时长
		'precision' => clampLimit($_GET['precision'],0.1,0.1,0.1,60.0), // 基准精度
	];
?>
<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.lrc') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.minlrc') ?></h3>
		<div class="codeblock shadowed"><?php
			echo addLineNumbers(buildMinifiedLrc($rdata));
		?></div>
		<p class="footnote">
			<?php LNGe('code.note.minlrc') ?><br />
			<?php echo LNG('code.api.minlrc','?raw&lrc=minified&delta=#&comment=#&precision=#') ?><br />
			<?php LNGe('code.api.forexample') ?><code>GET <?php echo BASIC_URL . cid() . '/code?raw&lrc=minified&delta=0&comment=0.7&precision=0.1' ?></code>
		</p>
	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.extlrc') ?></h3>
		<div class="codeblock shadowed"><?php
			echo addLineNumbers(buildExtendedLrc($rdata));
		?></div>
		<p class="footnote">
			<?php LNGe('code.note.extlrc') ?><br />
			<?php echo LNG('code.api.extlrc','?raw&lrc=fancy&delta=#&comment=#&precision=#') ?><br />
			<?php LNGe('code.api.forexample') ?><code>GET <?php echo BASIC_URL . cid() . '/code?raw&lrc=fancy&delta=0&comment=0.7&precision=0.1' ?></code>
		</p>
	</div>
</div>

<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.compinfo') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.source') ?></h3>
		<div class="codeblock shadowed"><?php
			echo addLineNumbers(getLyricFile(preSubstr($_GET["_lnk"],"/")));
		?></div>

	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.page.compinfo') ?></h3>
		<div class="codeblock shadowed"><?php
			echo getCompileIssueMsg2(cid(),$dbg['message']);
		?></div>

	</div>
</div>

<?php if(_CT('show_comp_process')) { ?>
<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.comp.linesep') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.source') ?></h3>
		<div class="codeblock shadowed"><?php
			echo addLineNumbers($dbg['source']);
		?></div>

	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.linesep') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['ld']));
		?></div>

	</div>
</div>

<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.comp.cmdsep') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.linesep') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['ld']));
		?></div>

	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.cmdsep') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['cd']));
		?></div>

	</div>
</div>

<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.comp.cmdcomb') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.cmdsep') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['cd']));
		?></div>

	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.cmdcomb') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['gd']));
		?></div>

	</div>
</div>

<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.comp.final') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.cmdcomb') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['gd']));
		?></div>

	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.final') ?></h3>
		<div class="codeblock shadowed"><?php
			echo htmlspecial2(f_json_encode($dbg['final']));
		?></div>

	</div>
</div>

<?php } ?>

<?php if(isKuwoId(cid())) { ?>
<div class="cdp-page" data-cdp-name="<?php LNGe('code.page.crawler') ?>">
	<div class="txmp-page-left">
		<h3><?php LNGe('code.cap.cache') ?></h3>
		<div class="codeblock shadowed"><?php
			global $akCrawler;
			remoteEncache(cid(),'K');
			echo htmlspecial2(encode_data($akCrawler[cid()]->cacheObject()));
		?></div>

	</div>
	<div class="txmp-page-right">
		<h3><?php LNGe('code.cap.rp_info') ?></h3>
		<div class="codeblock shadowed"><?php
			echo LNG('rp.info.source').COLON.LNG('rp.info.source.kuwo')."\n";
			echo LNG('rp.info.program').COLON."KuwoCrawler (Created Q4'18)\n";
			echo LNG('rp.info.refer').COLON.GCM()['O']."\n";
			$akCrawler[cid()]->printAddition();
		?></div>
		<p class="footnote">
			<?php LNGe('code.rp.cache_expire') ?><?php
				echo date('Y/m/d H:i:s',$akCrawler[cid()]->cacheTime()+_CT('cache_expire')+_CT('timezone'));
			?>&nbsp;&nbsp;&nbsp;&nbsp;<a style="color:#999999" onclick="code_refresh_cache()"><span class="fa fa-refresh force-refresh-logo"></span>&nbsp;<?php LNGe('code.rp.refresh') ?></a>
		</p>
	</div>
</div>
<?php } ?>

<?php
	load_js('js/common/cdp-page');
?>
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
