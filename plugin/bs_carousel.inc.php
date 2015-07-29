<?php
/**
 *   Image Carousel
 *   -------------------------------------------
 *   bs_carousel.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/05/21
 *   modified :
 *
 *   Description
 *
 *
 *   Usage :
 *
 */
function plugin_bs_carousel_convert()
{
	global $vars, $script;
	static $slide_num = 0;

	$qt = get_qt();

	$args = func_get_args();
	$body = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	$indicator = $slide_button = TRUE;
	$item_height = '';
	$item_class = ' fit';
	$fit = TRUE;

    $cols = $cols_offset = NULL;

	//options
	foreach ($args as $arg)
	{
		switch ($arg)
		{
			case 'nobutton':
				$indicator = $slide_button = FALSE;
				break;
			case 'noindicator':
				$indicator = FALSE;
				break;
			case 'noslidebutton':
				$slide_button = FALSE;
				break;
			case 'nofit';
				$item_class = '';
				$fit = FALSE;
				break;
            case (preg_match('/^height=(.+)$/', $arg, $mts) ? true: false):
                $item_height = $mts[1];
                break;
            case (preg_match('/^(\d+)(?:\+(\d+))?$/', $arg, $mts) ? true : false):
                $cols = $mts[1];
                $cols_offset = &$mts[2];
                break;
			default:
		}
	}

	$body = str_replace("\r", "\n", $body);
	$lines = explode("\n", $body);

	$slide_num++;

	$items = array();
	$cnt = 0;

	$min_width = FALSE;
	foreach ($lines as $line)
	{
		$line = trim($line);
		if ($line == '')
		{
			continue;
		}

		$options = explode(',', $line);
		$to = '';
		foreach ($options as $i => $opt)
		{
			$opt = trim($opt);
			if (preg_match('/^link_to=(.*)$/', $opt, $mts))
			{
				$to = $mts[1];
				if ( ! is_url($to) && is_page($to))
				{
					$to = $script . '?' . rawurlencode($to);
				}
				break;
			}
		}
		if ($to) array_splice($options, $i, 1);

		list($filename, $title, $caption) = $options;
		$filepath = get_file_path($filename);

		$image = '';
		if (file_exists($filepath))
		{
			list($_width, $_height) = getimagesize($filepath);
			$min_width = ($min_width !== FALSE) ? min($_width, $min_width) : $_width;
			$image = '<img src="'.h($filepath).'" alt="">';
			$image_url = dirname($script) . '/' . $filepath;
		}
		else
		{
			$image = '<img src="'.h($filepath).'" alt="">';
			$image_url = $filepath;
		}

		// set first image
		$qt->set_first_image($image_url);

		$h = $title ? '<h3 class="no-toc">'.h($title).'</h3>' : '';

		// アイキャッチの場合は、タイトルをh1にする
		global $is_eyecatch;
		if ($is_eyecatch)
		{
			$h = $title ? '<h1 class="no-toc">'.h($title).'</h1>' : '';
		}

		// 画像クリックでリンク可能にする
		$onclick = '';
		$add_style = '';
		if ($to !== '')
		{
			$onclick = ' onclick="location.href = \'' . h($to) . '\'"';
			$add_style= 'cursor:pointer;';
		}

		$p = $caption ? convert_html($caption) : '';

		$block = ($h OR $p);

		$items[] = '
		<div class="item'.($cnt ? '' : ' active'). $item_class. '" style="'. ($item_height ? ' max-height:'.h($item_height).'px;min-height:'.h($item_height).'px;' : ''). $add_style.'"' . $onclick .'>
			'.$image.'
			<div class="'. ($block ? 'carousel-caption' : '') .'">
			'.$h.'
			'.$p.'
			</div>
		</div>
';
			$cnt++;
	}

	$plural = ($cnt > 1);

	if ($cnt > 0)
	{
		$id = 'slide_' . $slide_num;
		$html = '
<div id="'.$id.'" class="qhm-bs-carousel carousel slide" data-ride="carousel" style="'. ($fit === FALSE && $min_width !== FALSE ? ('max-width:'.$min_width.'px;') : '').'">
';
		if ($plural && $indicator)
		{
			$html .= '
	<ol class="carousel-indicators">
';
			for ($i = 0; $i < $cnt; $i++)
			{
				$html .= '
		<li data-target="#'.$id.'" data-slide-to="'.$i.'" class="'.($i ? '' : 'active' ).'"></li>
';
			}
			$html .= '
</ol>';
		}
		$html .='
	<div class="carousel-inner">
		'.join("\n", $items).'
	</div>
';
		if ($plural && $slide_button)
		{
			$html .= '
	<a href="#'.$id.'" class="left carousel-control" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
	<a href="#'.$id.'" class="right carousel-control" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
';
		}
		$html .= '
</div>
';

        if ($cols)
        {
            $class_attr = 'col-sm-' . $cols;
            if ($cols_offset)
            {
                $class_attr .= ' col-sm-offset-' . $cols_offset;
            }
            $html = '<div class="row"><div class="'.$class_attr.'">'. $html .'</div></div>' . "\n";
        }

        // Output CSS
        $addstyle = '
<style>
.carousel.qhm-bs-carousel .item.fit > img {
  width: 100%;
  max-width: 100%;
  min-width: 100%;
}
.carousel .carousel-control {
  outline-style: none;
}
</style>
';
        $qt->appendv_once('plugin_bs_carousel_css', 'beforescript', $addstyle);
	}

	return $html;
}
