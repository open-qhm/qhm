<?php
/**
 *   Bootstrap Tabs
 *   -------------------------------------------
 *   bs_tabs.inc.php
 *
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *
 *   created  : 13/01/17
 *   modified : 13/08/01
 *
 *   Description
 *
 *
 *   Usage :
 *
 *    #tabs(1,justify){{
 *    - head1
 *    - head2
 *    - [[head3>URL]]
 *
 *    ====
 *    panel1
 *    ====
 *    panel2
 *
 *    }}
 *
 */
function plugin_bs_tabs_convert()
{
    static $s_tabs_cnt = 1;

    $args   = func_get_args();
    $body   = array_pop($args);
    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));

    $qt = get_qt();

    $open_index = 1;
    $justified = FALSE;

    foreach ($args as $arg)
    {
        $arg = trim($arg);
        if (is_numeric($arg))
        {
            $open_index = trim($args[0]);
        }
        else if (substr($arg, 0, 4) === 'just')
        {
            $justified = TRUE;
        }
    }

    $panes = explode('====', $body);
    $header = array_shift($panes);

    $header_html = '';
    $str = '';
    $headnum = 1;
    foreach (explode("\n", $header) as $line)
    {
        if (preg_match("/^\-\s?(.*)$/",$line,$matches))
        {
            $tmp = convert_html($matches[1], TRUE);

            $link = "#tab{$s_tabs_cnt}_pane{$headnum}";
            $head = $tmp;
            $data_toggle = 'tab';
            if (preg_match("/^<a href=\"([^\"]*)\".*?>(.*?)<\/a>/",$tmp, $matches2))
            {
                $link = $matches2[1];
                $head = $matches2[2];
                $data_toggle = '';
            }

            $add_class = '';
            // あらかじめ開いておくインデックスの指定
            if ($open_index == $headnum)
            {
                $add_class = ' active';
            }

            $str = '<li class="'.$add_class.'"><a href="'.$link.'" data-toggle="'.$data_toggle.'">'.$head.'</a></li>';
            $header_html .= $str;
            $headnum++;
        }
    }

    $tab_pane_html = '';
    for ($i=0; $i < $headnum; $i++)
    {
        $tab_pane_id = 'tab'.$s_tabs_cnt.'_pane'.($i+1);
        $pane_body = '';
        if (isset($panes[$i]) && trim($panes[$i]) != '')
        {
            $add_class = '';

            // あらかじめ開いておくインデックスの指定
            if ($open_index == ($i+1))
            {
                $add_class = ' active';
            }

            $pane_body = convert_html($panes[$i]);
            $pane_body = <<< EOD
        <div class="tab-pane{$add_class} qhm-bs-block" id="{$tab_pane_id}">
            {$pane_body}
        </div>
EOD;
        }

        $tab_pane_html .= $pane_body;
    }

    $data_parent = 'tab'.$s_tabs_cnt;

    $class = '';
    $justified_class =  $justified ? ' nav-justified' : '';
    $class = 'nav-tabs'. $justified_class;

    $html = <<< EOD
    <ul class="nav {$class}" id="{$data_parent}">
        {$header_html}
    </ul>
    <div class="tab-content">
        {$tab_pane_html}
    </div>
EOD;

    $s_tabs_cnt++;
    return $html;
}
