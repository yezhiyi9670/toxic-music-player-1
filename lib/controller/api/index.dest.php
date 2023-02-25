<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

show_json(200, [
	'namespace' => IN_SYSTEM,
	'app_prefix' => APP_PREFIX,
	'app_name' => _CT('app_name'),
	'version' => VERSION,
	'base_url' => BASIC_URL,
	'username' => uauth_username()
]);
