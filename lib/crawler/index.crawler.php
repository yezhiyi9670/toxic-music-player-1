<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function rp_can_pay_play() {
	return _CT('rp_can_pay_play') && (!_CT('rp_pay_play_admin_only') || is_root());
}

// 内置提供器
require(CRAWLER.'legacy.crawler.php');

// 酷我音乐爬虫程序（akCrawler）  by yezhiyi
require(CRAWLER.'kuwo.crawler.php');
