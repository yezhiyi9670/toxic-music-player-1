<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php
if(isset($_GET['isSubmit'])) {
	if($_GET['isSubmit']=='KuwoSearch') {
		kuwoSearchSong();
	}
	exit;
}
?>

<script>
	document.title='<?php echo addslashes(_CT('app_name_title')) ?> | <?php echo addslashes(_CT('app_desc')) ?>';
	set_section_name(LNG('list.title'));
</script>
<?php declare_allow_overscroll() ?>
<div class="txmp-page-full">
	<?php if(!isset($_GET['iframe'])) { ?><h3 id="page-title-clickable"><?php LNGe('list.title') ?></h3>
	<p><?php printFuncLink('list/index') ?></p>
	<?php
		if(file_exists(DATA_PATH.'bc/bc.html')) {
			echo '<div class="tooltip-box" style="padding:16px;margin-bottom:16px;">';
			require(DATA_PATH.'bc/bc.html');
			echo '</div>';
		}
	?>
	<?php } ?>
	<div class="tooltip-box">
		<p><?php LNGe('list.source.select') ?>&nbsp;&nbsp;
			<a onclick="$('#list-legacy').css('display','block');
				$('#list-kuwo').css('display','none');"><?php LNGe('list.source.internal') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a onclick="$('#list-kuwo').css('display','block');
				$('#list-legacy').css('display','none');"><?php LNGe('list.source.kuwo') ?></a>
		</p>
	</div>
	<div id="list-legacy">
		<p><?php LNGe('list.caption.internal.list') ?>&nbsp;<a href="<?php echo BASIC_URL ?>playlist/__syscall/0" target="_blank"><?php LNGe('list.play_all') ?></a></p>
		<ul>
			<?php
				$menu=dir_list(FILES);
				$item_count = 0;
				foreach($menu as $item) {
					if(isValidMusic($item,false) && (getPerm($item)['list/show'] || is_root())) {
						printIndexList($item);
						$item_count++;
					}
				}

				if($item_count == 0) {
					echo '<li>';
					LNGe('list.list.empty');
					echo '</li>';
				}
			?>
		</ul>
	</div>
	<div id="list-kuwo" style="display:none;">
		<p><strong><?php LNGe('list.caption.kuwo.featured') ?></strong></p>
		<div id="list-kuwo-suggestion">
			<button id="kuwo-show-suggestion" class="am-btn am-btn-primary" onclick="kuwo_search(1,'> __mp_suggestions__','#list-kuwo-suggestion')"><?php LNGe('ui.show') ?></button>
		</div>
		<p><strong><?php LNGe('list.caption.kuwo.search') ?></strong></p>
		<input name="keyword" id="keyword" type="text" />
		<button type="button" class="am-btn am-btn-primary" onclick="kuwo_search(1)"><?php LNGe('ui.search') ?></button>
		<p id="list-kuwo-show">
			<!---->
		</p>
	</div>
</div>
<script>
	function kuwo_search(pageid,cont="",dist='#list-kuwo-show') {
		dist = $(dist);
		curr_pageid=pageid;
		if(cont=="") cont=$('#keyword')[0].value;
		else {
			// $('#keyword')[0].value=cont;
		}
		var al=modal_loading(LNG('ui.wait'),LNG('list.rp.querying'));
		$.ajax({
			async:true,
			timeout:9000,
			dataType:"text",
			url:'?isSubmit=KuwoSearch&key='+encodeURIComponent(cont)+'&pageid='+pageid,
			error:function(e){
				close_modal(al);
				modal_alert(LNG('ui.error'),LNG('list.rp.query_fail'));
			},
			success:function(e){
				close_modal(al);
				dist.html(e);
				if(!G.is_iframe) handle_rp_item();
			}
		});
	}
</script>
