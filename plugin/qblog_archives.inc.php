<?php
/**
 *   QBlog Achives Plugin
 *   -------------------------------------------
 *   ./plugin/qblog_archives.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 12/07/27
 *   modified :
 *
 *   Description
 *
 *
 *   Usage :
 *
 */
function plugin_qblog_archives_convert()
{
    global $vars, $script, $qblog_close;

    //閉鎖中は何も表示しない
    if ($qblog_close && ! ss_admin_check())
    {
    return '';
    }

    $args = func_get_args();

    //---- キャッシュのための処理を登録 -----
    $qt = get_qt();
    if($qt->create_cache) {
      return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
    }
    //------------------------------------

    $by_year = false;        // 年数毎にまとめる
    $by_year_threashold = 0; // 何件を超えたらまとめ出すか

    foreach ($args as $arg) {
        $arg = trim($arg);
        if (preg_match('/by_year(?:=(\d+))?/', $arg, $matches)) {
            $by_year = true;
            $by_year_threashold = (int)$matches[1];
        }
    }

    $archives_file = CACHEQBLOG_DIR . 'qblog_archives.dat';
    if( file_exists($archives_file) ){
    $archives_list = file_get_contents($archives_file);
    }
    else{
    $archives_list = array();
    }

    // 件数が by_year_threashold に満たなければ by_year を解除する
    $archives = explode("\n", $archives_list);
    if ($by_year && count($archives) < $by_year_threashold) {
        $by_year = false;
    }

    $list = '';

    if ($by_year) {
        $current_year = 0;
        $list .= '<div class="qblog_archives by-year">';
        $list .= '<div class="list-group">';
        $year_heading = false;
        $year_collapse = true;
        foreach (explode("\n", $archives_list) as $line) {
            if (rtrim($line) != '') {
                list($year, $month, $num) = explode(",", rtrim($line));
                if ($year != $current_year) {
                    $current_year = $year;
                    if ($year_heading) {
                        $list .= '</div></div>';
                    } else {
                        $year_heading = true;
                    }
                    $year_collapse = $current_year !== date('Y');
                    $list .= '<a data-toggle="collapse" href="#qblog_archives_by_year_'.$year.'" class="list-group-item plugin-qblog-archives-year '. ($year_collapse ? 'collapsed' : '') .'">'. $current_year .'</a><div class="plugin-qblog-archives-year-container collapse '. ($year_collapse ? '' : 'in') .'" id="qblog_archives_by_year_'.$year.'"><div class="list-group">';
                }
                $archives_url = $script.'?QBlog&amp;mode=archives&amp;date='.rawurlencode($year.$month);
                $list .= '<a href="'.$archives_url.'" class="list-group-item" data-count="'. $num .'">'.$year.'年'.$month.'月 ('.$num.')'.'</a>';
            }
        }
        $list .= '</div></div></div></div>';
    } else {
        $list .= '<ul class="qblog_archives">';
        foreach (explode("\n", $archives_list) as $line) {
            if (rtrim($line) != '') {
                list($year, $month, $num) = explode(",", rtrim($line));
                $archives_url = $script.'?QBlog&amp;mode=archives&amp;date='.rawurlencode($year.$month);
                $list .= '<li><a href="'.$archives_url.'">'.$year.'年'.$month.'月 ('.$num.')'.'</a></li>';
            }
        }
        $list .= '</ul>';
    }

    if ($by_year) {
        plugin_qblog_archives_set_js_for_by_year();
    }

    return $list;
}

function plugin_qblog_archives_set_js_for_by_year() {
    $qt = get_qt();

    $js = '
<script>
$(function(){
    $(".plugin-qblog-archives-year").each(function(){
        var year = $(this).text();
        var $archives = $(this).next().find("a");
        var count = 0;
        $archives.each(function(){
            count += parseInt($(this).data("count"), 10);
        });
        $(this).text(year + "年（" + count + "）");
    });
});
</script>
';
    $qt->appendv_once('plugin_qblog_archives_set_js_for_by_year', 'beforescript', $js);
}
