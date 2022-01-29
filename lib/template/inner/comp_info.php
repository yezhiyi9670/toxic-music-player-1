<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<script>
	document.title="<?php echo LNGk('code.cap.compinfo') ?> ‹ <?php echo addslashes(GCM()['N']) ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	set_section_name(LNG('code.cap.compinfo'));
</script>

<?php

echo '<div class="codeblock codeblock-white" style="font-size: 14px;">';

if(!isset($_GET['iframe'])) {
	echo "// ";
	LNGe('code.compinfo.tips');
	echo "\n";
}

$data = parseCmpLyric(cid(),true,true,'cmpi_ADD_ERROR_P');
echo getCompileIssueMsg2(cid(),$data['message']);

echo '</div>';
