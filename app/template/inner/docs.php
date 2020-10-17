<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<script>
    <?php if(!isset($_GET['list'])){if(!isset($GLOBALS['listname'])) { ?>
        document.title="<?php echo addslashes(GCM()['N']) ?> > 歌词文档 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
    <?php } else { ?>
        document.title="制作歌词本 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
    <?php }}else{ ?>
        document.title="制作歌词本 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
    <?php } ?>
</script>
<style>
.cmt{
    color:#999999;
}
.follow-field {
    white-space: pre;
    border:1px solid #DDD;
    background-color:#EEE;
}
.field-remember {
    font-size:16px;
}
.f-short{
    width:56px !important;
}
.f-midshort{
    width:130px !important;
}
.f-mid {
    width:260px !important;
}
</style>

<?php 
    $internal = isset($GLOBALS['listname']);
?>
<?php
function Field($name,$default,$extra='') {
    echo '<input class="field-remember '.$extra.'" type="text" id="'.$name.'" name="'.$name.'" value="'.$default.'">';
}
function Text($name) {
    echo '<span class="follow-field" data-name="'.$name.'">'.$name.'</span>';
}

$ioi = null;
if($internal) {
    $ioi = readPlaylistData($GLOBALS['listname'],$GLOBALS['listid']);
}
?>
<div class="txmp-page-full">
    <h3>下载可打印文档</h3>
    <p>由于技术问题，我们目前只能生成XML格式的Word文档。移动版Word不一定能打开。</p>
    <?php if(!isset($_GET['list']) && !$internal) { ?>
    <p>生成单曲Word文档：请提供字体名称，然后点击下载。</p>
    <p><form action="<?php echo BASIC_URL.cid().'/make-doc' ?>" method="POST" id="dl-form">
        字体名称（中英文名皆可）：<input type="text" id="font" name="font" value="Noto Serif SC">
        <input type="submit" value="下载" class="am-btn am-btn-primary">
    </form></p>
    <?php } else { ?>
    <p>根据歌单生成歌词本。请填写详细信息，然后点击下载</p>
    <hr>
    <form action="<?php echo BASIC_URL.cid().'/make-doc' ?>" method="POST" id="multi-form">
        <p>列表（除第一个外）：<input type="text" name="list" disabled value="<?php
            if(!$internal) echo $_GET['list'];
            else {
                for($i=1;$i<count($ioi['playlist']);$i++) {
                    if($i>1) echo '|';
                    echo $ioi['playlist'][$i]['id'];
                }
            }
        ?>"></p>
        <p>自定义编号：<input type="text" name="customname" value="<?php
            if(!$internal) echo cid().'|'.$_GET['list'];
            else {
                for($i=0;$i<count($ioi['playlist']);$i++) {
                    if($i>0) echo '|';
                    echo $ioi['playlist'][$i]['canonical'] ?
                        $ioi['playlist'][$i]['canonical']:$ioi['playlist'][$i]['id'];
                }
            }
        ?>"></p>
        <p class="cmt">自定义编号建议配合内置云保存歌单使用。</p>
        <p>文件缓存地址：<input type="text" name="cacheid" disabled value="<?php echo md5(rand()) ?>"></p>
        <p class="cmt">缓存歌词本是为了稳定的下载过程。缓存将在点击“下载”后的<?php echo _CT('temp_expire') ?>秒内清除。</p>
        <p>字体名称（中英文名皆可）：<input type="text" class="field-remember" id="mfont" name="font" value="Noto Serif SC"></p>
        <hr>
        <p><?php Field('name','歌词参考本') ?><span class="cmt">（书名）</span></p>
        <p><?php Field('subname','临时本') ?><span class="cmt">（书名副标题）</span></p>
        <p><?php Field('author','佚名  整理') ?><span class="cmt">（作者信息）</span></p>
        <p><?php Field('press','TOXIC Developers Press') ?><span class="cmt">（出版社）</span></p>
        <hr>
        <p><strong>本图书在版编目 (CIP) 数据</strong></p>
        <p><?php Text('name') ?> / <?php Text('author') ?>.  ——<?php Field('city','杭州','f-midshort') ?>：<?php Text('press') ?></p>
        <p>ISBN  <?php Field('isbn','978-7-9493-4025-2') ?></p>
        <p>I. ①<?php Field('cipname','歌···','f-short') ?>  II. ①<?php Field('cipauthor','佚···','f-short') ?>  III. ①<?php Field('cipcate','作品集-中国-歌词-收集-整理','f-mid') ?>  IV. <?php Field('cipgb','①G792.326','f-midshort') ?></p>
        <p><?php Text('press') ?>CIP数据核字 (<?php Field('year','2048','f-midshort') ?>) 第<?php Field('number','114514','f-midshort') ?> 号</p>
        <hr>
        <p><?php Field('ititle','歌词参考本-临时本   Lyric Viewing Book - Temp') ?><span class="cmt">（完整双语书名）</span></p>
        <p><?php Field('isubtitle','整理：佚名') ?><span class="cmt">（CIP作者数据）</span></p>
        <p>出版者：<?php Text('press') ?></p>
        <p>地址：<?php Field('address','浙江 杭州 西湖区 碳90街道 梦想社区 19-1-9810') ?></p>
        <p>官方网址：<?php Field('website','https://example.com/') ?></p>
        <p>版本：<?php Field('version','2048年01月第1版') ?></p>
        <p>开本：A4</p>
        <p>印张：<?php Field('printpapers','2','f-midshort') ?></p>
        <p>书号：ISBN <?php Text('isbn') ?></p>
        <p>定价：<?php Field('price','12元','f-midshort') ?></p>
        <hr>
        <p><input type="submit" value="下载" style="margin-right:8px;" class="am-btn am-btn-primary dl-btn"><input style="display:none;" type="button" value="修改" class="am-btn re-btn" onclick="reActive()"></p>
    </form>
    <?php } ?>
</div>
<script>
"DocsFontSave BEGIN";

<?php if(!isset($_GET['list']) && !$internal) { ?>
if(localStorage['txmp-save-dl-font']) {
    $('#font')[0].value=localStorage['txmp-save-dl-font'];
}

$('#dl-form')[0].onsubmit=function(){
    localStorage['txmp-save-dl-font']=$('#font')[0].value;
}

<?php }else{ ?>

var saveData=[
    
];

$('.field-remember').each(function(x) {
    var e=$('.field-remember')[x];
    saveData[saveData.length]=e.id;
});

for(var i=0;i<saveData.length;i++) {
    if(localStorage['txmp-save-dl-'+saveData[i]]) {
        $('#'+saveData[i])[0].value=localStorage['txmp-save-dl-'+saveData[i]];
        //console.log(localStorage['txmp-save-dl-'+saveData[i]]);
    }
    
}

$('#multi-form')[0].onsubmit=function(){
    $('[name=list]')[0].removeAttribute('disabled');
    // $('[name=customname]')[0].removeAttribute('disabled');
    $('[name=cacheid]')[0].removeAttribute('disabled');
    for(var i=0;i<saveData.length;i++) {
        $('#'+saveData[i])[0].removeAttribute('disabled');
        localStorage['txmp-save-dl-'+saveData[i]]=$('#'+saveData[i])[0].value;
    }
    $('[name=customname]')[0].removeAttribute('disabled');
    
    setTimeout(function(){
        $('[name=list]')[0].setAttribute('disabled','disabled');
        // $('[name=customname]')[0].setAttribute('disabled','disabled');
        $('[name=cacheid]')[0].setAttribute('disabled','disabled');
        for(var i=0;i<saveData.length;i++) {
            $('#'+saveData[i])[0].setAttribute('disabled','disabled');
        }
        $('[name=customname]')[0].setAttribute('disabled','disabled');
        $('.dl-btn')[0].value="重新下载";
        $('.re-btn').css('display','inline-block');
    },20);
}


function reActive() {
    for(var i=0;i<saveData.length;i++) {
        $('#'+saveData[i])[0].removeAttribute('disabled');
    }
    $('[name=customname]')[0].removeAttribute('disabled');
    $('[name=cacheid]')[0].value=md5(Math.random());
    $('.dl-btn')[0].value="下载";
    $('.re-btn').css('display','none');
}

setInterval(function(){
    $('.follow-field').each(function(x) {
        var e=$('.follow-field')[x];
        e.innerHTML=escapeXml($('#'+e.getAttribute('data-name'))[0].value);
    });
},200);

<?php } ?>

</script>
