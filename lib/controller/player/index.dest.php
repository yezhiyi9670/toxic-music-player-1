<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(), false)) {
	print404('No Such Music');
}
checkPermission('music/index', cid());
include_header();
tpl("player/index");
include_footer();
