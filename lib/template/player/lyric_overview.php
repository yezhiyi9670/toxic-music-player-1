<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	$data=json_decode(parseCmpLyric(cid()),true);
	if($data==NULL)
	{
		echo LNG('comp.system_error');
	}
	else
	{
		foreach($data['lyrics'] as $v)
		{
			if($v['display']) {
				if($v['type'] == 'lyrics') {
					echo '<span class="lo-item" id="lo-'.$v['id'].'" onclick="scrollToPara('.$v['id'].');return false;">'.(!isset($v['premark']) ? $v['ac'] : '>').'</span>';
				} else if($v['type'] == 'split') {
					echo '<span class="lo-item" id="lo-'.$v['id'].'">/</span>';
				}
			}
		}
	}
?>
