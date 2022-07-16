<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

//////// 色彩 ////////

define("MAIN_COLOR","E64A19"); // [默认]主颜色
define("GC_COLOR_1",MAIN_COLOR);
define("GC_COLOR_2","FFA000"); // 渐变色2

//////// 地址 ////////

define("BASIC_URL",str_replace('__hostname__',$_SERVER['HTTP_HOST'],"http://__hostname__/project/txmp/"));

//////// 存储 ////////

/**
 * 应用代号
 * 用于 Cookie 和 localStorage 前缀，允许多实例在同一网站运行，并阻止冲突。
 * 设置后，用户的登录状态、偏好设置、计次信息、进度信息等本地信息会全部失效，但系统不负责清除它们。
 */
define("APP_PREFIX",'nesic-player');

//////// 密码 ////////

/**
 * 此密钥用于最大程度保护用户密码安全
 * 建议随机。长度至少 11（建议 64）。
 * 一旦设置，不得更改，否则会错乱
 */
define("PASS_KEY",'undefined');
