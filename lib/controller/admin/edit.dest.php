<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false,false)) {
	print404('No Such Music');
}
checkPermission('admin/edit', cid());

include_header();
tpl("admin/editor");
include_footer();
