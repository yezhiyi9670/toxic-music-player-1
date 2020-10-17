<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$luser = $GLOBALS['listname'];
$lid = $GLOBALS['listid'];

$ioi = readPlaylistData($luser,$lid);

?>

"This is an embeddable code for an external webpage";

// TXMP-js by toxic-music-player "<?php echo addslashes(_CT('app_name')) ?>" at "<?php echo addslashes(BASIC_URL) ?>"
// Generated for playlist <?php echo $luser ?>/<?php echo $lid ?>.
// See <?php echo BASIC_URL . 'playlist/' . $luser . '/' . $lid ?> for the playlist.

var G = {};

G.playList = [
    
];
