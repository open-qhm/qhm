<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: lightbox2.inc.php,v 1.22 2005/06/16 15:04:08 hokuken.com Exp $
//
// Text lightbox2 plugin
// Created: 2011-05-27 HOKUKEN.INC
// Slides is licensed under the Apache license.
// Slider design by Orman Clark at Premium Pixels.
// Button style designedby Daniel J. Sullivan.
// Designs from http://365psd.com.

function plugin_slides_convert()
{
	global $vars,$script;
	$qm = get_qm();
	$qt = get_qt();
	$qt->setv('jquery_include', true);
	$qt->setv('jquery_cookie_include', true);
	
	static $id = 0;
	$id++;
		
	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)
	
	list($frame, $float, $play, $easing, $easing1) = array_pad($args, 5, '');

	global $enable_smart_style;


	$frame_width = 739;
	$frame_height = 341;
	$slides_container_width = 570;
	$slides_container_height = 270;
	$container_width = 550;
	$container_height = 320;
	$slides_btn_top = 107;
	if($frame == "small"){
	$frame_image = "frame_small.png";
	$frame_width = 350;
	$frame_height = 252;
	$slides_container_width = 182;
	$slides_container_height = 181;
	$container_width = 168;
	$container_height = 220;
	$slides_btn_top = 68;
	} else if($frame == "large"){
	$frame_image = "frame_large.png";
	$frame_width = 1067;
	$frame_height = 300;
	$slides_container_width = 900;
	$slides_container_height = 200;
	$container_width = 1067;
	$container_height = 250;
	$slides_btn_top = 76;
		if (is_smart_phone() && $enable_smart_style)
		{
			$frame_image = "frame_large.png";
			$frame_width = 550;
			$frame_height = 180;
			$slides_container_width = 540;
			$slides_container_height = 120;
			$container_width = 550;
			$container_height = 180;
			$slides_btn_top = 30;
		}
	} else if($frame == "portrait"){
	$frame_image = "frame_portrait.png";
	$frame_width = 388;
	$frame_height = 406;
	$slides_container_width = 227;
	$slides_container_height = 333;
	$container_width = 216;
	$container_height = 406;
	$slides_btn_top = 146;
	} else if($frame == "black"){
	$frame_image = "frame_black.png";
	} else if($frame == "cargo"){
	$frame_image = "frame_cargo.png";
	} else if($frame == "cotton"){
	$frame_image = "frame_cotton.png";
	} else if($frame == "iron"){
	$frame_image = "frame_iron.png";
	} else if($frame == "wood"){
	$frame_image = "frame_wood.png";
	} else if($frame == "board"){
	$frame_image = "frame_board.png";
	} else {
	$frame_image = "frame_default.png";
	}
	$play = $play == ''? 5000 : $play * 1000;
	$easing = $easing == ''? "easeOutQuint" : $easing;
	$easing1 = $easing1 == ''? "easeOutQuint" : $easing1;

	if ($float == "right"){
		$float = $float;
		$marginLeft = '40px';
		$marginRight = '40px';
	} else if ($float == "left"){
		$float = $float;
		$marginLeft = '40px';
		$marginRight = '40px';	
	} else {
		$float = "none";
		$marginLeft = "auto";
		$marginRight = "auto";
	}
	
	
	$addoncescript = '
		<script src="js/jquery.easing.1.3.js" type="text/javascript"></script>
		<script src="js/slides.jquery.js" type="text/javascript"></script>	
	';
	
	$qt->prependv_once('plugin_slides_jquery', 'beforescript', $addoncescript);
	
	$addscript = '
	<style type="text/css">
a.prev:hover,a.next:hover{
background-color:transparent!important;
}

.slides_container a img {
	display:block;
	margin: 0 auto;
}
.slides_pagination li {
	float:left;
	margin:0 1px;
	list-style:none;
}

.slides_pagination li a {
	display:block;
	width:12px;
	height:0;
	padding-top:12px;
	background-image:url(image/slides/pagination.png);
	background-position:0 0;
	float:left;
	overflow:hidden;
}

.slides_pagination li.current a {
	background-position:0 -12px;
}

</style>
';

	$qt->appendv_once('plugin_slides', 'beforescript', $addscript);
	
	$eachscript = '
		<style type="text/css">
#slides_'.$id.' .slides_container {
	width:'.$slides_container_width.'px;
	overflow:hidden;
	position:relative;
	display:none;
}
#slides_'.$id.' .slides_container a {
	width:'.$slides_container_width.'px;
	height:'.$slides_container_height.'px;
	display:block;
}

#container_'. $id .' {
	width:'.$container_width.'px;
	height: '.$container_height.'px;
	padding:10px;
	margin:10px auto 0;
	position:relative;
	z-index:0;
	float: '.$float.';
	margin-left: '.$marginLeft.';
	margin-right: '.$marginRight.';
}
#frame_'. $id .' {
	position:absolute;
	z-index:0;
	width:'.$frame_width.'px;
	height:'.$frame_height.'px;
	top:-3px;
 	left:-80px;
 	max-width: none;
}
#slides_'. $id .' {
	position:absolute;
	top:15px;
	left:4px;
	z-index:100;
}

#slides_'. $id .' .next,#slides_'. $id .' .prev {
	position:absolute;
	top:'.$slides_btn_top.'px;
	left: -24px;
	width:24px;
	height:34px;
	display:block;
	z-index:101;
}
#slides_'. $id .' .next img,#slides_'. $id .' .prev img{
-ms-filter: "alpha( opacity=30 )";
filter: alpha( opacity=30 );
opacity: 0.3;
width: 100%;
}
#slides_'. $id .' .next:hover img,#slides_'. $id .' .prev:hover img {
opacity:0.8;
}

#slides_'. $id .' .next {
left: '.$slides_container_width.'px;
}
#wrapper ul.slides_pagination {
	margin:26px auto 0;
	width:135px;
	padding: 0;
}
</style>
<script type="text/javascript">
<!--
	$(function(){
			$("#slides_'. $id .'").slides({
				preload: true,
				preloadImage: "image/slides/slide_loading.gif",
				play: '.$play.',
				pause: 2500,
				hoverPause: true,
				slideEasing: "'.$easing.'",
				paginationClass : "slides_pagination"
			});
		});
//-->
</script>
<script type="text/javascript">
<!--
$(function(){
	 $("#slides_'. $id .' .next img,#slides_'. $id .' .prev img").hover(function(){
	 $(this).fadeTo("fast",0.8);
	 },function(){
	 $(this).fadeTo("fast",0.3);
	 });
	 $("#slides_'. $id .' .slides_control img").hover(function(){
	 $(this).fadeTo("100",0.8,"'.$easing1.'");
	 },function(){
	 $(this).fadeTo("fast",1.0);
	 });
});
//-->
</script>
';
	if ($frame == "large" && is_smart_phone() && $enable_smart_style)
	{
		$eachscript .= '
<style>
#main_visual #frame_'.$id.' {
	left: -36px;
	width: 640px;
	height: 178px;
	top: 0;
}
#main_visual #slides_'. $id .' {
	left: 14px;
	top: 10px;
}

#slides_'. $id .' .slides_container img{
	max-width: 100%;
}
</style>
';
	}

	$qt->appendv('beforescript', $eachscript);

    $text = str_replace(array("\r\n", "\r"), array("\n", "\n"), $text);
    $lines = explode("\n", $text);
	
	$ret = '	<div id="container_'. $id .'">
			<div id="slides_'. $id .'">
				<div class="slides_container">';
	
	foreach ($lines as $line) {
		list($url, $link, $title, $alt) = array_pad(explode(',', $line), 4, '');
		if (trim($url) == '') {
			continue;
		}
		
		$is_url = is_url($url);
		if ( ! $is_url)
		{
			$file = $url;
			if ( ! is_file($file))
			{
				$file = SWFU_IMAGE_DIR.$file;
				if ( ! is_file($file))
				{
					return $qm->replace('plg_show.err_notfound', h($url));
				}
			}
		} else {
			$file = $url;
		}
		
		//first image をセット
		$qt->set_first_image($is_url? $url: (dirname($script). '/'. $file));
		
		if (is_page($link))
		{
			if ($vars['page'] != $link) {
				$link = $script. '?'. rawurlencode($link);
			}
			else {
				$link = '#';
			}
		}
		
		$ret .= '<a href="'.$link.'"><img src="'.$file.'" alt="'.$alt.'" title="'.$title.'" /></a>'."\n";
	
	}
	$ret .= '</div>
	<a href="#" class="prev"><img src="image/slides/arrow-prev.png" width="24" height="43" alt="Arrow Prev" /></a>
	<a href="#" class="next"><img src="image/slides/arrow-next.png" width="24" height="43" alt="Arrow Next" /></a>
	</div>
	<img src="image/slides/'.$frame_image.'" alt="Frame" id="frame_'. $id .'" width="'.$frame_width.'" height="'.$frame_height.'" /></div>';

	return $ret;
}
