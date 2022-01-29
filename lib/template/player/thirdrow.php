<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<div class="title-dropdown-father am-dropdown am-dropdown-up">
	<a class="am-dropdown-toggle cl-g-2"><?php LNGe('player.tr.action') ?> ▼</a>
	<ul class="am-dropdown-content song-list-show" onclick="$('.title-dropdown-father').dropdown('close')">
		<li class="am-dropdown-header"><?php LNGe('player.tr.action') ?></li>
		<?php if(getPerm(cid())['music/audio/dl'] || is_root()) { ?><li><a onclick="downloadAudio('<?php echo getDownloadUrl(cid()) ?>')" target="_blank"><i class="fa fa-download"></i><span> <?php echo !isKuwoId(cid()) ? htmlspecial2(LNG('player.action.dl')) : htmlspecial2(LNG('player.action.dl.rp')) ?></span><?php if(paymentStatus(cid())['pay_download']) {echo '<span class="txmp-tag tag-red-g wid-lp-12">'.fa_icon('diamond').LNG('list.tag.rp_lim.paydl').'</span>';} ?></a></li><?php } ?>
		<?php if(getPerm(cid())['music/code'] || is_root()) { ?><li><a href="<?php echo BASIC_URL.cid() ?>/code" target="_blank"><i class="fa fa-code"></i><span> <?php LNGe('player.action.viewcode') ?></span></a></li><?php } ?>
		<?php if(getPerm(cid())['music/download_doc'] || is_root()) { ?><li><a href="<?php echo BASIC_URL.cid() ?>/docs" target="_blank"><i class="fa fa-file-word-o"></i><span> <?php LNGe('player.action.docs') ?></span></a></li><?php } ?>
		<?php if(_checkPermission('admin/edit',cid()) && isValidMusic(cid(),false,false)) { ?><li><a href="<?php echo BASIC_URL.cid() ?>/edit" target="_blank"><i class="fa fa-edit"></i><span> <?php LNGe('player.action.edit') ?></span></a></li><?php } ?>
		<li><a href="javascript:void()" onclick="setZoom()"><i class="fa fa-search-plus"></i><span> <?php LNGe('player.action.zoom') ?></span></a></li>
	</ul>
</div>
