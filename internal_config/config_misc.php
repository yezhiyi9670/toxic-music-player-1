<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

//////// 杂项 ////////

function _C(){
	return array(
		"app_name" => LNG('config.app_name'),//软件名称自定义
		"app_name_title" => LNG('config.app_title'),
		"app_desc" => LNG('config.app_desc'), // 软件描述
		"timezone" => 0, //时区校准
		"cache_expire" => 24*60*60*93, //爬虫缓存时间（用于查看。爬虫较慢，建议缓存3个月）
		"cache_expire_inivalid" => 24*60*60*2,
		"cache_refresh_chance" => 0.35, // RemotePlay 在特定条件下自动尝试刷新缓存的概率
		"temp_expire" => 3600, //歌词本缓存时间（用于下载。建议1小时）

		"rp_search_retry" => 4, // RemotePlay 搜索查询失败后的最大重试次数
		"rp_search_retry_delay" => 0.1,
		"rp_can_pay_play" => false, // 目前已不再支持
		"rp_pay_play_admin_only" => false,

		"can_register" => true, // 允许用户注册
		"ip_reg_limit" => 1, // 一个IP的注册限制量

		"user_playlist_quota" => 128, // 一个用户允许创建的最多歌单数量
		"user_playlist_limit" => 76800,
		"user_exam_quota" => 50, // 允许用户创建的最大试卷数量
		"user_submission_capacity" => 20, // 保留的最近用户提交记录数量

		"exam_problem_limit" => 512, // 试卷中允许的最大的试题数量

		"offline_usage" => true, // 是否在离线环境下使用（将避免外源字体加载）

		"compiled_cache" => true, // 对已转换的歌词文件进行缓存

		"debug" => true, // 是否调试模式
		"show_comp_process" => true, // 在调试代码页显示完整编译过程
	);
}

//////// 时区 ////////

date_default_timezone_set('Asia/Shanghai'); // 系统时区
