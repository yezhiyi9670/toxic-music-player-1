{
	"baseurl":"<?php echo BASIC_URL.preSubstr($_GET['_lnk']) ?>",
	"song_id":"<?php echo preSubstr($_GET['_lnk']) ?>",

	"src1":"<?php echo getAudioUrl(preSubstr($_GET['_lnk']))?>",
	"src2":"<?php echo getAudioUrl(preSubstr($_GET['_lnk']),"back","background")?>",

	"player_colored_css":"<?php echo BASIC_URL ?>dynamic/css/player/player-colored.css?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>&A=X<?php echo addslashes(GCM()['A']) ?>&S=X<?php echo addslashes(GCM()['X']) ?>&G1=X<?php echo addslashes(GCM()['G1']) ?>&G2=X<?php echo addslashes(GCM()['G2']) ?>",
	"main_colored_css":"<?php echo BASIC_URL ?>dynamic/css/common/main-colored.css?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>&A=X<?php echo addslashes(GCM()['A']) ?>&S=X<?php echo addslashes(GCM()['X']) ?>&G1=X<?php echo addslashes(GCM()['G1']) ?>&G2=X<?php echo addslashes(GCM()['G2']) ?>",

	"title":"<?php echo addslashes(GCM()['N']) ?> - <?php echo addslashes(_CT('app_name_title')) ?>",

	"source": "<?php echo isKuwoId(cid()) ? 'kuwo' : 'internal' ?>",
	"payment": <?php echo encode_data(paymentStatus(cid())) ?>
}
