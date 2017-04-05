<?php
/**
 *   Embed Video Plugin
 *   -------------------------------------------
 *   /haik-contents/plugin/video.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/08/20
 *   modified :
 *
 *   設置した動画や動画共有サイト上の動画を貼り付けられる。
 *
 *   Usage :
 *   #video(filename[,poster][,ted|wmp][,16:9|4:3])
 *
 */
define('PLUGIN_VIDEO_MIMETYPE_MP4',  'video/mp4');
define('PLUGIN_VIDEO_MIMETYPE_WEBM', 'video/webm');

define('PLUGIN_VIDEO_EMBED_WIDTH', 500);
define('PLUGIN_VIDEO_EMBED_HEIGHT', 281);

define('PLUGIN_VIDEO_YOUTUBE_EMBEDED_OPTION', '?theme=light&showinfo=0&autohide=1');


function plugin_video_inline()
{
    global $vars, $script;

    $qt = get_qt();

    $args = func_get_args();
    $body = array_pop($args);
    $args[] = "popup";

    return plugin_video_body($args, $body);
}

function plugin_video_convert()
{
    global $vars, $script;

    $qm = get_qm();
    $qt = get_qt();

    $args = func_get_args();

    return plugin_video_body($args);
}

function plugin_video_body($args = array(), $body = "")
{
    static $num = 0;
    static $called_sources = array();
    $num++;

    $id = "qhm_plugin_video_{$num}";

    $qt = get_qt();
    $qt->setv("jquery_include", TRUE);

    if (exist_plugin("icon"))
    {
        plugin_icon_set_font_awesome();
    }

    $embed_url = $popup = FALSE;
    $sources = array();
    $poster = FALSE;
    $aspect_ratio = "16by9";

    $width = PLUGIN_VIDEO_EMBED_WIDTH;
    $height = PLUGIN_VIDEO_EMBED_HEIGHT;
    $aspect_ratio_float = 0.5625;

    $theme = FALSE;
    $youtube_thumbnail_type = FALSE;

    $rel = true;  // 関連動画の表示（youtube）

    foreach ($args as $arg)
    {
        $arg = trim($arg);
        switch ($arg)
        {
            case "16:9":
            case "4:3":
                list($aspect_width, $aspect_height) = explode(':', $arg);
                $aspect_ratio_float = (float)$aspect_height / (float)$aspect_width;
                $aspect_ratio = str_replace(":", "by", $arg);
                break;

            case "popup":
                $popup = TRUE;
                break;

            case (preg_match('*^https?://(www\.youtube\.com|youtu\.be|vimeo.com)/*', $arg) ? TRUE : FALSE):
                $embed_url = $arg;
                break;

            case (preg_match('/\Aytpreview=(.+)\z/', $arg, $mts) ? TRUE : FALSE):
                // YouTube thumbnail type
                $_types = array('thumb-default', 'thumb-1', 'thumb-2', 'thumb-3', 'default', 'medium', 'high');
                if (in_array($mts[1], $_types))
                {
                    $youtube_thumbnail_type = $mts[1];
                }
                break;

            case (preg_match('/^(.+\.(png|jpeg|jpg|gif))\z/i', $arg, $mts) ? TRUE: FALSE):
                $poster = get_file_path($mts[1]);
                break;

            case (preg_match('/^(.+\.mp4)\z/i', $arg, $mts) ? TRUE: FALSE):
                $sources[PLUGIN_VIDEO_MIMETYPE_MP4] = get_file_path($mts[1]);
                break;
            case "ted":
            case "wmp":
                $theme = $arg;
                break;
            case "norel":
                $rel = false;
                break;
        }
    }

    $mediaelement_path = 'js/mediaelementplayer/';

    $include_mediaelement = '
<link rel="stylesheet" href="'.$mediaelement_path.'mediaelementplayer.min.css">
<script src="'.$mediaelement_path.'mediaelement-and-player.min.js"></script>
';
    $qt->appendv_once("include_mediaelement", "beforescript", $include_mediaelement);

    $addstyle = '
<link rel="stylesheet" href="'.$mediaelement_path.'mejs-skins.css">
<link rel="stylesheet" href="' . PLUGIN_DIR . 'video/video.min.css">
';
    $qt->appendv_once("plugin_video_style", "beforescript", $addstyle);

    $qt->appendv_once(
        "plugin_video_script",
        "beforescript",
        '<script src="'.PLUGIN_DIR.'video/video.min.js"></script>'
    );

    $popup_html = $popup_style = '';
    if ($popup)
    {
        if ($body == '')
        {
            if ($poster !== FALSE)
            {
                $body = '<img src="'. h($poster) . '">';
            }
            else
            {
                $body = '<i class="fa fa-play-circle-o"></i>';
            }
        }
        $popup_html = <<< EOD
<a class="qhm-plugin-video-trigger" data-toggle="modal" data-target="#{$id}_modal">{$body}</a>
EOD;
    }

    if ($embed_url !== FALSE)
    {
        return $popup_html . plugin_video_embed($embed_url, $id, $aspect_ratio, $body, $popup, $poster, $youtube_thumbnail_type, $rel);
    }

    if ( ! isset($sources[PLUGIN_VIDEO_MIMETYPE_MP4]))
    {
        return '<p>Usage: #video(video.mp4)</p>'."\n";
    }
    else
    {
        if (is_url($sources[PLUGIN_VIDEO_MIMETYPE_MP4]))
        {
            $sources[PLUGIN_VIDEO_MIMETYPE_WEBM] = substr($sources[PLUGIN_VIDEO_MIMETYPE_MP4], 0, -3) . "webm";
        }
        else if (file_exists($sources[PLUGIN_VIDEO_MIMETYPE_MP4]))
        {
            $webm_path = substr($sources[PLUGIN_VIDEO_MIMETYPE_MP4], 0, -3) . "webm";
            if (file_exists($webm_path))
            {
                $sources[PLUGIN_VIDEO_MIMETYPE_WEBM] = $webm_path;
            }
        }
    }

    foreach ($sources as $mime_type => $url)
    {
        if (isset($called_sources[$url]))
        {
            $called_sources[$url] += 1;
            $sources[$mime_type] = $url . '?' . $called_sources[$url];
        }
        else
        {
            $called_sources[$url] = 1;
        }
    }

    if ($poster !== FALSE)
    {
        if (! is_url($poster) &&  ! file_exists($poster))
        {
            $poster = FALSE;
        }
    }

    $sources_html = '';
    foreach ($sources as $type => $source)
    {
        $sources_html .= <<< EOD
  <source src="{$source}" type="{$type}">
EOD;
    }

    // video tag attribute params
    $params = array(
        'controls' => TRUE,
        'preload'  => ($poster === FALSE ? "auto" : "none"),
        'poster'   => $poster,
        'style'    => '"width:100%; height:100%;',
        'width'    => '100%',
        'height'   => '100%',
        'data-aspect-ratio' => $aspect_ratio_float
    );

    if ($theme !== FALSE)
    {
        $params['class'] = "mejs-{$theme}";
    }

    if ($popup)
    {
        $params['data-popup'] = 'popup';
    }

    $attrs = array();
    foreach ($params as $key => $val)
    {
        if ($val === FALSE)
        {
            continue;
        }
        if ($val === TRUE)
        {
            $attrs[] = $key;
            continue;
        }
        $attrs[] = $key . '="' . $val . '"';
    }
    $attrs = join(' ', $attrs);

    $mp4 = $sources[PLUGIN_VIDEO_MIMETYPE_MP4];
    $not_support_html = '<p>動画を再生するには、videoタグをサポートしたブラウザが必要です。</p>';

    $poster_html = '';
    if ($poster !== FALSE)
    {
        $poster_html =  <<< EOD
        <img src="{$poster}" width="320" height="240" title="No video playback capabilities">
EOD;
    }

    $video_html = <<< EOD
  <div class="qhm-plugin-video-video embed-responsive-item">
    <video id="{$id}_video" {$attrs}>
      {$sources_html}
      <object width="320" height="240" type="application/x-shockwave-flash" data="{$mediaelement_path}flashmediaelement.swf">
        <param name="movie" value="{$mediaelement_path}flashmediaelement.swf">
        <param name="flashvars" value="controls=true&file={$mp4}">
        {$poster_html}
      </object>
      {$not_support_html}
    </video>
  </div>
EOD;

    if ($popup)
    {
        $html = <<< EOD
{$popup_html}
<div id="{$id}_modal" class="qhm-plugin-video modal fade" data-type="video">
  <div class="modal-dialog modal-lg">
    <div class="embed-responsive embed-responsive-{$aspect_ratio}">
      {$video_html}
    </div>
  </div>
</div>
EOD;
    }
    else
    {
        $html = <<< EOD
<div id="{$id}" class="qhm-plugin-video embed-responsive embed-responsive-{$aspect_ratio}" $popup_style>
  {$video_html}
</div>
EOD;
    }

    return $html;
}

/**
 * YouTube と Vimeo を貼り付ける
 */
function plugin_video_embed($url, $id, $aspect_ratio, $body, $popup = FALSE, $poster = FALSE, $youtube_thumbnail_type = FALSE, $rel = TRUE)
{
    $qt = get_qt();
    $data = plugin_video_embed_data($url);

    $html = '';
    switch ($data["type"])
    {
        case "youtube":
            $data_popup = $popup ? 'data-popup="popup"' : '';
            $data_rel = $rel ? 'true' : 'false';
            $html = '
<div class="pretty-embed"
     data-pe-videoid="'.h($data['id']).'"
     data-pe-fitvids="true"
     data-pe-custom-preview-image="'.h($poster).'"
     data-pe-preview-size="'.$youtube_thumbnail_type.'"
     data-pe-show-related="'.$data_rel.'"
     '.$data_popup.'></div>';

            $addscript = '
<script src="js/jquery.prettyembed.min.js"></script>
';
            $qt->appendv_once("plugin_video_prettyembed_script", "beforescript", $addscript);
            break;

        case "vimeo":
            $html = '<iframe class="embed-responsive-item" src="'. h($data['url']) .'" allowfullscreen></iframe>';

            $qt->appendv_once(
                  "plugin_video_viemo_script",
                  "beforescript",
                  '<script src="//f.vimeocdn.com/js/froogaloop2.min.js"></script>'
            );
            break;
    }

    if ($popup)
    {
        $html =  <<< EOD
<div id="{$id}_modal" class="qhm-plugin-video modal fade" data-type="{$data["type"]}">
  <div class="modal-dialog modal-lg">
        <div class="embed-responsive embed-responsive-{$aspect_ratio}">
          <div id="{$id}" class="qhm-plugin-video-video embed-responsive-item">
            {$html}
          </div>
        </div>
  </div>
</div>

EOD;
    }
    else
    {
        $html = <<< EOD
<div id="{$id}" class="qhm-plugin-video embed-responsive embed-responsive-{$aspect_ratio}">{$html}</div>
EOD;

    }

    return $html;
}

/**
 * get embed data (url, type:vimemo, youtube)
 *
 */
function plugin_video_embed_data($url)
{
    //YouTube
    //短縮URL(youtu.be)も
    $embed_url = '';
    $type = '';
    $vid = '';

    if (preg_match('|^https?://www\.youtube\.com/watch\?v=([-\w]+)|', $url, $mts)
            OR preg_match('|^https?://youtu\.be/([-\w]+)|', $url, $mts))
    {
        $vid = $mts[1];
        $type = 'youtube';
        $embed_url = '//www.youtube.com/embed/' . $vid . PLUGIN_VIDEO_YOUTUBE_EMBEDED_OPTION;
    }
    //Vimeo
    else if (preg_match('|^https?://vimeo\.com/(\d+)$|', $url, $mts))
    {
        $vid = $mts[1];
        $type = 'vimeo';
        $embed_url = '//player.vimeo.com/video/'. $vid . '?title=0&portrait=0&byline=0&api=1';
    }
    else
    {
        return FALSE;
    }

    return array('url' => $embed_url, 'type' => $type, 'id' => $vid);
}
