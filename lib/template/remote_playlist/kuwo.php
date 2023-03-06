<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title='<?php echo jsspecial(LNG('rp.list.title')) . COLON ?><?php $ioi = $GLOBALS['remote_playlist'];echo jsspecial($ioi['data']['name']) ?> - <?php echo jsspecial(LNG('rp.title')) ?> - <?php echo jsspecial(_CT('app_name_title')) ?>';
	set_section_name(LNG('rp.list.title'));
</script>
<div class="txmp-page-full">
	
	<h3><?php LNGe('klist.caption') ?><?php echo htmlspecial($ioi['data']['name']) ?><span style="font-size:12px;font-weight:normal;">&nbsp;by <?php echo htmlspecial2($ioi['data']['uname']) ?></span></h3>
	
	<p><a target="_blank" href="https://kuwo.cn/playlist_detail/<?php echo $GLOBALS['remote_playlist_id'] ?>"><?php LNGe('klist.view') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo BASIC_URL ?>playlist/__syscall/1701<?php echo $GLOBALS['remote_playlist_id'] ?>"><?php LNGe('list.play_all') ?></a></p>
	
	<div id="playlist-showbox"><?php
		$id = trim(substr($_GET['key'],1));
		@$data = $ioi;
		
		$npage=ceil($data['data']['total']/50.0);
		$startid=50*$_GET['pageid']-49;
		$endid=50*$_GET['pageid'];

		if(true) {
			if(!isset($data['data']['musicList'])) {
				LNGe('list.rp.query_fail');
				
				exit;
			}
			
			echo '<p>' . LNG('page.total',$data['data']['total']) . '</p>' . "\n";
			echo '<ol>' . "\n";
			foreach($data['data']['musicList'] as $item) {
				printRmpList($item);
				echo "\n";
			}
			echo '</ol>' . "\n";
			echo '<p><a onclick="turn_page(1)">' . LNG('page.first') . '</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'turn_page(curr_pageid-1)').'">&lt; ' . LNG('page.prev') . '</a>&nbsp;&nbsp;';

			echo LNG('page.pagedesc',$_GET['pageid'],$npage);
			echo '&nbsp;&nbsp;';
			echo LNG('page.itemdesc',$data['data']['total'],$startid,min($endid,$data['data']['total']));

			echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'turn_page(curr_pageid+1)').'">' . LNG('page.next') . ' &gt;</a>&nbsp;&nbsp;<a onclick="turn_page('.$npage.')">' . LNG('page.last') . '</a></p>';
		}
	?></div>
</div>

<script>
	var curr_pageid = 1;
	function turn_page(pageid) {
		var dist = $('#playlist-showbox');
		curr_pageid=pageid;
		var al=modal_loading(LNG('ui.wait'),LNG('list.rp.querying'));
		$.ajax({
			async:true,
			timeout:9000,
			dataType:"text",
			url:'<?php echo BASIC_URL ?>?isSubmit=KuwoSearch&key=^<?php echo $GLOBALS['remote_playlist_id'] ?>&pageid='+pageid,
			error:function(e){
				close_modal(al);
				modal_alert(LNG('ui.error'),LNG('list.rp.query_fail'));
			},
			success:function(e){
				close_modal(al);
				dist.html(e);
				handle_rp_item();
			}
		});
	}
	handle_rp_item();
</script>
