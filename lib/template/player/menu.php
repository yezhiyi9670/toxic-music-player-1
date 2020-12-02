<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<div class="rmenu-selection-tabs">
	<span data-tab="list"   class="rmenu-tab rmenu-tab-list rmenu-tab-active"><?php LNGe('player.hub.list') ?></span>
	<span data-tab="saving" class="rmenu-tab rmenu-tab-saving"><?php LNGe('player.hub.action') ?></span>
	<span data-tab="stats"  class="rmenu-tab rmenu-tab-stats"><?php LNGe('player.hub.stats') ?></span>
	<a class="rmenu-close" href="javascript:;" onclick="rmenu_hide()" style="color: #000; float:right; font-size:20px;">
		<i class="fa fa-times"></i>
	</a>
	<div class="rmenu-content rmenu-content-list">
		<!--h2 class="rmenu-clt">
			<a class="rmenu-toggle" href="javascript:;" onclick="rmenu_toggle('queuelist',this)">
				<i class="fa fa-minus"></i> <?php LNGe('player.hub.list.queue') ?>
			</a>
		</h2>
		<div class="rmenu-collapse-queuelist" style="display:block;">
			<ul id="rmenu-queue-showbox" style="list-style:none;padding-left:0" style="user-select:none;">
				There's nothing here.
			</ul>
		</div-->

		<h2 class="rmenu-clt">
			<a class="rmenu-toggle" href="javascript:;" onclick="rmenu_toggle('userlist',this)">
				<i class="fa fa-minus"></i> <?php LNGe('player.hub.list.user') ?>
			</a>
		</h2>
		<div class="rmenu-collapse-userlist" style="display:block;">
			<ul id="rmenu-list-filterbox" class="fake-operation" style="list-style:none;padding-left:0;margin-bottom:-12px;">
				<li><a onclick="removeFilter()"><i class="fa fa-times"></i> <?php LNGe('player.hub.list.filter.remove') ?></a></li>
				<li><a>
					<i class="fa fa-filter"></i> <input type="text" id="filter-terms" placeholder="<?php LNGe('player.hub.list.filter') ?>" style="
						width: calc(100% - 32px);
						padding:0;
						border:none;
						box-shadow:none;
						height:20px;
						margin-bottom:3px;
						background-color:#EEE" oninput="doFilter()" />
				</li></a>
			</ul>
			<ul id="rmenu-list-showbox" style="list-style:none;padding-left:0" style="user-select:none;">
				<?php LNGe('player.hub.list.not_loaded') ?>
			</ul>
		</div>
	</div>
	<div class="rmenu-content rmenu-content-saving" style="display:none">
		<?php LNGe('player.hub.action.tips') ?>
	</div>
	<div class="rmenu-content rmenu-content-stats" style="display:none">
		<?php LNGe('player.hub.stats.tips') ?>
	</div>
</div>
