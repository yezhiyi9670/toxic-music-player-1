<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$menu=dir_list(FILES);
$tot = 0;
$nissue = 0;

$found_data = [];
$query_mask = [
	'all' => [],
	'unstd' => ['notice'],
	'unstd_only' => ['notice','warn','error','fatal'],
	'warn' => ['notice','unstd'],
	'error' => ['notice','unstd','warn'],
	'fatal' => ['notice','unstd','warn','error']
];

$mode = $_REQUEST['key'];
if(!isset($query_mask[$mode])) {
	$mode = 'all';
}

foreach($menu as $item) {
	if(isValidMusic($item,false)) {
		// printAdminList($item);

		$dbg = parseCmpLyric($item,true,true,'cmpi_ADD_ERROR_P');
		$cnt = countCompileIssue($dbg['message'],$query_mask[$mode]);
		if($cnt > 0) {
			$found_data[$item] = $dbg['message'];

			$tot++;
			$nissue += $cnt;
		}
	}
}

echo '<p>';
if($tot == 0) {
	LNGe('admin.comp.empty');
} else {
	LNGe('admin.comp.summary',$tot,$nissue,LNG('admin.compl.' . $mode));
}
echo '</p>';

foreach($found_data as $item => $msglist) {
	echo '<div class="comp-list-item comp-list-item-'.$item.'">';

	echo '<ul>';
	printAdminList($item);
	echo '</ul>';

	echo '<div class="comp-close">';
	echo '<a href="javascript:;" onclick="$(\'.comp-list-item-'.$item.'\').remove()">';
	echo '<i class="fa fa-ban"></i> ' . LNG('admin.hide');
	echo '</a>';
	echo '</div>';

	$ft = getLyricFile($item);

	echo '<div class="codeblock codeblock-white comp-info">';
	echo getCompileIssueMsg($item . '/lyric.txt',$ft,$msglist,1,$query_mask[$mode]);
	echo '</div>';

	echo '</div>';

}

?>

<script>
	$('[data-am-dropdown]').dropdown();
</script>
