<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if($_POST['isSubmit']=='yes') {
    setting_upd($_POST);
    echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("已保存你的设置。")</script>';
    exit;
}

?>

<?php
    function printSettingField($desc,$id,$type) {
        echo $desc . '：<input name="' . $id . '" type="' . $type . '" value="' . htmlspecial(setting_gt($id)) . '" />' . '<br><br>' . "\n";
    }
?>
<script>
    document.title="用户设置 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
</script>
<div class="txmp-page-full">
    <h3>设置</h3>
    <p>在此调节使用偏好</p>
    <?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
        <a href="javascript:;" onclick="F_HideNotice()">知道了</a>
    </p><?php } ?>
    <p><form method="POST">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="yes">
        <?php printSettingField('全局字体','global-font','text') ?>
        <?php printSettingField('歌词正文字体','lyric-font-formal','text') ?>
        <?php printSettingField('歌词标题字体','lyric-font-title','text') ?>
        <?php printSettingField('歌词注释字体','lyric-font-comment','text') ?>
        <?php printSettingField('标题字体','title-font','text') ?>
        <?php printSettingField('名称字体（无效）','name-font','text') ?>
        <?php printSettingField('输入框字体','input-font','text') ?>
        <?php printSettingField('代码字体（无效）','code-font','text') ?>
        <?php printSettingField('是否使用新样式(Y/n)','new-look','text') ?>
        <?php printSettingField('全部播放时随机切换(Y/n)','allplay-rand','text') ?>
        <?php printSettingField('禁用主题色变换(y/N)','no-color-switch','text') ?>
        <?php printSettingField('限制文本选择(y/N)','limited-selection','text') ?>
        <input type="submit" class="am-btn am-btn-primary" value="保存">
    </form></p>
    <p>
        <a onclick="toggleVisible(this)">
            ▶ 提示
        </a>
    </p>
    <p id="notes" style="display:none;margin-left:8px;margin-top:-12px;line-height:26px;">
        <b>字体：界面中使用的字体，</b>不同类型显示位置不同。<br>
        有2种填法。1.直接填入字体名称，如“微软雅黑”“等线”。2.使用网络中的字体文件，网址前加上'$'，如“$https://example.com/fake/path/static/fonts/common.ttf”<br><br>
        预置字体：<code>Noto Sans SC</code>（作为默认字体，暂时避免字形引起的问题）<br><br>
        <b>是否使用新样式：是否使用 v125d 版本引入的新样式，</b>全局生效。填写 Y 或 N。<br><br>
        <b>全部播放时随机切换：使用“全部播放”功能是，歌单是否随机切换，</b>全局生效。填写 Y 或 N。<br><br>
        <b>禁用主题色变换：是否禁用界面主题色随歌曲变换的特性。</b>如果网络不佳，可以使用此方法降低故障概率和网络流量。<br><br>
        <b>限制文本选择：禁止除代码、输入框和歌词外的内容被选中。</b>这可以改进用户体验，但在某些情况下显得不太方便。
    </p>
</div>
