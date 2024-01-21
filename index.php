<?php
error_reporting(~E_ALL);

define("IN_SYSTEM",'WMSDFCL/txmp');
define("VERSION","129a-pre1");
define("CSV_VERSION","1");
define("DATAVER","201805");
define("BASIC_PATH",str_replace("\\","/",__DIR__)."/");

define("LIB_PATH",BASIC_PATH."lib/");
define("ROUTER",LIB_PATH."router/");
define("CONTROLLER",LIB_PATH."controller/");
define("TEMPLATE",LIB_PATH."template/");
define("FUNCTIONS",LIB_PATH."function/");
define("CRAWLER",LIB_PATH."crawler/");
define("VLUSER",LIB_PATH."vluser/");
define("I18N",LIB_PATH."i18n/lang/");

define("DATA_PATH",BASIC_PATH."data/");
define("REMOTE_CACHE",DATA_PATH."remotecache/");
if(!file_exists(REMOTE_CACHE) && file_exists(DATA_PATH)) mkdir(REMOTE_CACHE); //117a
define("STATISTICS",DATA_PATH."stat/");
define("RAW",LIB_PATH."raw/");
define("USER_DATA",DATA_PATH."user/");
if(!file_exists(USER_DATA) && file_exists(DATA_PATH)) mkdir(USER_DATA); //124a
define("I18N_USER",DATA_PATH."i18n/");

define("STATICS",BASIC_PATH."static/");

define("CHANGELOG",BASIC_PATH.'changelog/versions.json');

if(!file_exists(DATA_PATH)) {
	die('Data Path Not Exist');
}

require(FUNCTIONS."index.function.php");
require(LIB_PATH.'dev_config.php');
require(BASIC_PATH.'internal_config/config_basic.php');

if(!defined("FILES")) {
	define("FILES",DATA_PATH."music/");
}

require(LIB_PATH.'i18n/i18nCore.php');
require(ROUTER."DataDrivenRouter.router.php");
require(BASIC_PATH.'internal_config/config_misc.php');
if(!defined('GC_COLOR_1')) {
	define("GC_COLOR_1","NULL");
}
if(!defined('GC_COLOR_2')) {
	define("GC_COLOR_2","NULL");
}
require(CRAWLER."index.crawler.php");
require(VLUSER."index.vluser.php");

header('Referrer-Policy: same-origin');

/**
 * 默认配置函数
 * 注意：请勿修改其中的配置选项。要修改配置，使用 configuration.php。
 */
function _CT($i){
	if(isset(_C()[$i])) return _C()[$i];
	$arr = array(
		"app_name" => LNG('config.app_name'), // 软件名称自定义
		"app_name_title" => LNG('config.app_title'),
		"app_desc" => LNG('config.app_desc'), // 应用程序描述

		"timezone" => 0, // 时区校准（请勿使用）
		"cache_expire" => 24*60*60*30, // RemotePlay 缓存有效时长
		"cache_expire_invalid" => 24*60*60*2, // RemotePlay 失败缓存有效时长
		"cache_refresh_chance" => 0.35, // RemotePlay 在特定条件下自动尝试刷新缓存的概率
		"temp_expire" => 3600, // 歌词本临时缓存有效时长

		"rp_search_retry" => 4, // RemotePlay 搜索查询失败后的最大重试次数
		"rp_search_retry_delay" => 0.1,
		"rp_can_pay_play" => false,
		"rp_pay_play_admin_only" => false,

		"can_register" => false, // 能否注册
		"ip_reg_limit" => 3, // 单个IP地址注册数量限制

		"user_playlist_quota" => 64, // 用户最大歌单数
		"user_playlist_limit" => 76800, // 单个歌单最大尺寸
		"user_exam_quota" => 50, // 允许用户创建的最大试卷数量 [NYI]
		"user_submission_capacity" => 20, // 保留的最近用户提交记录数量 [NYI]
		"exam_problem_limit" => 512, // 试卷中允许的最大的试题数量 [NYI]

		"offline_usage" => false, // 是否支持在客户端不能接入互联网时使用 [NYI]
		"compiled_cache" => false, // 对已转换的歌词文件进行缓存 [NYI]

		"show_comp_process" => false, // 在调试代码页显示完整编译过程
		"debug" => false, // 是否调试模式 [NYI]
	);
	if(!isset($arr[$i])) {
		throw new Exception('Undefined config key ' . strval($i));
	}
	return $arr[$i];
}

if(_CT('debug')) {
	error_reporting(E_ALL & (~E_NOTICE));
}

// 不允许没有密钥
if(!defined('PASS_KEY') || strlen(PASS_KEY) <= 10) {
	die(LNG('init.empty_key'));
}
// 不允许没有应用前缀
if(!defined('APP_PREFIX')) {
	die(LNG('init.empty_prefix'));
}

// 尝试垃圾清理
if(mt_rand(1,20) <= 2) {
	(new GarbageCleaner())->clean();
}

if(!isset($_GET['_lnk'])) $_GET['_lnk']="";
$GLOBALS['_lnk'] = $_GET['_lnk'];
$router=new DataDrivenRouter($GLOBALS['_lnk']);
$router->route();
