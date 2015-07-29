<?php
/**
 *  List Tagged Pages Plugin
 *
 *  @author     sonots
 *  @license    http://www.gnu.org/licenses/gpl.html    GPL
 *  @link       http://lsx.sourceforge.jp/?Plugin%2Ftag.inc.php
 *  @version    $Id: taglist.inc.php,v 1.1 2008-03-19 07:23:17Z sonots $
 *  @uses       tag.inc.php
 *  @package    plugin
 */

exist_plugin('tag') or die_message('tag.inc.php does not exist.');
class PluginTaglist
{
    function PluginTaglist()
    {
        static $default_options = array();
        if (empty($default_options)) {
            $default_options['tag']       = '';
            $default_options['related']   = NULL;
        }
        // static
        $this->default_options = & $default_options;
        // init
        $this->options = $default_options;
        global $plugin_tag_name;
        $this->plugin_tag = new $plugin_tag_name();
    }

    var $plugin_tag;
    var $default_options;
    var $options;
    var $plugin = 'taglist';

    function action() // taglist
    {
        global $vars;
        if (isset($vars['tag'])) {
            $this->options['tag'] = $vars['tag'];
            $msg = htmlspecialchars($this->options['tag']);
        } elseif (isset($vars['related'])) {
            $this->options['related'] = $vars['related'];
            $msg = htmlspecialchars($this->options['related']);
        } else {
            $msg = _('Taglist');
        } 
        $body = $this->taglist($this->options['tag'], $this->options['related']);
        return array('msg'=>$msg, 'body'=>$body);
    }

    function convert() // taglist
    {
        $args  = func_get_args();
        ///// Support old versions (The 1st arg is tagtok) ///
        $fisrtIsOption = FALSE;
        foreach ($this->options as $key => $val) {
            if (!isset($args) && strpos($args[0], $key) === 0) {
                $firstIsOption = TRUE; break;
            }
        }
        if (func_num_args() >= 1 && ! $firstIsOption) {
            $this->options['tag'] = array_shift($args);
        }
        //////////////////////////////////////////////////////
        parse_options($args, $this->options);
        return $this->taglist($this->options['tag'], $this->options['related']);
    }

    /**
     * Body Function of this plugin
     */
    function taglist($tagtok = '', $relate_tag = NULL)
    {
        if ($tagtok !== '') {
            $pages = $this->plugin_tag->get_taggedpages($tagtok);
            $html = $this->display_pagelist($pages);
        } else {
            $html = $this->display_tagpagelist($relate_tag);
        }
        return $html;
    }

    /**
     * Display tags and tagged pages
     *
     * Future Work: Use a common function with lsx.inc.php
     *
     * @param array $relate_tag Show only related tags of this
     * @return string HTML
     */
    function display_tagpagelist($relate_tag = NULL, $cssclass = 'taglist tags')
    {
        $tagcloud = $this->plugin_tag->get_tagcloud(NULL, $relate_tag);
        $html = '<ul class="' . $cssclass . '">';
        foreach ($tagcloud as $tag => $count) {
            $html .= '<li>' . $this->plugin_tag->get_taglink($tag);
            $pages = $this->plugin_tag->get_taggedpages($tag);
            $html .= $this->display_pagelist($pages);
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Display pages
     *
     * Future Work: Use a common function with lsx.inc.php
     *
     * @param array $pages pagenames
     * @return string HTML
     */
    function display_pagelist($pages, $cssclass = 'taglist pages')
    {
        /* PukiWiki standard does listing as <ul><li style="padding-left:16*2px;margin-left:16*2px">. 
         Since I do no like it, I do as <ul><li style="list-type:none"><ul><li>
         
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
        global $script;
        $html = '';
        
        $ul = $pdepth = 0;
        foreach ($pages as $i => $page) {
            $exist    = is_page($page);
            $depth    = 1;
            $link     = make_pagelink_nopg($page);
            
            if(! preg_match('/=edit&page=/', $link) ){
            	$title = get_page_title($page);
				$link = "<a href=\"{$script}?{$page}\" title=\"{$title}\">{$title}</a>";
			}
            
            $info     = null;
            if ($depth > $pdepth) {
                $diff = $depth - $pdepth;
                $html .= str_repeat('<ul><li style="list-style:none">', $diff - 1);
                if ($depth == 1) { // first flag
                    $html .= '<ul' . (isset($cssclass) ? ' class="' . $cssclass . '"' : '') . '><li>';
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
            $html .= isset($info) ? $info: '';
        }
        $html .= str_repeat('</li></ul>', $ul);
        return $html;
    }
}

function plugin_taglist_init()
{
    global $plugin_taglist_name;
     
    if (class_exists('PluginTaglistUnitTest')) {
        $plugin_taglist_name = 'PluginTaglistUnitTest';
    } elseif (class_exists('PluginTaglistUser')) {
        $plugin_taglist_name = 'PluginTaglistUser';
    } else {
        $plugin_taglist_name = 'PluginTaglist';
    }
    plugin_tag_init();
}

function plugin_taglist_convert()
{
    global $plugin_taglist, $plugin_taglist_name;
     
    $plugin_taglist = new $plugin_taglist_name();
    $args = func_get_args();
    return call_user_func_array(array(&$plugin_taglist, 'convert'), $args);
}

function plugin_taglist_action()
{
    global $plugin_taglist, $plugin_taglist_name;
    $plugin_taglist = new $plugin_taglist_name();
    return $plugin_taglist->action();
}

?>
