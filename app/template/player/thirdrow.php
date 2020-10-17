<div class="title-dropdown-father am-dropdown am-dropdown-up">
    <a class="am-dropdown-toggle cl-g-2">操作 ▼</a>
    <ul class="am-dropdown-content song-list-show" onclick="$('.title-dropdown-father').dropdown('close')">
        <li class="am-dropdown-header">操作</li>
        <?php if(getPerm(cid())['music/audio/dl'] || is_root()) { ?><li><a href="<?php echo getDownloadUrl(cid()) ?>" target="_blank"><i class="fa fa-download"></i><span> <?php echo !isKuwoId(cid()) ? '下载音乐' : '白 嫖' ?></span></a></li><?php } ?>
        <?php if(getPerm(cid())['music/code'] || is_root()) { ?><li><a href="<?php echo BASIC_URL.cid() ?>/code" target="_blank"><i class="fa fa-code"></i><span> 查看歌词配置</span></a></li><?php } ?>
        <?php if(getPerm(cid())['music/download_doc'] || is_root()) { ?><li><a href="<?php echo BASIC_URL.cid() ?>/docs" target="_blank"><i class="fa fa-file-word-o"></i><span> 可打印歌词</span></a></li><?php } ?>
        <?php if(getPerm(cid())['admin/edit'] || is_root()) { ?><li><a href="<?php echo BASIC_URL.cid() ?>/edit" target="_blank"><i class="fa fa-edit"></i><span> 编辑</span></a></li><?php } ?>
        <li><a href="javascript:void()" onclick="setZoom()"><i class="fa fa-search-plus"></i><span> 歌词缩放</span></a></li>
    </ul>
</div>
    