<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	$listType = '';
	if(preg_match('/^list-maker\/(\d+)$/',$_GET['_lnk'])) $listType = 'internal';
	else $listType = 'tmp';
	$intlist_arr = [];
	preg_match('/^list-maker\/(\d+)$/',$_GET['_lnk'],$intlist_arr);
	
	if($listType == 'internal') {
		$uname = uauth_username();
		$listdata = readPlaylistData($uname,$intlist_arr[1]);
	}
?>

<script>
	document.title="<?php echo LNGk('led.title') ?><?php if($listType == 'internal') echo ' < '.htmlspecial($listdata['title']) ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
</script>
<script>
//状态初始化

var isList=<?php echo ((isset($_GET['list']) || $listType == 'internal') ? "true":"false") ?>;
var isRand=<?php echo (isset($_GET['randList']) ? "true":"false") ?>;
var isRandShuffle=<?php echo (isset($_GET['randShuffle']) ? "true":"false") ?>;

var list=[
	<?php 
		$otherList=explode('|',$_GET['list']);
		if($listType == 'internal') {
			$otherList = [];
			foreach($listdata['playlist'] as $item) {
				$otherList[count($otherList)] = $item['id'];
			}
		}
		$first=true;
		for($i=0;$i<count($otherList);$i++) {
			if(!preg_match('/^(\w+)$/',$otherList[$i])) continue;
			if(!_checkPermission('music/index',$otherList[$i])) continue;
			if($first) $first=false;
			else echo ",\n";
			echo '"'.$otherList[$i].'"';
		}
	?>
];
var listName=[
	<?php 
		$first=true;
		for($i=0;$i<count($otherList);$i++) {
			if(!preg_match('/^(\w+)$/',$otherList[$i])) continue;
			if(!_checkPermission('music/index',$otherList[$i])) continue;
			if($first) $first=false;
			else echo ",\n";
			$curr=$otherList[$i];
			echo '"';
			printPlayerList($otherList[$i],$listType=='internal');
			echo '"';
		}
	?>
];
var listColor=[
	<?php
		$first=true;
		for($i=0;$i<count($otherList);$i++) {
			if(!preg_match('/^(\w+)$/',$otherList[$i])) continue;
			if(!_checkPermission('music/index',$otherList[$i])) continue;
			if($first) $first=false;
			else echo ",\n";
			echo '"'.GSM($otherList[$i])['A'].'"';
		}
	?>
];
var home='<?php echo BASIC_URL ?>';

var isCloudSave=<?php echo ($listType == 'internal')?'true':'false'; ?>;
var cloudId='<?php echo strval($intlist_arr[1]); ?>';

<?php if($listType == 'internal') { ?>
var cloudData = <?php echo encode_data($listdata) ?>;

var isCsv = <?php $is_csv = uauth_has_data($uname,'playlist',$intlist_arr[1].'.csv'); echo $is_csv ? 'true' : 'false'; ?>;
<?php } ?>

</script>
<script src="<?php echo BASIC_URL ?>static/js/maker/makerapp.js?v=<?php echo VERSION ?>"></script>
<div class="txmp-page-full">
	<h3><?php LNGe('led.title') ?></code></h3>
	<div style="border:1px solid #DDD;padding:16px;padding-bottom:0;margin-bottom:16px;" class="tooltip-box">
		<span class="cloudsave-disabled" style="display:<?php echo ($listType == 'tmp')?'inline-block':'none' ?>">
			<a onclick="toggleVisible(this,'.type-form-content')" style="font-weight:bold;margin-bottom:16px;display:block;">▶ <?php LNGe('led.type.temp') ?></a>
			<span class="type-form-content" style="display:none;">
				<p><?php LNGe('led.type.temp.tip.1') ?></p>
				<p><?php LNGe('led.type.temp.tip.2') ?></p>
				<p>
					<button class="am-btn am-btn-warning login-only" onclick="internal_cloudsave()"><?php LNGe('led.type.temp.save') ?></button>
					<button class="am-btn am-btn-success login-only" onclick="importData(false)"><?php LNGe('led.type.temp.import') ?></button>
				</p>
			</span>
		</span>
		<span class="internalsave-disabled" style="display:<?php echo ($listType == 'internal')?'inline-block':'none' ?>">
			<a onclick="toggleVisible(this,'.type-form-content')" style="font-weight:bold;margin-bottom:16px;display:block;">▶ <?php LNGe('led.type.online') ?> <span style="font-weight:normal"><?php echo uauth_username() . '/' . $intlist_arr[1] ?></span></a>
			<span class="type-form-content" style="display:none;">
				<?php if(!$is_csv) { ?>
					<p class="text-danger"></p>
				<?php } ?>
				<p><?php LNGe('led.type.online.tip.1') ?></p>
				<p><?php LNGe('led.type.online.tip.2') ?></p>
				<p><?php LNGe('led.type.online.tip.3') ?></p>
				<p>
					<button class="am-btn am-btn-warning internal-unsave op-btn am-disabled" onclick="internal_conv()"><?php LNGe('led.type.online.convert') ?></button>
					<button class="am-btn am-btn-secondary op-btn am-disabled" onclick="A_confirm_create_another()"><?php LNGe('led.type.online.copy') ?></button>
					<script>
						async function A_confirm_create_another() {
							if(!await modal_confirm_p(LNG('led.alert.copy'),LNG('led.alert.copy.tips'))) return;
							openUrl(1);
						}
					</script>
					<button class="am-btn am-btn-danger op-btn am-disabled" onclick="A_confirm_list_delete()"><?php LNGe('led.type.online.delete') ?></button>
					<script>
						async function A_confirm_list_delete() {
							if(!await modal_confirm_by_input(LNG('led.alert.delete.action'),LNG('led.alert.delete.prompt'),cloudData['title'])) return;
							openUrl(-1);
						}
					</script>
					<button class="am-btn am-btn-success op-btn am-disabled" onclick="openUrl(0,2)"><?php LNGe('led.type.online.export') ?></button>
					<button class="am-btn am-btn-success op-btn am-disabled" onclick="importData()"><?php LNGe('led.type.online.import') ?></button>
				</p>
			</span>
		</span>
	</div>
	<div>
		<?php LNGe('led.label.url') ?><br>
		<input type="text" id="g-url" autocomplete="off" placeholder="<?php LNGe('led.label.url.val') ?>" style="margin-right:8px;"><button type="button" class="am-btn am-btn-primary list-submit" disabled onclick="openUrl()"><?php echo (isset($_GET['fmid']) || $listType == 'internal')? LNG('led.action.save_open'):LNG('led.action.open') ?></button>
		<p <?php if($listType == 'internal') echo 'style="display:none;"'; ?>><input type="checkbox" placeholder="" id="isRand" /> <?php LNGe('led.temp.rand_next') ?>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" placeholder="" id="isRandShuffle" /> <?php LNGe('led.temp.rand_shuffle') ?>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" placeholder="" disabled id="isIframe" /> <?php LNGe('led.temp.integrated') ?></p>
		<p><?php LNGe('led.data_len') ?><code class="list-len-show">0/0</code></p>
	</div> 
	<?php if($listType == 'internal') { ?>
		<div style="border:1px solid #DDD;padding:16px;margin-bottom:16px;" class="tooltip-box">
			<h3><?php LNGe('led.online.feature') ?></h3>
			<p>
				<?php LNGe('led.online.title') ?>
				<input value="<?php echo htmlspecial($listdata['title']) ?>" type="text" id="cloudTitle" />
			</p>
			<p>
				<input <?php if($listdata['public']) echo 'checked'; ?> type="checkbox" placeholder="" id="cloudPublic" /> <?php LNGe('led.online.public') ?><br>
				<input <?php if($listdata['transform']['pick'] == 'rand') echo 'checked'; ?> type="checkbox" placeholder="" id="cloudIsRand" /> <?php LNGe('led.online.rand_next') ?><br>
				<input <?php if($listdata['transform']['random_shuffle']) echo 'checked'; ?> type="checkbox" placeholder="" id="cloudRandShuffle" /> <?php LNGe('led.online.rand_shuffle') ?>
			</p>
			<p>
				(1) <?php LNGe('led.online.cons.1') ?>&nbsp;
				<select id="cloudConstComparator">
					<option <?php if($listdata['transform']['constraints']['comparator'] == '<') echo 'selected' ?>>&lt;</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '<=') echo 'selected' ?>>&lt;=</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '>') echo 'selected' ?>>&gt;</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '>=') echo 'selected' ?>>&gt;=</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '!=') echo 'selected' ?>>!=</option>
				</select>
				&nbsp;<?php LNGe('led.online.cons.2') ?> ×&nbsp;
				<input type="number" id="cloudConstMultiplier" value="<?php echo htmlspecial($listdata['transform']['constraints']['multiplier']) ?>" />
				&nbsp;+&nbsp;
				<input type="number" id="cloudConstDelta" value="<?php echo htmlspecial($listdata['transform']['constraints']['delta']) ?>" />
			</p>
			<p>
				<?php
					if(!isset($listdata['transform']['constraints2'])) {
						$listdata['transform']['constraints2'] = [];
						$listdata['transform']['constraints2']['comparator'] = '>';
						$listdata['transform']['constraints2']['multiplier'] = 0;
						$listdata['transform']['constraints2']['delta'] = -1;
					}
				?>
				(2) <?php LNGe('led.online.cons.1') ?>&nbsp;
				<select id="cloudConstComparator2">
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '<') echo 'selected' ?>>&lt;</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '<=') echo 'selected' ?>>&lt;=</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '>') echo 'selected' ?>>&gt;</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '>=') echo 'selected' ?>>&gt;=</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '!=') echo 'selected' ?>>!=</option>
				</select>
				&nbsp;<?php LNGe('led.online.cons.2') ?> ×&nbsp;
				<input type="number" id="cloudConstMultiplier2" value="<?php echo htmlspecial($listdata['transform']['constraints2']['multiplier']) ?>" />
				&nbsp;+&nbsp;
				<input type="number" id="cloudConstDelta2" value="<?php echo htmlspecial($listdata['transform']['constraints2']['delta']) ?>" />
			</p>
			<p>
				<?php LNGe('led.online.no_choice') ?>
				<select id="cloudTermination">
					<option <?php if($listdata['transform']['termination'] == 'end') echo 'selected' ?> value="end"><?php LNGe('led.online.choice.end') ?></option>
					<option <?php if($listdata['transform']['termination'] == 'loop') echo 'selected' ?> value="loop"><?php LNGe('led.online.choice.single') ?></option>
					<option <?php if($listdata['transform']['termination'] == 'all') echo 'selected' ?> value="all"><?php LNGe('led.online.choice.random') ?></option>
				</select>
			</p>
		</div>
	<?php } ?>
	<div data-am-widget="list_news" class="am-list-news am-list-news-default" <?php if(!is_wap()) { ?>tabindex="0"<?php } ?>>
		<div class="am-list-news-hd am-cf">
			<span class="" onclick="modal_alert(LNG('led.count'),amount)">
				<h2><?php LNGe('led.list') ?></h2>
			</span>
		</div>
		<div class="am-list-news-bd">
			<ul class="am-list maker-list">
				<li class="am-g am-list-item-dated maker-list-example" style="display:block;" data-order="0" data-id="undefined">
					<a class="am-list-item-hd " style="margin-right:50px" ondblclick="selectItem(this)" onclick="_focus_to(this.parentElement)">
						<span><?php LNGe('led.list.default') ?></span>
						<br>
						<span class="addition-cmt"><?php LNGe('led.list.default.tips') ?></span>
					</a>
		
					<span class="am-list-date am-dropdown am-dropdown-up am-dropdown-father">
						<a class="am-dropdown-toggle" style="padding-right:0;font-size:16px;margin-top:-2px;"><?php LNGe('led.list.action') ?></a>
						<ul class="am-dropdown-content song-list-show" style="max-height:500px;overflow:auto;" onclick="$('.am-dropdown-father').dropdown('close')">
							<?php if(!is_wap()) { ?>
								<li><a onclick="focus_to(this)"><?php LNGe('led.list.action.focus') ?></a></li>
							<?php } ?>
							<li><a onclick="move(this,-1)"><?php LNGe('led.list.action.up') ?><?php if(!is_wap()) { ?> (E)<?php } ?></a></li>
							<li><a onclick="move(this,1)"><?php LNGe('led.list.action.down') ?><?php if(!is_wap()) { ?> (D)<?php } ?></a></li>
							<li><a onclick="duplicate(this)"><?php LNGe('led.list.action.add') ?><?php if(!is_wap()) { ?> (A)<?php } ?></a></li>
							<li><a onclick="removeItem(this)"><?php LNGe('led.list.action.remove') ?><?php if(!is_wap()) { ?> (R)<?php } ?></a></li>
							<li><a onclick="mark(this)"><?php LNGe('led.list.action.mark') ?><?php if(!is_wap()) { ?> (Z)<?php } ?></a></li>
							<li><a onclick="moveto(this)"><?php LNGe('led.list.action.move') ?><?php if(!is_wap()) { ?> (X)<?php } ?></a></li>
							<?php if($listType == 'internal') { ?><li><a onclick="rate(this)"><?php LNGe('led.list.action.score') ?><?php if(!is_wap()) { ?> (C)<?php } ?></a></li><?php } ?>
						</ul>
					</span>
				</li>
			</ul>
		</div>
	</div>
	<div class="toxic-dialog-cover" id="selector" style="display:none;">
		<iframe style="position:fixed;left:0;top:0;width:100%;height:100%;border:1px solid #000000;" src="<?php echo BASIC_URL ?>?iframe"></iframe>
		<a style="font-weight:bold;color:#000;font-size:24px;top:16px;right:16px;position:absolute;margin-right:24px;z-index:1;" id="button-cancel"><?php LNGe('led.list.cancel') ?></a>
	</div>
</div>
