<?php
/**
 *   Haik Icon Plugin
 *   -------------------------------------------
 *   plugin/icon.inc.php
 *
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *
 *   created  : 13/01/29
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

function plugin_icon_inline()
{
	$args = func_get_args();

	$class = '';
	$icon_base = 'glyphicon';
	$icon_prefix = $icon_base . '-';
	$icon_name = $icon_options = '';
	$icon_text = '';

	$format = '<i class="%s %s%s" aria-hidden="true"></i>';

	if (isset($args[0]) && preg_match('/^<span class="material-symbols-(outlined|rounded|sharp)">\s*(\w+)\s*<\/span>$/', $args[0], $matches)) {
		plugin_icon_set_google_material_symbols($matches[1]);
		return $args[0];
	}

	foreach ($args as $arg)
	{
		if ($arg === 'glyphicon')
		{
			$icon_base = 'glyphicon';
			$icon_prefix = $icon_base . '-';
		}
		// FontAwesome 4 系互換の記述
		if ($arg === 'font-awesome' OR $arg === 'fa')
		{
			$icon_base = 'fa';
			$icon_prefix = $icon_base . '-';
			plugin_icon_set_font_awesome();
		}
		// FontAwesome 5
		else if (preg_match('/^(fa[bsrl])$/', $arg)) {
			$icon_base = $arg;
			$icon_prefix = 'fa-';
			plugin_icon_set_font_awesome();
		}
		else if (preg_match('/^fa[bsrl]?$/', $icon_base) && preg_match('/^[1-5]x|lg|fw$/', $arg))
		{
			$icon_options = " {$icon_prefix}{$arg}";
		}
		// Bootstrap Icons
		else if ($arg === 'bootstrap-icons' OR $arg === 'bi')
		{
			$icon_base = 'bi';
			$icon_prefix = $icon_base . '-';
			plugin_icon_set_bootstrap_icons();
		}
		// Google Material Symbols
		else if (preg_match('/^gms(o|r|s)$/', $arg, $mts)) {
			$map = [
				"o" => "outlined",
				"r" => "rounded",
				"s" => "sharp"
			];
			$type = $map[$mts[1]];
			$icon_base = 'material-symbols-' . $type;
			$icon_prefix = '';
			$format = '<i class="%s">%s</i>';
			plugin_icon_set_google_material_symbols($type);
		}
		else if ($arg !== '')
		{
			$icon_name = $arg;
		}
	}

	$icon_name = $icon_prefix.$icon_name;

	return sprintf($format, h($icon_base), h($icon_name), $icon_options);
}

/**
 * FontAwesome 5 を読みこむ
 * $search_pseudo_elements を true にすると、疑似要素にFontAwesomeを使えるが、
 * IE, Edge で #bs_carousel が正常に動作しなくなる影響があるので解決するまで使わないこと。
 *
 * @param boolean $search_pseudo_elements CSS Pseudo Elements を利用するかどうか
 * @see https://fontawesome.com/how-to-use/svg-with-js#pseudo-elements
 */
function plugin_icon_set_font_awesome($search_pseudo_elements = false)
{
	$qt = get_qt();
	$js = <<<HTML
<script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js"></script>
<script defer src="https://use.fontawesome.com/releases/v5.15.4/js/v4-shims.js"></script>
HTML;
    // CSS Pseudo-elements を利用する場合
    if ($search_pseudo_elements) {
        $extrajs = <<< HTML
<script>
  FontAwesomeConfig = { searchPseudoElements: true };
</script>
HTML;
        $qt->prependv_once('plugin_icon_font_awesome_pseudo_elements', 'beforescript', $extrajs);
    }
	$qt->appendv_once('plugin_icon_font_awesome', 'beforescript', $js);
}

function plugin_icon_set_bootstrap_icons()
{
	$qt = get_qt();
	$head = <<<HTML
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
HTML;
	$qt->appendv_once('plugin_icon_bootstrap_icons', 'beforescript', $head);
}

function plugin_icon_set_google_material_symbols($type) {
	$type_capitalized = ucfirst($type);
	$qt = get_qt();
	$head = <<<HTML
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+$type_capitalized:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
<style>
.material-symbols-$type {
  display: inline-flex;
  vertical-align: middle;
	font-size: inherit;
}
</style>
HTML;
	$qt->appendv_once("plugin_icon_google_material_icons_$type", 'beforescript', $head);
}