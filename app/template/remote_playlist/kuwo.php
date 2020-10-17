<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>document.title='<?php $ioi = $GLOBALS['remote_playlist'];echo htmlspecial2($ioi['data']['name']) ?> - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
    
    <h3>酷我歌单：<?php echo htmlspecial($ioi['data']['name']) ?><span style="font-size:12px;font-weight:normal;">&nbsp;by <?php echo htmlspecial2($ioi['data']['uname']) ?></span></h3>
    
    <p><a target="_blank" href="http://kuwo.cn/playlist_detail/<?php echo $GLOBALS['remote_playlist_id'] ?>">在酷我音乐查看</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo BASIC_URL ?>playlist/__syscall/1701<?php echo $GLOBALS['remote_playlist_id'] ?>">全部播放</a></p>
    
    <div id="playlist-showbox"><?php
        $id = trim(substr($_GET['key'],1));
        @$data = $ioi;
        
        $npage=ceil($data['data']['total']/50.0);
        $startid=50*$_GET['pageid']-49;
        $endid=50*$_GET['pageid'];

        if(true) {
            if(!isset($data['data']['musicList'])) {
                echo '查询失败，请重试。';
                
                exit;
            }
            
            echo '<p>共有 '.$data['data']['total'].' 个项目</p>'."\n";
            echo '<ol>';
            foreach($data['data']['musicList'] as $item) {
                printRmpList($item);
            }
            echo '</ol>';
            echo '<p><a onclick="turn_page(1)">最前</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'turn_page(curr_pageid-1)').'">&lt; 上一页</a>&nbsp;&nbsp;';
        
            echo '页面'.$_GET['pageid'].'/'.$npage;
            echo '&nbsp;&nbsp;';
            echo ''.$data['data']['total'].'个中的第'.$startid.'-'.min($endid,$data['data']['total']).'个';
            
            echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'turn_page(curr_pageid+1)').'">下一页 &gt;</a>&nbsp;&nbsp;<a onclick="turn_page('.$npage.')">最后</a></p>';
        }
    ?></div>
</div>

<script>
    var curr_pageid = 1;
    function turn_page(pageid) {
        var dist = $('#playlist-showbox');
        curr_pageid=pageid;
        var al=modal_loading("稍等","正在查询");
        $.ajax({
            async:true,
            timeout:9000,
            dataType:"text",
            url:'<?php echo BASIC_URL ?>?isSubmit=KuwoSearch&key=^<?php echo $GLOBALS['remote_playlist_id'] ?>&pageid='+pageid,
            error:function(e){
                close_modal(al);
                modal_alert('出问题了','查询失败');
            },
            success:function(e){
                close_modal(al);
                dist.html(e);
            }
        });
    }
</script>
