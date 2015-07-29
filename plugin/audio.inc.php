<?php

define('PLUGIN_AUDIO_MIMETYPE_MP3', 'audio/mp3');
define('PLUGIN_AUDIO_MIMETYPE_OGG', 'audio/ogg');

function plugin_audio_inline()
{
    $args = func_get_args();
    return plugin_audio_body($args);
}

function plugin_audio_convert()
{
    $args = func_get_args();
    return '<div class="qhm-plugin-audio-wrapper">'.plugin_audio_body($args).'</div>';
}

function plugin_audio_body($args = array())
{
    static $num = 0;
    $num++;
    $id = "qhm_plugin_audio_{$num}";

    $qt = get_qt();
    $qt->setv('jquery_include', true);

    $controls = TRUE;
    $compact = FALSE;
    $loop = $autoplay = $preload = FALSE;
    $allow_download = TRUE;
    $sources = array();

    // Compact Player Setting
    $play_label = '再生';
    $pause_label = '停止';

    foreach ($args as $arg)
    {
        $arg = trim($arg);
        switch ($arg)
        {
            case 'loop':
            case 'repeat':
                $loop = TRUE;
                break;
            case 'auto':
            case 'autoplay':
                $autoplay = TRUE;
                break;
            case 'preload':
                $preload = TRUE;
                break;
            case 'nocontrol':
                $controls = FALSE;
                break;
            case 'nodownload':
                $allow_download = FALSE;
                break;
            case 'compact':
                $compact = TRUE;
                $controls = FALSE;
                break;
            case (preg_match('/^(.+\.mp3)\z/i', $arg, $mts) ? TRUE: FALSE):
                $sources[PLUGIN_AUDIO_MIMETYPE_MP3] = get_file_path($mts[1]);
                break;
            case (preg_match('/\A(play|pause)=(.+)\z/', $arg, $mts) ? TRUE: FALSE):
                $varname = $mts[1] . '_label';
                $$varname = $mts[2];
                break;
        }
    }

    if ( ! isset($sources[PLUGIN_AUDIO_MIMETYPE_MP3]))
    {
        return '<p>Usage: #audio(audio.mp3)</p>'."\n";
    }
    else
    {
        if (is_url($sources[PLUGIN_AUDIO_MIMETYPE_MP3]))
        {
            $sources[PLUGIN_AUDIO_MIMETYPE_OGG] = substr($sources[PLUGIN_AUDIO_MIMETYPE_MP3], 0, -3) . "ogg";
        }
        else if (file_exists($sources[PLUGIN_AUDIO_MIMETYPE_MP3]))
        {
            $ogg_path = substr($sources[PLUGIN_AUDIO_MIMETYPE_MP3], 0, -3) . "ogg";
            if (file_exists($ogg_path))
            {
                $sources[PLUGIN_AUDIO_MIMETYPE_OGG] = $ogg_path;
            }
        }
        else {
            return '<p>'.$sources[PLUGIN_AUDIO_MIMETYPE_MP3].' が見つかりません</p>'."\n";
        }
    }

    $attrs = '';
    foreach (array('controls', 'loop', 'autoplay', 'preload') as $attr)
    {
        if ($$attr)
        {
            $attrs .= ' ' . $attr;
        }
    }

    $sources_html = '';
    foreach ($sources as $type => $source)
    {
        $sources_html .= <<< EOD
<source src="$source" type="$type">
EOD;
    }

    $not_support_html = '音声を再生できません。';
    if ($allow_download)
    {
        $not_support_html .= '<a href="'.$sources[PLUGIN_AUDIO_MIMETYPE_MP3].'">ダウンロード</a>';
    }
    $not_support_html = "<p>{$not_support_html}</p>";
    if ( ! $controls && ! $compact)
    {
        $not_support_html = '';
    }

    $compact_html = '';
    if ($compact)
    {
        $compact_html = plugin_audio_compact_player($id, $play_label, $pause_label);
    }
    else
    {
        plugin_audio_include_mediaelement();
        $player_options_json = json_encode(array(
            'customError' => $not_support_html
        ));
        $addjs = <<< EOD
<script>
$(function(){
    var options = {$player_options_json};
    $("audio.qhm-plugin-audio[controls]").mediaelementplayer(options);
});
</script>
EOD;
        $qt->appendv_once('plugin_audio_call_mediaelement', 'beforescript', $addjs);
    }

    $qt->appendv_once('plugin_audio_include_css', 'beforescript', "\n".'<link rel="stylesheet" href="'.PLUGIN_DIR.'audio/audio.min.css"></link>
');

    $html = <<< EOD
$compact_html
<audio id="{$id}"{$attrs} class="qhm-plugin-audio">
$sources_html
$not_support_html
</audio>
EOD;
    return $html;
}

function plugin_audio_include_mediaelement()
{
    $qt = get_qt();
    $include_head = <<< EOD
<link rel="stylesheet" href="js/mediaelementplayer/mediaelementplayer.min.css">
<script src="js/mediaelementplayer/mediaelement-and-player.min.js"></script>
EOD;

    $qt->appendv_once('include_mediaelement', 'beforescript', $include_head);
}

function plugin_audio_compact_player($id, $play_label = null, $pause_label = null)
{
    $qt = get_qt();

    if (exist_plugin('icon'))
    {
        plugin_icon_set_font_awesome();
        $play_label = '<i class="fa fa-play-circle"></i> '.$play_label;
        $pause_label = '<i class="fa fa-pause"></i> '.$pause_label;
    }
    $e_play_label = h($play_label);
    $e_pause_label = h($pause_label);
    $compact_html = <<< EOD
<a href="#{$id}" data-toggle="qhm-audio" class="qhm-plugin-audio-compact-player"
   data-play-label="{$e_play_label}" data-pause-label="{$e_pause_label}"></a>
EOD;

    $include_head = '
<script src="'.PLUGIN_DIR.'audio/audio.min.js"></script>';
    $qt->appendv_once('plugin_audio_head', 'beforescript', $include_head);

    return $compact_html;
}
