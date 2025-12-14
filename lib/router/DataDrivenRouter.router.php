<?php
use function WMSDFCL\RouterFramework\router_variable\check_url_variable;
?><?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

require(LIB_PATH . 'router/variable/router_variable.php');

/**
 * Router map 节点格式说明：
 * {
 *     dest: 目标控制器
 *     csrf: 声明经过此节点需要进行 CSRF 检测
 *     variable: 在 children 标签处获得的变量，存储于此名称（将进入 $_REQUEST, $_GET 数组）
 *     param: 对象，表示此处直接进入 $_REQUEST, $_GET 数组的内容
 *     require: 必须要求有值的参数，若缺失则会 404
 *     target: 此节点导航到其他 map 文件（此节点不能定义 dest）。此节点数据将与该文件根节点合并
 *     children: {
 *         名称: 子节点
 *         #变量类型: 子节点
 *     }
 * }
 * 
 * 关于变量类型的定义与验证，请看 variable/router_variable.php。
 */

/**
 * 检查CSRF攻击（不返回，错误即终止）
 */
function checkCSRF($isCheck) {
	if($isCheck && !_C('csrf_bypass')) {
		if(!isset($_COOKIE['X-'.APP_PREFIX.'-csrf']) || !isset($_COOKIE['X-'.APP_PREFIX.'-csrf'][$_REQUEST['csrf-token-name'] ?? '']) || $_COOKIE['X-'.APP_PREFIX.'-csrf'][$_REQUEST['csrf-token-name'] ?? ''] !== $_REQUEST['csrf-token-value'] ?? '') {
			// 这里主要针对 API，故不设置返回码
			show_error(429, 'csrf_fail', 'Missing CSRF token.');
			exit;
		}
	}
	if(!isset($_COOKIE['X-'.APP_PREFIX.'-csrf']) || !is_array($_COOKIE['X-'.APP_PREFIX.'-csrf']) || count($_COOKIE['X-'.APP_PREFIX.'-csrf'])==0) {
		$GLOBALS['sess'] = randAlnumString(32); // 创建新会话
		$GLOBALS['token'] = randAlnumString(32);
		setcookie('X-'.APP_PREFIX.'-csrf['.$GLOBALS['sess'].']',$GLOBALS['token'],time()+86400,'/');
	} else {
		if(is_array($_COOKIE['X-'.APP_PREFIX.'-csrf'])) {
			foreach($_COOKIE['X-'.APP_PREFIX.'-csrf'] as $k=>$v) {
				$GLOBALS['sess']=$k;
				$GLOBALS['token']=$v;
				break;
			}
		}
	}
}

/**
 * 页面判断与分发
 * 采用数据驱动方式，详见 lib/router/map/root.json5 和 lib/router/variable/router_variable.php
 * 添加新页面时，此程序无需修改。
 */
class DataDrivenRouter {
	function __construct() {}

	public function openController($id) {
		// echo $id;
		// echo "\n";
		// print_r($_GET);
		$dest = CONTROLLER . $id . '.dest.php';
		if(file_exists($dest)) {
			require($dest);
		} else {
			$param_txt = [];
			foreach($_GET as $key => $val) {
				if(!_CT('debug') || in_array($key,['_lnk'])) continue;
				$param_txt[] = $key . ': ' . json_encode($val,JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES);
			}
			set_response_code(500, 'Internal Error');
			$this->openController('error/dest-tan90');
		}
	}

	public function route() {
		if(!isset($_GET['_lnk'])) {
			$_GET['_lnk'] = '';
		}
		// 修复奇奇怪怪的问题
		$_GET['_lnk'] = str_replace(' ','+',$_GET['_lnk']);

		$currdir = __DIR__ . '/map/';
		$curr = json5_decode_file($currdir . 'root.json5', true);
		$curr_url = trim(preSubstr($_GET['_lnk'],'?'));
		$require_csrf = false;
		$no_setting_upd = false;

		if($_GET['_lnk'] != '' && $_GET['_lnk'][strlen($_GET['_lnk']) - 1] == '/') {
			if(isset($curr['globals'])) {
				foreach($curr['globals'] as $key => $item) {
					$GLOBALS[$key] = $item;
				}
			}

			$_REQUEST['_dest'] = $_GET['_dest'] = 'error/404';
			$this->openController('error/404');
			return;
		}

		while(true) {
			while(isset($curr['target'])) {
				if(isset($curr['param'])) {
					// Param 固定参数值设定（可能需要）
					foreach($curr['param'] as $key => $item) {
						$_REQUEST[$key] = $_GET[$key] = $item;
					}
				}
				if(isset($curr['globals'])) {
					foreach($curr['globals'] as $key => $item) {
						$GLOBALS[$key] = $item;
					}
				}
				if(isset($curr['require'])) {
					// Require 检查
					foreach($curr['require'] as $key) {
						if(!isset($_REQUEST[$key]) || $_REQUEST[$key] == '') {
							$this->openController('error/404');
							return;
						}
					}
				}
				if(isset($curr['csrf']) && $curr['csrf']) {
					$require_csrf = true;
				}
				if(isset($curr['nosetting']) && $curr['nosetting']) {
					$no_setting_upd = true;
				}
				// 文件调用
				$filename = $currdir . $curr['target'];
				$curr = json5_decode_file($filename, true);
				$currdir = dirname($filename) . '/';
			}
			if(isset($curr['param'])) {
				// Param 固定参数值设定
				foreach($curr['param'] as $key => $item) {
					$_REQUEST[$key] = $_GET[$key] = $item;
				}
			}
			if(isset($curr['globals'])) {
				foreach($curr['globals'] as $key => $item) {
					$GLOBALS[$key] = $item;
				}
			}
			if(isset($curr['require'])) {
				// Require 检查
				foreach($curr['require'] as $key) {
					if(!isset($_REQUEST[$key]) || $_REQUEST[$key] == '') {
						$this->openController('error/404');
						return;
					}
				}
			}
			if(isset($curr['csrf']) && $curr['csrf']) {
				$require_csrf = true;
			}
			if(isset($curr['nosetting']) && $curr['nosetting']) {
				$no_setting_upd = true;
			}
			if(strlen($curr_url) == 0) {
				// 完成。
				if(isset($curr['dest'])) {
					$_REQUEST['_dest'] = $_GET['_dest'] = $curr['dest'];
					checkCSRF($require_csrf);
					if(!$no_setting_upd) {
						setting_upd();
					}
					$this->openController($curr['dest']);
					return;
				}
			}
			$success = false;
			if($curr['children'] ?? null) foreach($curr['children'] as $cond => $next) {
				$firstword = preSubstr($curr_url, '/');
				if($cond == '#path') {
					// 末尾路径参数，一步到位
					$curr = $next;
					if(isset($curr['variable'])) {
						$key = $curr['variable'];
						$_REQUEST[$key] = $_GET[$key] = $curr_url;
					}
					$curr_url = '';
					$success = true;
					break;
				} else if($cond[0] == '#') {
					$var_val = check_url_variable(substr($cond,1),$firstword);
					if($var_val !== false) {
						// 可变参数判定成功
						$curr = $next;
						if(isset($curr['variable'])) {
							$key = $curr['variable'];
							$_REQUEST[$key] = $_GET[$key] = $var_val;
						}
						$curr_url = strip_first($curr_url, '/');
						$success = true;
						break;
					}
				} else {
					// 固定参数
					if($cond == $firstword) {
						$curr = $next;
						if(isset($curr['variable'])) {
							$key = $curr['variable'];
							$_REQUEST[$key] = $_GET[$key] = $firstword;
						}
						$curr_url = strip_first($curr_url, '/');
						$success = true;
						break;
					}
				}
			}

			// 未成功。
			if(!$success) {
				$_REQUEST['_dest'] = $_GET['_dest'] = 'error/404';
				set_response_code(404, 'Not Found');
				$this->openController('error/404');
				return;
			}
		}
	}
}
