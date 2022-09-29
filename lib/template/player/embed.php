<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$luser = $GLOBALS['listname'];
$lid = $GLOBALS['listid'];

$listdata = readPlaylistData($luser,$lid);

?>

/* <script> */

"mod ExternalEmbedding";

/**
 * txmp ExternalEmbedding
 * ----------------------
 * This is an embeddable code for an external webpage, such as a blog article.
 * You need to include the JS. Use at your own risk!
 */

// TXMP-js by toxic-music-player "<?php echo jsspecial(_CT('app_name')) ?>" at "<?php echo jsspecial(BASIC_URL) ?>"
// Generated for playlist <?php echo $luser ?>/<?php echo $lid ?>.
// See <?php echo BASIC_URL . 'playlist/' . $luser . '/' . $lid ?> for the playlist.

var G = {};

G.playList = [
	// gugugu
];

/* </script> */
