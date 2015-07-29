<?php
/**
 *   Section Plugin
 *   -------------------------------------------
 *   /plugin/section.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2013/12/09
 *   modified : 2014/07/18
 *
 *   Description
 *
 *   Usage :
 *
 *   ### Notice: Background-Video on any Browser
 *   When specify background-video of MP4 Search Webm(.webm) and Ogg Video(.ogv) in same dir.
 *   Video sources founded are output in video element.
 *   But, Firefox occurs warning and ignore video unless web server return valid Content-Type.
 *
 *     *Apache solution*
 *     AddType video/webm .webm
 *     AddType video/ogg  .ogv
 *
 */

function plugin_section_convert()
{
    global $script;
    static $cnt = 0;

    $qt = get_qt();

    $args = func_get_args();
    $body = array_pop($args);

    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));

    $title = '';

    $h_align  = 'center';//left|center|right
    $v_align  = 'middle';//top|middle|bottom
    $height   = $height_xs = '';//awesome!
    $type     = 'cover';//cover|repeat
    $style    = 'default';//primary|info|success|warning|danger
    $fullpage =  FALSE;//full height section; fit window size

    $relative  = FALSE;//position: relative
    $container = TRUE;//FALSE to enable 'fit' option
    $jumbotron = FALSE;//FALSE to remove .jumbotron

    $color = FALSE;//inherit

    $background_image = $background_image_xs = FALSE;
    $background_fix   = FALSE;
    $filter           = FALSE;
    $background_color = FALSE;//transparent
    $additional_class = $container_class = '';
    $additional_style = '';
    $background_video = FALSE;
    $background_filename = '';
    $attrs = array(
        'id'    => 'qhm_section_' . ++$cnt,
        'class' => 'qhm-section',
    );
    $styles = array(
        '__xs__'  => array(),
        '__raw__' => '',
    );

    $is_eyecatch = FALSE;

    foreach ($args as $arg)
    {
        if (preg_match('/\A(left|center|right)\z/i', $arg, $mts))
        {
            $h_align = strtolower($mts[1]);
        }
        else if (preg_match('/\A(top|middle|bottom)\z/i', $arg, $mts))
        {
            $v_align = strtolower($mts[1]);
        }
        else if (preg_match('/\A(cover|repeat)\z/i', $arg, $mts))
        {
            $type = strtolower($mts[1]);
        }
        else if (preg_match('/\Axsbg=(.+)\z/', $arg, $mts))
        {
            $background_image_xs = $mts[1];
        }
        else if (preg_match('/\Axsheight=([\d.]+)(.*)\z/', $arg, $mts))
        {
            $height_xs = $mts[1] . ($mts[2] ? $mts[2] : 'px');
        }
        else if (preg_match('/\.(jpe?g|gif|png)\z/i', $arg))
        {
            $background_image = trim($arg);
        }
        else if (preg_match('/\Aimage=(.+)\z/', $arg, $mts))
        {
            $background_image = trim($mts[1]);
        }
        else if (preg_match('/\A([\d.]+)(.*)\z/', $arg, $mts))
        {
            $height = $mts[1] . ($mts[2] ? $mts[2] : 'px');
        }
        else if ($arg === 'page')
        {
            $fullpage = TRUE;
        }
        else if (preg_match('/\A(primary|info|success|warning|danger)\z/i', $arg, $mts))
        {
            $style = $mts[1];
        }
        else if (preg_match('/\Aclass=(.+)\z/', $arg, $mts))
        {
            $additional_class .= ' ' . $mts[1];
        }
        else if (preg_match('/\Astyle=(.+)\z/', $arg, $mts))
        {
            $additional_style .= $mts[1];
        }
        else if (preg_match('/\Acolor=(.+)\z/', $arg, $mts))
        {
            $color = $mts[1];
        }
        else if (preg_match('/\Abgcolor=(.+)\z/', $arg, $mts))
        {
            $background_color = $mts[1];
        }
        else if (preg_match('/\Abgvideo=(.+)\z/', $arg, $mts))
        {
            $background_video = $mts[1];
        }
        else if (preg_match('/\Atitle=(.+)\z/', $arg, $mts))
        {
            $title = $mts[1];
        }
        else if ($arg === 'fit')
        {
            $container = FALSE;
            $styles['padding'] = 0;
            $styles['height']  = 'auto';
        }
        else if ($arg === 'fix')
        {
            $background_fix = TRUE;
        }
        else if ($arg === 'relative')
        {
            $relative = TRUE;
        }
        else if ($arg === 'jumbotron')
        {
            $jumbotron = TRUE;
        }
        else if ($arg === 'nojumbotron')
        {
            $jumbotron = FALSE;
        }
        // eyecatch プラグインからの呼び出し時に自動的に付けられるオプション
        else if ($arg === 'eyecatch')
        {
            $additional_class .= ' qhm-eyecatch';
            $jumbotron = TRUE;
            $is_eyecatch = TRUE;
        }
        else if ($arg === 'blur')
        {
            $filter = 'blur';
        }
        else if (preg_match('/\A(dark|light)(?:=(.+))?\z/', $arg, $mts))
        {
            $filter = $mts[1];
            $filter_value = & $mts[2];
        }
        else if ($arg === '__default')
        {
	        $additional_class .= ' qhm-eyecatch-default';
        }
    }

    // !set attributes
    if ($jumbotron)
    {
        $attrs['class'] = 'jumbotron ' . $attrs['class'];
    }

    //set base class
    $attrs['class'] .= ' qhm-section-' . $style;

    if ($relative)
    {
        $styles['position'] = 'relative';
    }

    if ($is_eyecatch)
    {
        if (isset($_SESSION['temp_design']))
        {
            $conf = get_skin_custom_vars($_SESSION['temp_design']);
        }
        else
        {
            global $style_name;
            $conf = get_skin_custom_vars($style_name);
        }

        if (isset($conf['default_eyecatch']) && $conf['default_eyecatch'])
        {
            if ($background_color === FALSE && $background_image === FALSE && isset($conf['eyecatch_bgimage']))
            {
                $background_image = $conf['eyecatch_bgimage'];
            }
            if ($background_image === FALSE && isset($conf['enable_eyecatch_bgimage']) && ! $conf['enable_eyecatch_bgimage'])
            {
                $background_image = '';
            }
            if ($color === FALSE && isset($conf['eyecatch_color']))
            {
                $color = $conf['eyecatch_color'];
            }
        }
    }

    if ($color)
    {
        $styles['color'] = $color;
    }

    if ($background_color)
    {
        $styles['background-color'] = $background_color;
    }

    if ($background_image)
    {
        if (is_url($background_image, TRUE, TRUE))
        {
            $background_filename = $background_image;
        }
        else
        {
            $background_filename = dirname($script . 'dummy').'/'.get_file_path($background_image);
        }
        //first image をセット
        $qt->set_first_image($background_filename);

        // フィルター
        if ($filter === 'blur')
        {
            $attrs['data-filter'] = $filter;
            $svg_html = '
<svg version="1.1" xmlns="http://www.w3.org/2000/svg">
  <filter id="blur">
    <feGaussianBlur stdDeviation="6" />
  </filter>
</svg>
';
            $qt->appendv_once('plugin_section_blur_svg', 'lastscript', $svg_html);

            $addjs = '
<script>
$(function(){
  var ua = {};
  ua.name = window.navigator.userAgent.toLowerCase();
  if (ua.name.indexOf("msie") >= 0 || ua.name.indexOf("trident") >= 0) {
    $(".qhm-section[data-filter=blur] > div").css({background: "none", position: "static"});
  }
});
</script>
';
            $qt->appendv_once('plugin_section_kill_blur_on_ie', 'beforescript', $addjs);
        }

        if ($background_video === FALSE)
        {
            $styles['background-image'] = 'url(' . h($background_filename) . ')';
        }

        if ($background_fix)
        {
            // iOS, Android では無効にする
            $addjs = '
<script>
$(function(){
  var ua = {};
  ua.name = window.navigator.userAgent.toLowerCase();
  if (ua.name.indexOf("ipad") >= 0 || ua.name.indexOf("ipod") >= 0 || ua.name.indexOf("iphone") >= 0 || ua.name.indexOf("android") >= 0) {
    $(".qhm-section[data-background-attachment=fixed]").css({backgroundAttachment: "inherit"}).removeAttr("data-background-attachment");
  }
});
</script>
';
            $qt->appendv_once('plugin_section_kill_fixed_on_ios+android', 'beforescript', $addjs);
            $styles['background-attachment'] = 'fixed';
            $attrs['data-background-attachment'] = 'fixed';
        }

        $attrs['data-background-image'] = $background_image;
        $attrs['data-background-type'] = $type;
    }

    $filter_wrapper_html = '';
    if ($filter === 'dark' || $filter === 'light')
    {
        $attrs['data-filter'] = $filter;
        $opacity = $filter_value ? ($filter_value / 100.0) : '0.4';
        $bg_color = ($filter === 'light') ? 255 : 0;
        $rgba = 'rgba('. str_repeat($bg_color.',', 3).$opacity.');';
        $filter_wrapper_html = '
<div class="dark-overlay-wrapper">
<div class="dark-overlay" style="background-color:'.$rgba.'"></div>
</div>
';
    }

    if ($background_image_xs)
    {
        if (is_url($background_image_xs, TRUE, TRUE))
        {
            $background_filename = $background_image_xs;
        }
        else
        {
            $background_filename = dirname($script . 'dummy').'/'.get_file_path($background_image_xs);
        }
        //first image をセット
        $qt->set_first_image($background_filename);
        $styles['__xs__']['background-image'] = 'url('.h($background_filename).')';
    }

    if ($background_video)
    {
        //dark フィルター以外は使えない
        if (isset($attrs['data-filter']) && ! in_array($attrs['data-filter'], array('dark', 'light')))
        {
            unset($attrs['data-filter']);
        }
        $ext = pathinfo($background_video, PATHINFO_EXTENSION);
        if ($ext == 'mp4')
        {
            $background_videofile = array();

            if (is_url($background_video))
            {
                $background_videofile["video/mp4"] = $background_video;
                $background_videofile["video/webm"] = preg_replace('/\.mp4$/i', '.webm', $background_video);
                $background_videofile["video/ogg"] = preg_replace('/\.mp4$/i', '.ogv', $background_video);
            }
            else
            {
                $background_videofile["video/mp4"] = $mp4_path = get_file_path($background_video);
                foreach (array("video/webm" => ".webm", "video/ogg" => ".ogv") as $mime => $ext)
                {
                    $tmpfile = preg_replace('/\.mp4$/i', $ext, $mp4_path);
                    if (file_exists($tmpfile))
                    {
                        $background_videofile[$mime] = $tmpfile;
                    }
                }
            }

            if ($background_image)
            {
                $bg_image_style = 'background-image:url('.h($background_filename).');';
            }

            $add_js = '
<script>
if (navigator.userAgent.toLowerCase().match(/iphone|ipod|ipad|android|windows phone/))
{
  $(window).on("load", function(){$(".qhm-section .background-video .qhm-section-video").hide()});
}
</script>
';
            $qt->appendv_once('plugin_section_prevent_video', 'beforescript', $add_js);
            $video_html = '
    <div class="background-video" style="'.$bg_image_style.'">
      <video class="qhm-section-video" preload="" loop="" autoplay="" muted="" poster="'.h($background_filename).'">
';
            foreach ($background_videofile as $mime => $path)
            {
                $video_html .= '<source src="' . h($path) . '" type="'.$mime.'">';
            }
            $video_html .= '
      </video>
    </div>
';
            $attrs['data-video'] = 'video';
        }
    }

    if ($fullpage)
    {
        $styles['height'] = '600px';
        $styles['__xs__']['height'] = '100vh';
        $attrs['data-height'] = 'page';
        $addjs = '
<script>
$(function(){
  var $sections = $(".qhm-section[data-height=page]");
  if ($sections.length === 0) return;

  var resizeSection = function resizeSection() {
    var windowHeight = $(window).height();
    $sections.innerHeight(windowHeight);
  };
  resizeSection();
  $(window).on("resize", resizeSection);
});
</script>
';
        $qt->appendv_once('plugin_section_page_height', 'beforescript', $addjs);
    }
    else
    {
        if ($height != '')
        {
            $styles['height'] = $height;
        }
        if ($height_xs != '')
        {
            $styles['__xs__']['height'] = $height_xs;
        }
        $attrs['data-height'] = $height;
    }

    if ($title !== '')
    {
        $attrs['data-title'] = $title;
    }

    $attrs['data-horizontal-align'] = $h_align;
    $attrs['data-vertical-align'] = $v_align;

    $attrs['class'] .= ' ' . $additional_class;

    $styles['__raw__'] = $additional_style;

    $attr_string = '';
    foreach ($attrs as $name => $value)
    {
        $attr_string .= ' ' . $name . '="' . h($value) . '"';
    }

    if ($container)
    {
        $container_class = 'container-fluid';
    }

    // ! make html
    $body = convert_html($body);

    $html = <<< EOH
<section {$attr_string}>
  {$filter_wrapper_html}
  {$video_html}
    <div>
      <div class="{$container_class} qhm-section-content">
        {$body}
      </div>
    </div>
</section>
EOH;

    $addstyle = '
<link rel="stylesheet" href="'.PLUGIN_DIR.'section/section.css" />
';

    $qt->appendv_once('plugin_section_style', 'beforescript', $addstyle);

    $style_tag  = '<style class="qhm-plugin-section-style">';
    $style_tag .= '#' . $attrs['id'] . ' {';
    $style_tag .= plugin_section_make_style($styles);
    $style_tag .= '}';
    if (isset($styles['__xs__']) && $styles['__xs__'])
    {
        $style_tag .= '@media (max-width:767px){';
        $style_tag .= '#' . $attrs['id'] . ' {';
        $style_tag .= plugin_section_make_style($styles['__xs__']);
        $style_tag .= '}}';
    }
    $style_tag .= '</style>' . "\n";
    $qt->appendv('beforescript', $style_tag);

    return $html;
}

function plugin_section_make_style($styles)
{
    $style = '';
    foreach ($styles as $property => $value)
    {
        if ($property === '__raw__')
        {
            $style .= $value
                ? (trim(trim($value), ';') . ';')
                : '';
        }
        else if ($property === '__xs__')
        {
            // ignore
        }
        else
        {
            $style .= "{$property}:$value;";
        }
    }
    return $style;
}
