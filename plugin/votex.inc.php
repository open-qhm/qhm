<?php
/**
 * Yet Another Vote Plugin eXtension
 * 
 * @author     sonots
 * @license    http://www.gnu.org/licenses/gpl.html GPL v2
 * @link       http://lsx.sourceforge.jp/?Plugin%2Fvotex.inc.php
 * @version    $Id: votex.inc.php,v 1.5 2007-06-05 13:23:20Z sonots $
 * @package    plugin
 */

/**
 *  votex plugin class
 *
 *  @author     sonots
 *  @license    http://www.gnu.org/licenses/gpl.html    GPL2
 *  @link       http://lsx.sourceforge.jp/?Plugin%2Fvotex
 */
class PluginVotex
{
    function PluginVotex()
    {
        // static
        static $CONF = array();
        $this->CONF = &$CONF;
        if (empty($this->CONF)) {
            $this->CONF['RECENT_PAGE']  = 'RecentVotes';
            $this->CONF['RECENT_LOG']   = CACHE_DIR . 'recentvotes.dat';
            $this->CONF['RECENT_LIMIT'] = 100;
            $this->CONF['COOKIE_EXPIRED'] = 60*60*24*3;
            $this->CONF['BARCHART_LIB_FILE'] = LIB_DIR . 'barchart.cls.php';
            $this->CONF['BARCHART_COLOR_BAR'] = ' #0000cc';
            $this->CONF['BARCHART_COLOR_BG'] = 'transparent';
            $this->CONF['BARCHART_COLOR_BORDER'] = 'transparent';
        }
        static $default_options = array();
        $this->default_options = &$default_options;
        if (empty($this->default_options)) {
            $this->default_options['readonly'] = FALSE;
            $this->default_options['addchoice'] = FALSE;
            $this->default_options['barchart'] = FALSE;
        }

        // init
        $this->options  = $this->default_options;
        if (function_exists('textdomain')) {
            textdomain('vote'); // use i18n msgs of vote.inc.php
        }
    }

    // static
    var $CONF;
    var $default_options;
    // var
    var $options;

    /**
     * Action Plugin Main Function
     * @static
     */
    function action()
    {
        global $vars;
        if ($vars['pcmd'] === 'inline') {
            return $this->action_inline();
        } else {
            return $this->action_convert();
        }
    }

    /**
     * POST action via inline plugin
     */
    function action_inline()
    {
        global $vars, $defaultpage;
        $_title_collided   = _('On updating $1, a collision has occurred.');
        $_title_updated    = _('$1 was updated');
        $_msg_collided = _('It seems that someone has already updated this page while you were editing it.<br />
 + is placed at the beginning of a line that was newly added.<br />
 ! is placed at the beginning of a line that has possibly been updated.<br />
 Edit those lines, and submit again.');
        
        if (method_exists('auth', 'check_role')) { // Plus!
            if (auth::check_role('readonly')) die_message('PKWK_READONLY prohibits editing');
        } else {
            if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
        }

        $page         = isset($vars['refer']) ? $vars['refer'] : $defaultpage;
        $pcmd         = $vars['pcmd'];
        $vote_id      = $vars['vote_id'];
        $vars['page'] = $page;
        $choice_id    = $vars['choice_id'];

        if ($this->is_continuous_vote($page, $pcmd, $vote_id)) {
            return array(
                         'msg'  => _('Error in vote'),
                         'body' => _('Continuation vote cannot be performed.'),
                         );
        }

        // parse contents of wiki page and get update
        $lines = get_source($page);
        list($linenum, $newline, $newtext, $newvotes) = $this->get_update_inline($lines, $vote_id, $choice_id);
        if ($linenum === false) {
            die_message(_('There was no matching vote. '));
        }
        $newlines = $lines;
        $newlines[$linenum] = $newline;
        $newcontents = implode('', $newlines);

        // collision check
        $contents = implode('', $lines);
        if (md5($contents) !== $vars['digest']) {
            $msg  = $_title_collided;
            $body = $this->show_preview_form($_msg_collided, $newline);
            return array('msg'=>$msg, 'body'=>$body);
        }

        page_write($page, $newcontents, TRUE); // notimestamp
        $this->update_recent_voted($page, $pcmd, $vote_id, $choice_id, $newvotes);
        //static in convert() was somehow wierd if return(msg=>'',body=>'');
        //$msg  = $_title_updated;
        //$body = '';
        //return array('msg'=>$msg, 'body'=>$body);
        $anchor = $this->get_anchor($pcmd, $vote_id);
        header('Location: ' . get_script_uri() . '?' . rawurlencode($page) . '#' . $anchor);
        exit;
    }

    /**
     * POST action via convert plugin
     */
    function action_convert()
    {
        global $vars, $defaultpage;
        $_title_collided   = _('On updating $1, a collision has occurred.');
        $_title_updated    = _('$1 was updated');
        $_msg_collided = _('It seems that someone has already updated this page while you were editing it.<br />
 + is placed at the beginning of a line that was newly added.<br />
 ! is placed at the beginning of a line that has possibly been updated.<br />
 Edit those lines, and submit again.');
        
        if (method_exists('auth', 'check_role')) { // Plus!
            if (auth::check_role('readonly')) die_message('PKWK_READONLY prohibits editing');
        } else {
            if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
        }

        $page         = isset($vars['refer']) ? $vars['refer'] : $defaultpage;
        $pcmd         = $vars['pcmd'];
        $vote_id      = $vars['vote_id'];
        $vars['page'] = $page;
        $choice_id    = $this->get_selected_choice_convert();
        $addchoice    = isset($vars['addchoice']) && $vars['addchoice'] !== ''
            ? $vars['addchoice'] : null;
        
        if ($this->is_continuous_vote($page, $pcmd, $vote_id)) {
            return array(
                         'msg'  => _('Error in vote'),
                         'body' => _('Continuation vote cannot be performed.'),
                         );
        }

        // parse contents of wiki page and get update
        $lines = get_source($page);
        list($linenum, $newline, $newtext, $newvotes) = $this->get_update_convert($lines, $vote_id, $choice_id, $addchoice);
        if ($linenum === false) {
            die_message(_('There was no matching vote. '));
        }
        $newlines = $lines;
        $newlines[$linenum] = $newline;
        $newcontents = implode('', $newlines);

        // collision check
        $contents = implode('', $lines);
        if (md5($contents) !== $vars['digest']) {
            $msg  = $_title_collided;
            $body = $this->show_preview_form($_msg_collided, $newline);
            return array('msg'=>$msg, 'body'=>$body);
        }

        page_write($page, $newcontents, TRUE); // notimestamp
        if (isset($addchoice)) $choice_id = count($newvotes) - 1; // to make sure
        $this->update_recent_voted($page, $pcmd, $vote_id, $choice_id, $newvotes);
        //static in convert() was somehow wierd if return(msg=>'',body=>'');
        //$msg  = $_title_updated;
        //$body = '';
        //return array('msg'=>$msg, 'body'=>$body);
        $anchor = $this->get_anchor($pcmd, $vote_id);
        header('Location: ' . get_script_uri() . '?' . rawurlencode($page) . '#' . $anchor);
        exit;
    }

    /**
     * Update Vote for inline plugin
     *
     * @param array &$lines
     * @param integer $vote_id
     * @parram string $choice_id
     * @return array array($linenum, $updated_line, $updated_text, $updated_votes)
     */
    function get_update_inline(&$lines, $vote_id, $choice_id) 
    {
        $contents = implode('', $lines);

        global $vars, $defaultpage;
        $page = isset($vars['refer']) ? $vars['refer'] : $defaultpage;

        $ic = new InlineConverter(array('plugin'));
        $vote_count = 0;
        foreach ($lines as $linenum => $line) {
            if (strpos($line, ' ') === 0) continue; // skip pre
            $inlines = $ic->get_objects($line, $page);
            $pos = 0;
            foreach ($inlines as $inline) {
                if ($inline->name !== 'votex') continue;
                $pos = strpos($line, '&votex', $pos);
                if ($vote_id > $vote_count++) {
                    $pos++;
                } else {
                    $l_remain = substr($line, 0, $pos);
                    $r_remain = substr($line, $pos + strlen($inline->text));
                    $arg      = $inline->param;
                    $body     = $inline->body;
                    $args     = csv_explode(',', $arg);
                    list($votes, $options) = $this->parse_args_inline($args, $this->default_options);
                    if ($options['readonly']) return array(false, false, false, false);

                    foreach ($votes as $i => $vote) {
                        list($choice, $count) = $vote;
                        if ($i == $choice_id) {
                            ++$count;
                            $votes[$i] = array($choice, $count);
                        }
                    }
                    $new_args = $this->restore_args_inline($votes, $options, $this->default_options);
                    $new_arg  = csv_implode(',', $new_args);
                    $body = ($body != '') ? '{' . $body . '};' : ';';
                    $newtext = '&votex(' . $new_arg . ')' . $body;
                    $newline = $l_remain . $newtext . $r_remain;
                    return array($linenum, $newline, $newtext, $votes);
                }
            }
        }
        return array(false, false, false, false);
    }

    /**
     * Update Vote for convert plugin
     *
     * @param array &$lines
     * @param integer $vote_id
     * @parram string $choice_id
     * @param string $addchoice
     * @return array array($linenum, $updated_line, $updated_text, $updated_votes)
     */
    function get_update_convert(&$lines, $vote_id, $choice_id, $addchoice = null) 
    {
        $vote_count  = 0;
        foreach($lines as $linenum => $line) {
            $matches = array();
            if (preg_match('/^#votex(?:\((.*)\)(.*))?$/i', $line, $matches)
                && $vote_id == $vote_count++) {

                $args   = csv_explode(',', $matches[1]);
                $remain = isset($matches[2]) ? $matches[2] : '';
                list($votes, $options) = $this->parse_args_convert($args, $this->default_options);
                if ($options['readonly']) return array(false, false, false, false);

                if (isset($addchoice)) {
                    $votes[] = array($addchoice, 1);
                } elseif (isset($votes[$choice_id])) {
                    list($choice, $count) = $votes[$choice_id];
                    $votes[$choice_id] = array($choice, $count + 1);
                }
                $new_args = $this->restore_args_convert($votes, $options, $this->default_options);
                $new_arg  = csv_implode(',', $new_args);
                $newtext = '#votex(' . $new_arg . ')';
                $newline = $newtext . $remain . "\n";
                return array($linenum, $newline, $newtext, $votes);
            }
        }
        return array(false, false, false, false);
    }

    /**
     * Get the selected choice id
     *
     * @global $vars;
     * @return string $choice_id
     * @uses decode_choice()
     */
    function get_selected_choice_convert()
    {
        global $vars;
        $choice_id = false;
        foreach ($vars as $key => $val) {
            if (strpos($key, 'choice_') === 0) {
                $choice_id = $this->decode_choice($key);
                break;
            }
        }
        return $choice_id;
    }

    /**
     * Recent Voted
     *
     * @param string $page voted page
     * @param string $pcmd convert or inline
     * @param integer $vote_id
     * @param integer $choice_id
     * @param array $votes
     * @return void
     */
    function update_recent_voted($page, $pcmd, $vote_id, $choice_id, $votes)
    {
        $limit = max(0, $this->CONF['RECENT_LIMIT']);
        $time = UTIME;

        // RecentVoted
        $lines = get_source($this->CONF['RECENT_PAGE']);
        $anchor  = $this->get_anchor($pcmd, $vote_id);
        $args = array();
        foreach ($votes as $vote) {
            list($choice, $count) = $vote;
            $args[] = $choice . '[' . $count . ']';
        }
        $arg = csv_implode(',', $args);
        list($choice, $count) = $votes[$choice_id];
        $addline =
            '-' . format_date($time) . 
            ' - [[' . $page . '#' . $vote_id . '>' . $page . '#' . $anchor . ']] ' .
            $choice . 
            ' (' . $arg . ')' .
            "\n";
        array_unshift($lines, $addline);
        $lines = array_splice($lines, 0, $limit);
        page_write($this->CONF['RECENT_PAGE'], implode('', $lines));

        // recentvoted.dat (serialization)
        if (is_readable($this->CONF['RECENT_LOG'])) {
            $log_contents = file_get_contents($this->CONF['RECENT_LOG']);
            $logs = unserialize($log_contents);
        } else {
            $logs = array();
        }
        $addlog = array($time, $page, $pcmd, $vote_id, $choice_id, $votes);
        array_unshift($logs, $addlog);
        $logs = array_splice($logs, 0, $limit);
        file_put_contents($this->CONF['RECENT_LOG'], serialize($logs));
    }

    /**
     * Check if a continuous vote
     *
     * @param string $page
     * @param string $pcmd convert or inline
     * @param integer $vote_id vote form id
     * @return boolean true if if is a continuous vote
     * @global $_COOKIE
     * @global $_SERVER
     * @vars $CONF 'COOKIE_EXPIRED'
     */
    function is_continuous_vote($page, $pcmd, $vote_id)
    {
        $cmd = 'votex';
        $votedkey = $cmd . '_' . $pcmd . '_' . $page . '_' . $vote_id;
        if (isset($_COOKIE[$votedkey])) {
            return true;
        }
        $_COOKIE[$votedkey] = 1;
        $matches = array();
        preg_match('!(.*/)!', $_SERVER['REQUEST_URI'], $matches);
        setcookie($votedkey, 1, time()+$this->CONF['COOKIE_EXPIRED'], $matches[0]);
        return false;
    }

    /**
     * Get Preview Form HTML (for when collision occured)
     *
     * @param string $msg message
     * @param string $body
     * @return string
     */
    function show_preview_form($msg = '', $body = '')
    {
        global $vars, $rows, $cols;
        $s_refer  = htmlspecialchars($vars['refer']);
        $s_digest = htmlspecialchars($vars['digest']);
        $s_body   = htmlspecialchars($body);
        $form  = '';
        $form .= $msg . "\n";
        $form .= '<form action="' . get_script_uri() . '?cmd=preview" method="post">' . "\n";
        $form .= '<div>' . "\n";
        $form .= ' <input type="hidden" name="refer"  value="' . $s_refer . '" />' . "\n";
        $form .= ' <input type="hidden" name="digest" value="' . $s_digest . '" />' . "\n";
        $form .= ' <textarea name="msg" rows="' . $rows . '" cols="' . $cols . '" id="textarea">' . $s_body . '</textarea><br />' . "\n";
        $form .= '</div>' . "\n";
        $form .= '</form>' . "\n";
        return $form;
    }

    /**
     * Get anchor
     *
     * @param $pcmd
     * @param $vote_id
     */
    function get_anchor($pcmd = 'convert', $vote_id = 0)
    {
        return rawurlencode('vote_' . $pcmd . '_' . $vote_id);
    }
     
    /**
     * Inline Plugin Main Function
     * @static
     */
    function inline()
    {
        global $vars, $defaultpage;
        static $number = array();

        $page = isset($vars['page']) ? $vars['page'] : $defaultpage;
        if (! isset($number[$page])) $number[$page] = 0; // Init
        $vote_id = $number[$page]++;

        $args = func_get_args();
        array_pop($args); // drop {}
        list($votes, $this->options) = $this->parse_args_inline($args, $this->default_options);

        $form = $this->get_vote_form_inline($votes, $vote_id);
        return $form;
    }

    /**
     * Get Vote Form HTML for inline plugin
     *
     * @static
     * @param array $vote
     * @param integer $vote_id vote form id
     * @global $vars
     * @global $vars['page']
     * @global $defaultpage
     * @global $digest
     * @var $options 'readonly'
     * @uses get_script_uri()
     * @return string
     */
    function get_vote_form_inline($votes, $vote_id)
    {
        global $vars, $defaultpage;
        global $digest;
        $page      = isset($vars['page']) ? $vars['page'] : $defaultpage;
        $r_page    = rawurlencode($page);
        $r_digest  = rawurlencode($digest);
        $r_vote_id = rawurlencode($vote_id);
        $anchor = $this->get_anchor('inline', $vote_id);

        $form = '';
        $form .= '<span class="votex">';
        $form .= '<a name="' . $anchor . '" id="' . $anchor . '"></a>';
        foreach ($votes as $choice_id => $vote) {
            list($choice, $count) = $vote;
            $r_choice_id = rawurlencode($choice_id);
            $r_choice    = rawurlencode($choice);
            $r_count     = rawurlencode($count);
            $s_choice    = htmlspecialchars($choice);
            $s_count     = htmlspecialchars($count);
            if ($this->options['readonly']) {
                $form .= $s_choice . '<span>&nbsp;' . $s_count . '&nbsp;</span>';
            } else {
                $form .=
                    '<a href="' . get_script_uri() . '?cmd=votex' .
                    '&amp;pcmd=inline' .
                    '&amp;refer=' . $r_page .
                    '&amp;digest=' . $r_digest .
                    '&amp;vote_id=' . $r_vote_id . 
                    '&amp;choice_id=' . $r_choice_id .
                    '">' . $s_choice . '</a>' .
                    '<span>&nbsp;' . $s_count . '&nbsp;</span>';
            }
        }
        $form .= '</span>' . "\n";
        return $form;
    }

    /**
     * Block Plugin Main Function
     * @static
     */
    function convert()
    {
        global $vars, $defaultpage;
        static $number = array();

        $page = isset($vars['page']) ? $vars['page'] : $defaultpage;
        if (! isset($number[$page])) $number[$page] = 0; // Init
        $vote_id = $number[$page]++;

        $args = func_get_args();
        list($votes, $this->options) = $this->parse_args_convert($args, $this->default_options);

        $form = $this->get_vote_form_convert($votes, $vote_id);
        return $form;
    }

    /**
     * Restore Arguments of inline plugin
     *
     * @param array &$votes
     * @param array &$options
     * @return array &$args
     */
    function &restore_args_inline(&$votes, &$options, &$default_options)
    {
        // currently same
        return $this->restore_args_convert($votes, $options, $default_options);
    }

    /**
     * Parse Arguemnts of inline plugin
     *
     * @param array &$args arguments
     * @param array &$default_options default_options
     * @return array $votes id => array($choice[id], $count[id])
     * @return array $options
     */
    function parse_args_inline(&$args, &$default_options)
    {
        // currently same
        return $this->parse_args_convert($args, $default_options);
    }

    /**
     * Restore Arguments of convert plugin
     *
     * @param array &$votes
     * @param array &$options
     * @param array &$default_options
     * @return array &$args
     */
    function &restore_args_convert(&$votes, &$options, &$default_options)
    {
        $vote_args = array();
        foreach ($votes as $vote) {
            list($choice, $count) = $vote;
            $vote_args[] = $choice . '[' . $count . ']';
        }
        $opt_args = array();
        foreach ($options as $key => $val) {
            if ($default_options[$key] !== $val) {
                if (is_bool($val)) {
                    $opt_args[] = $key; // currently supports only on
                } else {
                    $opt_args[] = $key . '=' . $val;
                }
            }
        }
        $args = array_merge($vote_args, $opt_args);
        return $args;
    }

    /**
     * Parse Arguemnts of convert plugin
     *
     * @param array &$args arguments
     * @param array &$default_options default_options
     * @return array $votes id => array($choice[id], $count[id])
     * @return array $options
     */
    function parse_args_convert(&$args, &$default_options)
    {
        $votes = array();
        $options = $default_options;
        foreach ($args as $arg) {
            $arg = trim($arg);
            list($key, $val) = array_pad(explode('=', $arg, 2), 2, TRUE);
            if (array_key_exists($key, $options)) {
                $options[$key] = $val;
                continue;
            }
            $matches = array();
            $choice  = $arg;
            $count   = 0;
            if (preg_match('/^(.+)\[(\d+)\]$/', $arg, $matches)) {
                $choice = $matches[1];
                $count  = $matches[2];
            }
            $votes[] = array($choice, $count);
        }
        if ($options['barchart']) {
            require_once($this->CONF['BARCHART_LIB_FILE']);
        }
        return array($votes, $options);
    }

    /**
     * Decode choice key
     *
     * @param string $choice_key
     * @return integer $id
     */
    function decode_choice($choice_key)
    {
        list($prefix, $id) = explode('_', $choice_key, 2);
        if ($prefix !== 'choice') return false;
        return $id;
    }

    /**
     * Encode choice to key
     *
     * @param integer $id
     * @return string
     */
    function encode_choice($id)
    {
        return 'choice_' . $id;
    }

    /**
     * Get Vote Form HTML for convert plugin
     *
     * @static
     * @param array $votes
     * @param integer $vote_id vote form id
     * @global $vars
     * @global $vars['page']
     * @global $defaultpage
     * @global $digest
     * @var $options 'readonly'
     * @var $options 'addchoice'
     * @var $options 'barchart'
     * @uses get_script_uri()
     * @return string
     */
    function get_vote_form_convert($votes, $vote_id)
    {
        $choice_label = _('Selection');
        $vote_label   = _('Vote');
        $vote_button  = _('Vote');
        $add_button   = _('Vote'); // _('Add');

        // Initilization
        global $vars, $defaultpage;
        global $digest;
        $page     = isset($vars['page']) ? $vars['page'] : $defaultpage;
        $s_page   = htmlspecialchars($page);
        $s_digest = htmlspecialchars($digest);
        $script = ($this->options['readonly']) ? '' : get_script_uri();
        $submit = ($this->options['readonly']) ? 'hidden' : 'submit';
        $choicestyle = 'padding-left:1em;padding-right:1em;';
        $countstyle = (($this->options['barchart']) ? 'width:120px;' : '') . 'padding-right:1em;';
        $buttonstyle = ($this->options['readonly']) ? 'display:none;' : '';
        $anchor = $this->get_anchor('convert', $vote_id);

        // Init barchart
        if ($this->options['barchart']) {
            $barchart = new BARCHART(0, 0, 100);
            $barchart->setColorCompound($this->CONF['BARCHART_COLOR_BAR']);
            $barchart->setColorBg($this->CONF['BARCHART_COLOR_BG']);
            $barchart->setColorBorder($this->CONF['BARCHART_COLOR_BORDER']);

            $sum = 0; $max = 0; $argmax = 0;
            foreach ($votes as $choice_id => $vote) {
                list($choice, $count) = $vote;
                $sum += $count;
                if ($max < $count) {
                    $max = $count;
                    $argmax = $choice_id;
                }
            }
        }

        // Header
        $form = '';
        $form .= '<form class="votex" action="' . $script . '" method="post">' . "\n";
        $form .= '<table cellspacing="0" cellpadding="2" class="style_table" summary="vote">' . "\n";
        $form .= ' <tr>' . "\n";
        $form .= '  <td align="left" class="vote_label" style="' . $choicestyle . '"><strong>' . $choice_label . '</strong>' . "\n";
        $form .= '   <a name="' . $anchor . '" id="' . $anchor . '"></a>' . "\n";
        $form .= '   <input type="hidden" name="cmd"     value="votex" />' . "\n";
        $form .= '   <input type="hidden" name="pcmd"    value="convert" />' . "\n";
        $form .= '   <input type="hidden" name="refer"   value="' . $s_page . '" />' . "\n";
        $form .= '   <input type="hidden" name="vote_id" value="' . $vote_id . '" />' . "\n";
        $form .= '   <input type="hidden" name="digest"  value="' . $s_digest . '" />' . "\n";
        $form .= '  </td>' . "\n";
        $form .= '  <td align="right" class="vote_label" style="' . $countstyle . '"><strong>' . $vote_label . '</strong></td>' . "\n";
        $form .= '  <td align="right" class="vote_label" style="' . $buttonstyle . '"></td>' . "\n";
        $form .= ' </tr>' . "\n";
        
        // Body
        foreach ($votes as $choice_id => $vote) {
            list($choice, $count) = $vote;
            $class       = ($choice_id % 2) ? 'vote_td1' : 'vote_td2';
            $s_choice    = make_link($choice);
            $s_count     = htmlspecialchars($count);
            $choice_key  = $this->encode_choice($choice_id);
            if ($this->options['barchart']) {
                $percent = (int)(($count / $max) * 100); // / $sum
                $barchart->setCurrPoint($percent);
                $barchart->setStrAfterBar('&nbsp;' . $s_count);
                $s_count = $barchart->getBar();
            }
            $form .= ' <tr>' . "\n";
            $form .= '  <td align="left"  class="' . $class . '" style="' . $choicestyle . '">' . $s_choice . '</td>' . "\n";
            $form .= '  <td align="right" class="' . $class . '" style="' . $countstyle . '">'  . $s_count . '</td>' . "\n";
            $form .= '  <td align="right" class="' . $class . '" style="' . $buttonstyle . '">' . "\n";
            $form .= '    <input type="' . $submit . '" name="' . $choice_key . '" value="' . $vote_button . '" class="submit" />' . "\n";
            $form .= '  </td>' . "\n";
            $form .= ' </tr>' . "\n";
        }

        // add choice
        if ($this->options['addchoice']) {
            $choice_id++;
            $class      = ($choice_id % 2) ? 'vote_td1' : 'vote_td2';
            $choice_key = $this->encode_choice($choice_id);
            $form .= ' <tr>' . "\n";
            $form .= '  <td colspan="2" align="left"  class="' . $class . '" style="' . $choicestyle . '">' . "\n";
            $form .= '    <input type="text" size="40" name="addchoice" value="" />' . "\n";
            $form .= '  </td>' . "\n";
            $form .= '  <td align="right" class="' . $class . '" style="' . $buttonstyle . '">' . "\n";
            $form .= '    <input type="' . $submit . '" name="' . $choice_key . '" value="' . $add_button . '" class="submit" />' . "\n";
            $form .= '  </td>' . "\n";
            $form .= ' </tr>' . "\n";
        }

        // Footer
        $form .= '</table>' . "\n";
        $form .= '</form>' . "\n";

        return $form;
    }
}

///////////////////// PHP Adapter
if (! function_exists('_')) {
    function &_($str) 
        {
            return $str;
        }
}
if (! function_exists('file_put_contents')) {
    if (! defined('FILE_APPEND'))
        define('FILE_APPEND', 8);
    if (! defined('FILE_USE_INCLUDE_PATH'))
        define('FILE_USE_INCLUDE_PATH', 1);
    function file_put_contents($filename, $data, $flags = 0)
    {
        $mode = ($flags & FILE_APPEND) ? 'a' : 'w';
        $fp = fopen($filename, $mode);
        if ($fp === false) {
            return false;
        }
        if (is_array($data)) $data = implode('', $data);
        if ($flags & LOCK_EX) flock($fp, LOCK_EX);
        $bytes = fwrite($fp, $data);
        if ($flags & LOCK_EX) flock($fp, LOCK_UN);
        fclose($fp);
        return $bytes;
    }
}

///////////////////////////////////////////
function plugin_votex_init()
{
    global $plugin_votex_name;
    if (class_exists('PluginVotexUnitTest')) {
        $plugin_votex_name = 'PluginVotexUnitTest';
    } elseif (class_exists('PluginVotexUser')) {
        $plugin_votex_name = 'PluginVotexUser';
    } else {
        $plugin_votex_name = 'PluginVotex';
    }
}

function plugin_votex_action()
{
    global $plugin_votex, $plugin_votex_name;
    $plugin_votex = new $plugin_votex_name();
    return $plugin_votex->action();
}

function plugin_votex_convert()
{
    global $plugin_votex, $plugin_votex_name;
    $plugin_votex = new $plugin_votex_name();
    $args = func_get_args();
    return call_user_func_array(array(&$plugin_votex, 'convert'), $args);
}

function plugin_votex_inline()
{
    global $plugin_votex, $plugin_votex_name;
    $plugin_votex = new $plugin_votex_name();
    $args = func_get_args();
    return call_user_func_array(array(&$plugin_votex, 'inline'), $args);
}

function plugin_votex_write_after()
{
    global $plugin_votex, $plugin_votex_name;
    $plugin_votex = new $plugin_votex_name();
    $args = func_get_args();
    return call_user_func_array(array(&$plugin_votex, 'write_after'), $args);
}

if (! defined('INIT_DIR')) // if not Plus! 
    if (file_exists(DATA_HOME . 'init/votex.ini.php')) 
        include_once(DATA_HOME . 'init/votex.ini.php');

?>
