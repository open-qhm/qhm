<?php
/**
 *   Search2 Plugin
 *   -------------------------------------------
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014/06/10
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *     #search2    // full size search form
 *     #search2(6) // half size search form
 *     #search2(showtype) // with search type selector AND/OR
 *     #search2(primary) // Use Bootstrap button color
 *
 */

define('PLUGIN_SEARCH2_MAX_LENGTH', 80);

// Show a search box on a page
function plugin_search2_convert()
{
    $args = func_get_args();
    return plugin_search2_form('', '', $args);
}

function plugin_search2_action()
{
    global $post, $vars;
    $qm = get_qm();

    $s_word = isset($vars['word']) ? h($vars['word']) : '';
    if (strlen($s_word) > PLUGIN_SEARCH2_MAX_LENGTH) {
        unset($vars['word']);
        die_message($qm->m['plg_search']['err_toolong']);
    }

    $type = isset($vars['type']) ? $vars['type'] : '';
    $base = isset($vars['base']) ? $vars['base'] : '';

    if ($s_word != '') {
        // Search
        $msg  = str_replace('$1', $s_word, $qm->m['plg_search']['title_result']);
        $body = plugin_search2_do_search($vars['word'], $type, FALSE, $base);
    } else {
        // Init
        unset($vars['word']);
        $msg  = $qm->m['plg_search']['title_search'];
        $body = '<br />' . "\n" . $qm->m['plg_search']['note'] . "\n";
    }

    // Show search form
    $bases = ($base == '') ? array() : array($base);
    $body .= plugin_search2_form($s_word, $type, $bases);

    return array('msg'=>$msg, 'body'=>$body);
}

function plugin_search2_form($s_word = '', $type = '', $bases = array())
{
    global $script;
    $qm = get_qm();
    $qt = get_qt();

    $cols = 12;
    $offset = 0;
    $show_type_selector = false;
    $btn_type = 'default';
    $form_type = '';
    foreach ($bases as $base)
    {
        $base = trim($base);
        switch ($base)
        {
            case 'showtype':
                $show_type_selector = true;
                break;
            case preg_match('/^(\d+)(?:\+(\d+))?$/', $base, $mts) ? true : false:
                $cols = $mts[1];
                $offset = isset($mts[2]) && $mts[2] ? $mts[2] : $offset;
                break;
            case 'primary':
            case 'success':
            case 'info':
            case 'warning':
            case 'danger':
            case 'default':
                $btn_type = $base;
                break;
            case 'compact':
                $form_type = $base;
                break;
        }
    }

    $width_class = 'col-sm-' . $cols;
    if ($offset)
    {
        $width_class .= ' col-sm-offset-' . $offset;
    }

    $and_check = $or_check = '';
    if ($type == 'OR') {
        $or_check  = ' checked="checked"';
    } else {
        $and_check = ' checked="checked"';
    }

    $type_selector_html = '<input type="hidden" name="type" value="AND" />';
    if ($show_type_selector)
    {
        $type_selector_html = <<< EOD
<div class="form-group">
  <label class="radio-inline" style="display:inline-block;">
    <input type="radio" name="type" value="AND" $and_check /> {$qm->m['plg_search']['lbl_and']}
  </label>
  <label class="radio-inline" style="display:inline-block;">
    <input type="radio" name="type" value="OR"  $or_check  /> {$qm->m['plg_search']['lbl_or']}
  </label>
</div>
EOD;
    }

    if ($form_type === 'compact')
    {
        $html ='
<form action="'.$script.'" method="get" class="qhm-search2 form-inline" data-plugin="search2">
  <input type="hidden" name="cmd" value="search2" />
  <input type="hidden" name="type" value="AND" />
  <div class="form-group">
    <i class="fas fa-search"></i>
    <input type="text"  name="word" value="'.h($s_word).'" class="form-control" placeholder="検索ワード" />
  </div>
</form>
';
    }
    else
    {
        $html = '
<form action="'.$script.'" method="get" class="qhm-search2 form-inline" data-plugin="search2">
  <input type="hidden" name="cmd" value="search2" />
  <div class="input-group '.$width_class.'">
    <i class="fas fa-search"></i>
    <input type="text"  name="word" value="'.h($s_word).'" class="form-control" placeholder="検索ワード" />
    <div class="input-group-btn">
      <input class="btn btn-'.$btn_type.'" type="submit" value="検索" />
    </div>
  </div>
  '.$type_selector_html.'
</form>
';
    }

    $style = '';
    if (exist_plugin('icon'))
    {
        plugin_icon_set_font_awesome();
        $style = <<< HTML
<style>
[data-plugin=search2] > .input-group,
[data-plugin=search2] > .form-group {
  position: relative;
}
[data-plugin=search2] > .form-group > .svg-inline--fa {
  position: absolute;
  top: 10px;
  left: 9px;
  color: #999;
}
[data-plugin=search2] > .input-group > .svg-inline--fa {
    position: absolute;
    top: 13px;
    left: 9px;
    color: #999;
    z-index: 10;
}
[data-plugin=search2] input[type="text"] {
  padding-left: 30px;
}
[data-plugin=search2] input[type="text"]:-ms-input-placeholder {
  line-height: 24px;
}
</style>
HTML;
    }

    $qt->appendv_once("plugin_search2_style", "beforescript", $style);


    return $html;

}

// 'Search' main function
function plugin_search2_do_search($word, $type = 'AND', $non_format = FALSE, $base = '')
{
    global $script, $whatsnew, $non_list, $search_non_list, $foot_explain;
    global $search_auth, $show_passage, $username, $vars;
    $qm = get_qm();

    $retval = array();

    $b_type = ($type == 'AND'); // AND:TRUE OR:FALSE
    mb_language('Japanese');
    $word = mb_convert_encoding($word,SOURCE_ENCODING,"UTF-8,EUC-JP,SJIS,ASCII,JIS");
    $word = mb_ereg_replace("　", " ", $word);
    $keys = get_search_words(preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));
    foreach ($keys as $key=>$value)
        $keys[$key] = '/' . $value . '/S';

    $pages = get_existpages();

    // Avoid
    if ($base != '') {
        $pages = preg_grep('/^' . preg_quote($base, '/') . '/S', $pages);
    }
    if (! $search_non_list) {
        $pages = array_diff($pages, preg_grep('/' . $non_list . '/S', $pages));
    }
    $pages = array_flip($pages);
    unset($pages[$whatsnew]);

    $count = count($pages);
    // Search for page contents
    global $ignore_plugin, $strip_plugin, $strip_plugin_inline;

    $titles = array();
    $head10s = array();

    // 一時的に認証を外す
    $user_name = null;
    if (isset($_SESSION['usr']))
    {
        $user_name = $_SESSION['usr'];
        unset($_SESSION['usr']);
    }

    foreach (array_keys($pages) as $page) {
        $vars['page'] = $page;
        $b_match = FALSE;

        // Search auth for page contents
        if ( ! check_readable($page, false, false, TRUE)) {
            unset($pages[$page]);
            continue;
        }

        $lines = get_source($page, TRUE, FALSE);

        //--- 検索専用のデータの作成、更新 ---
        $srh_fname = CACHE_DIR . encode($page).'_search.txt';

        if ( ! file_exists($srh_fname) ||
            ( filemtime($srh_fname) < filemtime(get_filename($page))))
        {
            $p_title = $page;
            $p_heads = '';
            foreach($lines as $k => $l)
            {
                if (preg_match($ignore_plugin, $l))
                {// 省く
                    $lines = array();
                    break;
                }
                if(preg_match($strip_plugin, $l, $ms))
                {// 省く
                    unset($lines[$k]);
                }
                if (preg_match('/^TITLE:(.*)/', $l, $ms))
                {
                    $p_title = trim($ms[1]);
                    if ($p_title !== $page)
                    {
                        $p_title = $p_title.' '.$page;
                    }
                    unset($lines[$k]);
                }
                if (preg_match('/^(?:!|(\*){1,3})(.*)\[#\w+\]\s?/', $l, $ms))
                {
                    $p_heads .=  trim($ms[2]).' ';
                    unset($lines[$k]);
                }
            }

            $lines = preg_replace($strip_plugin_inline, '', $lines); // 省く
            $html = convert_html($lines);
            $html = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/i', '', $html);
            $html = preg_replace('/<img\b[^>]*alt="(.*?)"[^>]*>/i', '\1', $html);
            $p_body = trim(strip_tags($html));
            foreach ($foot_explain as $id => $note)
            {
                $p_body .= "\n" . strip_tags($note);
            }
            $foot_explain = array();

            $p_body = (count($lines) > 0) ? $p_title."\n".$p_heads."\n".$p_body : '';
            file_put_contents($srh_fname, $p_body);
        }
        else
        {
            $fp = fopen($srh_fname, "r");
            flock($fp, LOCK_SH);
            $lines = file($srh_fname);
            flock($fp, LOCK_UN);
            fclose($fp);

            $p_title = trim($lines[0]);
            unset($lines[0]);

            $p_heads = trim($lines[1]);
            unset($lines[1]);

            $p_body = implode('', $lines);
        }

        //////////////////////////////////////////////
        //
        //  検索スタート！
        //
        ///////////////////////////////////////////////
        $match_title = 0;
        $match_heads = 0;
        $match_body = 0;

        //--- ページタイトル検索 ---
        $point = 0; $ok = false;
        if ( ! $non_format) {

            foreach ($keys as $key) {
                $b_match = preg_match($key, $p_title);
                if( ! $b_match){
                    $ok = false; break;
                }
                else{
                    $ok = true;    $point += 15;
                }
            }
            if($ok){ $match_title = $point; }
        }

        //--- ヘッダー検索 ---
        $point = 0; $ok = false;
        foreach ($keys as $key) {
            $b_match = preg_match_all($key, $p_title, $ms);
            if(!$b_match){
                $ok = false; break;
            }
            else{
                $ok = true;    $point += 10;
            }
        }
        if($ok){ $match_heads = $point; }

        //--- コンテンツ検索 ---
        foreach ($keys as $key) {
            $b_match = preg_match_all($key, $p_body, $ms);
            if(!$b_match){
                $ok = false; break;
            }
            else{
                $ok = true;    $point += count($ms[0]);
            }
        }
        if($ok){ $match_body = $point; }

        //検索結果
        $total = $match_title + $match_heads + $match_body;

        if ($total == 0)
        {
            unset($pages[$page]); // Miss
        }
        else
        {
            $pages[$page] = $total;
            $titles[$page] = $p_title;
            $head10s[$page] = mb_substr($p_body, 0 , 60*3);
        }
    }

    if ($user_name !== null)
    {
        $_SESSION['usr'] = $user_name;
    }

    $vars['page'] = '';

    //注釈の削除
    $foot_explain = array();

    if ($non_format) return array_keys($pages);

    $r_word = rawurlencode($word);
    $s_word = h($word);
    if (empty($pages))
    {
        return str_replace('$1', $s_word, '$1 を含むページは見つかりませんでした。');
    }

    arsort($pages);

    $retval = '<div class="container-fluid"><div class="list-group">' . "\n";
    foreach ($pages as $page=>$v)
    {
        $title  = $titles[$page];
        if ($title !== $page)
        {
            $rpos = strrpos($title, $page);
            if ($rpos !== FALSE)
            {
                $title = trim(substr($title, 0, $rpos));
            }
            $title = $title . ' - ' . $page;
        }
        $r_page  = rawurlencode($page);

        $tmp_li = '  <div class="list-group-item" style="border-style:none;"><a class="list-group-item-heading" href="' . $script . '?cmd=read&amp;page=' .
        $r_page . '&amp;word=' . $r_word . '" style="font-weight:bold;">' .h($title).
            '</a><p class="list-group-item-text text-muted" style="margin: 5px 0;">'.$head10s[$page].'</p></div>' . "\n";

        $retval .= $tmp_li;
    }
    $retval .= '</div><p>' . "\n";
    $retval .= str_replace('$1', $s_word, str_replace('$2', count($pages),
        str_replace('$3', $count, $b_type ? '$1 のすべてを含むページは <strong>$3</strong> ページ中、 <strong>$2</strong> ページ見つかりました。' : '$1 のいずれかを含むページは <strong>$3</strong> ページ中、 <strong>$2</strong> ページ見つかりました。')));
    $retval .= '</p></div>';

    return $retval;
}
