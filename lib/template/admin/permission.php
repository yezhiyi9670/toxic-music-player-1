<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	if($_POST['isSubmit']=='yes') {
		$id=cid();
		if(!file_exists(FILES.$id."/")) {
			redirectToNote(LNG('editor.msg.tan90'));
			exit;
		}

		$e=array();
		foreach($_POST as $k=>$v) {
			if(permissionNames($k)) {
				$e[$k]=($v=='off' ? false : true);
			}
		}
		file_put_contents(FILES.cid().'/permission.json',encode_data($e,true));

		echo '<script>location.href="'.BASIC_URL.'admin#item-'.cid().'"</script>';
		exit;
	}

?>
<script>
	document.title='<?php LNGe('permitter.title') ?> ‹ <?php echo addslashes(GCM()['N']) ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('permitter.title'));
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('permitter.title') ?></h3>
	<span class="bid-linking">
		<a href="<?php echo BASIC_URL . cid() ?>/edit"><?php LNGe('editor.title') ?></a>&nbsp;▪
		<a href="<?php echo BASIC_URL . cid() ?>/resource"><?php LNGe('resource.title') ?></a>&nbsp;▪
		<strong><?php LNGe('permitter.title') ?></strong>
	</span>
	<?php showToastMessage(); ?>
	<p><?php LNGe('permitter.tip1') ?><br>
	<?php LNGe('permitter.tip2') ?></p>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="yes">
		<table>
			<?php
				$spt = getPerm(cid());
				$rtn = permissionNames();
				foreach($rtn as $k=>$v) {
					echo '<tr>';
					echo '<td style="padding-right:16px;padding-bottom:8px;font-size:14px;">';
					echo $v;
					echo '</td>';
					echo '<td style="padding-right:16px;padding-bottom:8px;font-size:14px;">';
					echo '<input type="hidden" name="'.$k.'" value="off">';
					if($k!="admin/edit") echo '<input type="checkbox" name="'.$k.'" '.($spt[$k] ? "checked" : "").'>';
					else echo '<input type="checkbox" name="'.$k.'" '.($spt[$k] ? "checked" : "").' disabled>';
					echo '</td></tr>';
				}
			?>
		</table>
		<input style="margin-top:8px;" type="submit" value="<?php LNGe('ui.confirm') ?>" class="am-btn am-btn-primary">
	</form></p>
	<p>
		<a onclick="toggleVisible(this)">
			▶ <?php LNGe('ui.tips') ?>
		</a>
	</p>
	<p id="notes" style="display:none;margin-left:8px;margin-top:-12px;line-height:26px;">
		<?php echo LNG('permitter.tip.L') ?><br>
		<?php echo LNG('permitter.tip.P') ?><br>
		<?php echo LNG('permitter.tip.C') ?><br>
		<?php echo LNG('permitter.tip.K') ?><br>
		<?php echo LNG('permitter.tip.D') ?><br>
		<?php echo LNG('permitter.tip.A') ?><br>
		<?php echo LNG('permitter.tip.XW') ?><br>
		<?php echo LNG('permitter.tip.E') ?><br>
	</p>
</div>
