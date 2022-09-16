<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	$data=json_decode(parseCmpLyric(cid()),true);
	if($data==NULL) echo LNG('comp.system_error');
	else
	{
		$curr_id = -1;
		foreach($data['lyrics'] as $v)
		{
			if(isset($v['in']) && is_array($v['in']) && count($v['in']))
			{
				if($v['type'] == 'lyrics') {
					echo '<div class="para" id="para-'.$v['id'].'">';
					if($v['title']) echo '<p class="para-title"><span class="para-title-text">['.htmlspecial2($v['n']).' '.$v['ac'].']</span></p>';
					foreach($v['in'] as $w)
					{
						if(false || $v['id'] != -1) {
							if($w['ts']<=1610612735) $curr_id++;
							echo '<p class="lrc-item';
							$interval_count = isIntervalContent($w['c']);
							$interval_flag = ($interval_count > 4) ? 'lrc-break' : 'lrc-empty';
							if($interval_count >= 0) echo ' lrc-interval-item ' . $interval_flag;
							echo '" id="lrc-'.$w['id'].'" data-time="'.$w['ts'].'">';
							echo '<span class="lrcline-id">' . ($w['ts']>1610612735 ? '~' : $curr_id) . '</span>';

							echo '<span class="lrc-text" id="lrc-text-'.$w['id'].'" data-time="'.$w['ts'].'">';
							if($w['ts']>1610612735) echo '<i style="opacity:0.7!important;">';
							echo $w['c'];
							if($w['ts']>1610612735) echo '</i>';
							echo '</span>';

							echo '</p>';
						} else {
							$curr_id++;
							echo '<p class="lrc-item lrc-wap-title';
							echo '" id="lrc-'.$w['id'].'" data-time="'.$w['ts'].'">';
							echo '<span class="lrcline-id">' . 0 . '</span>';

							echo '<span class="lrc-text" id="lrc-text-'.$w['id'].'" data-time="'.$w['ts'].'">';
							echo htmlspecial2(GCM()['N']);
							echo "<br />";
							echo '<span class="lrc-wap-title-singer">' . htmlspecial2(GCM()['S']) . '</span>';
							echo '</span>';

							echo '</p>';
						}
					}
					echo '</div>';
				}
			}
		}
	}
?>
