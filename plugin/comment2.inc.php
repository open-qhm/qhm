<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: comment2.inc.php,v 1.36 2006/01/28 14:54:51 teanan Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Comment plugin

define('PLUGIN_COMMENT2_DIRECTION_DEFAULT', '1'); // 1: above 0: below
define('PLUGIN_COMMENT2_SIZE_MSG',  35);
define('PLUGIN_COMMENT2_SIZE_NAME', 15);

// ----
define('PLUGIN_COMMENT2_FORMAT_MSG',  '$msg');
define('PLUGIN_COMMENT2_FORMAT_NAME', '$name');
define('PLUGIN_COMMENT2_FORMAT_NOW',  '&new{$now};');
define('PLUGIN_COMMENT2_FORMAT_STRING', "\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");

function plugin_comment2_action()
{
	global $script, $vars, $now;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	$vars['msg'] = str_replace("\n", '&br;', $vars['msg']); // Cut LFs
	$head = '';
	$match = array();
	if (preg_match('/^(-{1,2})-*\s*(.*)/', $vars['msg'], $match)) {
		$head        = & $match[1];
		$vars['msg'] = & $match[2];
	}
	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing

	$comment2  = str_replace('$msg', $vars['msg'], PLUGIN_COMMENT2_FORMAT_MSG);
	if(isset($vars['name']) || ($vars['nodate'] != '1')) {
		$_name = (! isset($vars['name']) || $vars['name'] == '') ? $qm->m['fmt_no_name'] : $vars['name'];
		$_name = ($_name == '') ? '' : str_replace('$name', $_name, PLUGIN_COMMENT2_FORMAT_NAME);
		$_now  = ($vars['nodate'] == '1') ? '' :
			str_replace('$now', $now, PLUGIN_COMMENT2_FORMAT_NOW);
		$comment2 = str_replace("\x08MSG\x08",  $comment2, PLUGIN_COMMENT2_FORMAT_STRING);
		$comment2 = str_replace("\x08NAME\x08", $_name, $comment2);
		$comment2 = str_replace("\x08NOW\x08",  $_now,  $comment2);
	}
	$comment2 = '-' . $head . ' ' . $comment2;

	$postdata    = '';
	$comment2_no  = 0;
	$above       = (isset($vars['above']) && $vars['above'] == '1');
	foreach (get_source($vars['refer']) as $line) {
		if (! $above) $postdata .= $line;
		if (preg_match('/^#comment2/i', $line) && $comment2_no++ == $vars['comment2_no']) {
			if ($above) {
				$postdata = rtrim($postdata) . "\n" .
					$comment2 . "\n" .
					"\n";  // Insert one blank line above #commment, to avoid indentation
			} else {
				$postdata = rtrim($postdata) . "\n" .
					$comment2 . "\n"; // Insert one blank line below #commment
			}
		}
		if ($above) $postdata .= $line;
	}

	$title = $qm->m['fmt_title_updated'];
	$body = '';
	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $qm->m['plg_comment']['title_collided'];
		$body  = $qm->m['plg_comment']['wng_collided'] . make_pagelink($vars['refer']);
	}

	if($vars['authcode_master'] === $vars['authcode']){
		$noupdate = $vars['noupdate']==1 ? true : false;
		page_write($vars['refer'], $postdata, $noupdate);
	}
	else{
		$vars['comment2_error'] = 'error!!!';
	}
	
	$retvars['msg']  = $title;
	$retvars['body'] = $body;
	
	
	$vars['page'] = $vars['refer'];

	return $retvars;
}

function plugin_comment2_convert()
{
    global $vars, $digest;
    static $numbers = array();
    static $comment2_cols = PLUGIN_COMMENT2_SIZE_MSG;
    $qm = get_qm();
    $plugin_comment2_auth = true;
    
    $s_msg = $s_name = "";
    
    if (PKWK_READONLY) return ''; // Show nothing
    
    if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
    $comment2_no = $numbers[$vars['page']]++;
    
    $options = func_num_args() ? func_get_args() : array();

    $nodate = in_array('nodate', $options) ? '1' : '0';
    $noupdate = in_array('noupdate', $options) ? '1' : '0';
    $above  = in_array('above',  $options) ? '1' :
    	(in_array('below', $options) ? '0' : PLUGIN_COMMENT2_DIRECTION_DEFAULT);
    
    $authcode = '' . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
    $auth_label = '認証コード';
    $auth_error_alert = '';
    $auth_error_style = '';
    $auth_error_class = '';

    if (in_array('textarea', $options))
    {
    	$area = isset($options[1]) && is_numeric($options[1]) ? $options[1] : 6;
    }
    else
    {
    	$area = 0;
    }

    $nametags = '';
    $commenttags = '';
    $input_area  = '';

    if(isset($vars['comment2_error']))
    {
        $auth_error_alert = '<div class="alert alert-danger">'. $qm->m['plg_comment2']['err_auth_code'] . '</div>';
        $auth_error_style = 'color:red;';
        $auth_error_class = 'has_error';
        $auth_label = $qm->m['plg_comment2']['err_auth_code'];

        if (isset($vars['name']))
        {
            $s_name = htmlspecialchars($vars['name']);
        }
        $s_msg = htmlspecialchars( str_replace('&br;', "\n", $vars['msg']) );
    }

    if (is_bootstrap_skin())
    {
        $auth_form = '
        <label class="control-label">認証コード('.$authcode.')</label>
        <input type="text" name="authcode" value="" class="form-control input-sm" size="4" />
';
        $name_form = '';
        $commenttags = '<label for="_p_comment2_comment2_' . $comment2_no . '">コメント: </label>';
        if (! in_array('noname', $options))
        {
            $name_form = '
          <label class="control-label" for="_p_comment2_name_' . $comment2_no . '">お名前: </label>
          <input type="text" name="name" class="form-control input-sm" id="_p_comment2_name_'.$comment2_no.'" size="24" value="'. $s_name. '" />
';
        }

        $comment_submit = '
          <div class="row">
            <div class="col-xs-12 col-sm-6 pull-right">
              <div class="form-inline">
                <div class="col-sm-12">
                  <div class="form-group">
                    <small>
                      '.$auth_form.'
                    </small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-6">
              <div class="form-group">
                <div class="col-sm-12">
                  <input type="submit" name="comment2" class="btn btn-default btn-sm" value="'.$qm->m['plg_comment']['btn_comment'].'" style="margin-bottom:0;white-space:normal;">
                </div>
              </div>
            </div>
          </div>
';

        $input_area = '';
        if ($area)
        {
            $input_area = '<textarea name="msg" id="_p_comment2_comment2_'.$comment2_no.'" class="form-control" rows="'.$area.'">'.$s_msg.'</textarea>';

            $comment_form = '
  <div class="form-horizontal">
    <div class="form-group">
      <div class="col-sm-12">
        '.$name_form.'
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-12">
      '.$input_area.'
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-12">
        '.$comment_submit.'
      </div>
    </div>
  </div>
';
        }
        else
        {
            if (in_array('noname', $options))
            {
                $input_area = $commenttags . '<input type="text" name="msg" class="form-control input-sm" id="_p_comment2_comment2_'.$comment2_no.'" size="'.$comment2_cols.'" value="'.$s_msg.'" />';

            $comment_form = '
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-12">
            '.$input_area.'
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12">
            '.$comment_submit.'
          </div>
        </div>
      </div>
';
        }
        else
        {
            $input_area = $commenttags . '<input type="text" name="msg" class="form-control input-sm" id="_p_comment2_comment2_'
    				.$comment2_no.'" size="'.$comment2_cols.'" value="'.$s_msg.'" />';

            $comment_form = '
      <div class="form-horizontal">
        <div class="form-group">
          <label class="control-label col-md-2" for="_p_comment2_name_' . $comment2_no . '">お名前: </label>
          <div class="col-md-10">
            <div class="row">
              <div class="col-md-6">
                <input type="text" name="name" class="form-control" id="_p_comment2_name_'.$comment2_no.'" size="24" value="'. $s_name. '" />
              </div>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-md-2" for="_p_comment2_comment2_' . $comment2_no . '">コメント: </label>
          <div class="col-md-10">
            <input type="text" name="msg" class="form-control" id="_p_comment2_comment2_'.$comment2_no.'" size="'.$comment2_cols.'" value="'.$s_msg.'" />
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-10 col-md-offset-2">
            <div class="row">
              <div class="col-md-6 col-sm-7 col-sm-push-6">
                <div class="form-inline">
                  <div class="col-sm-12">

                    <div class="form-group">
                      <small>
                        '.$auth_form.'
                      </small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-5 col-sm-pull-6">
                <div class="form-group">
                  <div class="col-sm-12">
                    <input type="submit" name="comment2" class="btn btn-default btn-sm" value="'.$qm->m['plg_comment']['btn_comment'].'" style="margin-bottom:0;white-space:normal;">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
';
            }
        }
    }
    else
    {
        $authcode_msg = '
    	  <span id="coment2_auth_msg_no_'.$comment2_no.'" style="font-size:11px;'.$auth_error_style.'">
    	    '.$auth_label.'('.$authcode.')
    		  <input type="text" name="authcode" value="" size="4" />
    		</span>
';

        if (in_array('noname', $options))
        {
            $commenttags = '<label for="_p_comment2_comment2_' . $comment2_no . '">コメント: </label>';
        }
        else
        {
            $nametags = '
          <label for="_p_comment2_name_' . $comment2_no . '">お名前: </label>
          <input type="text" name="name" id="_p_comment2_name_' . $comment2_no .  '" size="' . PLUGIN_COMMENT2_SIZE_NAME . '" value="'.$s_name.'" />
';
        }

        if ($area)
        {
            $input_area = '<br /><textarea name="msg" id="_p_comment2_comment2_'.$comment2_no.'" style="width:90%;" rows="'.$area.'">'.$s_msg.'</textarea>';
        }
        else
        {
            $input_area = $commenttags . '<input type="text"   name="msg" id="_p_comment2_comment2_'.$comment2_no.'" size="'.$comment2_cols.'" value="'.$s_msg.'" />';
        }

        $input_button = '<input type="submit" name="comment2" value="'.$qm->m['plg_comment']['btn_comment'].'" />';
        $comment_form = $nametags . $authcode_msg . $input_area .$input_button;
    }
	
    $script = get_script_uri();
    $s_page = htmlspecialchars($vars['page']);
    $string = <<<EOD
<br />
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="comment2" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="comment2_no" value="$comment2_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="noupdate" value="$noupdate" />
  <input type="hidden" name="authcode_master" value="{$authcode}" />
  $auth_error_alert
  $comment_form
 </div>
</form>
EOD;

    return $string;
}
