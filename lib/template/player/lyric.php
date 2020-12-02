<div class="lrc-overview">
	<?php
		$data=json_decode(parseCmpLyric(cid()),true);
		if($data==NULL)
		{
			echo "系统错误";
		}
		else
		{
			foreach($data['lyrics'] as $v)
			{
				if($v['display']) echo '<span class="lo-item" id="lo-'.$v['id'].'" onclick="scrollToPara('.$v['id'].')">'.$v['ac'].'</span>';
			}
		}
	?>
</div>
<div class="lrc-content" style="height:32px;">
	<?php
		if($data==NULL) echo "系统错误";
		else
		{
			foreach($data['lyrics'] as $v)
			{
				if(is_array($v['in']) && count($v['in']))
				{
					echo '<div class="para" id="para-'.$v['id'].'">';
					if($v['title']) echo '<p class="para-title"><span class="para-title-text">['.htmlspecial2($v['n']).' '.$v['ac'].']</span></p>';
					foreach($v['in'] as $w)
					{
						if($w['ts']>1610612735) echo '<i style="color:#888888 !important;">';
						echo '<p class="lrc-item';
						if($v['ac']=='--') echo ' lrc-interval-item';
						echo '" id="lrc-'.$w['id'].'" data-time="'.$w['ts'].'">';

						echo '<span class="lrc-text" id="lrc-text-'.$w['id'].'" data-time="'.$w['ts'].'">';
						echo $w['c'];
						echo '</span>';

						echo '</p>';
						if($w['ts']>1610612735) echo '</i>';
					}
					echo '</div>';
				}
			}
		}
	?>
	<div class="para" style="height:calc(50% - 4px)">
	</div>
	<div id="sync-button" onclick="roll_toggle(!S); if(S)highlight_lyric(1);">
		<span class="fa fa-location-arrow" id="sync-ico"></span>
	</div>
</div>
