<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false,false)) {
	print404('No Such Music');
}
checkROOT();

include_header();
tpl("admin/permission");
include_footer();
