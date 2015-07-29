<?php
/**
 *  TagCloud Plugin
 *
 *  @author     sonots
 *  @license    http://www.gnu.org/licenses/gpl.html    GPL
 *  @link       http://lsx.sourceforge.jp/?Plugin%2Ftag.inc.php
 *  @version    $Id: tagcloud.inc.php,v 1.1 2008-03-19 07:23:17Z sonots $
 *  @uses       tag.inc.php
 *  @package    plugin
 */

exist_plugin('tag') or die_message('tag.inc.php does not exist.');
class PluginTagcloud
{
    var $plugin_tag;

    function PluginTagcloud()
    {
        static $default_options = array();
        if (empty($default_options)) {
            $default_options['limit']   = NULL;
            $default_options['related'] = NULL;
            $default_options['cloud']   = TRUE;
        }
        // static
        $this->default_options = & $default_options;
        // init
        $this->options = $default_options;
        global $plugin_tag_name;
        $this->plugin_tag = new $plugin_tag_name();
    }

    function convert() // tagcloud
    {
        $args  = func_get_args();
        parse_options($args, $this->options);
        if ($this->options['limit'] === "0") {
            $this->options['limit'] = NULL;
        }
        if ($this->options['cloud'] === 'off' ||
            $this->options['cloud'] === 'false' ) {
            $this->options['cloud'] = FALSE;
        }
        //print_r($this->options);
        if ($this->options['cloud']) {
            $html = $this->plugin_tag->display_tagcloud($this->options['limit'], $this->options['related']);
        } else {
            $html = $this->plugin_tag->display_taglist($this->options['limit'], $this->options['related']);
        }
        return $html;
    }
}

function plugin_tagcloud_init()
{
    global $plugin_tagcloud_name;
    if (class_exists('PluginTagcloudUnitTest')) {
        $plugin_tagcloud_name = 'PluginTagcloudUnitTest';
    } elseif (class_exists('PluginTagcloudUser')) {
        $plugin_tagcloud_name = 'PluginTagcloudUser';
    } else {
        $plugin_tagcloud_name = 'PluginTagcloud';
    }
    plugin_tag_init();
}

function plugin_tagcloud_convert()
{
    global $plugin_tagcloud, $plugin_tagcloud_name;
    $plugin_tagcloud = new $plugin_tagcloud_name();
    $args = func_get_args();
    return call_user_func_array(array(&$plugin_tagcloud, 'convert'), $args);
}
?>
