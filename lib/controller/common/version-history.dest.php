<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!file_exists(CHANGELOG)) {
	print404('Not Found');
}

include_header();
tpl('common/version_history');
include_footer();
