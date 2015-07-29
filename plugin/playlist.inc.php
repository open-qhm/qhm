<?php
// $Id: lightbox2.inc.php, by hokuken.com
//
// Text lightbox2 plugin

define('PLAYLIST_LIB','plugin/playlist/');

function plugin_playlist_convert()
{
	$playlist_lib = PLAYLIST_LIB;
	
	$addscript = <<<EOD
<!-- for soundmanager2 playlist -->
<link media="screen" type="text/css" rel="stylesheet" href="./plugin/playlist/css/player.css">
<script type="text/javascript" src="{$playlist_lib}script/soundmanager2-jsmin.js"></script>
<script type="text/javascript" src="{$playlist_lib}script/jsamp-preview.js"></script>
EOD;
	$qt = get_qt();
	$qt->appendv_once('plugin_playlist', 'beforescript', $addscript);

	$args = func_get_args();
	$text = array_pop($args); // Already htmlspecialchars(text)
	$player_title = array_pop($args);
	
	$player_title = ($player_title=='') ? 'Musice Player powered by SoundManager2 :: QHM Plugin' : $player_title;
	
	$lines = preg_split("/[\r\n(\r\n)]/",$text);
	foreach($lines as $line){
		$playlist[] = list($url, $label) = array_pad( explode(',', $line, 2), 2, '');
	}
	
	return plugin_playlist_getcode($player_title,$playlist);	
}




function plugin_playlist_getcode($title, $playlist){
	$qm = get_qm();
	
	$playlist_lib = PLAYLIST_LIB;

	$start_tag = <<<EOD
<div> <!-- Start : soundmanager2 box  -->
  <div id="player-template" class="sm2player"> <!-- player-template -->
    <div class="ui"> <!-- player UI (bar) -->
      <div class="left">
        <a href="#" title="Pause/Play" onclick="soundPlayer.togglePause();return false" class="trigger pauseplay"><span></span></a>
      </div>
      <div class="mid">
        <div class="progress"></div>
        <div class="info"><span class="caption text">%{artist} - %{title} [%{album}], (%{year}) (%{time})</span></div>
        <div class="default">$title</div>
        <div class="seek">Seek to %{time1} of %{time2} (%{percent}%)</div>
        <div class="divider">&nbsp;&nbsp;---&nbsp;&nbsp;</div>
        <a href="#" title="" class="slider"></a>
   </div> <!-- end: ui -->

   <div class="right"><!-- start:right -->
     <div class="divider"></div>
     <div class="time" title="Time">0:00</div>
     <a href="#" title="Previous" class="trigger prev" onclick="soundPlayer.oPlaylist.playPreviousItem();return false"><span></span></a>
     <a href="#" title="Next" class="trigger next" onclick="soundPlayer.oPlaylist.playNextItem();return false"><span></span></a>
     <a href="#" title="Shuffle" class="trigger s1 shuffle" onclick="soundPlayer.toggleShuffle();return false"><span></span></a>
     <a href="#" title="Repeat" class="trigger s2 loop" onclick="soundPlayer.toggleRepeat();return false"><span></span></a>
     <a href="#" title="Mute" class="trigger s3 mute" onclick="soundPlayer.toggleMute();return false"><span></span></a>
     <a href="#" title="Volume" onmousedown="soundPlayer.volumeDown(event);return false" onclick="return false" class="trigger s4 volume"><span></span></a>
     <a href="#" title="Playlist" class="trigger dropdown" onclick="soundPlayer.togglePlaylist();return false"><span></span></a>
   </div><!-- end:right -->

  </div><!-- end:player-template -->
</div><!-- end : soundmanager2 box  -->
<div style="clear:both"></div>
<div class="sm2player2">
<div class="sm2playlist-box">
  <div id="playlist-template" class="sm2playlist"><!-- playlist / controls -->
    <div class="hd"><div class="c"></div></div>
    <div class="bd">
      <ul style="margin:0px">
       <!-- playlist items created, inserted here
        <li><a href="/path/to/some.mp3"><span>Artist - Song Name, etc.</span></a></li>
       -->
      </ul>
   </div>
   <div class="ft"><div class="c"></div></div>
  </div>
</div>
</div>

<div style="position:absolute;left:0px;top:-9999px;width:30em">
 <p>This is a normal list of HTML links to MP3 files, which jsAMP picks up and turns into a playlist.</p>
 <ul>
EOD;

	$end_tag =  '</ul>
 </div>
</div>';


	//generate links(playlist);
	$list = '';
	foreach($playlist as $tune){
		$list .= "<li><a href=\"{$tune[0]}\">{$tune[1]}</a></li>\n";
	}

	return $start_tag . $list . $end_tag ;
}

?>
