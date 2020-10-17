<?php if(!is_wap()) { ?><div class = "song-avatar">
    <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" title="描述图片/封面图片 【未实现】"
        style="width: 100%; height: 100%;" />
</div>
<?php } ?>
<?php if(!is_wap()) { ?><center style="margin-bottom:-18px;"><?php } ?>
<div class="title-dropdown-father am-dropdown <?php if(is_wap()) echo 'am-dropdown-up' ?>">
    <a class="song-title am-dropdown-toggle" <?php if(getAudioPath(FILES.preSubstr($_GET['_lnk'])."/back")) { ?>data-back="yes"<?php } ?>><?php echo htmlspecial2(GCM()['N']) ?></a>
    <ul class="am-dropdown-content song-list-show" style="max-height:480px;overflow:auto;overflow-x:hidden;right:auto;<?php if(!is_wap()) echo 'margin-left:-320px;margin-top:-300px;'; ?>">
        <li class="am-dropdown-header">操作</li>
        <li>
            <a onclick="changeTo(song_id)">
                <i class="fa fa-refresh "></i> 刷新音频
            </a>
        </li>
        <li>
            <a onclick="trackSwitch();">
                <i class="fa fa-microphone-slash "></i> 切换原声/消声版本
            </a>
        </li>
        <li>
            <a onclick="setVolume();">
                <i class="fa fa-volume-up "></i> 设置相对音量
            </a>
        </li>
        <?php if(getPerm(cid())['music/audio/dl'] || is_root()) { ?>
            <li>
                <a href="<?php echo getDownloadUrl(cid()) ?>" target="_blank">
                    <i class="fa fa-download "></i> <?php echo !isKuwoId(cid()) ? '下载音乐' : '白 嫖 音 乐' ?>
                </a>
            </li>
        <?php } ?>
        <?php if(getPerm(cid())['music/code'] || is_root()) { ?>
            <li>
                <a href="<?php echo BASIC_URL.cid() ?>/code" target="_blank">
                    <i class="fa fa-code "></i> 查看代码
                </a>
            </li>
        <?php } ?>
        <?php if(true) { ?>
            <li>
                <a href="<?php echo BASIC_URL.cid() ?>/switch-all" target="_blank">
                    <i class="fa fa-file-text-o "></i> 查看打包数据
                </a>
            </li>
        <?php } ?>
        <?php if(is_root()) { ?>
            <li>
                <a href="<?php echo BASIC_URL.cid() ?>/edit" target="_blank">
                    <i class="fa fa-pencil"></i> 编辑
                </a>
            </li>
        <?php } ?>
        <li class="am-divider"></li>
        <li class="am-dropdown-header">播放列表</li>
        <li><a onclick="listEdit()"><i class="fa fa-pencil"></i> <span class="edit-label">在歌单构造器中编辑</span></a></li>
        <li><a onclick="switchNext()"><i class="fa fa-arrow-right "></i> 跳过此歌曲</a></li>
        <li><a onclick="listPrint()"><i class="fa fa-print"></i> 生成全部歌词文档</a></li>
        <li style="border-top: 1px dotted rgb(204, 204, 204);">
            <a class="menu-curr-display" target="_blank" href="<?php echo BASIC_URL . cid() ?>">
            </a>
        </li>
        <!--该功能不会被实现。-->
        <!--li><a onclick="listDownload()"><i class="fa fa-download"></i> 生成离线播放器</a></li-->
    </ul>
</div>
<span class="song-id"><?php echo preSubstr($_GET['_lnk']) ?></span>
<?php if(!is_wap()) { ?></center><?php } ?>
<?php if(!is_wap()) echo '<br>' ?>
<span class="song-length"><?php if(!is_wap()) { ?>长度: <?php } ?><span id="total-len">--</span></span>
<span class="song-process"><span id="elasped">Loading...</span> / -<span id="remain">Loading...</span> (<span id="accurate">Loading...</span>)</span>
