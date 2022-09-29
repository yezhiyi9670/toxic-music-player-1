<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php
if(isset($_GET['isSubmit'])) {
	if($_GET['isSubmit']=='KuwoSearch') {
		kuwoSearchSong();
		exit;
	} else if($_GET['isSubmit']=='IDSearch') {
		IDSearchSong();
		exit;
	}
	exit;
}
?>

<script>
	document.title='<?php echo jsspecial(_CT('app_name_title')) ?> | <?php echo jsspecial(_CT('app_desc')) ?>';
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
			<a class="txmp-list-sel-legacy" onclick="$('.txmp-list-type').css('display','none');
				$('#list-legacy').css('display','block');"><?php LNGe('list.source.internal') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a class="txmp-list-sel-kuwo" onclick="$('.txmp-list-type').css('display','none');
				$('#list-kuwo').css('display','block');"><?php LNGe('list.source.kuwo') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a class="txmp-list-sel-id" onclick="$('.txmp-list-type').css('display','none');
				$('#list-id').css('display','block');"><?php LNGe('list.source.id') ?></a>
		</p>
	</div>
	<div id="list-legacy" class="txmp-list-type">
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
	<div id="list-kuwo" class="txmp-list-type" style="display:none;">
		<p><strong><?php LNGe('list.caption.kuwo.featured') ?></strong></p>
		<div id="list-kuwo-suggestion">
			<button id="kuwo-show-suggestion" class="am-btn am-btn-primary" onclick="kuwo_search(1,'> __mp_suggestions__','#list-kuwo-suggestion')"><?php LNGe('ui.show') ?></button>
		</div>
		<p><strong><?php LNGe('list.caption.kuwo.search') ?></strong></p>
		<input name="kuwo-keyword" id="kuwo-keyword" type="text" data-wcl-enter-target=".txmp-kuwo-go" />
		<button type="button" class="am-btn am-btn-primary txmp-kuwo-go" onclick="kuwo_search(1)"><?php LNGe('ui.search') ?></button>
		<p id="list-kuwo-show">
			<!-- -->
		</p>
	</div>
	<div id="list-id" class="txmp-list-type" style="display:none;">
		<p><strong><?php LNGe('list.caption.id') ?></strong></p>
		<p><?php echo LNG('list.desc.id') ?></p>
		<input name="id-keyword" id="id-keyword" type="text" data-wcl-enter-target=".txmp-id-go" />
		<button type="button" class="am-btn am-btn-primary txmp-id-go" onclick="kuwo_search(1, '', '#list-id-show', '#id-keyword', 'IDSearch')"><?php LNGe('ui.search') ?></button>
		<p id="list-id-show">
			<!-- -->
		</p>
	</div>
</div>
<script>
	function kuwo_search(pageid,cont="",dist='#list-kuwo-show',src='#kuwo-keyword',submitter='KuwoSearch') {
		dist = $(dist);
		curr_pageid=pageid;
		if(cont=="") cont=$(src)[0].value;
		var al=modal_loading(LNG('ui.wait'),LNG('list.rp.querying'));
		$.ajax({
			async:true,
			timeout:9000,
			dataType:"text",
			url:'?isSubmit='+submitter+'&key='+encodeURIComponent(cont)+'&pageid='+pageid,
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
	function ready_for_search_go(keyword) {
		$('.txmp-list-sel-id').click();
		$('#id-keyword').val(keyword);
		$('.txmp-id-go').click();
	}
	function ready_for_search_check() {
		var keyword = storeData('maker.ready_for_search');
		if(keyword) {
			storeData('maker.ready_for_search', null);
			Toast.make_toast_text(LNG('list.toast.rfs_active', keyword), 1000);
			ready_for_search_go(keyword);
			return true;
		}
		return false;
	}
</script>
