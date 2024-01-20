<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php if(!is_wap()) { ?><div class = "song-avatar shadowed-intense">
	<img src="<?php
			echo 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		?>" title="<?php LNGe('player.cover_image') ?>"
		style="width: 100%; height: 100%;" ondragstart="return false;" />
</div>
<?php } ?>
<?php if(!is_wap()) { ?><center style="margin-bottom:-18px;"><?php } ?>
<div class="title-dropdown-father am-dropdown <?php if(is_wap()) echo 'am-dropdown-up' ?>">
	<a class="song-title am-dropdown-toggle" <?php if(getAudioPath(FILES.cid()."/back")) { ?>data-back="yes"<?php } ?>>
		<?php echo htmlspecial2(GCM()['N']) ?><span style="display:inline-block;width:0.15em"></span><?php echo fa_icon('caret-down', '0') ?>
	</a>
	<?php // 这里是操作菜单 ?>
	<ul class="am-dropdown-content song-list-show" onclick="$('.title-dropdown-father').dropdown('close')" style="max-height:520px;overflow:auto;overflow-x:hidden;right:auto;<?php if(!is_wap()) echo 'margin-left:-320px;margin-top:-300px;'; ?>">
		<li class="am-dropdown-header"><?php LNGe('player.menu.action') ?></li>
		<li>
			<a onclick="changeTo(song_id)" oncontextmenu="changeTo(song_id,false,true);return false">
				<?php echo fa_icon('refresh', '0') ?><?php LNGe('player.menu.refresh') ?>
			</a>
		</li>
		<?php if(getAudioPath(FILES.cid()."/back")) { ?>
		<li>
			<a onclick="trackSwitch();">
				<?php echo fa_icon('microphone-slash', '0') ?><?php LNGe('player.menu.track_switch') ?>
			</a>
		</li>
		<?php } ?>
		<?php if(getPerm(cid())['music/audio/dl'] || is_root()) { ?>
			<li>
				<a onclick="downloadAudio('<?php echo getDownloadUrl(cid()) ?>')" target="_blank" class="download-button">
					<?php echo fa_icon('download', '0') ?><?php !isKuwoId(cid()) ? LNGe('player.menu.download') : LNGe('player.menu.download.rp') ?><?php if(paymentStatus(cid())['pay_download']) {echo '<span class="txmp-tag tag-red-g wid-lp-12 tag-rplim-paydl">'.fa_icon('diamond').LNG('list.tag.rp_lim.paydl').'</span>';} ?>
				</a>
			</li>
		<?php } ?>
		<?php if(getPerm(cid())['music/code'] || is_root()) { ?>
			<li>
				<a href="<?php echo BASIC_URL.cid() ?>/code" target="_blank">
					<?php echo fa_icon('code', '0') ?><?php LNGe('player.menu.viewcode') ?>
				</a>
			</li>
		<?php } ?>
		<?php if(true) { ?>
			<li>
				<a href="<?php echo BASIC_URL.cid() ?>/switch-all" target="_blank">
					<?php echo fa_icon('file-o', '0') ?><?php LNGe('player.menu.switchdata') ?>
				</a>
			</li>
		<?php } ?>
		<?php if(_checkPermission('admin/edit',cid()) && isValidMusic(cid(),false,false)) { ?>
			<li>
				<a href="<?php echo BASIC_URL.cid() ?>/edit" target="_blank">
					<?php echo fa_icon('pencil', '0') ?><?php LNGe('player.menu.edit') ?>
				</a>
			</li>
		<?php } ?>
		<li>
			<a href="javascript:;" onclick="ready_for_search_store()">
				<?php echo fa_icon('star', '0') ?><?php LNGe('player.menu.ready_for_search') ?>
			</a>
		</li>
		<?php // 我是分割线 ---------------------------------------------------------------- ?>
		<li class="am-divider"></li>
		<li class="am-dropdown-header"><?php LNGe('player.list') ?></li>
		<?php if(!isset($_GET['iframe'])) { ?>
		<li><a onclick="listEdit()"><?php echo fa_icon('pencil', '0') ?><span class="edit-label"><?php LNGe('player.list.edit') ?></span></a></li>
		<?php } ?>
		<li><a onclick="timedPause()"><?php echo fa_icon('hourglass-2', '0') ?><span class="timed-pause-state"><?php LNGe('player.list.timer') ?></span></a></li>
		<li><a onclick="listPrint()"><?php echo fa_icon('print', '0') ?><?php LNGe('player.list.print') ?></a></li>
		<?php if(false && isKuwoId(cid())) { ?>
		<li class="am-divider"></li>
		<li class="am-dropdown-header"><?php LNGe('player.suggest') ?></li>
		<li><a onclick="showSuggestions()"><i class="fa fa-chevron-down"></i> <?php LNGe('player.suggest.show') ?></a></li>
		<?php } ?>
		<li class="menu-next-song-item"><a onclick="javascript:;"><span class="next-song-label"></span></a></li>
		<li class="menu-curr-item" style="border-top: 1px dotted rgb(204, 204, 204);">
			<a class="menu-curr-display" target="_blank" href="<?php echo BASIC_URL . cid() ?>">
			</a>
		</li>
		<?php // 我是分割线 ---------------------------------------------------------------- ?>
	</ul>
</div>
<span class="song-id"><?php echo cid() ?></span>
<?php if(!is_wap()) { ?></center><?php } ?>
<?php if(!is_wap()) echo '<br>' ?>
<?php
	$analytics = getAudioAnalysis(cid());
	$length_str = '--';
	if($analytics != null) {
		$length_str = formatDuration($analytics['time']);
	}
?>
<span class="song-length"><?php if(!is_wap()) {LNGe('player.status.length');} ?><span id="total-len"><?php echo $length_str ?></span></span>
<span class="song-status song-status-process" style="display:none;"><span id="elasped">NaN</span> / -<span id="remain">NaN</span> (<span id="accurate">NaN</span>)</span><span class="song-status song-status-loading"><i class="fa fa-circle-o-notch fa-spin"></i> <?php LNGe('player.status.loading') ?></span><span class="song-status song-status-errored" style="display:none;"><i class="fa fa-exclamation-triangle"></i> <?php LNGe('player.status.errored') ?></span>
