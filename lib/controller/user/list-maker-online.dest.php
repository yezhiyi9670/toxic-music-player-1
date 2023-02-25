<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$id = $_REQUEST['list_id'];

if(uauth_username() && hasPlaylist(uauth_username(),$id) || uauth_has_data(uauth_username(),'playlist',$id.'.csv')) {
	include_header();
	tpl('user/listmaker');
	include_footer();
} else if(uauth_username()) {
	print404('Not Found');
} else {
	print401('Require Authentication');
}
