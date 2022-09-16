<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$nmlist = getListOf('ann');
$target = $_REQUEST['key'];

if(!isset($nmlist[$target])) {
	echo '<p>';
	LNGe('admin.ann.tan90',htmlspecial2($target));
	echo '</p>';
}

$menu=dir_list(FILES);
$tot = 0;
$nissue = 0;

$found_data = [];

// 查找
foreach($menu as $item) {
	if(isValidMusic($item,false)) {
		// printAdminList($item);

		$ft = getLyricFile($item);
		$ft = explode("\n",str_replace("\r\n","\n",$ft));

		foreach($ft as $lineid => $linecont) {
			$linecont = trim($linecont);
			if(substr($linecont,0,2) == '//') {
				$linecont = ltrim($linecont,'/');
				$linecont = trim($linecont);
				if(substr($linecont,0,strlen($target)+1) == '@' . $target) {
					$linecont = substr($linecont, strlen($target)+1);
					$ch = $linecont[0];
					if(strlen($linecont) < 1 || trim($ch) != $ch) {
						// 锁定成功。取出标记内容。
						$linecont = trim($linecont);
						if(!is_array($found_data[$item] ?? null)) {
							$found_data[$item] = [];
							$tot++;
						}
						$found_data[$item][] = [
							'line' => $lineid + 1,
							'text' => '<strong>' . $nmlist[$target] . COLON . '</strong>' . htmlspecial2($linecont),
							'text_html' => true
						];
						$nissue++;
					}
				}
			}
		}
	}
}

// 统计
echo '<p>';
if($tot == 0) {
	LNGe('admin.ann.empty',$target);
} else {
	LNGe('admin.ann.summary',$target,$tot,$nissue);
}
echo '</p>';

// 展示
foreach($found_data as $item => $msglist) {
	echo '<div class="ann-list-item ann-list-item-'.$item.'">';

	echo '<ul>';
	printAdminList($item);
	echo '</ul>';

	echo '<div class="ann-close">';
	echo '<a href="javascript:;" onclick="$(\'.ann-list-item-'.$item.'\').remove()">';
	echo '<i class="fa fa-ban"></i> ' . LNG('admin.hide');
	echo '</a>';
	echo '</div>';

	$ft = getLyricFile($item);

	echo '<div class="codeblock codeblock-white ann-info">';
	echo getCompileIssueMsg($item . '/lyric.txt',$ft,$msglist);
	echo '</div>';

	echo '</div>';

}

?>

<script>
	$('[data-am-dropdown]').dropdown();
</script>

