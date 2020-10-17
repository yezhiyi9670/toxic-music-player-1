<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	if($_POST['isSubmit']=='rname-item') {
		echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("暂不支持！")</script>';
		exit;
	}
	else if(isset($_POST['isSubmit'])) {
		echo '<script>location.href=location.href</script>';
		exit;
	}

?>
<script>document.title='个人中心 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
	<h3>个人中心</h3>
	<p>欢迎回来，<?php echo uauth_username() ?>！</p>
	<p><a href="<?php echo BASIC_URL ?>user/passwd">修改密码</a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo BASIC_URL ?>user/logout">退出登录</a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;</span><a href="<?php echo BASIC_URL ?>">返回主页</a></p>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()">知道了</a>
	</p><?php } ?>
	<?php
		$uname = uauth_username();
		$jsonlst = uauth_list_data($uname,'playlist','.json');
		$csvlst = uauth_list_data($uname,'playlist','.csv');
	?>
	<p><b>保存的歌单 (<?php echo count($jsonlst) + count($csvlst) ?>/<?php echo _CT('user_playlist_quota') ?>)</b></p>
	<p>注意，这里不可以新建歌单。要创建，请在<a href="<?php echo BASIC_URL ?>list-maker">歌单构造器</a>中完成构造后存到这里。</p>
	<ul>
		<?php
			// var_dump($lst);
			function print_list_entry($listdata,$user,$id,$danger=false) {
				$meta = GSM($listdata['playlist'][0]['id']);

				$sum = 0;
				foreach($listdata['playlist'] as $pl) {
					$sum += $pl['rating'];
				}
				$sum /= count($listdata['playlist']);

				echo '<li style="color:'.$meta['A'].';padding-bottom:6px;">';
				echo '<a href="'.BASIC_URL.'playlist/'.$user.'/'.$id.'" target="_blank" style="color:'.$meta['A'].';">';
				if($danger) {
					echo '<span class="text-danger" title="请尽快将此歌单转换为新版本歌单">(危)</span> ';
				}
				echo htmlspecial($listdata['title']);
				echo '</a>';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<a href="'.BASIC_URL.'list-maker/'.$id.'" target="_blank" style="color:'.$meta['A'].';">';
				echo '[编辑]';
				echo '</a>';
				echo '<br>';
				echo '<span class="addition-cmt">';
				echo '<span class="txmp-tag tag-default">'.$user.'/'.$id.'</span>';
				echo '<span class="txmp-tag tag-red-g">'.($listdata['public']?'公开':'私密').'</span>';
				echo '<span class="txmp-tag tag-orange-g">入口点：'.htmlspecial2($meta['N']).'</span>';
				echo '<span class="txmp-tag tag-blue-g">项目数：'.count($listdata['playlist']).'</span>';
				echo '<span class="txmp-tag tag-purple-g">平均分：'.floor($sum).'</span>';
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
