<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

define('COLON',LNG('punc.colon'));
function fa_icon($id,$mleft='05',$mright=3) {
	return '<span class="fa fa-'.$id.'" style="margin-left:.'.$mleft.'em;margin-right:.'.$mright.'em">'.'</span>';
}
