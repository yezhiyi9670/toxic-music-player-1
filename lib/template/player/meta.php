<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$modified = modifiedTime(cid());

echo encode_data([
	'baseurl' => BASIC_URL . cid(),
	'song_id' => cid(),
	'src1' => getAudioUrl(preSubstr($_GET['_lnk'])),
	'src2' => getAudioUrl(preSubstr($_GET['_lnk']),"back","background"),
	'player_colored_css' => BASIC_URL . 'dynamic/css/player/player-colored.css?v=' . VERSION . '&w=' . is_wap() . '&A=X' . GCM()['A'] . '&S=X' . GCM()['X'] . '&G1=X' . GCM()['G1'] . '&G2=X' . GCM()['G2'],
	'main_colored_css' => BASIC_URL . 'dynamic/css/common/main-colored.css?v=' . VERSION . '&w=' . is_wap() . '&A=X' . GCM()['A'] . '&S=X' . GCM()['X'] . '&G1=X' . GCM()['G1'] . '&G2=X' . GCM()['G2'],

	'title' => LNG('player.title') . ' ‹ ' . GCM()['N'] . ' - ' . _CT('app_name_title'),
	'source' => isKuwoId(cid()) ? 'kuwo' : 'internal',
	'payment' => paymentStatus(cid()),

	'meta' => GCM(),
	'modified' => $modified,
	'audio_info' => getAudioAnalysis(cid()),
]);
