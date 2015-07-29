<?php
/**
 *   cols
 *   -------------------------------------------
 *   cols.inc.php
 *
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *
 *   created  : 13/01/10
 *   modified : 13/08/07
 *
 *   Description
 *   
 *   
 *   Usage :
 *   
 */

function plugin_cols_convert()
{
	$qm = get_qm();
	$qt = get_qt();

	$args   = func_get_args();
	$body   = array_pop($args);
	
	$type = plugin_cols_type('get');

	$msg = '';
	$cols = array();
	
	$delim = "\r====\r";

	$row_class = '';
	$num = $args[0];
	
	$col_push_index = false;
	$col_push_num = 0;

	$col_pull_index = false;
	$col_pull_num = 0;


	if (count($args) > 0)
	{
		$max = 12;
		$total = 0;
		
		for ($i = 0; $i < count($args); $i++)
		{
		  $args[$i] = trim($args[$i]);
			if ($args[$i] === '') continue;


			if ( ! preg_match('/^(\d+)(?:\+(\d+))?(r|l)?((?:\.[a-zA-Z0-9_-]+)+)?$/', $args[$i], $mts))
			{
				if (preg_match('/^class=(.+)$/', $args[$i], $mts))
				{
					$row_class = " " . trim($mts[1]);
				}
				else if (preg_match('/^(left|center|right)$/i', $args[$i], $mts))
				{
					$row_class = " text-" . trim($mts[1]);
				}
				else
				{
					$delim = "\r" . trim($args[$i]) . "\r";
				}
				continue;
			}
			
			$col_num = (int)$mts[1];
			$col_offset = isset($mts[2]) ? (int)$mts[2] : 0;

			$col_push = false;
			if ($col_push_index === false && $col_pull_index === false && isset($mts[3]) && $mts[3] == 'r')
			{
  		    $col_push_index = count($cols);
          $col_push = $col_push_num = $col_num;
			}

			$col_pull = false;
			if ($col_push_index === false && $col_pull_index === false && isset($mts[3]) && $mts[3] == 'l')
			{
  		    $col_pull_index = count($cols);
          $col_pull = $col_pull_num = $col_num;
			}

			$col_class = isset($mts[4]) ? $mts[4] : '';
			$total += $col_num + $col_offset;
			$cols[] = array('span'=>$col_num, 'offset' => $col_offset, 'class'=>$col_class, 'push'=>$col_push, 'pull'=>$col_pull);
		}
		
		if (ss_admin_check())
		{
			if ($max < $total)
			{
				$msg = <<<EOD
<div class="alert alert-danger">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<p>指定しているカラムの合計数が12を超えています。</p>
	<p>※このメッセージは、管理者にのみ表示しています</p>
</div>
EOD;
			}
		}
	}
	
	if (count($cols) === 0)
	{
		$data = explode($delim, $body);

		$col_num = (int)(12 / count($data));
		for ($i = 0; $i < count($data); $i++)
		{
			$cols[] = array('span'=>$col_num, 'offset'=>0, 'class'=>NULL, 'push'=>false, 'pull'=>false);
		}
	}

  if ($col_push_index !== false)
  {
      $push_sum = 0;
    	for($i = count($cols)-1; $i > $col_push_index; $i--)
    	{
    	    $cols[$i]['pull'] = $col_push_num;
    	    $push_sum += $cols[$i]['span'];
      }
      $cols[$col_push_index]['push'] = $push_sum;    
  }

  if ($col_pull_index !== false)
  {
      $pull_sum = 0;
    	for($i = 0; $i < $col_pull_index; $i++)
    	{
    	    $cols[$i]['push'] = $col_pull_num;
    	    $pull_sum += $cols[$i]['span'];
      }
      $cols[$col_pull_index]['pull'] = $pull_sum;    
  }


	$html = '<div class="row%s">';

	if ($type === 'thumbnails')
	{
		$html = '<div class="row">';
	}

	$html = sprintf($html, h($row_class));

	$data = array_pad(explode($delim, $body, count($cols)), count($cols), '');

	global $block_style, $block_class, $block_image;
	if ( ! isset($block_style)) $block_style = '';
	if ( ! isset($block_class)) $block_class = '';
	if ( ! isset($block_image)) $block_image = '';

	for($i = 0; $i < count($cols); $i++)
	{
		$option = $cols[$i];
		$offset = $option['offset'] ? (' col-sm-offset-' . $option['offset']) : '';
		$col_class = $option['class'] ? str_replace('.', ' ', $option['class']) : '';
		$col_push = $option['push'] ? (' col-sm-push-'.$option['push']) : '';
		$col_pull = $option['pull'] ? (' col-sm-pull-'.$option['pull']) : '';

		$open_tag = '<div class="col-sm-'.$option['span']. $offset . $col_push . $col_pull . $col_class . '%s" style="%s">';
		$close_tag = '</div>';
		if ($type === 'thumbnails')
		{
			$open_tag = '<div class="col-sm-'.$option['span']. $offset . ' %s" style="%s"><div class="thumbnail">%s<div class="caption">';
			$close_tag = '</div></div></div>';
		}
		
		$str = '';
		if (isset($data[$i]))
		{
	        $str = str_replace("\r", "\n", str_replace("\r\n", "\n", $data[$i]));
	        $lines = explode("\n", $str);
	        $str = convert_html($lines);
		}
		$html .= sprintf($open_tag, " " . h($block_class), h($block_style), $block_image);
		$html .= $str . $close_tag;
		$block_class = $block_style = $block_image = '';
	}
	
	if ($type === 'thumbnails')
	{
		$html .= '</div>';
	}
	else
	{
		$html .= '</div>';
	}
	
	return $msg.$html;
}

function plugin_cols_type($action = 'get', $value = NULL)
{
	static $type = 'normal';
	
	if ($action === 'get')
	{
		return $type;
	}
	else
	{
		$type = $value;
		return TRUE;
	}
}
