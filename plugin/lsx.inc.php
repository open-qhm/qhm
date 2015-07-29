<?php
/**
 * Page List (ls) Plugin
 *
 * @author     sonots <http://note.sonots.com>
 * @license    http://www.gnu.org/licenses/gpl.html    GPL
 * @link       http://note.sonots.com/?PukiWiki/lsx.inc.php
 * @version    $Id$
 * @package    lsx.inc.php
 */

class PluginLsx
{
    function PluginLsx()
    {
        // Configure external plugins
        static $conf = array(
            'plugin_contents' => 'contentsx',
            'plugin_include'  => 'includex',
            'plugin_new'      => 'new',
            'plugin_tag'      => 'tag',
        );
        // Modify here for default option values
        static $default_options = array(
            'hierarchy' => array('bool', true),
            'non_list'  => array('bool', true),
            'reverse'   => array('bool', false), 
            'basename'  => array('bool', false), // obsolete
            'sort'      => array('enum', 'name', array('name', 'reading', 'date')),
            'tree'      => array('enum', false, array(false, 'leaf', 'dir')),
            'depth'     => array('number', ''),
            'num'       => array('number', ''),
            'next'      => array('bool', false),
            'except'    => array('string', ''),
            'filter'    => array('string', ''),
            'prefix'    => array('string', ''),
            'contents'  => array('array', ''),
            'include'   => array('array', ''),
            'info'      => array('enumarray', array(), array('date', 'new')),
            'date'      => array('bool', false), // will be obsolete
            'new'       => array('bool', false),
            'tag'       => array('string', ''),
            'linkstr'   => array('enum', 'relative', array('relative', 'absolute', 'basename', 'title', 'headline')),
            'link'      => array('enum', 'page', array('page', 'anchor', 'off')),
            'newpage'   => array('enum', false, array('on', 'except')),
            'popular'   => array('enum', false, array('total', 'today', 'yesterday', 'recent')), // alpha
        );
        $this->conf            = &$conf;
        $this->default_options = &$default_options;

        // init
        $this->options = $this->default_options;
        if (function_exists('mb_ereg')) { // extension_loaded('mbstring')
            mb_regex_encoding(SOURCE_ENCODING);
            $this->ereg = 'mb_ereg';
        } else {
            $this->ereg = 'ereg';
        }
    }
    
    // static
    var $conf;
    var $default_options;
    // var
    var $options;
    var $error = "";
    var $plugin = "lsx";
    var $metapages;

    function convert()
    {
        $args = func_get_args();
        $body = $this->body($args);
        if ($this->error != "") {
            $body = "<p>$this->plugin(): $this->error</p>";
        }
        return $body;
    }

    function action()
    {
        global $vars;
        $qm = get_qm();

        $args = $vars;
        $body = $this->body($args);
        if ($this->error != "") {
            $body = "<p>$this->plugin(): $this->error</p>";
        }
        if (! isset($body)) $body = $qm->m['plg_lsx']['err_no_result'];

        if ($this->options['tag'][1] != '') {
            $msg = htmlspecialchars($this->options['tag'][1]);
        } elseif ($this->options['prefix'][1] != '') {
            $msg = htmlspecialchars($this->options['prefix'][1]);
        } else {
            $msg = $this->plugin;
        }
        return array('msg'=>$msg, 'body'=>$body);
    }

    function body($args)
    {
        $parser = new PluginLsxOptionParser();
        $this->options = $parser->parse_options($args, $this->options);
        if ($parser->error != "") { $this->error = $parser->error; return; }

        $this->validate_options();
        if ($this->error !== "") { return $this->error; }
        
        $this->init_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->prefix_filter_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->nonlist_filter_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->relative_metapages(); // before filter, except
        if ($this->error !== "") { return $this->error; }
        $this->filter_filter_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->except_filter_metapages();
        if ($this->error !== "") { return $this->error; }

        $this->newpage_filter_metapages();
        if ($this->error !== "") { return $this->error; }

        $parser = new PluginLsxOptionParser();
        $this->maxdepth = $this->depth_metapages();
        $this->options['depth'][1] = $parser->parse_numoption($this->options['depth'][1], 1, $this->maxdepth);
        if ($parser->error != "") { $this->error = $parser->error; return; }
        $this->depth_filter_metapages();
        if ($this->error !== "") { return $this->error; }

        $this->tree_filter_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->popular_metapages(); // before sort
        if ($this->error !== "") { return $this->error; }
        $this->timestamp_metapages(); // before sort
        if ($this->error !== "") { return $this->error; }
        $this->sort_metapages(); // before num_filter
        if ($this->error !== "") { return $this->error; }

        $this->maxnum = sizeof($this->metapages); // after all filters
        $this->options['num'][1] = $parser->parse_numoption($this->options['num'][1], 1, $this->maxnum);
        if ($parser->error != "") { $this->error = $parser->error; return; }
        $this->num_filter_metapages();
        if ($this->error !== "") { return $this->error; }

        $this->hierarchy_metapages();
        if ($this->error !== "") { return $this->error; }

        $this->info_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->linkstr_metapages();
        if ($this->error !== "") { return $this->error; }
        $this->link_metapages();
        if ($this->error !== "") { return $this->error; }

        $body = $this->list_pages();
        $body .= $this->next_pages();

        return $body;
    }
    
    function validate_options()
    {
        global $vars;
        $qm = get_qm();
        if ($this->options['tag'][1] != '') {
            if(! exist_plugin($this->conf['plugin_tag'])) {
                $this->error .= $qm->replace('plg_lsx.err_plg', 'tag', $this->conf['plugin_tag']);
                return;
            }
            $this->options['hierarchy'][1] = false;
            // best is to turn off the default only so that 'hierarchy' can be configured by option. 
        } else {
            if ($this->options['prefix'][1] == '') {
                $this->options['prefix'][1] = $vars['page'] != '' ? $vars['page'] . '/' : '';
            }
        }
        if ($this->options['prefix'][1] == '/') {
            $this->options['prefix'][1] = '';
        } elseif ($this->options['prefix'][1] != '') {
            $this->options['prefix'][1] = $this->get_fullname($this->options['prefix'][1], $vars['page']);
        }
        $this->options['prefix'][4] = $this->options['prefix'][1];

        if ($this->options['sort'][1] == 'date') {
            $this->options['hierarchy'][1] = false;
        }

        // alpha func
        if ($this->options['popular'][1] != false) {
            $this->options['sort'][1] = 'popular';
            $this->options['hierarchy'][1] = false;
            // Future Work: info_popular. hmmm
        }
        // Another Idea
        // sort=popular>today,popular>total,popular>yesterday,popular>recent
        // if (strpos($this->options['sort'][1], 'popular>') !== false) { 
        //     list($this->optiions['sort'][1], $this->options['popular'][1]) = explode('>', $this->options['sort'][1]);
        //     $this->options['hierarchy'][1] = false;
        // }

        if ($this->options['contents'][1] != '') {
            if(! exist_plugin_convert($this->conf['plugin_contents'])) {
                $this->error .= $qm->replace('plg_lsx.err_plg', 'contents', $this->conf['plugin_contents']);
                return;
            }
        }

        if ($this->options['include'][1] != '') {
            if(! exist_plugin_convert($this->conf['plugin_include'])) {
                $this->error .= $qm->replace('plg_lsx.err_plg', 'include', $this->conf['plugin_include']);
                return;
            }
            $this->options['hierarchy'][1] = false; // hierarchy + include => XHTML invalid
            $this->options['date'][1] = false;      // include does not use definitely
            $this->options['new'][1]  = false;      // include does not use definitely
            $this->options['contents'][1] = '';     // include does not use definitely
        }

        if ($this->options['linkstr'][1] === 'title' || $this->options['linkstr'][1] === 'headline') {
            if(! exist_plugin_convert($this->conf['plugin_contents'])) {
                $this->error .= $qm->replace('plg_lsx.err_plg', 'linkstr', $this->conf['plugin_contents']);
                return;
            }
        }

        // to support lower versions
        // basename -> linkstr
        if ($this->options['basename'][1] === true) {
            $this->options['linkstr'][1] = 'basename'; 
        }

        // new,date -> info
        foreach ($this->options['info'][2] as $key) {
            if ($this->options[$key][1]) {
                array_push($this->options['info'][1], $key);
            }
        }
        $this->options['info'][1] = array_unique($this->options['info'][1]);
        // to save time (to avoid in_array everytime)
        foreach ($this->options['info'][1] as $key) {
            $this->options[$key][1] = true;
        }
        if ($this->options['new'][1] && ! exist_plugin_inline($this->conf['plugin_new'])) {
            $this->error .= $qm->replace('plg_lsx.err_plg', 'new', $this->conf['plugin_new']);
            return;
        }
    }

    function next_pages()
    {
        if (! $this->options['next'][1] || $this->options['num'][1] == '') return;
        $qm = get_qm();

        $options = $this->options;
        unset($options['num']);
        $href = get_script_uri() . '?' . 'cmd=lsx';
        foreach ($options as $key => $val) {
            if (isset($val[4])) {
                $href .= '&amp;' . htmlspecialchars($key) . '=' . htmlspecialchars(rawurlencode($val[4]));
            }
        }
        $count = count($this->options['num'][1]);
        $min   = reset($this->options['num'][1]);
        $max   = end($this->options['num'][1]);
        $maxnum = $this->maxnum;
        $prevmin = max($min - $count, 0);
        $prevmax = min($min - 1, $maxnum);
        $prevlink = '';
        if ($prevmax > 0) {
            $prevhref = $href . '&amp;num=' . $prevmin . ':' . $prevmax;
            $prevlink = '<span class="prev" style="float:left;"><a href="' . $prevhref . '">' . $qm->m['plg_lsx']['prev'] . ' '.  $count . '</a></span>';
        }
        $nextmin = max($max + 1, 0);
        $nextmax = min($max + $count, $maxnum);
        $nextlink = '';
        if ($nextmin < $maxnum) {
            $nexthref = $href . '&amp;num=' . $nextmin . ':' . $nextmax;
            $nextlink = '<span class="next" style="float:right;"><a href="' . $nexthref . '">' . $qm->m['plg_lsx']['next'] . ' ' . $count . '</a></span>';
        }
        $ret = '';
        $ret .= '<div class="lsx">' . $prevlink . $nextlink . '</div><div style="clear:both;"></div>';
        return $ret;
    }

    function list_pages()
    {
        global $script;

        if (sizeof($this->metapages) == 0) {
            return;
        }
        
        /* HTML validate (without <ul><li style="list-type:none"><ul><li>, we have to do as 
           <ul><li style="padding-left:16*2px;margin-left:16*2px"> as pukiwiki standard. I did not like it)
           
        <ul>              <ul><li>1
        <li>1</li>        </li><li>1
        <li>1             <ul><li>2
        <ul>              </li></ul></li><li>1
        <li>2</li>        </li><li>1
        </ul>        =>   <ul><li style="list-type:none"><ul><li>3
        </li>             </li></ul></li></ul></li></ul>
        <li>1</li>
        <li>1</li>
        <ul><li style="list-type:none"><ul>
        <li>3</li>
        </ul></li></ul>
        </li>
        </ul>
        */
        $ul = $pdepth = 0;
        $html = '';
        foreach ($this->metapages as $i => $metapage) {
            $page     = isset($metapage['page'])? $metapage['page']: '';
            $exist    = isset($metapage['exist'])? $metapage['exist']: '';
            $depth    = isset($metapage['listdepth'])? $metapage['listdepth']: '';
            $info     = isset($metapage['info'])? $metapage['info']: '';
            $link     = isset($metapage['link'])? $metapage['link']: '';
            if ($exist && $this->options['include'][1] != '') {
                $option = '"' . $page . '"';
                if (! empty($this->options['include'][1])) {
                    $option .= ',' . csv_implode(',', $this->options['include'][1]);
                }
                $html .= do_plugin_convert($this->conf['plugin_include'], $option);
                continue;
            }
            if ($depth > $pdepth) {
                $diff = $depth - $pdepth;
                $html .= str_repeat('<ul><li style="list-style:none">', $diff - 1);
                if ($depth == 1) { // or $first flag
                    $html .= '<ul class="' . $this->plugin . '"><li>';
                } else {
                    $html .= '<ul><li>';
                }
                $ul += $diff;
            } elseif ($depth == $pdepth) {
                $html .= '</li><li>';
            } elseif ($depth < $pdepth) {
                $diff = $pdepth - $depth;
                $html .= str_repeat('</li></ul>', $diff);
                $html .= '</li><li>';
                $ul -= $diff;
            }
            $pdepth = $depth;

            $html .= $link;
            if (isset($info) && $info != '') {
                $html .= '<span class="lsx_info">' . $info . '</span>' . "\n";
            }
            
            if ($exist && $this->options['contents'][1] != '') {
                $args = $this->options['contents'][1];
                $pagearg = 'page=' . $page ;
                array_unshift($args, $pagearg);
                $contentsx = new PluginContentsx();
                $html .= call_user_func(array($contentsx, 'body'), $args);
            }
        }
        $html .= str_repeat('</li></ul>', $ul);
        return $html;
    }

    function link_metapages()
    {
        switch ($this->options['link'][1]) {
        case 'page':
            foreach ($this->metapages as $i => $metapage) {
                if ($metapage['exist']) {
                    $this->metapages[$i]['link'] = 
                        $this->make_pagelink($metapage['page'], $metapage['linkstr']);
                } else {
                    $this->metapages[$i]['link'] = $metapage['linkstr'];
                }
            }
            break;
        case 'anchor':
            foreach ($this->metapages as $i => $metapage) {
                // PluginIncludex::get_page_anchor($metapage['page'])
                $anchor = 'z' . md5($metapage['page']);
                $anchor = '#' . htmlspecialchars($anchor);
                if ($metapage['exist']) {
                    $this->metapages[$i]['link'] = 
                        $this->make_pagelink('', $metapage['linkstr'], $anchor);
                } else {
                    $this->metapages[$i]['link'] = $metapage['linkstr'];
                }
            }
            break;
        case 'off':
            foreach ($this->metapages as $i => $metapage) {
                $this->metapages[$i]['link'] = $metapage['linkstr'];
            }
            break;
        }
    }

    function linkstr_metapages()
    {
        switch ($this->options['linkstr'][1]) {
        case 'absolute':
            foreach ($this->metapages as $i => $metapage) {
                $this->metapages[$i]['linkstr'] = 
                    htmlspecialchars($metapage['page']);
            }
            break;
        case 'basename':
            foreach ($this->metapages as $i => $metapage) {
                $this->metapages[$i]['linkstr'] = 
                    htmlspecialchars($this->my_basename($metapage['page']));
            }
            break;
        case 'title':
            $contentsx = new PluginContentsx();
            foreach ($this->metapages as $i => $metapage) {
                $title = $contentsx->get_title($metapage['page']);
                $title = strip_htmltag(make_link($title));
                $this->metapages[$i]['linkstr'] = $title;
            }
            break;
        case 'headline':
            $contentsx = new PluginContentsx();
            foreach ($this->metapages as $i => $metapage) {
                $metalines = $contentsx->get_metalines($metapage['page']);
                $title =  $metalines[0]['headline'];
                $title = strip_htmltag(make_link($title));
                $this->metapages[$i]['linkstr'] = $title;
            }
            break;
        }
        // default: relative
        if ($this->options['hierarchy'][1] === true) {
            foreach ($this->metapages as $i => $metapage) {
                if (! isset($metapage['linkstr']) || $metapage['linkstr'] === '') {
                    $this->metapages[$i]['linkstr'] = 
                        htmlspecialchars($this->my_basename($metapage['page']));
                }
            }
        } else {
            foreach ($this->metapages as $i => $metapage) {
                if (! isset($metapage['linkstr']) || $metapage['linkstr'] === '') {
                    $this->metapages[$i]['linkstr'] = 
                        htmlspecialchars($metapage['relative']);
                }
            }
        }
    }

    function popular_metapages()
    {
        if ($this->options['popular'][1] === false) {
            return;
        }

        if (function_exists('set_timezone')) { // plus
            list($zone, $zonetime) = set_timezone(DEFAULT_LANG);
            $localtime = UTIME + $zonetime;
            $today = gmdate('Y/m/d', $localtime);
            $yesterday = gmdate('Y/m/d',gmmktime(0,0,0, gmdate('m',$localtime), gmdate('d',$localtime)-1, gmdate('Y',$localtime)));
        } else {
            $localtime = ZONETIME + UTIME;
            $today = get_date('Y/m/d'); // == get_date('Y/m/d', UTIME) == date('Y/m/d, ZONETIME + UTIME);
            $yesterday = get_date('Y/m/d', mktime(0,0,0, date('m',$localtime), date('d',$localtime)-1, date('Y',$localtime)));
        }
        
        foreach ($this->metapages as $i => $metapage) {
            $page = $metapage['page'];
            $lines = file(COUNTER_DIR . encode($page) . '.count');
            $lines = array_map('rtrim', $lines);
            list($total_count, $date, $today_count, $yesterday_count, $ip) = $lines;
            
            $popular = 0;
            switch ($this->options['popular'][1]) {
            case 'total':
                $popular = $total_count;
                break;
            case 'today':
                if ($date == $today) {
                    $popular = $today_count;
                }
                break;
            case 'yesterday':
                if ($date == $today) {
                    $popular = $yesterday_count;
                } elseif ($date == $yesterday) {
                    $popular = $today_count;
                }
                break;
            case 'recent':
                if ($date == $today) {
                    $popular = $today_count + $yesterday_count;
                } elseif ($date == $yesterday) {
                    $popular = $today_count;
                }
                break;
            }
            if ($popular > 0) {
                $this->metapages[$i]['popular'] = $popular;
            } else {
                unset($this->metapages[$i]); // like popular plugin
            }
        }
    }

    function timestamp_metapages()
    {
        if (! $this->options['date'][1] && ! $this->options['new'][1] && 
            $this->options['sort'][1] !== 'date') {
            return;
        }
        foreach ($this->metapages as $i => $metapage) {
            $page = $metapage['page'];
            $timestamp = $this->get_filetime($page);
            $this->metapages[$i]['timestamp'] = $timestamp;
        }
    }

    function date_metapages() 
    {
        if (! $this->options['date'][1] && ! $this->options['new'][1]) {
            return;
        }
        foreach ($this->metapages as $i => $metapage) {
            $timestamp = $metapage['timestamp'];
            $date = format_date($timestamp);
            $this->metapages[$i]['date'] = $date;
        }
    }

    function info_date_metapages()
    {
        if (! $this->options['date'][1]) {
            return;
        }
        foreach ($this->metapages as $i => $metapage) {
            $this->metapages[$i]['info_date'] = 
                '<span class="comment_date">' .  $metapage['date'] . '</span>';
        }
    }

    function info_new_metapages()
    {
        if (! $this->options['new'][1]) {
            return;
        }
        foreach ($this->metapages as $i => $metapage) {
            $date = $this->metapages[$i]['date'];
            // burdonsome, but to use configuration of new plugin
            $new = do_plugin_inline($this->conf['plugin_new'], 'nodate', $date);
            $this->metapages[$i]['info_new'] = $new;
        }
    }

    function info_metapages()
    {
        if (empty($this->options['info'][1])) {
            return;
        }

        $this->date_metapages();
        $this->info_date_metapages();
        $this->info_new_metapages();
        
        //foreach ($this->options['info'][2] as $key) {
        //    call_user_func(array($this, $key . '_metapages'));
        //}
        foreach ($this->metapages as $i => $metapage) {
            $info = '';
            foreach ($this->options['info'][1] as $key) {
                $info .= ' ' . $metapage['info_' . $key];
            }
            $this->metapages[$i]['info'] = $info;
        }
    }

    function tree_filter_metapages()
    {
        if ($this->options['tree'][1] === false) {
            return;
        }
        $allpages = get_existpages();
        $this->sort_pages($allpages);
        $current = current($allpages);
        while ($next = next($allpages)) {
            if (strpos($next, $current . '/') === FALSE) {
                $leafs[$current] = TRUE;
            } else {
                $leafs[$current] = FALSE;
            }
            $current = $next;
        }
        $leafs[$current] = TRUE;

        switch ($this->options['tree'][1]) {
        case 'dir':
            foreach ($this->metapages as $i => $metapage) {
                $page = $metapage['page'];
                if ($leafs[$page]) {
                    unset($this->metapages[$i]);
                }
            }
            break;
        case 'leaf':
            foreach ($this->metapages as $i => $metapage) {
                $page = $metapage['page'];
                if (! $leafs[$page]) {
                    unset($this->metapages[$i]);
                }
            }
            break;
        }
    }

    function hierarchy_metapages()
    {
        if ($this->options['hierarchy'][1] === false) {
            return;
        }
        $pdepth  = substr_count($this->options['prefix'][1], '/') - 1;
        $pdir    = $this->my_dirname($this->options['prefix'][1]);
        $pdirlen = ($pdir == '') ? 0 : strlen($pdir) + 1; // Add '/'
        $num = count($this->metapages);
        foreach ($this->metapages as $i => $metapage) {
            $page  = $metapage['page'];
            $depth = $metapage['depth']; // depth_metapages()
            if ($this->options['hierarchy'][1] === true) {
                $this->metapages[$i]['listdepth'] = $depth;
            }
            while ($depth > 1) {
                $page = $this->my_dirname($page);
                if ($page == '') break;
                $depth = substr_count($page, '/') - $pdepth;

                // if parent dir does not exist, complement
                if (($j = $this->array_search_by($page, $this->metapages, 'page')) === false) {
                    if ($this->options['hierarchy'][1] === true) {
                        $relative = substr($page, $pdirlen);
                        $listdepth = $depth;
                        $this->metapages[] = array('reading'=>$page,'page'=>$page, 'relative'=>$relative, 'exist'=>false, 'depth'=>$depth, 'listdepth'=>$listdepth, 'timestamp'=>1, 'date'=>'', 'leaf'=>false);
                        // PHP: new item is ignored on this loop
                    }
                }
            }
        }
        if (count($this->metapages) != $num) {
            $this->sort_metapages();
        }
    }

    function sort_metapages($sort = 'natcasesort', $sortflag = SORT_REGULAR)
    {
        switch ($this->options['sort'][1]) {
        case 'name':
            $this->sort_by($this->metapages, 'page', 'sort', SORT_STRING);
            break;
        case 'date':
            $this->sort_by($this->metapages, 'timestamp', 'rsort', SORT_NUMERIC);
            break;
        case 'reading':
            $this->sort_by($this->metapages, 'reading', 'sort', SORT_STRING);
            break;
        case 'popular':
            $this->sort_by($this->metapages, 'popular', 'rsort', SORT_NUMERIC);
            break;
        default:
            $this->sort_by($this->metapages, $this->options['sort'][1], $sort, $sortflag);
            break;
        }
        
        if ($this->options['reverse'][1]) {
            $this->metapages = array_reverse($this->metapages);
        }
    }
    
    function depth_metapages()
    {
        if ($this->options['depth'][1] === '' && $this->options['hierarchy'][1] === false &&
            $this->options['tree'][1] === false ) {
            return;
        }
        $pdepth = substr_count($this->options['prefix'][1], '/') - 1;

        foreach ($this->metapages as $i => $metapage) {
            $page  = $metapage['page'];
            $depth = substr_count($page, '/');
            $this->metapages[$i]['depth']   = $depth - $pdepth;
        }
        
        return $this->max_by($this->metapages, 'depth');
    }
    
    function relative_metapages()
    {
        $pdir = $this->my_dirname($this->options['prefix'][1]);
        if ($pdir == '') {
            foreach ($this->metapages as $i => $metapage) {
                $this->metapages[$i]['relative'] = $metapage['page'];
            }
        } else {
            $pdirlen = strlen($pdir) + 1; // Add strlen('/')
            foreach ($this->metapages as $i => $metapage) {
                $this->metapages[$i]['relative'] = substr($metapage['page'], $pdirlen);
            }
        }
    }
    
    function init_metapages()
    {
    	$qm = get_qm();
        if ($this->options['sort'][1] === 'reading') { 
            // Beta Function
            if ($this->options['tag'][1] == '') {
                $readings = $this->get_readings();
            } else {
                $plugin_tag = new PluginTag();
                $pages = $plugin_tag->get_taggedpages($this->options['tag'][1]);
                if ($pages === FALSE) {
                	$this->error = $qm->replace('plg_lsx.err_invalid_tag', h($this->options['tag'][1]));
                }
                $readings = $this->get_readings(); // why can not set pages...
                foreach ($pages as $page)
                    $tagged_readings[$page] = '';
                // array_intersect_key >= PHP 5.1.0 RC1
                // $readings = array_intersect_key($readings, $tagged_readings);
                foreach ($readings as $page => $reading) {
                    if (! isset($tagged_readings[$page])) unset($readings[$page]);
                }
            }
            $metapages = array();
            foreach ($readings as $page => $reading) {
                unset($readings[$page]);
                $metapages[] = array('reading'=>$reading,'page'=>$page, 'exist'=>true, 'depth'=>1, 'listdepth'=>1, 'timestamp'=>1, 'date'=>'');
            }
            $this->metapages = $metapages;
        } else {
            if ($this->options['tag'][1] == '') {
                $pages = get_existpages();
            } else {
                $plugin_tag = new PluginTag();
                $pages = $plugin_tag->get_taggedpages($this->options['tag'][1]);
                if ($pages === FALSE) {
                	$this->error = $qm->replace('plg_lsx.err_invalid_tag', h($this->options['tag'][1]));
                }
            }
            $metapages = array();
            foreach ($pages as $i => $page) {
                unset($pages[$i]);
                $metapages[] = array('page'=>$page, 'exist'=>true, 'depth'=>1, 'listdepth'=>1, 'timestamp'=>1, 'date'=>'');
            }
            $this->metapages = $metapages;
        }
    }

    function depth_filter_metapages()
    {
        if ($this->options['depth'][1] === '') {
            return;
        }
        $metapages = array();
        foreach ($this->metapages as $i => $metapage) {
            unset($this->metapages[$i]);
            if (in_array($metapage['depth'], $this->options['depth'][1])) {
                $metapages[] = $metapage;
            }
        }
        $this->metapages = $metapages;
    }
    
    // sort before this ($this->sort_by)
    function num_filter_metapages()
    {
        if ($this->options['num'][1] === '') {
            return;
        }
        $metapages = array();
        // $num < count($this->metapages) is assured. 
        foreach ($this->options['num'][1] as $num) {
            $metapages[] = $this->metapages[$num - 1];
        }
        $this->metapages = $metapages;
    }

    function newpage_filter_metapages()
    {
        if ($this->options['newpage'][1] === false) {
            return;
        }
        if ($this->options['newpage'][1] == 'on') {
            $new = true;
        } elseif ($this->options['newpage'][1] == 'except') {
            $new = false;
        }
        $metapages = array();
        foreach ($this->metapages as $i => $metapage) {
            unset($this->metapages[$i]);
            if ($new == $this->is_newpage($metapage['page'])) {
                $metapages[] = $metapage;
            }
        }
        $this->metapages = $metapages;
    }
    
    function prefix_filter_metapages()
    {
        if ($this->options['prefix'][1] === "") {
            return;
        }
        $metapages = array();
        foreach ($this->metapages as $i => $metapage) {
            unset($this->metapages[$i]);
            if (strpos($metapage['page'], $this->options['prefix'][1]) !== 0) { 
                continue;
            }
            $metapages[] = $metapage;
        }
        $this->metapages = $metapages;
    }

    function nonlist_filter_metapages()
    {
        if ($this->options['non_list'][1] === false) {
            return;
        }
        global $non_list;
        $metapages = array();
        foreach ($this->metapages as $i => $metapage) {
            unset($this->metapages[$i]);
            if (preg_match("/$non_list/", $metapage['page'])) { 
                continue; 
            }
            $metapages[] = $metapage;
        }
        $this->metapages = $metapages;
    }

    function except_filter_metapages()
    {
        if ($this->options['except'][1] === "") {
            return;
        }
        $metapages = array();
        foreach ($this->metapages as $i => $metapage) {
            unset($this->metapages[$i]);
            if (call_user_func($this->ereg, $this->options['except'][1], $metapage['relative'])) { 
                continue;
            }
            $metapages[] = $metapage;
        }
        $this->metapages = $metapages;
    }

    function filter_filter_metapages()
    {
        if ($this->options['filter'][1] === "") {
            return;
        }
        $metapages = array();
        foreach ($this->metapages as $i => $metapage) {
            unset($this->metapages[$i]);
            if (! call_user_func($this->ereg, $this->options['filter'][1], $metapage['relative'])) {
                continue;
            }
            $metapages[] = $metapage;
        }
        $this->metapages = $metapages;
    }

    // PukiWiki API Extension

    function sort_pages(&$pages)
    {
        $pages = str_replace('/', "\0", $pages);
        sort($pages, SORT_STRING);
        $pages = str_replace("\0", '/', $pages);
    }

    // No PREG_SPLIT_NO_EMPTY version
    // copy from lib/make_link.php#get_fullname
    function get_fullname($name, $refer)
    {
        global $defaultpage;
        
        // 'Here'
        if ($name == '' || $name == './') return $refer;
        
        // Absolute path
        if ($name{0} == '/') {
            $name = substr($name, 1);
            return ($name == '') ? $defaultpage : $name;
        }
        
        // Relative path from 'Here'
        if (substr($name, 0, 2) == './') {
            $arrn    = preg_split('#/#', $name, -1); //, PREG_SPLIT_NO_EMPTY);
            $arrn[0] = $refer;
            return join('/', $arrn);
        }
        
        // Relative path from dirname()
        if (substr($name, 0, 3) == '../') {
            $arrn = preg_split('#/#', $name,  -1); //, PREG_SPLIT_NO_EMPTY);
            $arrp = preg_split('#/#', $refer, -1, PREG_SPLIT_NO_EMPTY);
            
            while (! empty($arrn) && $arrn[0] == '..') {
                array_shift($arrn);
                array_pop($arrp);
            }
            $name = ! empty($arrp) ? join('/', array_merge($arrp, $arrn)) :
                (! empty($arrn) ? $defaultpage . '/' . join('/', $arrn) : $defaultpage);
        }
        
        return $name;
    }

    function is_newpage($page)
    {
        // pukiwiki trick
        return ! _backup_file_exists($page);
    }

    function make_pagelink($page, $alias = '', $anchor = '', $refer = '', $isautolink = FALSE)
    {
        // no passage
        global $show_passage;
        $tmp = $show_passage; $show_passage = 0;
        $link = make_pagelink($page, $alias, $anchor, $refer, $isautolink);
        $show_passage = $tmp;
        return $link;
    }

    function get_readings()
    {
        return get_readings();
    }

    function get_filetime($page)
    {
        return get_filetime($page);
    }

    // PHP Extension

    // dirname(Page/) => '.' , dirname(Page/a) => Page, dirname(Page) => '.'
    // But, want Page/ => Page, Page/a => Page, Page => ''
    function my_dirname($page)
    {
        if (($pos = strrpos($page, '/')) !== false) {
            return substr($page, 0, $pos);
        } else {
            return '';
        }
    }

    // basename(Page/) => Page , basename(Page/a) => a, basename(Page) => Page
    // But, want Page/ => '', Page/a => a, Page => Page
    function my_basename($page)
    {
        if (($pos = strrpos($page, '/')) !== false) {
            return substr($page, $pos + 1);
        } else {
            return $page;
        }
    }

    function array_search_by($value, $array, $fieldname = null)
    {
        foreach ($array as $i => $val) {
            if ($value == $val[$fieldname]) {
                return $i;
            }
        }
        return false;
    }

    function in_array_by($value, $array, $fieldname = null)
    {
        //foreach ($array as $i => $befree) {
        //    $field_array[$i] = $array[$i][$fieldname];
        //}
        //return in_array($value, $field_array);
        
        foreach ($array as $i => $val) {
            if ($value == $val[$fieldname]) {
                return true;
            }
        }
        return false;
    }

    # sort arrays by a specific field without maintaining key association
    function sort_by(&$array,  $fieldname = null, $sort, $sortflag = SORT_REGULAR)
    {
        $field_array = $inarray = array();
        # store the keyvalues in a seperate array
        foreach ($array as $i => $befree) {
            $field_array[$i] = $array[$i][$fieldname];
        }
        $field_array = str_replace('/', "\0", $field_array); // must not be here. Refactor me.
        switch ($sort) {
        case 'sort':
            # sort an array and maintain index association...
            asort($field_array, $sortflag);
            break;
        case 'rsort':
            # sort an array in reverse order and maintain index association
            arsort($field_array, $sortflag);
            break;
        case 'natsort':
            natsort($field_array);
        case 'natcasesort':
            # sort an array using a case insensitive "natural order" algorithm
            natcasesort($field_array);
        break;
        }
        # rebuild the array
    	$outarray = array();
        foreach ( $field_array as $i=> $befree) {
            $outarray[] = $array[$i];
            unset($array[$i]);
        }
        $array = $outarray;
    } 

    function max_by($array, $fieldname = null)
    {
        $field_array = $inarray = array();
        # store the keyvalues in a seperate array
        foreach ($array as $i => $befree) {
            $field_array[$i] = $array[$i][$fieldname];
        }
        if (count($field_array) > 0) {
        	return max($field_array);
        }
        else {
        	return '';
        }
    }
}
///////////////////////////////////////
class PluginLsxOptionParser
{
    var $error = "";

    function parse_options($args, $options)
    {
    	$qm = get_qm();
        if (! $this->is_associative_array($args)) {
            $args = $this->associative_args($args, $options);
            if ($this->error != "") { return; }
        }

        foreach ($args as $key => $val) {
            if ( !isset($options[$key]) ) { continue; } // for action ($vars)
            $type = $options[$key][0];
            $options[$key][4] = $val;

            switch ($type) {
            case 'bool':
                if($val == "" || $val == "on" || $val == "true") {
                    $options[$key][1] = true;
                } elseif ($val == "off" || $val == "false" ) {
                    $options[$key][1] = false;
                } else {
                	$this->error = $qm->replace('plg_lsx.err_usage_boolean', h($key), h($val), 'lsx');
                    return;
                }
                break;
            case 'string':
                $options[$key][1] = $val;
                break;
            case 'sanitize':
                $options[$key][1] = htmlspecialchars($val);
                break;
            case 'number':
                // Do not parse yet, parse after getting min and max. Here, just format checking
                if ($val === '') {
                    $options[$key][1] = '';
                    break;
                }
                if ($val[0] === '(' && $val[strlen($val) - 1] == ')') {
                    $val = substr($val, 1, strlen($val) - 2);
                }
                foreach (explode(",", $val) as $range) {
                    if (preg_match('/^-?\d+$/', $range)) {
                    } elseif (preg_match('/^-?\d*\:-?\d*$/', $range)) {
                    } elseif (preg_match('/^-?\d+\+-?\d+$/', $range)) {
                    } else {
                    	$this->error = $qm->replace('plg_lsx.err_usage_number', h($key), h($val));
                        return;
                    }
                }
                $options[$key][1] = $val;
                break;
            case 'enum':
                if($val == "") {
                    $options[$key][1] = $options[$key][2][0];
                } elseif (in_array($val, $options[$key][2])) {
                    $options[$key][1] = $val;
                } else {
                	$this->error = $qm->replace('plg_lsx.err_usage_enum', h($key), h($val), join(',', $options[$key][2]), 'lsx', $options[$key][2][0]);
                    return;
                }
                break;
            case 'array':
                if ($val == '') {
                    $options[$key][1] = array();
                    break;
                }
                if ($val[0] === '(' && $val[strlen($val) - 1] == ')') {
                    $val = substr($val, 1, strlen($val) - 2);
                }
                $val = explode(',', $val);
                //$val = $this->support_paren($val);
                $options[$key][1] = $val;
                break;
            case 'enumarray':
                if ($val == '') {
                    $options[$key][1] = $options[$key][2];
                    break;
                }
                if ($val[0] === '(' && $val[strlen($val) - 1] == ')') {
                    $val = substr($val, 1, strlen($val) - 2);
                }
                $val = explode(',', $val);
                //$val = $this->support_paren($val);
                $options[$key][1] = $val;
                foreach ($options[$key][1] as $each) {
                    if (! in_array($each, $options[$key][2])) {
                    	$this->error = $qm->replace('plg_lsx.err_usage_enumarray', h($key), h(join(",", $options[$key][1])), join(",", $options[$key][2]), 'lsx');
                        return;
                    }
                } 
                break;
            default:
            }
        }

        return $options;
    }
    
    /**
     * Handle associative type option arguments as
     * ["prefix=Hoge/", "contents=(hoge", "hoge", "hoge)"] => ["prefix"=>"hoge/", "contents"=>"(hoge,hoge,hoge)"]
     * This has special supports for parentheses type arguments (number, array, enumarray)
     * Check option in along with.
     * @access    public
     * @param     Array $args      Original option arguments
     * @return    Array $result    Converted associative option arguments
     */
    function associative_args($args, $options)
    {
    	$qm = get_qm();
        $result = array();
        while (($arg = current($args)) !== false) {
            list($key, $val) = array_pad(explode("=", $arg, 2), 2, '');
            if (! isset($options[$key])) {
            	$this->error = $qm->replace('plg_lsx.err_no_option', h($key));
                return;
            }
            // paren support
            if ($val[0] === '(' && ($options[$key][0] == 'number' || 
                 $options[$key][0] == 'array' || $options[$key][0] == 'enumarray')) {
                while(true) {
                    if ($val[strlen($val)-1] === ')' && substr_count($val, '(') == substr_count($val, ')')) {
                        break;
                    }
                    $arg = next($args);
                    if ($arg === false) {
                    	$this->error = $qm->m['plg_lsx']['err_paren'];
                        return;
                    }
                    $val .= ',' . $arg;
                }
            }
            $result[$key] = $val;
            next($args);
        }
        return $result;
    }

    function parse_numoption($optionval, $min, $max)
    {
        if ($optionval === '') {
            return '';
        }
        $result = array();
        foreach (explode(",", $optionval) as $range) {
            if (preg_match('/^-?\d+$/', $range)) {
                $left = $right = $range;
            } elseif (preg_match('/^-?\d*\:-?\d*$/', $range)) {
                list($left, $right) = explode(":", $range, 2);
                if ($left == "" && $right == "") {
                    $left = $min;
                    $right = $max;
                } elseif($left == "") {
                    $left = $min;
                } elseif ($right == "") {
                    $right = $max;
                }
            } elseif (preg_match('/^-?\d+\+-?\d+$/', $range)) {
                list($left, $right) = explode("+", $range, 2);
                $right += $left;
            }
            if ($left < 0) {
                $left += $max + 1;
            }
            if ($right < 0) {
                $right += $max + 1;
            }
            $result = array_merge($result, range($left, $right));
            // range allows like range(5, 3) also
        }
        // filter
        foreach (array_keys($result) as $i) {
            if ($result[$i] < $min || $result[$i] > $max) {
                unset($result[$i]);
            }
        }
        sort($result);
        $result = array_unique($result);

        return $result;
    }

    function option_debug_print($options) {
        foreach ($options as $key => $val) {
            $type = $val[0];
            $val = $val[1];
            if(is_array($val)) {
                $val=join(',', $val);
            }
            $body .= "$key=>($type, $val),";
        }
        return $body;
    }

    // php extension
    function is_associative_array($array) 
    {
        if (!is_array($array) || empty($array))
            return false;
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
        // or
        //return is_array($array) && !is_numeric(implode(array_keys($array)));
    }
}

//////////////////////////////////
function plugin_lsx_common_init()
{
    global $plugin_lsx;
    if (class_exists('PluginLsxUnitTest')) {
        $plugin_lsx = new PluginLsxUnitTest();
    } elseif (class_exists('PluginLsxUser')) {
        $plugin_lsx = new PluginLsxUser();
    } else {
        $plugin_lsx = new PluginLsx();
    }
}

function plugin_lsx_convert()
{
    global $plugin_lsx; plugin_lsx_common_init();
    $args = func_get_args();
    return call_user_func_array(array(&$plugin_lsx, 'convert'), $args);
}

function plugin_lsx_action()
{
    global $plugin_lsx; plugin_lsx_common_init();
    return call_user_func(array(&$plugin_lsx, 'action'));
}

?>
