<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<div class="title-dropdown-father am-dropdown am-dropdown-up" style="margin-right:8px;">
	<a class="am-dropdown-toggle cl-g-2"><?php LNGe('player.tr.details') ?> ▼</a>
	<ul class="am-dropdown-content song-list-show" onclick="$('.title-dropdown-father').dropdown('close')">
		<li class="am-dropdown-header"><?php LNGe('player.tr.details') ?></li>
		<li><a href="javascript:;"><i class="fa fa-microphone"></i> <?php LNGe('player.detail.singer') ?><span id="singer"><?php echo GCM()['S'] ?></span></a></li>
		<li><a href="javascript:;"><i class="fa fa-pencil"></i> <?php LNGe('player.detail.author') ?><span id="lauthor"><?php echo GCM()['LA'] ?></span> | <span id="mauthor"><?php echo GCM()['MA'] ?></span></a></li>
		<li><a href="javascript:;"><i class="fa fa-folder"></i> <?php LNGe('player.detail.cate') ?><span id="cate"><?php echo GCM()['C'] ?></span></a></li>
		<?php if(GCM()['O']){ ?>
		<li><a href="<?php echo GCM()['O'] ?>" target="_blank"><i class="fa fa-external-link"></i> <?php LNGe('player.detail.origin') ?><span><?php LNGe('player.detail.origin.val') ?></span></a></o>
		<?php } ?>
	</ul>
</div>
