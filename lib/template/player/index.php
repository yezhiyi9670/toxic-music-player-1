<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	load_css('css/player/player','wc',VERSION,'playerColoredCss');

	if(is_wap()) {
		echo '<style>.lrc-content{font-size:'.setting_gt('wap-font-size','15').'px!important;}</style>';
	}
?>
<script>
var home="<?php echo BASIC_URL ?>";

var isPreviewPlayer=<?php echo isset($_GET['preview']) ? 'true' : 'false' ?>;

var data=<?php echo parseCmpLyric(cid()) ?>;
var baseurl="<?php echo BASIC_URL.cid() ?>";
var song_id="<?php echo cid() ?>";

var curr_date_str = "<?php echo date('Ymd',time()); ?>";

var src1="<?php echo getAudioUrl(cid()) ?>";
var src2="<?php echo getAudioUrl(cid(),"back","background") ?>";

var isCloudSave = <?php echo ($internal = (isset($GLOBALS['listname'])))?'true':'false'; ?>;
var isList=<?php echo ((isset($_GET['list']) || $internal) ? "true":"false") ?>;

<?php if(!$internal) { ?>
var isRand=<?php echo (isset($_GET['randList']) ? "true":"false") ?>;
var isRandShuffle=<?php echo (isset($_GET['randShuffle']) ? "true":"false") ?>;

var list=[
	<?php
		$isInvalid = [];
		$otherList = explode('|',$_GET['list'] ?? '');

		echo '"'.cid().'"';
		if(isset($_GET['list'])){
			for($i=0;$i<count($otherList);$i++) {
				echo ",";
				echo '"'.$otherList[$i].'"';
				$isInvalid[$otherList[$i]] = !_checkPermission('music/index',$otherList[$i]);
			}
		}
	?>
];
var listMeta=[
	<?php
		$meta_arr = GCM();
		if(!isValidMusic(cid()) || !_checkPermission('music/audio/out')) {
			$meta_arr['cant_play'] = true;
		}
		echo encode_data_html($meta_arr);
		if(isset($_GET['list'])){
			for($i=0;$i<count($otherList);$i++) {
				echo ",";
				if($isInvalid[$otherList[$i]]) {
					echo encode_data_html(null);
				} else {
					$meta_arr = GSM($otherList[$i]);
					if(!isValidMusic($otherList[$i]) || !_checkPermission('music/audio/out',$otherList[$i])) {
						$meta_arr['cant_play'] = true;
					}
					echo encode_data_html($meta_arr);
				}
			}
		}
	?>
];
var listName=[
	<?php
		echo '"';
		printPlayerList(cid());
		echo '"';
		if(isset($_GET['list'])){
			for($i=0;$i<count($otherList);$i++) {
				echo ",";
				$curr=$otherList[$i];
				echo '"';
				if($isInvalid[$otherList[$i]]) {
					printPlayerList($curr,false,true);
				} else {
					printPlayerList($curr,false);
				}
				echo '"';
			}
		}
	?>
];
<?php } else { ?>
<?php
	$listdata = readPlaylistData($GLOBALS['listname'],$GLOBALS['listid']);
?>
var cloudData = <?php echo encode_data_html($listdata) ?>;

var isRand = <?php echo ($listdata['transform']['pick']=='rand')?'true':'false'; ?>;
var isRandShuffle = <?php echo ($listdata['transform']['random_shuffle'])?'true':'false'; ?>;

var list=[
	<?php
		$isInvalid = [];

		$otherList=[];
		foreach($listdata['playlist'] as $item) {
			$otherList[count($otherList)] = $item['id'];
		}
		for($i=0;$i<count($otherList);$i++) {
			if($i) echo ",";
			echo '"'.$otherList[$i].'"';
			$isInvalid[$otherList[$i]] = !_checkPermission('music/index',$otherList[$i]);
		}
	?>
];

var listMeta=[
	<?php
		for($i=0;$i<count($otherList);$i++) {
			if($i) echo ",";
			if($isInvalid[$otherList[$i]]) {
				echo encode_data_html(null);
			} else {
				$meta_arr = GSM($otherList[$i]);
				if(!isValidMusic($otherList[$i]) || !_checkPermission('music/audio/out',$otherList[$i])) {
					$meta_arr['cant_play'] = true;
				}
				echo encode_data_html($meta_arr);
			}
		}
	?>
];

var listName=[
	<?php
		for($i=0;$i<count($otherList);$i++) {
			if($i) echo ",";
			$curr=$otherList[$i];
			echo '"';
			if($isInvalid[$otherList[$i]]) {
				printPlayerList($curr,true,true);
			} else {
				printPlayerList($curr,true);
			}
			echo '"';
		}
	?>
];

var cloudId = '<?php echo $GLOBALS['listid']; ?>';
var cloudUser = '<?php echo $GLOBALS['listname']; ?>';

var myRating = [];

for(var i=0;i<cloudData['playlist'].length;i++) {
	myRating[cloudData['playlist'][i]['id']] = cloudData['playlist'][i]['rating'];
}

var myIds = [];

for(var i=0;i<cloudData['playlist'].length;i++) {
	myIds[cloudData['playlist'][i]['id']] =
		cloudData['playlist'][i]['canonical']?
			cloudData['playlist'][i]['canonical']
			:cloudData['playlist'][i]['id'];
}

var isCsv = <?php $is_csv = uauth_has_data($GLOBALS['listname'],'playlist',$GLOBALS['listid'].'.csv'); echo $is_csv ? 'true' : 'false'; ?>;

<?php } ?>

var ordlist={};
for(var i=0;i<list.length;i++) {
	ordlist[i]=list[i];
}
ordlist.length=list.length;
if(isRandShuffle) {
	var listTmp=random_shuffle(list,listName);
	list=listTmp.first;
	listName=listTmp.second;
}

var fmRandId='<?php echo $_GET['fmid'] ?? '' ?>';
var isFmSave=<?php echo isset($_GET['fmid'])?'true':'false' ?>;
</script>
<script src="<?php echo BASIC_URL ?>static/js/player/playerflex.js?v=<?php echo VERSION ?>"></script>
<script src="<?php echo BASIC_URL ?>static/js/player/playerapp.js?v=<?php echo VERSION ?>"></script>
<script>
	document.title="<?php echo jsspecial(LNG('player.title')) ?> ‹ <?php echo jsspecial(GCM()['N']) ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	set_section_name(LNG('player.title'));
</script>
<script>
	titleformat="<?php echo jsspecial(LNG('player.title')) ?> ‹ %{list_name} - <?php echo jsspecial(_CT('app_name_title')) ?>";
</script>

<div class="txmp-page-left lrc-area txmp-wappage-lrc">
	<div class="lrc-overview">
		<?php tpl('player/lyric_overview') ?>
	</div>
	<div class="lrc-content" style="height:32px;">
		<div class="lrc-content__wrapperin">
			<?php tpl('player/lyric_content') ?>
		</div>
		<div class="para" style="height:calc(50% - 4px)">
		</div>
	</div>
</div>

<?php // 这是移动端播放器的封面页面 -------------------------------------------------- ?>
<?php if(is_wap()) { ?>
<div class="txmp-page-left cover-area txmp-wappage-cover" style="display: none;">
	<div class="txmp-coverpage-flex"></div>
	<div class="txmp-coverpage-pic-container" style="text-align: center;">
		<img class="txmp-coverpage-pic shadowed-intense" src="<?php echo 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' ?>" style="width: 10086px;" />
	</div>
	<div class="txmp-coverpage-flex"></div>
	<div class="txmp-coverpage-title-line">
		<p class="txmp-coverpage-title-line-title"></p>
		<p class="txmp-coverpage-title-line-singer"></p>
	</div>
	<div class="txmp-coverpage-lrc-line">
		<p class="txmp-coverpage-lrc-line-current wcl-scrollable" data-wcl-overscroll="32">
			<span class="txmp-coverpage-lrc-line-current-i wcl-scrollable-i"></span>
		</p>
	</div>
	<div class="txmp-coverpage-flex"></div>
	<div class="txmp-coverpage-next-line">
		<p class="next-song-label"></p>
	</div>
	<div class="txmp-coverpage-flex"></div>
</div>
<?php } ?>

<?php // 这是快捷控制按钮 -------------------------------------------------- ?>
<div class="lyric-controls" style="right: -100; top: 0;">
	<div class="lyric-controls__wrapperin">
		<a class="float-btn float-btn-active shadowed" id="sync-button" onclick="roll_toggle(!S); if(S)highlight_lyric(1);">
			<span class="fa fa-location-arrow" id="sync-ico"></span>
		</a>
		<div class="am-dropdown am-dropdown-up" style="width:36px;height:36px;" data-am-dropdown id="volume-div">
			<a class="am-dropdown-toggle float-btn float-btn-active shadowed" id="volume-button">
				<span class="fa fa-volume-up" id="volume-ico"></span>
			</a>
			<ul class="am-dropdown-content volume-select-list" onclick="$('.am-dropdown').dropdown('close')">
				<li class="am-dropdown-header"><?php LNGe('player.menu.volume') ?></li>
				<li><a class="volume-choice" onclick="setVolume(0)">0.00</a></li>
				<li><a class="volume-choice" onclick="setVolume(0.2)">0.20</a></li>
				<li><a class="volume-choice" onclick="setVolume(0.4)">0.40</a></li>
				<li><a class="volume-choice" onclick="setVolume(0.6)">0.60</a></li>
				<li><a class="volume-choice" onclick="setVolume(0.8)">0.80</a></li>
				<li><a class="volume-choice" onclick="setVolume(1)">1.00</a></li>
				<li><a class="volume-choice volume-choice-custom" onclick="setVolumeCustom()"><?php LNGe('player.menu.volume.custom') ?></a></li>
			</ul>
		</div>
		<div class="am-dropdown am-dropdown-up" style="width:36px;height:36px;" data-am-dropdown id="speed-div">
			<a class="am-dropdown-toggle float-btn shadowed" id="speed-button">
				<span class="fa fa-forward" id="speed-ico"></span>
			</a>
			<ul class="am-dropdown-content speed-select-list" onclick="$('.am-dropdown').dropdown('close')">
				<li class="am-dropdown-header"><?php LNGe('player.menu.speed') ?></li>
				<li><a class="speed-choice" onclick="setPlayRate(0.5)">0.5x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(0.94)">0.94x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(1)">1.0x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(1.06)">1.06x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(1.26)">1.26x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(1.5)">1.5x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(2)">2.0x</a></li>
				<li><a class="speed-choice" onclick="setPlayRate(3)">3.0x</a></li>
				<li><a class="speed-choice speed-choice-custom" onclick="setPlayRateCustom()"><?php LNGe('player.menu.speed.custom') ?></a></li>
				<li class="am-divider"></li>
				<li class="am-dropdown-header"><?php LNGe('player.menu.speed.mode') ?></li>
				<li><a class="speed-preserve-pitch" onclick="togglePreservePitch()"><?php LNGe('player.menu.pitch.on') ?></a></li>
			</ul>
		</div>
		<a class="float-btn shadowed" id="skip-button" onclick="switchNext()" oncontextmenu="switchNext(true)">
			<span class="fa fa-arrow-right" id="skip-ico"></span>
		</a>
		<?php if(is_wap()) { ?>
			<a class="float-btn shadowed" id="coverpage-button" onclick="wapSwitchPage()">
				<span class="fa fa-align-left" id="coverpage-ico"></span>
			</a>
		<?php } ?>
	</div>
</div>

<?php if(isset($_GET['iframe'])) { ?>
	<a class="float-btn shadowed float-btn-active" id="folder-button" onclick="rmenu_show()">
		<span class="fa fa-folder-open" id="folder-ico"></span>
	</a>
<?php } ?>

<div class="txmp-page-right pr-player">
	<div style="display:none;" id="downloader-link">

	</div>
	<div id="right-container">
		<div class="right-first-row" style="<?php if(!is_wap()) echo 'margin-bottom:16px;' ?>">
			<?php tpl("player/firstrow") ?>
		</div>
		<div class="right-second-row">
			<audio
				id="audio_1"
				preload="none"
				hidden="true"
				src="<?php echo getAudioUrl(cid()) ?>"
				<?php echo (($_GET['autoplay'] ?? '')=='y') ? 'autoplay="autoplay"':"" ?> >
				<?php LNGe('player.ancient_browser') ?>
			</audio><audio
				id="audio_2"
				preload="none"
				hidden="true">
				<?php LNGe('player.ancient_browser') ?>
			</audio>
			<a class="fa fa-play-circle player-icon" id="play-button" href="javascript:;" onclick="play_click($(this))"></a>
			<a class="fa fa-circle-o player-icon" id="repeat-button" href="javascript:;" onclick="rep_click($(this))"></a>
			<div class="player-processbar" style="width:32px;">
				<div class="player-processbar-i" style="width:0%;"></div>
			</div>
		</div>
		<div class="right-third-row right-third-row-n">
			<?php tpl("player/thirdrow-n") ?>
			<?php tpl("player/thirdrow") ?>
		</div>
	</div>
</div>

<div id="right-menu-overlay" style="display:none;opacity:0" onclick="rmenu_hide()"></div>
<div id="right-menu" style="display:none; right: 0; width: <?php echo is_wap()?'100%':'480px' ?>; margin-right:calc(-<?php echo is_wap()?'100%':'480px' ?> - 8px)">
	<?php
		tpl("player/menu");
	?>
</div>

<script>
	$(() => {$('#audio_1,#audio_2').attr('preload','all');});
	$(() => {$('.txmp-coverpage-pic, .song-avatar img').attr('src', "<?php echo jsspecial(getCoverUrl(cid())) ?>")});
</script>
