<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>

<?php
	$data=json_decode(parseCmpLyric(cid()),true);
	if($data==NULL) echo LNG('comp.system_error');
	else {
		$curr_id = -1;
		foreach($data['lyrics'] as $v) {
			if(isset($v['in']) && is_array($v['in']) && count($v['in'])) {
				if($v['type'] == 'lyrics') {
					echo '<div class="para" id="para-'.$v['id'].'">';
					if($v['title']) echo '<p class="para-title"><span class="para-title-text">['.htmlspecial2($v['n']).' '.$v['ac'].']</span></p>';
					foreach($v['in'] as $w) {
						if($v['id'] != -1) {  // Normal line
							$is_whitespace = trim($w['c']) == '';
							$is_comment = $w['ts'] >= TS_IS_COMMENT;
							if(!$is_whitespace && !$is_comment) $curr_id++;
							echo '<p class="lrc-item';
							
							// Interval class
							$interval_count = isIntervalContent($w['c']);
							$interval_flag = ($interval_count > 4) ? 'lrc-interval' : 'lrc-empty';
							if($interval_count >= 0) echo ' lrc-interval-item ' . $interval_flag;
							
							// Whitespace class
							if($is_whitespace) {
								echo ' lrc-whitespace';
							}

							echo '" id="lrc-'.$w['id'].'" data-time="'.$w['ts'].'">';
							echo '<span class="lrcline-id">' . ($is_comment ? '~' : $curr_id) . '</span>';

							echo '<span class="lrc-text" id="lrc-text-'.$w['id'].'" data-time="'.$w['ts'].'">';
							if($is_comment) echo '<i style="opacity:0.7!important;">';
							echo $w['c'];
							if($is_comment) echo '</i>';
							echo '</span>';

							echo '</p>';
						} else {  // Title of article
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
