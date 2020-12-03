<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title="<?php LNGe('ver.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
</script>
<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/cdp-page.css" />

<div class="cdp-header">
	<a style="width:32px;font-size:19px;padding-right:32px;" onclick="prev_page()"><i class="fa fa-chevron-left"></i></a>
	<div id="cdp-header-text" style="display:inline-block;width:128px;font-size:19px;text-align:center">
		<span class="am-dropdown" data-am-dropdown>
			<a style="color:#000" class="am-dropdown-toggle"><?php LNGe('page.cdp.pagedesc') ?> <span id="page-now">?</span>/<span id="page-tot">?</span><?php echo COLON ?><span id="page-name"><?php LNGe('page.cdp.pagename.null') ?></span></a>
			<ul class="am-dropdown-content" id="cdp-header-nav" style="overflow:auto;font-size:15px;max-height:500px;" onclick="$('.am-dropdown').dropdown('close')">
				<li class="am-dropdown-header"><?php LNGe('page.cdp.nav') ?></li>
				<!---->
			</ul>
		</span>
	</div>
	<a style="width:32px;font-size:19px;padding-left:32px;" onclick="next_page()"><i class="fa fa-chevron-right cl-g-2"></i></a>
</div>

<style>
	.verh-list {
		padding-left: 1.5em;
	}
	ul>li>ul {
		margin-top: 0.3em;
		margin-bottom: 0.5em;
	}
	li {
		margin-top: 0.2em;
	}
	.txmp-tag {
		vertical-align: top;
		display: inline-block;
		margin-top: 2px;
		padding-top: 0;
	}
	li.milestone {
		margin-left: -23px;
		margin-top: 1.2em;
		margin-bottom: 0.5em;
	}
	li.milestone::marker {
		font-size: 0;
	}
	li.milestone::before {
		content: '✅ ';
		font-weight: 700;
	}
</style>

<?php
	$data = json_decode(file_get_contents(CHANGELOG),true);
	function print_tag($tag) {
		if($tag == 'CHANGE') {
			echo '<span class="txmp-tag tag-blue-g">' . LNG('ver.tag.change') . '</span>';
		} else if($tag == 'ISSUE') {
			echo '<span class="txmp-tag tag-deep-orange-l">' . LNG('ver.tag.issue') . '</span>';
		} else if($tag == 'ADD') {
			echo '<span class="txmp-tag tag-green-g">' . LNG('ver.tag.add') . '</span>';
		} else if($tag == 'FIX') {
			echo '<span class="txmp-tag tag-purple-g">' . LNG('ver.tag.fix') . '</span>';
		} else if($tag == 'CLEAN') {
			echo '<span class="txmp-tag tag-cyan-g">' . LNG('ver.tag.clean') . '</span>';
		} else if($tag == 'BREAKING') {
			echo '<span class="txmp-tag tag-red-l">' . LNG('ver.tag.break') . '</span>';
		} else if($tag == 'INIT') {
			echo '<span class="txmp-tag tag-green-g">' . LNG('ver.tag.init') . '</span>';
		} else if($tag == 'PUBLISH') {
			echo '<span class="txmp-tag tag-default">' . LNG('ver.tag.publish') . '</span>';
		} else if($tag == 'REMOVE') {
			echo '<span class="txmp-tag tag-red-g">' . LNG('ver.tag.remove') . '</span>';
		} else if($tag == 'EXPERIMENTAL') {
			echo '<span class="txmp-tag tag-deep-orange-l">' . LNG('ver.tag.danger') . '</span>';
		}
	}
	function print_items($r) {
		echo '<ul class="verh-list">';
		foreach($r as $item) {
			$is_mile = false;
			if(isset($item['tag'])) {
				foreach($item['tag'] as $tag) {
					if($tag == 'MILESTONE') {
						$is_mile = true;
					}
				}
			}
			if(!$is_mile) echo '<li>';
			else echo '<li class="milestone">';
			if(isset($item['tag'])) {
				foreach($item['tag'] as $tag) {
					print_tag($tag);
				}
			}
			if($is_mile) echo '<strong>' . LNG('ver.desc.milestone');
			echo htmlspecial2($item['text']);
			if($is_mile) echo '</strong>';

			if(isset($item['extra'])) {
				echo '<br>';
				print_items($item['extra']);
			}

			echo '</li>';
		}
		echo '</ul>';
	}
?>

<?php
	$ver_list = [];
	foreach($data['versions'] as $ver => $item) {
		$ver_list = array_merge([$ver],$ver_list);
	}
	foreach($ver_list as $ver) {
		$item = $data['versions'][$ver];

		$count = 0;
		foreach($item['changes'] as $list_item) {
			if(!isset($list_item['tag']) || !in_array('MILESTONE',$list_item['tag'])) {
				$count++;
			}
		}
		echo '<div class="cdp-page" data-cdp-name="'.htmlspecial2($ver).'">';
		echo '<div class="txmp-page-full">';
		echo '<p class="ver-header"><strong>';
		echo htmlspecial2($ver);
		echo '</strong>';
		echo '&nbsp;(' . $count . ')';
		echo '&nbsp;&nbsp;';
		if(isset($item['date'])) {
			echo '<span class="txmp-tag tag-default">';
			echo $item['date'];
			echo '</span>';
		} else if(0 == 1) {
			echo '<span class="txmp-tag tag-default">';
			echo LNG('ver.type.unknown_date');
			echo '</span>';
		}
		if(intval(substr($ver,1,3)) < 114) {
			echo '<span class="txmp-tag tag-purple-g">' . LNG('ver.type.classic') . '</span>';
		} else if(strstr($ver,'-pre')) {
			echo '<span class="txmp-tag tag-deep-orange-l">' . LNG('ver.type.preview') . '</span>';
		} else {
			echo '<span class="txmp-tag tag-green-g">' . LNG('ver.type.release') . '</span>';
		}
		if(isset($item['tag'])) {
			foreach($item['tag'] as $tag) {
				if($tag == 'OFFLINE_DEV') {
					echo '<span class="txmp-tag tag-cyan-g">' . LNG('ver.type.offline_dev') . '</span>';
				} else if($tag == 'UNUSABLE') {
					echo '<span class="txmp-tag tag-red-l">' . LNG('ver.type.bugged') . '</span>';
				} else if($tag == 'EMERGENCY') {
					echo '<span class="txmp-tag tag-purple-g">' . LNG('ver.type.emergency') . '</span>';
				} else if($tag == 'STABLE') {
					echo '<span class="txmp-tag tag-blue-g">' . LNG('ver.type.latest_stable') . '</span>';
				} else if($tag == 'WIP') {
					echo '<span class="txmp-tag tag-orange-g">' . LNG('ver.type.wip') . '</span>';
				}
			}
		}
		if($data['current'] == $ver) {
			echo '<span class="txmp-tag tag-canonical">' . LNG('ver.type.current') . '</span>';
		}
		echo '</p>';
		if(isset($item['featured'])) {
			echo '<p>' . LNG('ver.desc.featured');
			echo htmlspecial2($item['featured']);
			echo '</p>';
		}
		print_items(array_reverse($item['changes']));
		echo '</div>';
		echo '</div>';
	}
?>
<?php if(isset($data['issues'])) { ?>
<div class="cdp-page" data-cdp-name="<?php LNGe('ver.caption.issues') ?>">
	<div class="txmp-page-full">
		<p class="ver-header"><strong><?php LNGe('ver.caption.issues') ?></strong>&nbsp;(<?php echo count($data['issues']) ?>)</p>
		<?php print_items($data['issues']) ?>
	</div>
</div>
<?php } ?>

<script>
var cdp_nav_gettext = function(ele) {
	return ele.children[0].children[0].innerHTML;
}
</script>
<script src="<?php echo BASIC_URL ?>static/js/common/cdp-page.js"></script>
<script>
turn_page(pagecount - currpage);
</script>
