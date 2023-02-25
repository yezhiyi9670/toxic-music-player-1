<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$frag = $_REQUEST['type'];

$tpl_name = [
	'lyric-overview' => 'lyric_overview',
	'lyric-content' => 'lyric_content',
	'fr' => 'firstrow',
	'tr' => 'thirdrow',
	'trn' => 'thirdrow-n'
][$frag];

tpl('player/' . $tpl_name);
