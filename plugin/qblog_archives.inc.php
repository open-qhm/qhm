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

    // ---- オプション解析 ----
    $by_year = false; // 年数毎にまとめる
    $default_year_collapse = false;

    foreach ($args as $arg) {
        $arg = trim($arg);
        if ($arg === 'by_year') {
            $by_year = true;
        } else if ($arg === 'year_collapse') {
            $default_year_collapse = true;
        }
    }

    $archives_file = CACHEQBLOG_DIR . 'qblog_archives.dat';
    if( file_exists($archives_file) ){
    $archives_list = file_get_contents($archives_file);
    }
    else{
    $archives_list = array();
    }

    $archives = array_map(function($line){
        list($year, $month, $num) = explode(",", rtrim($line));
        return array(
            'year' => $year,
            'month' => $month,
            'count' => $num,
            'url' => plugin_qblog_archives_get_archive_url($year, $month),
        );
    }, array_filter(explode("\n", $archives_list)));

    if ($by_year) {
        $year_archives = array_reduce($archives, function($carry, $archive) use ($default_year_collapse) {
            $year = $archive['year'];
            if ( ! isset($carry[$year])) {
                $carry[$year] = array(
                    'year' => $year,
                    'archives' => array(),
                    'count' => 0,
                    'collapse' => $default_year_collapse ? true : ($year !== date('Y')),
                );
            }
            $carry[$year]['archives'][] = $archive;
            $carry[$year]['count'] += $archive['count'];
            return $carry;
        }, array());

        ob_start();
        include __DIR__ . '/qblog/qblog_archives_by_year.html';
        $html = ob_get_clean();
    } else {
        ob_start();
        include __DIR__ . '/qblog/qblog_archives.html';
        $html = ob_get_clean();
    }

    return $html;
}

/**
 * 渡した年月のアーカイブページへのURLを生成する。
 * @return [String] url
 */
function plugin_qblog_archives_get_archive_url($year, $month) {
    global $script;
    return $script . '?QBlog&mode=archives&date=' . rawurlencode($year.$month);
}
