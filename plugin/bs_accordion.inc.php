<?php
/**
 *   Bootstrap Accordion
 *   -------------------------------------------
 *   bs_accordion.inc.php
 *
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *
 *   created  : 13/01/17
 *   modified :
 *
 *   Description
 *
 *
 *   Usage :
 *
 *    #accordion(1){{
 *    - head1
 *    - head2
 *    - [[head3>URL]]
 *
 *    ====
 *    content1
 *    ====
 *    content2
 *
 *    }}
 *
 */
function plugin_bs_accordion_convert()
{
    static $s_accordion_cnt = 1;

    $qt = get_qt();

    $args   = func_get_args();
    $body   = array_pop($args);
    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));

    $nostyle = FALSE;
    $panel_color = 'panel-default';

    $open_index = 0;
    if (count($args) > 0)
    {
        foreach ($args as $arg)
        {
            // 開いておくタブ指定
            if (is_numeric(trim($arg)))
            {
                $open_index = trim($arg);
            }
            // パネルを利用しない
            else if ($arg === 'nostyle')
            {
                $nostyle = TRUE;
            }
            else
            {
                $panel_color = get_bs_style($arg, 'panel');
            }
        }
    }

    $blocks = explode('====', $body);
    $header = array_shift($blocks);
    $headnum = 1;
    $headers = array();
    foreach (explode("\n", $header) as $line)
    {
        if (preg_match("/^\-\s?(.*)$/",$line,$matches))
        {
            $tmp = convert_html($matches[1], TRUE);

            $link = "#acc{$s_accordion_cnt}_collapse{$headnum}";
            $head = $tmp;
            if (preg_match("/^<a href=\"([^\"]*)\".*?>(.*?)<\/a>/",$tmp, $matches2))
            {
                $link = $matches2[1];
                $head = $matches2[2];
            }
            $headers[] = array(
                'link' => h($link),
                'head' => $head
            );
            $headnum++;
        }
    }

    $data_parent = 'accordion'.$s_accordion_cnt;
    $accordion_body = '';

    $accordion_collase_class = $nostyle ? "accordion-collapse" : "panel-collapse";
    $accordion_body_class = $nostyle ? "accordion-body qhm-block" : "panel-body qhm-bs-block";


    for ($i=0; $i < count($headers); $i++)
    {
        $collapse_id = 'acc'.$s_accordion_cnt.'_collapse'.($i+1);
        $block_body = '';
        if (isset($blocks[$i]) && trim($blocks[$i]) != '')
        {
            $add_class = '';

            // あらかじめ開いておくインデックスの指定
            if ($open_index == ($i+1))
            {
                $add_class = ' in';
            }

            $block_body = convert_html($blocks[$i]);
            $block_body = <<< EOD
        <div id="{$collapse_id}" class="$accordion_collase_class collapse{$add_class}">
            <div class="{$accordion_body_class}">{$block_body}</div>
        </div>
EOD;
        }

        // リンクが設定されている場合は、そのURLヘ移動するため、アコーディオンの設定をオフに
        $data_toggle = 'collapse';
        if ($headers[$i]['link'] != '#'.$collapse_id)
        {
            $data_toggle = '';
        }

        $accordion_group_class   = $nostyle ? "accordion-panel" : "panel ".$panel_color;
        $accordion_heading_class = $nostyle ? "accordion-heading" : "panel-heading";
        $accordion_title_start   = $nostyle ? "" : '<h4 class="panel-title no-toc">';
        $accordion_title_end     = $nostyle ? "" : '</h4>';

        $str = <<< EOD
    <div class="{$accordion_group_class}">
        <div class="{$accordion_heading_class}">
            {$accordion_title_start}
                <a class="accordion-toggle" data-toggle="{$data_toggle}" data-parent="#{$data_parent}" href="{$headers[$i]['link']}">{$headers[$i]['head']}</a>
            {$accordion_title_end}
        </div>
        {$block_body}
    </div>
EOD;
        $accordion_body .= $str;
    }


    $accordion_class = $nostyle ? "accordion-group" : "panel-group";

    $html = <<< EOD
<div class="orgm-accordion {$accordion_class}" id="{$data_parent}">
{$accordion_body}
</div>
EOD;

    $s_accordion_cnt++;
    return $html;
}
