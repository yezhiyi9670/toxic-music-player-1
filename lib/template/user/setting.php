<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if($_POST['isSubmit']=='yes') {
    setting_upd($_POST);
    echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('setting.save.success').'")</script>';
    exit;
}

?>

<?php
    function printSettingField($desc,$id,$type) {
        echo $desc . COLON . '<input name="' . $id . '" type="' . $type . '" value="' . htmlspecial(setting_gt($id)) . '" />' . '<br><br>' . "\n";
    }
?>
<script>
    document.title="<?php echo LNGk('setting.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
    set_section_name(LNG('setting.title'));
</script>
<div class="txmp-page-full">
    <h3><?php echo LNGk('setting.title') ?></h3>
    <?php showToastMessage(); ?>
    <p><form method="POST">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="yes">
        <?php printSettingField(LNG('setting.item.font.global'),'global-font','text') ?>
        <?php printSettingField(LNG('setting.item.font.lyricl'),'lyric-font-formal','text') ?>
        <?php printSettingField(LNG('setting.item.font.lyricp'),'lyric-font-title','text') ?>
        <?php printSettingField(LNG('setting.item.font.lyricc'),'lyric-font-comment','text') ?>
        <?php printSettingField(LNG('setting.item.font.title'),'title-font','text') ?>
        <?php printSettingField(LNG('setting.item.font.name'),'name-font','text') ?>
        <?php printSettingField(LNG('setting.item.font.input'),'input-font','text') ?>
        <?php printSettingField(LNG('setting.item.font.code'),'code-font','text') ?>
        <?php printSettingField(LNG('setting.item.randplay'),'allplay-rand','text') ?>
        <?php printSettingField(LNG('setting.item.no_color'),'no-color-switch','text') ?>
        <?php printSettingField(LNG('setting.item.limited_select'),'limited-selection','text') ?>
        <?php printSettingField(LNG('setting.item.ag_op'),'aggressive-optimize','text') ?>
        <?php printSettingField(LNG('setting.item.wap_font_size'),'wap-font-size','text') ?>
        <?php printSettingField(LNG('setting.item.wap_scale'),'wap-scale','text') ?>
        <input type="submit" class="am-btn am-btn-primary" value="<?php LNGe('setting.save') ?>">
    </form></p>
</div>
