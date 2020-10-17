<?php
error_reporting(E_ALL & (~E_NOTICE));
//error_reporting(~E_ALL);

/*
  最小接受的实际分辨率是：942x550
  仅支持缩放至 100% 125% 150% 250% 的页面
*/

define("IN_SYSTEM",'yezhiyi9670/txmp');
define("VERSION","126d-pre8");
define("CSV_VERSION","1");
define("BASIC_PATH",str_replace("\\","/",__DIR__)."/");
define("LIB_PATH",BASIC_PATH."app/");
define("ROUTER",LIB_PATH."router/");
define("TEMPLATE",LIB_PATH."template/");
define("FUNCTIONS",LIB_PATH."function/");
define("CRAWLER",LIB_PATH."crawler/");
define("VLUSER",LIB_PATH."vluser/");
define("DATA_PATH",BASIC_PATH."data/");
define("FILES",DATA_PATH."music/");
define("REMOTE_CACHE",DATA_PATH."remotecache/");
if(!file_exists(REMOTE_CACHE) && file_exists(DATA_PATH)) mkdir(REMOTE_CACHE); //对117a以下的版本，防止文件夹不存在。
define("STATISTICS",DATA_PATH."stat/");
define("RAW",LIB_PATH."raw/");
define("USER_DATA",DATA_PATH."user/");
if(!file_exists(USER_DATA) && file_exists(DATA_PATH)) mkdir(USER_DATA); //对124a以下的版本，防止文件夹不存在。
define("CHANGELOG",BASIC_PATH.'changelog/versions.json');

if(!file_exists(DATA_PATH)) {
	die('Data Path Not Exist');
}

require(ROUTER."TopLevelRouter.class.php");
require(FUNCTIONS."index.function.php");
require(BASIC_PATH.'configuration.php');
if(!defined('GC_COLOR_1')) {
	define("GC_COLOR_1","NULL");
}
if(!defined('GC_COLOR_2')) {
	define("GC_COLOR_2","NULL");
}
require(CRAWLER."index.crawler.php");
require(VLUSER."index.vluser.php");

function _CT($i){
	if(isset(_C()[$i])) return _C()[$i];
	else return array(
		"timezone" => 0, //时区校准
		"cache_expire" => 24*60*60*2,
		"temp_expire" => 3600,

		"can_register" => false,
		"ip_reg_limit" => 3,

		"user_playlist_quota"   => 20,
		"user_exam_quota" => 50, // 允许用户创建的最大试卷数量
		"user_submission_capacity" => 20, // 保留的最近用户提交记录数量

		"exam_problem_limit" => 512, // 试卷中允许的最大的试题数量

		"offline_usage" => false,

		"compiled_cache" => false, // 对已转换的歌词文件进行缓存
	)[$i];
}

$cleaner=new GarbageCleaner();
$cleaner -> clean();

if(!isset($_GET['_lnk'])) $_GET['_lnk']="";
$GLOBALS['_lnk'] = $_GET['_lnk'];
$router=new TopLevelRouter($GLOBALS['_lnk']);
if(!($router->route())) {
	print404("Not Found");
}
