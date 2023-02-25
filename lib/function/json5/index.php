<?php
// 目测这里不需要调用合法性检测

require(__DIR__ . '/Comment.php');
require(__DIR__ . '/Formatter.php');
require(__DIR__ . '/Parser.php');
require(__DIR__ . '/RuntimeException.php');
require(__DIR__ . '/JSON5.php');

use RedCat\JSON5\JSON5;

/**
 * 解析 json5 字符串
 */
function json5_decode($str, $assoc = false, $keepComments = false) {
  if(!is_string($str)) {
    return null;
  }
  return JSON5::decode($str,$assoc,$keepComments);
}

/**
 * 解析 json5 文件
 */
function json5_decode_file($str, $assoc = false, $keepComments = false) {
  return JSON5::decodeFile($str, $assoc, $keepComments);
}

/**
 * 编码 json5
 * 注意：若键名符合变量名规则，键名的引号将被舍弃。因此即使不保留注释，输出值可能也不符合传统 json 规范。
 */
function json5_encode($str, $options = 0, $keepComments = false) {
  return JSON5::encode($str,$options,$keepComments);
}
