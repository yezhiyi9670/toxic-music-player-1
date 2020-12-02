<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<script>document.title='<?php echo LNGk('uc.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';</script>
<div class="txmp-page-full">
	<h3><?php LNGe('uc.title') ?></h3>
	<p><?php LNGe('uc.welcome',uauth_username()) ?></p>
	<p><a href="<?php echo BASIC_URL ?>user/passwd"><?php LNGe('ui.change_password') ?></a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo BASIC_URL ?>user/logout"><?php LNGe('ui.logout') ?></a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;</span><a href="<?php echo BASIC_URL ?>"><?php LNGe('ui.return_mainpage') ?></a></p>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()" class="notice-confirm"><?php LNGe('ui.hide_notice') ?></a>
	</p><?php } ?>
	<?php
		$uname = uauth_username();
		$jsonlst = uauth_list_data($uname,'playlist','.json');
		$csvlst = uauth_list_data($uname,'playlist','.csv');
	?>
	<p><b><?php LNGe('uc.playlists') ?> (<?php echo count($jsonlst) + count($csvlst) ?>/<?php echo _CT('user_playlist_quota') ?>)</b></p>
	<p><?php echo LNG('uc.create_list',BASIC_URL . 'list-maker') ?></p>
	<ul>
		<?php
			// var_dump($lst);
			function print_list_entry($listdata,$user,$id,$danger=false) {
				$meta = GSM($listdata['playlist'][0]['id']);

				$sum = 0;
				$time = 0;
				foreach($listdata['playlist'] as $pl) {
					$sum += $pl['rating'];
					$ana = getAudioAnalysis($pl['id']);
					if($ana != NULL) {
						$time += $ana['time'];
					}
				}
				$sum /= count($listdata['playlist']);

				echo '<li style="color:'.$meta['A'].';padding-bottom:6px;">';
				echo '<a href="'.BASIC_URL.'playlist/'.$user.'/'.$id.'" target="_blank" style="color:'.$meta['A'].';">';
				if($danger) {
					echo '<span class="text-danger" title="'.LNG('uc.playlist.danger.tips').'">'.LNG('uc.playlist.danger').'</span> ';
				}
				echo htmlspecial($listdata['title']);
				echo '</a>';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<a href="'.BASIC_URL.'list-maker/'.$id.'" target="_blank" style="color:'.$meta['A'].';">';
				echo '['.LNG('uc.playlist.edit').']';
				echo '</a>';
				echo '<br>';
				echo '<span class="addition-cmt">';
				echo '<span class="txmp-tag tag-default">'.$user.'/'.$id.'</span>';
				echo '<span class="txmp-tag tag-length">'.formatDuration($time).'</span>';
				echo '<span class="txmp-tag tag-red-g">'.($listdata['public']?LNG('uc.playlist.public'):LNG('uc.playlist.private')).'</span>';
				echo '<span class="txmp-tag tag-orange-g">'.LNG('uc.playlist.entry').htmlspecial2($meta['N']).'</span>';
				echo '<span class="txmp-tag tag-blue-g">'.LNG('uc.playlist.count').count($listdata['playlist']).'</span>';
				echo '<span class="txmp-tag tag-purple-g">'.LNG('uc.playlist.avg').floor($sum).'</span>';
				echo '</span>';
				echo '</li>';
			}

			foreach($jsonlst as $item) {
				$fid = 'user/'.$uname.'/playlist/'.$item;
				$fp = USER_DATA . $uname . '/playlist/' . $item . '.json';

				wait_file($fid);

				$listdata = json_decode(file_get_contents($fp),true);
				print_list_entry($listdata,$uname,$item,true);
			}
			foreach($csvlst as $item) {
				$fid = 'user/'.$uname.'/playlist/'.$item.'-csv';
				$fp = USER_DATA . $uname . '/playlist/' . $item . '.csv';

				wait_file($fid);

				$listdata = plcsv_decode(file_get_contents($fp),true);
				print_list_entry($listdata,$uname,$item,false);
			}
		?>
	</ul>
</div>
