<?php
/**
 *   Plugin: Equal Navi
 *   -------------------------------------------
 *   ./plugin/equal_navi.inc.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-12-12
 *   modified : 2011-12-14
 *
 *   ナビのアイテム幅をすべて同じに（平均化）します。
 *   オプションが2つあり、
 *   grow: 長いアイテムは平均化から除外する
 *   equal: 複数段ある場合、全段内の最小平均幅に合わせる
 *
 *   Usage :
 *     #equal_navi(grow|equal)
 *
 */


function plugin_equal_navi_convert()
{
	global $script, $vars;

	if (is_bootstrap_skin()) return '';

	$qt = get_qt();
	$qt->setv('jquery_include', TRUE);

	$args = func_get_args();
	$option = array_shift($args);
	$auto_grow = $equal_width = FALSE;
	switch ($option)
	{
		case 'grow':
			$auto_grow = TRUE;
			break;
		case 'equal':
			$equal_width = TRUE;
			break;
	}

	$beforescript = '
<script type="text/javascript">
$(function(){
    if ($(window).width() < 768) {
        return;
    }

	var navi_width = $("#navigator").width(),
		min_li_width = navi_width,
		auto_grow = '. ($auto_grow? 'true': 'false') .',
		equal_width = '. ($equal_width? 'true': 'false') .',
		$uls = $("#navigator ul.list1"),
		max_ul_children_length = 0,
		$all_lis = $("> li", $uls);

	$uls.each(function(i){
		var $lis = $("> li", this),
			$ul = $(this),
			ul_width = $ul.width(), width;
		ul_width -= intval($ul.css("border-left-width")) + intval($ul.css("border-right-width"));
		if (max_ul_children_length < $lis.length) {
			max_ul_children_length = $lis.length;
		}

		$lis.each(function(){
			var $li = $(this),
				li_margin = intval($li.css("margin-left")) + intval($li.css("margin-right")),
				li_padding = intval($li.css("padding-left")) + intval($li.css("padding-right")),
				li_extra_width = li_margin + li_padding;
			ul_width -= li_extra_width;
			$li.data("equal_navi", {extra_width: li_extra_width});
		});
		width = ul_width / $lis.length;

		/* auto grow */
		if (auto_grow) {
			var repeat = true;
			while (repeat) {
				repeat = false;
				$lis.each(function(){
					var $li = $(this), new_width;
					if ($li.width() > width) {
						repeat = true;
						new_width = $li.width() + 0.5;
						$li.width(new_width);
						ul_width -= new_width;
						$lis = $lis.not(this);
					}
				});
				width = ul_width / $lis.length;
			}
		}

		if (min_li_width > width) {
			min_li_width = width;
		}
		$lis.width(width);
		$("> li:last", this).width(ul_width - ($lis.length-1) * width);
	});

	/* set all lists to same width */
	if (equal_width && $uls.length > 1) {
		var cnt = 0, actual_width;
		$all_lis.width(min_li_width);
		$all_lis.each(function(){
			var $li = $(this),
				$ul = $li.closest("ul.list1");
			if ($li.is(":first-child")) {
				actual_width = 0;
				cnt = 0;
			}
			if ($li.is(":last-child") && cnt == max_ul_children_length - 1) {
				$li.width($li.closest("ul.list1").width() - actual_width - $li.data("equal_navi").extra_width);
			} else {
				actual_width += min_li_width + $li.data("equal_navi").extra_width;
				$li.width(min_li_width);
			}
			cnt++;
		});
	}

	function intval(data)
	{
		return isNaN(parseInt(data)) ? 0 : parseInt(data);
	}
});
</script>
';

	$qt->appendv_once('plugin_equal_navi', 'beforescript', $beforescript);

	return '';
}
