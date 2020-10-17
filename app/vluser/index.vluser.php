<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class VlUserSyscall {
    function __construct() {
        
    }
    
    function allplay_obj($menu,$name='播放器存储') {
        $obj = [];
        $obj['public'] = true;
        $obj['title'] = $name;
        $obj['playlist'] = [];
        foreach($menu as $item) {
            if(isValidMusic($item,false) && (getPerm($item)['music/index'] || is_root())) {
                $obj['playlist'][count($obj['playlist'])] = [
                    'id' => $item,
                    'canonical' => $item,
                    'rating' => 0
                ];
            }
        }
        $obj['transform'] = [
            'pick' => (setting_gt('allplay-rand') == 'Y' || setting_gt('allplay-rand') == 'y') ? 'rand' : 'next',
            'random_shuffle' => false,
            'constraints' => [
                'comparator' => '>=',
                'multiplier' => 0,
                'delta' => -1,
            ],
            'termination' => 'loop'
        ];
        
        return $obj;
    }
    
    function data($cate,$name,$flag) {
        if($cate == 'playlist') {
            // The All-internal playlist
            if($name == '0') {
                if(!$flag) return true;
                else {
                    $menu=dir_list(FILES);
                    return $this -> allplay_obj($menu);
                }
            }
            // 酷我音乐的全部播放
            if(strlen($name) > 4 && substr($name,0,4) == '1701') {
                $id = substr($name,4);
                @$data = json_decode(kuwo_search_httpget('http://kuwo.cn/api/www/playlist/playListInfo?pid='.$id.'&pn=1&rn=1024'),true);
                if($data['code'] != '200') {
                    if(!$flag) return false;
                    else {
                        return null;
                    }
                }
                if(!$flag) return true;
                $lst = [];
                foreach($data['data']['musicList'] as $item) {
                    $lst[count($lst)] = 'K_'.$item['rid'];
                }
                // var_dump($this -> allplay_obj($lst,$data['data']['name']));exit;
                return $this -> allplay_obj($lst,$data['data']['name']);
            }
        }
        
        if($flag) return null;
        else return false;
    }
    
    function dataExist($cate,$name) {
        return $this -> data($cate,$name,false);
    }
    
    function fetchData($cate,$name) {
        return $this -> data($cate,$name,true);
    }
};

global $__syscall;
$__syscall = new VlUserSyscall();
