<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<div class="rmenu-selection-tabs">
	<span data-tab="list"   class="rmenu-tab rmenu-tab-list rmenu-tab-active">播放列表</span>
	<span data-tab="saving" class="rmenu-tab rmenu-tab-saving">歌单操作</span>
	<span data-tab="stats"  class="rmenu-tab rmenu-tab-stats">统计数据</span>
	<a class="rmenu-close" href="javascript:;" onclick="rmenu_hide()" style="color: #000; float:right; font-size:20px;">
		<i class="fa fa-times"></i>
	</a>
	<div class="rmenu-content rmenu-content-list">
		<!--播放队列-->
		<!--h2 class="rmenu-clt">
			<a class="rmenu-toggle" href="javascript:;" onclick="rmenu_toggle('queuelist',this)">
				<i class="fa fa-minus"></i> 播放队列【未实现】
			</a>
		</h2>
		<div class="rmenu-collapse-queuelist" style="display:block;">
			<ul id="rmenu-queue-showbox" style="list-style:none;padding-left:0" style="user-select:none;">
				There's nothing here.
			</ul>
		</div-->

		<!--用户歌单-->
		<h2 class="rmenu-clt">
			<a class="rmenu-toggle" href="javascript:;" onclick="rmenu_toggle('userlist',this)">
				<i class="fa fa-minus"></i> 用户歌单
			</a>
		</h2>
		<div class="rmenu-collapse-userlist" style="display:block;">
			<ul id="rmenu-list-filterbox" class="fake-operation" style="list-style:none;padding-left:0;margin-bottom:-12px;">
				<li><a onclick="removeFilter()"><i class="fa fa-times"></i> 清除筛选条件</a></li>
				<li><a>
					<i class="fa fa-filter"></i> <input type="text" id="filter-terms" placeholder="筛选" style="
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
				播放列表暂未加载
			</ul>
		</div>
	</div>
	<div class="rmenu-content rmenu-content-saving" style="display:none">
		歌单操作：将取代歌单构造器，暂未实现。
	</div>
	<div class="rmenu-content rmenu-content-stats" style="display:none">
		统计数据：暂未实现。目前只能提供最基础的数据，因为其他的需要自定义标签系统支持。
	</div>
</div>
