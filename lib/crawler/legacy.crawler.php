<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function IDSearchSong() {
	$keyword = $_GET['key'];
	if(!isValidMusic($keyword)) {
		echo LNG('rp.search.no_such_id');
		exit;
	}
	echo '<ul>';
	printIndexList($keyword, true);
	echo '</ul>';
	exit;
}
