<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php 
if(isset($_GET['isSubmit'])) {
    if($_GET['isSubmit']=='KuwoSearch') {
        kuwoSearchSong();
    }
    exit;
}
?>

<script>document.title='列表 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
    <?php if(!isset($_GET['iframe'])) { ?><h3 id="page-title-clickable">列表</h3>
    <p><a href="<?php echo BASIC_URL ?>user/login">用户登录</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo BASIC_URL ?>setting">用户设置</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo BASIC_URL ?>list-maker" target="_blank">歌单构造器</a></p>
    <?php
        if(file_exists(DATA_PATH.'bc/bc.html')) {
            echo '<div class="tooltip-box" style="border:1px solid #DDD;padding:16px;margin-bottom:16px;">';
            require(DATA_PATH.'bc/bc.html');
            echo '</div>';
        }
    ?>
    <hr><?php } ?>
    <p>选择来源：
        <a onclick="$('#list-legacy').css('display','block');
            $('#list-kuwo').css('display','none');">播放器存储</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a onclick="$('#list-kuwo').css('display','block');
            $('#list-legacy').css('display','none');">酷我音乐</a>
    </p>
    <hr>
    <div id="list-legacy">
        <p>列表/播放器存储&nbsp;<a href="<?php echo BASIC_URL ?>playlist/__syscall/0" target="_blank">全部播放</a></p>
        <ul>
            <?php
                $menu=dir_list(FILES);
                foreach($menu as $item) {
                    if(isValidMusic($item,false) && (getPerm($item)['list/show'] || is_root())) {
                        printIndexList($item);
                    }
                }
            ?>
        </ul>
    </div>
    <div id="list-kuwo" style="display:none;">
        <p><strong>酷我音乐/今日推荐歌单</strong></p>
        <div id="list-kuwo-suggestion">
            <button id="kuwo-show-suggestion" class="am-btn am-btn-primary" onclick="kuwo_search(1,'> __mp_suggestions__','#list-kuwo-suggestion')">显示</button>
        </div>
        <p><strong>酷我音乐/关键词搜索</strong></p>
        <input name="keyword" id="keyword" type="text" />
        <button type="button" class="am-btn am-btn-primary" onclick="kuwo_search(1)">搜索</button>
        <p id="list-kuwo-show">
            
        </p>
            <p>
                <a onclick="toggleVisible(this)">
                    ▶ 提示
                </a>
            </p>
            <div id="notes" style="display:none;margin-left:8px;margin-top:-12px;line-height:26px;">
                <p>利用本站爬虫对酷我音乐进行搜索、抓取和下载。</p>
                <p>本站系统不对抓取内容负责。</p>
                <p>搜索语法：</p>
                <p>1. 常规搜索：直接输入，如<code>十字诀</code></p>
                <p>2. 输入酷我的歌曲号：前面加冒号，如<code>:6877870</code></p>
                <p>3. 输入歌手号，查找歌手的全部歌曲：前面加百分号，如<code>%370</code></p>
                <p>4. 查找歌手：前面加@，如<code>@阿悄</code></p>
            </div>
    </div>
</div>
<script>
    function kuwo_search(pageid,cont="",dist='#list-kuwo-show') {
        dist = $(dist);
        curr_pageid=pageid;
        if(cont=="") cont=$('#keyword')[0].value;
        else {
            // $('#keyword')[0].value=cont;
        }
        var al=modal_loading("稍等","正在查询");
        $.ajax({
            async:true,
            timeout:9000,
            dataType:"text",
            url:'?isSubmit=KuwoSearch&key='+encodeURIComponent(cont)+'&pageid='+pageid,
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
