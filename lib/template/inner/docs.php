<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<script>
	<?php if(!isset($_GET['list'])){if(!isset($GLOBALS['listname'])) { ?>
		document.title="<?php echo addslashes(GCM()['N']) ?> > <?php echo LNGk('docs.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	<?php } else { ?>
		document.title="<?php echo LNGk('docs.title.bulk') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	<?php }}else{ ?>
		document.title="<?php echo LNGk('docs.title.bulk') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
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
	<h3><?php LNGe('docs.cap') ?></h3>
	<p style="opacity: 0.6;"><?php LNGe('docs.hint.problem') ?></p>
	<?php if(!isset($_GET['list']) && !$internal) { ?>
	<p><?php LNGe('docs.hint.single') ?></p>
	<p><form action="<?php echo BASIC_URL.cid().'/make-doc' ?>" method="POST" id="dl-form">
		<?php LNGe('docs.label.font') ?><input type="text" id="font" name="font" value="Noto Serif SC">
		<input type="submit" value="<?php LNGe('docs.label.download') ?>" class="am-btn am-btn-primary">
	</form></p>
	<?php } else { ?>
	<p><?php LNGe('docs.hint.bulk') ?></p>
	<hr>
	<form action="<?php echo BASIC_URL.cid().'/make-doc' ?>" method="POST" id="multi-form">
		<p><?php LNGe('docs.label.list') ?><input type="text" name="list" disabled value="<?php
			if(!$internal) echo $_GET['list'];
			else {
				for($i=1;$i<count($ioi['playlist']);$i++) {
					if($i>1) echo '|';
					echo $ioi['playlist'][$i]['id'];
				}
			}
		?>"></p>
		<p>
			<?php LNGe('docs.label.canonical') ?><input type="text" name="customname" value="<?php
				if(!$internal) echo cid().'|'.$_GET['list'];
				else {
					for($i=0;$i<count($ioi['playlist']);$i++) {
						if($i>0) echo '|';
						echo $ioi['playlist'][$i]['canonical'] ?
							$ioi['playlist'][$i]['canonical']:$ioi['playlist'][$i]['id'];
					}
				}
			?>"><br />
			<span class="cmt"><?php LNGe('docs.label.canonical.tips') ?></span>
		</p>
		<p>
			<?php LNGe('docs.label.cache_id') ?><input type="text" name="cacheid" disabled value="<?php echo md5(rand()) ?>"><br />
			<span class="cmt"><?php LNGe('docs.label.cache_id.tips',_CT('temp_expire')) ?></span>
		</p>
		<p><?php LNGe('docs.label.font') ?><input type="text" class="field-remember" id="mfont" name="font" value="Noto Serif SC"></p>
		<hr>
		<p><?php Field('name',LNG('docs.cover.title.val')) ?><span class="cmt"><?php LNGe('docs.cover.title') ?></span></p>
		<p><?php Field('subname',LNG('docs.cover.subtitle.val')) ?><span class="cmt"><?php LNGe('docs.cover.subtitle') ?></span></p>
		<p><?php Field('author',LNG('docs.cover.author.val')) ?><span class="cmt"><?php LNGe('docs.cover.author') ?></span></p>
		<p><?php Field('press',LNG('docs.cover.press.val')) ?><span class="cmt"><?php LNGe('docs.cover.press') ?></span></p>
		<hr>
		<p><strong><?php LNGe('docs.cip.caption') ?></strong></p>
		<p><?php Text('name') ?> / <?php Text('author') ?>.  ——<?php Field('city',LNG('docs.cip.city.val'),'f-midshort') ?>：<?php Text('press') ?></p>
		<p>ISBN  <?php Field('isbn','978-7-9493-4025-2') ?></p>
		<p>I. ①<?php Field('cipname',LNG('docs.cip.title_s.val'),'f-short') ?>  II. ①<?php Field('cipauthor',LNG('docs.cip.author_s.val'),'f-short') ?>  III. ①<?php Field('cipcate',LNG('docs.cip.cate.val'),'f-mid') ?>  IV. <?php Field('cipgb','①G792.326','f-midshort') ?></p>
		<p><?php Text('press');LNGe('docs.cip.verify.1');Field('year','2048','f-midshort');LNGe('docs.cip.verify.2');Field('number','114514','f-midshort');LNGe('docs.cip.verify.3') ?></p>
		<hr>
		<p><?php Field('ititle',LNG('docs.cip.title.val')) ?><span class="cmt"><?php LNGe('docs.cip.title') ?></span></p>
		<p><?php Field('isubtitle',LNG('docs.cip.author.val')) ?><span class="cmt"><?php LNGe('docs.cip.author') ?></span></p>
		<p><?php LNGe('docs.cip.publisher') ?><?php Text('press') ?></p>
		<p><?php LNGe('docs.cip.address') ?><?php Field('address',LNG('docs.cip.address.val')) ?></p>
		<p><?php LNGe('docs.cip.website') ?><?php Field('website','https://example.com/') ?></p>
		<p><?php LNGe('docs.cip.version') ?><?php Field('version',LNG('docs.cip.version.val')) ?></p>
		<p><?php LNGe('docs.cip.size') ?>A4</p>
		<p><?php LNGe('docs.cip.paper_cnt') ?><?php Field('printpapers','2','f-midshort') ?></p>
		<p><?php LNGe('docs.cip.number') ?>ISBN <?php Text('isbn') ?></p>
		<p><?php LNGe('docs.cip.price') ?><?php Field('price',LNG('docs.cip.price.val'),'f-midshort') ?></p>
		<hr>
		<p><input type="submit" value="<?php LNGe('docs.label.download') ?>" style="margin-right:8px;" class="am-btn am-btn-primary dl-btn"><input style="display:none;" type="button" value="<?php LNGe('docs.label.modify') ?>" class="am-btn re-btn" onclick="reActive()"></p>
	</form>
	<?php } ?>
</div>
<script>
"DocsFontSave BEGIN";

<?php if(!isset($_GET['list']) && !$internal) { ?>
if(localStorage[G.app_prefix+'-save-dl-font']) {
	$('#font')[0].value=localStorage[G.app_prefix+'-save-dl-font'];
}

$('#dl-form')[0].onsubmit=function(){
	localStorage[G.app_prefix+'-save-dl-font']=$('#font')[0].value;
}

<?php }else{ ?>

var saveData=[
	
];

$('.field-remember').each(function(x) {
	var e=$('.field-remember')[x];
	saveData[saveData.length]=e.id;
});

for(var i=0;i<saveData.length;i++) {
	if(localStorage[G.app_prefix+'-save-dl-'+saveData[i]]) {
		$('#'+saveData[i])[0].value=localStorage[G.app_prefix+'-save-dl-'+saveData[i]];
	}
}

$('#multi-form')[0].onsubmit=function(){
	$('[name=list]')[0].removeAttribute('disabled');
	// $('[name=customname]')[0].removeAttribute('disabled');
	$('[name=cacheid]')[0].removeAttribute('disabled');
	for(var i=0;i<saveData.length;i++) {
		$('#'+saveData[i])[0].removeAttribute('disabled');
		localStorage[G.app_prefix+'-save-dl-'+saveData[i]]=$('#'+saveData[i])[0].value;
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
		$('.dl-btn')[0].value=LNG('docs.label.retry');
		$('.re-btn').css('display','inline-block');
	},20);
}


function reActive() {
	for(var i=0;i<saveData.length;i++) {
		$('#'+saveData[i])[0].removeAttribute('disabled');
	}
	$('[name=customname]')[0].removeAttribute('disabled');
	$('[name=cacheid]')[0].value=md5(Math.random());
	$('.dl-btn')[0].value=LNG('docs.label.download');
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
