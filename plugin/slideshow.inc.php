<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: lightbox2.inc.php,v 1.22 2005/06/16 15:04:08 hokuken.com Exp $
//
// Text lightbox2 plugin

function plugin_slideshow_convert()
{
	global $vars;
	$qm = get_qm();
	$qt = get_qt();
	$qt->setv('jquery_include', true);
	$qt->setv('jquery_cookie_include', true);
	
	static $id = 0;
	$id++;
		
	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	list($height, $random, $interval, $margin_left, $speed) = array_pad($args, 5, '');
	if ($height==''){
		return "<p>{$qm->m['plg_slideshow']['err_noheight']}</p>";
	}
	
	$height = is_numeric($height)? $height : 350;
	$random = $random == 'true' ? $random : 'false';
	$interval = $interval == ''? 3000 : $interval * 1000;
	$margin_left = $margin_left == '' ? 0 : $margin_left;
	$speed = $speed == '' ? 1000 : $speed * 1000;

	
	
	$addscript = '
<script type="text/javascript">
<!--
$(function(){
	$("div.plugin_slideshow").each(function(){
		var $$ = $(this),
			option = $$.data("plugin_slideshow_option");

		$$.css({
			height: option.height,
			marginLeft: option.marginLeft
		});
		
		$$.bind("slideImage", function(){

		    var $active = $("img.active", $$);
		
		    if ( $active.length == 0 ) {
		    	$active = $("img:last-child", $$);
		    }
			
			if (option.random) {
				var $sibs = $active.siblings("img"),
					randnum = Math.floor(Math.random() * $sibs.length),
					$next = $sibs.eq(randnum);
			} else {
				var $next = $active.next("img").length? $active.next("img"): $("img:first", $$);
			}
			$active.addClass("last-active");
			
			$next
			.css("opacity", 0)
			.addClass("active")
			.animate({opacity: 1.0}, option.speed, function(){
				$active.removeClass("active last-active");
				setTimeout(function(){
					$$.triggerHandler("slideImage");
				}, option.interval);
			});
			
		});
		
		setTimeout(function(){
			$$.triggerHandler("slideImage");
		}, option.interval);
	});
});
//-->
</script>
<style type="text/css">
div.plugin_slideshow {
    position:relative;
}
div.plugin_slideshow img {
    position:absolute;
    top:0;
    left:0;
    z-index:8;
    opacity:0.0;
}
div.plugin_slideshow img.active {
    z-index:10;
    opacity:1.0;
}
div.plugin_slideshow img.last-active {
    z-index:9;
}
</style>
';
	$qt->appendv_once('plugin_slideshow', 'beforescript', $addscript);
	
	$eachscript = '
<script type="text/javascript">
<!--
$(function(){
	$("#slideshow_'. $id .'").data("plugin_slideshow_option",  {height:'.$height.', random:'.$random.', interval:'.$interval.', marginLeft:'.$margin_left.', speed:'.$speed.'});
});
//-->
</script>
';
	$qt->prependv('beforescript', $eachscript);

	$lines = $lines = preg_split("/[\r\n(\r\n)]/",$text);
	
	$active = 'class="active"';
	$ret = '	<div id="slideshow_'. $id .'" class="plugin_slideshow" >';
	
	foreach($lines as $line){
		list($url, $alt) = array_pad( explode(',', $line), 2, '');
		
		if($url!=''){
			$ret .= '<img src="'. h($url). '" alt="'. h($alt). '" '. $active. '/>'."\n";
			$active = '';
			
			//first image をセット
			$qt->set_first_image(is_url($url)? $url: (dirname($script). '/'. $url));
		}
	}
	$ret .= '</div>';
	
	return $ret;
}
?>
