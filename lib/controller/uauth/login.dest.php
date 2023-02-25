<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!!uauth_username()) {
	header('HTTP/1.1 302 Not Authenticated');
	header('Location: '.BASIC_URL.'user');
	exit;
}

include_header();
tpl('uauth/login');
include_footer();
