<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false,false)) {
	print404('No Such Music');
}
checkPermission('admin/edit');

include_header();
tpl("admin/resource");
include_footer();
