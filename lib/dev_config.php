<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 仅限调试
// 千万不要在生产环境中使用这里暗示的方法进行配置。

if(file_exists(DATA_PATH . 'dev/passkey/passkey.txt')) {
	define('PASS_KEY',trim(file_get_contents(DATA_PATH . 'dev/passkey/passkey.txt')));
}
