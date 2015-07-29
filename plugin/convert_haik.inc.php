<?php
/**
 *   convert haik to qhm
 *   -------------------------------------------
 *   convert_haik.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/06/17
 *   modified :
 *
 *   Description
 *   
 *   
 *   Usage :
 *   
 */
function plugin_convert_haik_init()
{
    if ( ! exist_plugin('qhmsetting'))
    {
        die("error: There is no qhmsetting.");
    }
}
 
function plugin_convert_haik_action()
{
    global $vars, $script;

    if ( ! ss_admin_check())
    {
        redirect($script, '管理者以外利用できません。');
    }
    if ( ! file_exists('haik-contents') OR ! is_dir('haik-contents'))
    {
        redirect($script, 'この機能はご利用いただけません。');
    }

    //確認画面
    if ( ! isset($vars['adminpass']) OR ! pkwk_login($vars['adminpass']))
    {

        $msg = 'haik データ移行';
        $info = plugin_convert_haik_get_info();
        $warning = plugin_convert_haik_get_warning();
        $danger = '';
        if (isset($vars['adminpass']))
        {
            $danger = <<< EOD
<div class="alert alert-danger">
  管理者パスワードが正しくありません。
</div>
EOD;
        }
        $body = <<< EOD
<h2>{$msg}</h2>
<p>
  haik のデータを QHM で動作するように変換します。<br>
  実行すると、<strong>現在のデータに対して</strong>上書きされます。
  よろしければ、<strong>開始</strong>ボタンを押して実行してください。
</p>
{$warning}
{$danger}
<form action="{$script}?cmd=convert_haik" method="post" class="form-inline">
  <div class="form-group">
    <label>管理者パスワード</label>
    <input type="password" name="adminpass" class="form-control">
  </div>
  <button type="submit" class="qhm-btn qhm-btn-primary">開始</button>
</form>
<hr>
<h3>移行情報</h3>
{$info}
EOD;
        return array('msg' => $msg, 'body' => $body);
    }

    // タイムスタンプを記録する
    plugin_convert_haik_write_log(date('Y-m-d H:i:s') . ' haik からのデータ移行開始' . "\n");

    //1．haik-contents/config/haik.ini.php を適宜 qhm.ini.php へ移植
    plugin_convert_haik_move_inifile();

    //2．haik-contents/upload/* を swfu/d/ へ移動し、ファイルチェックを行う
    plugin_convert_haik_move_uploadfile();

    //3．haik-contents/wiki/*.txt を wiki/ へコピーする
    plugin_convert_haik_move_wiki();
    
    //4．haik と qhm で名前が異なるプラグインを変換する
    plugin_convert_haik_replace_plugin();
    
    //5．haik-contents/meta/*.php を解釈して書式をソースへ追加する
    plugin_convert_haik_set_meta();

    plugin_convert_haik_write_log('');

    $log_text = file_get_contents(CACHE_DIR . 'convert_haik.log');
    $url = dirname($script."dummy"). '/swfu/check.php';
    $body = <<< EOD
<h2>移行が完了しました</h2>
<p>
  <a href="{$script}" class="qhm-btn qhm-btn-info">トップへ戻る</a>
</p>

<div class="alert alert-warning">
  haik と QHM で対応するプラグインが無い場合、変換が行われていないため、手動での削除、修正をお願いいたします。
  <pre>* download プラグイン
* mc_form プラグイン
* form プラグイン
* goo_gl プラグイン
* scrollup プラグイン</pre>
</div>

<div class="alert alert-warning">
  icon プラグインは IcoMoon から <a href="http://getbootstrap.com/components/#glyphicons" title="Bootstrap glyphicons" target="_blank">glyphicon</a>, <a href="http://fortawesome.github.io/Font-Awesome/cheatsheet/" title="FontAwesome Cheatsheat" target="_blank">font-awesome</a> に変更されました。<br>
  指定したアイコン名によっては表示されなくなる場合があります。
</div>

<hr>

<h3>移行ログ</h3>

<div style="height:300px;overflow-y:scroll">
  <pre>{$log_text}</pre>
</div>

EOD;
    $body .= '<iframe src="'.$url.'" width="0" height="0"></iframe>';
    return array('msg' => 'complete', 'body' => $body);
}

function plugin_convert_haik_move_inifile()
{
    if ( ! file_exists('haik-contents/config/haik.ini.php'))
    {
        return false;
    }
    
    include('haik-contents/config/haik.ini.php');
    
    $_SESSION['qhmsetting'] = array();
    foreach ($config as $key => $val)
    {
        switch($key)
        {
            case 'display_login':
                $_SESSION['qhmsetting']['qhm_adminmenu'] = $val;
                break;
            case 'site_title': 
                $_SESSION['qhmsetting']['page_title'] = $val;
                break;
            case 'user_head':
                $_SESSION['qhmsetting']['custom_meta'] = $val;
                break;
            case 'tracking_script':
                $_SESSION['qhmsetting']['accesstag'] = $val;
                break;
            case 'username': 
                $_SESSION['qhmsetting']['admin_email'] = $_SESSION['qhmsetting']['username'] = $val;
                break;
            case 'logo_image':
            case 'passwd': 
            case 'script':
            case 'script_ssl':
            case 'enable_cache':
            case 'autolink':
            case 'session_save_path':
            case 'site_close_all':
            case 'check_login':
            case 'unload_confirm':
            case 'http_scheme':
            case 'encrypt_ftp':
            case 'qhm_pw_str':
            case 'ogp_tag':
            case 'add_xmlns':
                $_SESSION['qhmsetting'][$key] = $val;
                break;
        }
    }
    
    plugin_qhmsetting_update_ini();
    plugin_convert_haik_write_log("サイトの設定を移行しました");

    return true;
}

function plugin_convert_haik_move_uploadfile()
{
    global $script;

    $src = 'haik-contents/upload';
    $dst = 'swfu/d';

    $dir = opendir($src); 
    while (false !== ( $file = readdir($dir)))
    {
        if (strpos($file, '.') !== 0)
        {
            if ( ! is_dir($src.'/'.$file))
            { 
                copy($src.'/'.$file, $dst.'/'.$file); 
            }
        }
    }
    closedir($dir);

    plugin_convert_haik_write_log("アップロードファイルを移行しました");
    return true;
}

function plugin_convert_haik_move_wiki()
{
    $src = 'haik-contents/wiki';
    $dst = DATA_DIR;
    
    $footer_file = encode('SiteFooter').'.txt';
    $footer_dstfile = encode('SiteNavigator2'). '.txt';

    $dir = opendir($src);
    while (false !== ( $file = readdir($dir)))
    {
        if (strpos($file, '.') !== 0)
        {
            if ( ! is_dir($src.'/'.$file))
            {
                if ($file === $footer_file)
                {
                    copy($src.'/'.$file, $dst.'/'.$footer_dstfile); 
                }
                else
                {
                    copy($src.'/'.$file, $dst.'/'.$file); 
                }
            
            }
        }
    }
    closedir($dir);
    
    return true;
}

function plugin_convert_haik_replace_plugin()
{
    $pages = get_existpages();

    foreach ($pages as $page)
    {
        $pagefile = get_filename($page);

        $src = get_source($page, TRUE, TRUE);
        $src = plugin_convert_haik_replace_source($src);
        
        file_put_contents($pagefile, $src, LOCK_EX);
        plugin_convert_haik_write_log("[{$page}] プラグインの移行をしました");
    }

    return true;
}

function plugin_convert_haik_replace_source($src)
{
    //accordion → bs_accordion
    $src = preg_replace('/^#accordion/m', '#bs_accordion', $src);

    //alert → box(alert,xxx)
    $src = preg_replace('/^#alert(?:\((.*)\))?/m', '#bs_box(alert,$1)', $src);

    //body_first → 囲みを消す
    $src = preg_replace('/^#body_first/m', '#html', $src);

    //body_last → lastscript
    $src = preg_replace('/^#body_last/m', '#lastscript', $src);

    //body_last → lastscript
    $src = preg_replace('/^#gmap/m', '#gmapfun', $src);

    //box → bs_box
    $src = preg_replace('/^#box/m', '#bs_box', $src);

    //openwin → otherwin
    $src = preg_replace('/&openwin/', '&otherwin', $src);

    //h1 → !
    $src = preg_replace('/^&h1{(.*)};$/m', '! $1', $src);

    //set_design → 消す
    //set_skin → 消す
    //set_template → 消す
    $src = preg_replace('/^#set_(design|skin|template)\(.*\)$/m', '', $src);

    //slide → bs_carousel
    $src = preg_replace('/^#slide/m', '#bs_carousel', $src);

    //tabs → bs_tabs
    $src = preg_replace('/^#tabs/m', '#bs_tabs', $src);

    //user_head → beforescript
    //user_script → beforescript
    $src = preg_replace('/^#user_(head|script)/m', '#beforescript', $src);

    //menu_stacked → リスト化
    $lines = explode("\n", $src);
    $start = $close = false;
    foreach ($lines as $i => $line)
    {
        if (preg_match('/^#menu_stacked(\{{2,})/', $line, $matches))
        {
            $start = true;
            $close = str_repeat('}', strlen($matches[1]));
            unset($lines[$i]);
        }
        
        if ($start && $line === $close)
        {
            unset($lines[$i]);
            $start = $close = false;
        }
    }
    $src = join("\n", $lines);

    return $src;    
    
}

function plugin_convert_haik_set_meta()
{
    $pages = get_existpages();

    foreach ($pages as $page)
    {
        $data = array();
        $title = '';
        
        $pagefile = get_filename($page);
        
        $metafile = 'haik-contents/meta/'.encode($page).'.php';
        include($metafile);
        
        foreach ($meta as $key => $val)
        {
            switch ($key)
            {
                case 'title':
                    $title = 'TITLE:'.$val;
                    break;
                case 'description':
                case 'keywords':
                    $data[$key] = '#'.$key.'('.$val.')';
                    break;

                case 'user_head':
                    $data[$key] = "#beforescript{{\n{$val}\n}}\n";
                    break;
            }
            
        }

        switch ($meta['close'])
        {
            case 'closed':
                $data[$key] = "#close";
                break;
            case 'password':
                $data[$key] = "#secret({$meta['password']})";
                break;
            case 'redirect':
                $status = $meta['redirect_status'] == '301' ?  ',301' : '';
                $data[$key] = "#redirect({$meta['redirect']}{$status})";
                break;
        }
        
        array_unshift($data, $title);
        $src = join("\n", $data). "\n\n";
        $src .= get_source($page, TRUE, TRUE);
        
        file_put_contents($pagefile, $src, LOCK_EX);
        plugin_convert_haik_write_log("[{$page}]ページ情報の移行をしました");
    }
    
    return true;
}

function plugin_convert_haik_write_log($msg = '')
{
    $logfile = CACHE_DIR.'convert_haik.log';
    $fp = fopen($logfile, 'a');
    if ($fp)
    {
        fwrite($fp, $msg. "\n");
    }
    fclose($fp);
}

function plugin_convert_haik_get_info()
{
    include('haik-contents/config/haik.ini.php');
    $site_title = $config['site_title'];
    $username = $config['username'];

    $file_cnt = 0;
    $upload_dir = 'haik-contents/upload';
    $dir = opendir($upload_dir);
    while ($entry = readdir($dir))
    {
        if ( ! is_dir($upload_dir . '/' . $entry))
        {
            $file_cnt++;
        }
    }

    $page_cnt = 0;
    $data_dir = 'haik-contents/meta';
    $dir = opendir($data_dir);
    while ($entry = readdir($dir))
    {
        if (preg_match('/\.php$/', $entry))
        {
            $page_cnt++;
        }
    }

    return <<< EOD
<dl>
  <dt>サイト名</dt>
  <dd>{$site_title}</dd>
  <dt>管理者ユーザー名</dt>
  <dd>{$username}</dd>
</dl>
EOD;
}

function plugin_convert_haik_get_warning()
{
    $log_file = CACHE_DIR . 'convert_haik.log';
    if ( ! file_exists($log_file)) return '';

    $last_modified = date('Y年m月d日', filemtime($log_file));
    $log_text = file_get_contents($log_file);

    return <<< EOD
<div class="alert alert-warning">
  既に {$last_modified} に実行済みです。<br>
  その後に編集している場合、もう一度実行すると昔のデータで上書きされます。<br>
  <a href="#convert_log" data-toggle="collapse" class=""><i class="glyphicon glyphicon-show"></i> 以前のログを見る</a>
</div>
<div id="convert_log" class="collapse">
  <pre>{$log_text}</pre>
</div>

EOD;
}
