<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	load_css('css/player/player','wc',VERSION,'playerColoredCss');
?>
<script>
var home="<?php echo BASIC_URL ?>";

var data=<?php echo parseCmpLyric(preSubstr($_GET['_lnk'])) ?>;
var baseurl="<?php echo BASIC_URL.preSubstr($_GET['_lnk']) ?>";
var song_id="<?php echo preSubstr($_GET['_lnk']) ?>";

var curr_date_str = "<?php echo date('Ymd',time()); ?>";

var src1="<?php echo getAudioUrl(preSubstr($_GET['_lnk'])) ?>";
var src2="<?php echo getAudioUrl(preSubstr($_GET['_lnk']),"back","background") ?>";

var isCloudSave = <?php echo ($internal = (isset($GLOBALS['listname'])))?'true':'false'; ?>;
var isList=<?php echo ((isset($_GET['list']) || $internal) ? "true":"false") ?>;

<?php if(!$internal) { ?>
var isRand=<?php echo (isset($_GET['randList']) ? "true":"false") ?>;
var isRandShuffle=<?php echo (isset($_GET['randShuffle']) ? "true":"false") ?>;

var list=[
	<?php
		echo '"'.cid().'"';
		if(isset($_GET['list'])){
			$otherList=explode('|',$_GET['list']);
			for($i=0;$i<count($otherList);$i++) {
				if(!_checkPermission('music/index',$otherList[$i])) continue;
				echo ",";
				echo '"'.$otherList[$i].'"';
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
			$otherList=explode('|',$_GET['list']);
			for($i=0;$i<count($otherList);$i++) {
				if(!_checkPermission('music/index',$otherList[$i])) continue;
				echo ",";
				$curr=$otherList[$i];
				echo '"';
				printPlayerList($curr);
				echo '"';
			}
		}
	?>
];

var listMeta=[
	<?php
		echo encode_data(GCM($otherList[$i]));
		if(isset($_GET['list'])){
			$otherList=explode('|',$_GET['list']);
			for($i=0;$i<count($otherList);$i++) {
				if(!_checkPermission('music/index',$otherList[$i])) continue;
				echo ",";
				$curr=$otherList[$i];
				echo encode_data(GSM($curr));
			}
		}
	?>
];
<?php } else { ?>
<?php
	$listdata = readPlaylistData($GLOBALS['listname'],$GLOBALS['listid']);
?>
var cloudData = <?php echo encode_data($listdata) ?>;

var isRand = <?php echo ($listdata['transform']['pick']=='rand')?'true':'false'; ?>;
var isRandShuffle = <?php echo ($listdata['transform']['random_shuffle'])?'true':'false'; ?>;

var list=[
	<?php
		$otherList=[];
		foreach($listdata['playlist'] as $item) {
			$otherList[count($otherList)] = $item['id'];
		}
		for($i=0;$i<count($otherList);$i++) {
			if(!_checkPermission('music/index',$otherList[$i])) continue;
			if($i) echo ",";
			echo '"'.$otherList[$i].'"';
		}
	?>
];

var listMeta=[
	<?php
		for($i=0;$i<count($otherList);$i++) {
			if(!_checkPermission('music/index',$otherList[$i])) continue;
			if($i) echo ",";
			echo encode_data(GSM($otherList[$i]));
		}
	?>
];

var listName=[
	<?php
		for($i=0;$i<count($otherList);$i++) {
			if(!_checkPermission('music/index',$otherList[$i])) continue;
			if($i) echo ",";
			$curr=$otherList[$i];
			echo '"';
			printPlayerList($curr,true);
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

var fmRandId='<?php echo $_GET['fmid'] ?>';
var isFmSave=<?php echo isset($_GET['fmid'])?'true':'false' ?>;
</script>
<script src="<?php echo BASIC_URL ?>static/js/player/playerflex.js?v=<?php echo VERSION ?>"></script>
<script src="<?php echo BASIC_URL ?>static/js/player/playerapp.js?v=<?php echo VERSION ?>"></script>
<script>
	document.title="<?php echo htmlspecial2(GCM()['N']) ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
</script>
<script>
	titleformat="%{list_name} - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
</script>
<div class="txmp-page-left lrc-area">
	<?php tpl("player/lyric") ?>
</div>
<div class="txmp-page-right pr-player">
	<div style="display:none;" id="downloader-link">

	</div>
	<div id="right-container">
		<div class="right-first-row" style="overflow-x:visible;overflow-y:visible;white-space: <?php is_wap()?'nowrap':'' ?>;<?php if(!is_wap()) echo 'margin-bottom:16px;' ?>">
			<?php tpl("player/firstrow") ?>
		</div>
		<div class="right-second-row">
			<?php if(getPerm(cid())['music/audio/out'] || is_root()) { ?><audio
				preload="all"
				id="audio"
				hidden="true"
				src="<?php echo getAudioUrl(preSubstr($_GET['_lnk'])) ?>"
				<?php echo ($_GET['autoplay']=='y') ? 'autoplay="autoplay"':"" ?> >
				<?php LNGe('player.ancient_browser') ?>
			</audio>
			<a class="fa fa-play-circle player-icon" id="play-button" href="javascript:;" onclick="play_click($(this))"></a>
			<a class="fa fa-circle-o player-icon" id="repeat-button" href="javascript:;" onclick="rep_click($(this))"></a>
			<div class="player-processbar" style="width:32px;">
				<div class="player-processbar-i" style="width:0%;"></div>
			</div><?php } ?>
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
