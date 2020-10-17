<div class="title-dropdown-father am-dropdown am-dropdown-up" style="margin-right:8px;">
    <a class="am-dropdown-toggle cl-g-2">详细信息 ▼</a>
    <ul class="am-dropdown-content song-list-show" onclick="$('.title-dropdown-father').dropdown('close')">
        <li class="am-dropdown-header">详细信息</li>
        <li><a href="javascript:;"><i class="fa fa-microphone"></i> 歌手：<span id="singer"><?php echo GCM()['S'] ?></span></a></li>
        <li><a href="javascript:;"><i class="fa fa-pencil"></i> 作者：<span id="lauthor"><?php echo GCM()['LA'] ?></span> | <span id="mauthor"><?php echo GCM()['MA'] ?></span></a></li>
        <li><a href="javascript:;"><i class="fa fa-folder"></i> 分类：<span id="cate"><?php echo GCM()['C'] ?></span></a></li>
        <?php if(GCM()['O']){ ?>
        <li><a href="<?php echo GCM()['O'] ?>" target="_blank"><i class="fa fa-external-link"></i> 来源：<span>打开原网页</span></a></o>
        <?php } ?>
    </ul>
</div>
    