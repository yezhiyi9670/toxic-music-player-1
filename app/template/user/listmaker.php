<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	$listType = '';
	if(isset($_GET['fmid'])) $listType = 'external';
	else if(preg_match('/^list-maker\/(\d+)$/',$_GET['_lnk'])) $listType = 'internal';
	else $listType = 'tmp';
	$intlist_arr = [];
	preg_match('/^list-maker\/(\d+)$/',$_GET['_lnk'],$intlist_arr);
	
	if($listType == 'internal') {
		$uname = uauth_username();
		$listdata = readPlaylistData($uname,$intlist_arr[1]);
	}
?>

<script>
	document.title="歌单构造器<?php if($listType == 'internal') echo ' < '.htmlspecial($listdata['title']) ?> - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
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

var fmRandId='<?php echo $_GET['fmid'] ?>';
var isFmSave=<?php echo ($listType == 'external') ? 'true':'false' ?>;
var isCloudSave=<?php echo ($listType == 'internal')?'true':'false'; ?>;
var cloudId='<?php echo strval($intlist_arr[1]); ?>';

<?php if($listType == 'internal') { ?>
var cloudData = <?php echo json_encode($listdata,JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES) ?>;

var isCsv = <?php $is_csv = uauth_has_data($uname,'playlist',$intlist_arr[1].'.csv'); echo $is_csv ? 'true' : 'false'; ?>;
<?php } ?>

</script>
<script src="<?php echo BASIC_URL ?>static/js/maker/makerapp.js.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>"></script>
<div class="txmp-page-full">
	<h3>歌单构造器</code></h3>
	<div style="border:1px solid #DDD;padding:16px;padding-bottom:0;margin-bottom:16px;" class="tooltip-box">
		<span class="cloudsave-enabled" style="display:<?php echo ($listType == 'external')?'inline-block':'none' ?>">
			<a onclick="toggleVisible(this,'.type-form-content')" style="font-weight:bold;margin-bottom:16px;display:block;">▶ 保存方式：外置网盘 <span style="font-weight:normal" class="opened-file-basename">songlist</span></a>
			<span class="type-form-content" style="display:none;">
				<p>本歌单从RojExplorer打开，一旦打开修改后的歌单即会保存到RojExplorer</p>
				<p>
					网盘地址：<code class="fm-base">https://fake.orz/path/to/rojexplorer/index.php</code>
				</p>
				<p>
					存到文件：<code class="opened-file">/fake/path/to/songlist.oexe</code>
				</p>
				<p><button class="am-btn am-btn-danger" onclick="cloud_unsave()">取消云保存</button></p>
			</span>
		</span>
		<span class="cloudsave-disabled" style="display:<?php echo ($listType == 'tmp')?'inline-block':'none' ?>">
			<a onclick="toggleVisible(this,'.type-form-content')" style="font-weight:bold;margin-bottom:16px;display:block;">▶ 保存方式：临时</a>
			<span class="type-form-content" style="display:none;">
				<!-- <p>现在<?php echo _C()['app_name'] ?>支持通过RojExplorer网盘保存歌单。</p>
				<p>要开启云保存，请通过当前浏览器在RojExplorer进入登陆状态，然后点击下面的“开启云保存”按钮并输入你使用的网盘网站的网址。</p>
				<p>注意：只能使用RojExplorer。不支持目前的主流网盘。使用的RojExplorer必须被设定与本站对接。<a target="_blank" href="https://ak-ioi.com/193-my-rojexplorer/">下载RojExplorer</a></p>
				<p>
					<button class="am-btn am-btn-secondary" onclick="cloud_ensave()">使用RojExplorer保存（不推荐）</button>
				</p> -->
				<p>你可以登录并将歌单在线保存。</p>
				<p>这个功能可以使歌单拥有精美的URL，能增加许多新特性，并且会增大歌单长度限制。</p>
				<p>
					<button class="am-btn am-btn-warning login-only" onclick="internal_cloudsave()">将当前歌单存至云储存</button>
					<button class="am-btn am-btn-success login-only" onclick="importData(false)">导入歌单至云储存</button>
				</p>
			</span>
		</span>
		<span class="internalsave-disabled" style="display:<?php echo ($listType == 'internal')?'inline-block':'none' ?>">
			<a onclick="toggleVisible(this,'.type-form-content')" style="font-weight:bold;margin-bottom:16px;display:block;">▶ 保存方式：内置云保存 <span style="font-weight:normal"><?php echo uauth_username() . '/' . $intlist_arr[1] ?></span></a>
			<span class="type-form-content" style="display:none;">
				<?php if(!$is_csv) { ?>
					<p class="text-danger">该歌单使用旧的格式保存。请尽快重新保存该歌单以将其转换为新的 CSV 格式。</p>
				<?php } ?>
				<p>已经启用内置云保存。</p>
				<p>请妥善使用导入导出的功能备份歌单数据。</p>
				<p>云保存歌单具有比传统歌单更大的大小限制，且有额外特性。若转换为临时歌单，将丢失这些特性。</p>
				<p>
					<button class="am-btn am-btn-warning internal-unsave op-btn am-disabled" onclick="internal_conv()">转换为临时歌单</button>
					<button class="am-btn am-btn-secondary op-btn am-disabled" onclick="A_confirm_create_another()">保存副本</button>
					<script>
						async function A_confirm_create_another() {
							if(!await modal_confirm_p('确定创建副本','确定要创建当前歌单的副本？')) return;
							openUrl(1);
						}
					</script>
					<button class="am-btn am-btn-danger op-btn am-disabled" onclick="A_confirm_list_delete()">删除歌单</button>
					<script>
						async function A_confirm_list_delete() {
							if(!await modal_confirm_by_input('删除歌单','歌单的原名',cloudData['title'])) return;
							openUrl(-1);
						}
					</script>
					<button class="am-btn am-btn-success op-btn am-disabled" onclick="openUrl(0,2)">导出</button>
					<button class="am-btn am-btn-success op-btn am-disabled" onclick="importData()">导入并覆盖</button>
				</p>
			</span>
		</span>
	</div>
	<div>
		播放器网址：<br>
		<input type="text" id="g-url" autocomplete="off" placeholder="生成网址" style="margin-right:8px;"><button type="button" class="am-btn am-btn-primary list-submit" disabled onclick="openUrl()"><?php echo (isset($_GET['fmid']) || $listType == 'internal')? '保存并打开':'打开' ?></button>
		<p <?php if($listType == 'internal') echo 'style="display:none;"'; ?>><input type="checkbox" placeholder="" id="isRand" /> 随机切换&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" placeholder="" id="isRandShuffle" /> 随机排序&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" placeholder="" disabled id="isIframe" /> 嵌入式</p>
		<p>数据长度限制：<code class="list-len-show">0/0</code></p>
	</div> 
	<?php if($listType == 'internal') { ?>
		<div style="border:1px solid #DDD;padding:16px;margin-bottom:16px;" class="tooltip-box">
			<h3>特性</h3>
			<p>
				标题：
				<input value="<?php echo htmlspecial($listdata['title']) ?>" type="text" id="cloudTitle" />
			</p>
			<p>
				<input <?php if($listdata['public']) echo 'checked'; ?> type="checkbox" placeholder="" id="cloudPublic" /> 公开<br>
				<input <?php if($listdata['transform']['pick'] == 'rand') echo 'checked'; ?> type="checkbox" placeholder="" id="cloudIsRand" /> 随机选择下一个歌曲<br>
				<input <?php if($listdata['transform']['random_shuffle']) echo 'checked'; ?> type="checkbox" placeholder="" id="cloudRandShuffle" /> 在播放开始前随机排序歌单
			</p>
			<p>
				(1) 要求播放的下一首歌评分&nbsp;
				<select id="cloudConstComparator">
					<option <?php if($listdata['transform']['constraints']['comparator'] == '<') echo 'selected' ?>>&lt;</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '<=') echo 'selected' ?>>&lt;=</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '>') echo 'selected' ?>>&gt;</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '>=') echo 'selected' ?>>&gt;=</option>
					<option <?php if($listdata['transform']['constraints']['comparator'] == '!=') echo 'selected' ?>>!=</option>
				</select>
				&nbsp;上一首 ×&nbsp;
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
				(2) 要求播放的下一首歌评分&nbsp;
				<select id="cloudConstComparator2">
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '<') echo 'selected' ?>>&lt;</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '<=') echo 'selected' ?>>&lt;=</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '>') echo 'selected' ?>>&gt;</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '>=') echo 'selected' ?>>&gt;=</option>
					<option <?php if($listdata['transform']['constraints2']['comparator'] == '!=') echo 'selected' ?>>!=</option>
				</select>
				&nbsp;上一首 ×&nbsp;
				<input type="number" id="cloudConstMultiplier2" value="<?php echo htmlspecial($listdata['transform']['constraints2']['multiplier']) ?>" />
				&nbsp;+&nbsp;
				<input type="number" id="cloudConstDelta2" value="<?php echo htmlspecial($listdata['transform']['constraints2']['delta']) ?>" />
			</p>
			<p>
				当无法找到符合要求的下一首时，
				<select id="cloudTermination">
					<option <?php if($listdata['transform']['termination'] == 'end') echo 'selected' ?> value="end">终止播放</option>
					<option <?php if($listdata['transform']['termination'] == 'loop') echo 'selected' ?> value="loop">单曲循环</option>
					<option <?php if($listdata['transform']['termination'] == 'all') echo 'selected' ?> value="all">随便选择</option>
				</select>
			</p>
		</div>
	<?php } ?>
	<div data-am-widget="list_news" class="am-list-news am-list-news-default" <?php if(!is_wap()) { ?>tabindex="0"<?php } ?>>
		<div class="am-list-news-hd am-cf">
			<span class="" onclick="modal_alert('数量',amount)">
				<h2>歌曲列表</h2>
			</span>
		</div>
		<div class="am-list-news-bd">
			<ul class="am-list maker-list">
				<li class="am-g am-list-item-dated maker-list-example" style="display:block;" data-order="0" data-id="undefined">
					<a class="am-list-item-hd " style="margin-right:50px" ondblclick="selectItem(this)" onclick="_focus_to(this.parentElement)">
						<span>-- 请选择 --</span>
						<br>
						<span class="addition-cmt">点击此处，开始创建歌单</span>
					</a>
		
					<span class="am-list-date am-dropdown am-dropdown-up am-dropdown-father">
						<a class="am-dropdown-toggle" style="padding-right:0;font-size:16px;margin-top:-2px;">操作</a>
						<ul class="am-dropdown-content song-list-show" style="max-height:500px;overflow:auto;" onclick="$('.am-dropdown-father').dropdown('close')">
							<?php if(!is_wap()) { ?>
								<li><a onclick="focus_to(this)">聚焦</a></li>
							<?php } ?>
							<li><a onclick="move(this,-1)">上移<?php if(!is_wap()) { ?> (E)<?php } ?></a></li>
							<li><a onclick="move(this,1)">下移<?php if(!is_wap()) { ?> (D)<?php } ?></a></li>
							<li><a onclick="duplicate(this)">新增项目<?php if(!is_wap()) { ?> (A)<?php } ?></a></li>
							<li><a onclick="removeItem(this)">移除这一项<?php if(!is_wap()) { ?> (R)<?php } ?></a></li>
							<li><a onclick="mark(this)">标记以进行移动<?php if(!is_wap()) { ?> (Z)<?php } ?></a></li>
							<li><a onclick="moveto(this)">移动到这一项上面<?php if(!is_wap()) { ?> (X)<?php } ?></a></li>
							<?php if($listType == 'internal') { ?><li><a onclick="rate(this)">钦定评分<?php if(!is_wap()) { ?> (C)<?php } ?></a></li><?php } ?>
						</ul>
					</span>
				</li>
			</ul>
		</div>
	</div>
	<div class="toxic-dialog-cover" id="selector" style="display:none;">
		<iframe style="position:fixed;left:0;top:0;width:100%;height:100%;border:1px solid #000000;" src="<?php echo BASIC_URL ?>?iframe"></iframe>
		<a style="font-weight:bold;color:#000;font-size:24px;top:16px;right:16px;position:absolute;margin-right:24px;z-index:1;" id="button-cancel">取消</a>
	</div>
</div>
