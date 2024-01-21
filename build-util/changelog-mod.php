<?php

/*
	changelog_updater
	将 changelog.txt 转换为 version.json
*/

function print_log($x) {
	echo $x . "\n";
}

function indent_count($x) {
	$ret = 0;
	for($ret = 0; $ret < strlen($x); $ret++) {
		if($x[$ret] != ' ') {
			break;
		}
	}
	return floor($ret / 2);
}

$data = [];

$txt = file_get_contents('../changelog/changelog.txt');
$txt = str_replace(["\r\n","\r"],["\n","\n"],$txt);

$txt = explode("\n",$txt);

$current_version = "";
$present_ver = "";
$desc_path = [];

$data['versions'] = [];

foreach($txt as $line) {
	if(trim($line) == '') continue;

	$level = indent_count($line);
	$line = trim($line);

	if($level == 0) {
		$tokens = explode(' ', $line);
		$ver = $tokens[0];
		$feature = '';
		if(strstr($ver,'（Feature：')) {
			$feature = strstr($ver,'（Feature：');
			$feature = substr($feature,strlen('（Feature：'));
			$feature = substr($feature,0,strlen($feature) - strlen('）'));
			$ver = substr($ver,0,strpos($ver,'（Feature：'));
		}
		$current_version = $ver;
		print_log("Version: ".$ver);

		$date = '';
		$tags = [];
		$isCurrent = false;

		foreach($tokens as $index => $item) {
			if($index == 0) continue;
			if($item == '') continue;
			if(substr($item,0,strlen('更新时间：')) == '更新时间：') {
				$date = substr($item,strlen('更新时间：'));
			}
			if(substr($item,0,strlen('--更新时间：')) == '--更新时间：') {
				$date = substr($item,strlen('--更新时间：'));
			}

			if($item == '[线下开发]') {
				$tags[] = 'OFFLINE_DEV';
			}
			if($item == '[当前版本]') {
				$present_ver = $current_version;
			}
			if($item == '[有严重问题]' || $item == '[严重问题]') {
				$tags[] = 'UNUSABLE';
			}
			if($item == '[紧急修复]') {
				$tags[] = 'EMERGENCY';
			}
			if($item == '[正式版]') {
				$tags[] = 'STABLE';
			}
			if($item == '[WIP]') {
				$tags[] = 'WIP';
			}
		}

		if($current_version != '需要注意') {
			$data['versions'][$current_version] = [
				'version' => $current_version,
				'tag' => $tags
			];
			if($feature != '') {
				$data['versions'][$current_version]['featured'] = $feature;
			}
			if($date != '') {
				$data['versions'][$current_version]['date'] = $date;
			}
			$data['versions'][$current_version]['changes'] = [];
		} else {
			$data['issues'] = [];
		}

		$desc_path = [];
	} else if($level >= 1) {
		if($current_version == '') continue;
		$heading = '';
		if($current_version != '需要注意') {
			$heading = '$data["versions"]["' . $current_version . '"]' . "['changes']";
		} else {
			$heading = '$data' . "['issues']";
		}

		$desc_path = array_slice($desc_path,0,$level-1);
		
		$dest = $heading;
		foreach($desc_path as $index => $val) {
			$dest .= '[' . $val . ']';
			$dest .= '["extra"]';
		}
		
		$is_arr = eval('return isset(' . $dest . ');');
		if($is_arr) {
			$ins_idx = eval('return count(' . $dest . ');');
		} else {
			$ins_idx = 0;
			eval($dest . ' = [];');
		}
		$txt = trim($line);
		if(substr($txt,0,2) == '- ') {
			$txt = substr($txt,2);
		}
		$tags = [];
		$tag_p = [];
		if(substr($txt,0,1) == '[') {
			$tags = substr($txt,0,strpos($txt,' '));
			$txt = trim(substr($txt,strpos($txt,' ')+1));
			$tags = substr($tags,1,strlen($tags) - 2);
			$tags = explode('][',$tags);
		}
		$has_mile = false;
		$has_problem = false;
		foreach($tags as $tag) {
			if($tag == '修改') {
				$tag_p[] = 'CHANGE';
			} else if($tag == '添加' || $tag == '新增') {
				$tag_p[] = 'ADD';
			} else if($tag == '修复' || $tag == '更改') {
				$tag_p[] = 'FIX';
			} else if($tag == '清理') {
				$tag_p[] = 'CLEAN';
			} else if($tag == '破坏') {
				$tag_p[] = 'BREAKING';
			} else if($tag == '创建' || $tag == '初始') {
				$tag_p[] = 'INIT';
			} else if($tag == '发布') {
				$tag_p[] = 'PUBLISH';
			} else if($tag == '移除') {
				$tag_p[] = 'REMOVE';
			} else if($tag == '里程') {
				$tag_p[] = 'MILESTONE';
				$has_mile = true;
			} else if($tag == '危') {
				$tag_p[] = 'EXPERIMENTAL';
			} else if($tag == '问题') {
				$tag_p = ['ISSUE'];
				$has_problem = true;
			}
		}
		if($current_version == '需要注意' && $level == 1 && !$has_mile && !$has_problem) {
			$tag_p = ['ISSUE'];
		}
		eval($dest . '[' . $ins_idx . '] = ["text"=>"' . str_replace("\'","'",addslashes($txt)) . '"];');
		if(count($tag_p) > 0) {
			eval($dest . '[' . $ins_idx . ']["tag"] = $tag_p;');
		}

		$desc_path[] = $ins_idx;
	}
}

$data = array_merge(['current' => $present_ver],$data);

// print_log('Test ' . $data['versions']['v127a-pre12']['changes'][0]['extra'][0]['text']);

print_log('Writing ' . count($data['versions']) . ' versions');
$final_str = str_replace('    ',"\t",
	json_encode($data,JSON_PRETTY_PRINT*0+JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES)
);
file_put_contents('../changelog/versions.json',$final_str);
