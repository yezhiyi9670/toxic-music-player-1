<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// getID3 音频处理
require(FUNCTIONS."getid3/getid3.php");

// Mime、网络和文件操作等基本功能
require(FUNCTIONS."mime.function.php");

// 网页模板
require(FUNCTIONS."tpl.function.php");

// 用户登录模块
require(FUNCTIONS."uauth.function.php"); 

// 编译功能
require(FUNCTIONS."compiler.function.php");

// RemotePlay相关（专用）
require(FUNCTIONS."remoteplay.function.php");

// 歌曲数据
require(FUNCTIONS."metadata.function.php");

// 超级管理员登录（已弃用）
require(FUNCTIONS."authenticate.function.php");

// 用户设置（目前存储在浏览器中）
require(FUNCTIONS."usersetting.function.php");

// 过期缓存清除
require(FUNCTIONS."gc.function.php");

// 输出某些列表项的函数，在此统一
require(FUNCTIONS."lists.function.php");

// 输出字体样式的代码
require(FUNCTIONS."font.function.php");

// “随机”数
require(FUNCTIONS."hashed_rand.class.php");

// Router 类功能
require(FUNCTIONS."routing.function.php");
