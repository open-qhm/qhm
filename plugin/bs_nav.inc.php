<?php
/**
 *   bootstrap nav
 *   -------------------------------------------
 *   bs_nav.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/05/23
 *   modified :
 *
 *   Usage : #bs_nav
 *
 */

function plugin_bs_nav_convert()
{
    $qt = get_qt();

    $args = func_get_args();
    $body = array_pop($args);

    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
    $body = convert_html($body, TRUE);
    $justified_script = '';

    $attr_class = array('qhm-bs-nav');
    if (strpos($body, '<ul ') !== FALSE)
    {
        $attr_class[] = 'nav';
        $attr_class[] = 'navbar-nav';
    }

    $has_form = false;
    $has_text = false;
    $has_button = false;

    $justified = false;
    foreach ($args as $key => $value)
    {
        $value = trim($value);
        switch ($value)
        {
            case 'button':
                $attr_class[] = 'navbar-btn';
                $has_button = true;
                break;
            case 'left':
            case 'right':
                $attr_class[] = 'navbar-'.$value;
                break;
            case 'form':
            case 'hasForm':
                $attr_class[] = 'navbar-form';
                $has_form = true;
                break;
            case 'text':
                $attr_class[] = 'navbar-text';
                $has_text = true;
                break;
            case 'justify':
            case 'justified':
                $justified = true;
                break;
        }
    }

    $attr_class = array_filter($attr_class);
    $attr_class = join(' ', $attr_class);

    $regx = array(
        '/<ul class="list1"/',
    );
    $replace = array(
        '<ul class="' . $attr_class . '"',
    );
    $body = preg_replace($regx, $replace, $body);

    if ($has_button)
    {
        if (strpos($body, '<ul') === FALSE)
        {
            $regx = array(
                '/<a class="/',
            );
            $replace = array(
                '<a class="' . $attr_class . ' ',
            );
            $body = preg_replace($regx, $replace, $body);
        }
    }

    if ($has_text)
    {
        if (strpos($body, '<ul') === FALSE)
        {
            $body = '<div class="'.$attr_class.'">' . $body . '</div>';
        }
    }

    if ($has_form)
    {
        $regx = array(
            '/<form/',
        );
        $replace = array(
            '<form class="'.$attr_class.'"',
        );
        $body = preg_replace($regx, $replace, $body);
    }
    if ($justified)
    {
        $justified_script = '
  if ($(".qhm-bs-nav").length === 1) {
    $(".qhm-bs-nav").addClass("qhm-bs-nav-justified");
  }
';

        $justified_style = '
<style>
.qhm-bs-nav.qhm-bs-nav-justified {
  float: none;
}
.qhm-bs-nav.qhm-bs-nav-justified > li {
  display: table-cell;
  width: 1%;
  float: none;
  text-align: center;
}

@media(max-width:768px) {
  .qhm-bs-nav.qhm-bs-nav-justified {
    padding-left: 0;
  }

  .qhm-bs-nav.qhm-bs-nav-justified > li {
    display: block;
    width: 100%;
    text-align: left;
  }
}
</style>
';
        $qt->appendv_once("plugin_bs_nav_justified_style", "beforescript", $justified_style);
    }

    // hide .list2 until init by dropdown
    // hide statement similars to bootstrap .sr-only

    $addcss = <<< EOS
<style>
.qhm-bs-nav .list2 {
  position: absolute;
  width: 1px;
  height: 1px;
  margin: -1px;
  padding: 0;
  overflow: hidden;
  clip: rect(0,0,0,0);
  border: 0;
}
</style>
EOS;
    $qt->appendv_once('plugin_bs_nav_css', 'beforescript', $addcss);

    $addscript = '
<script>
$(function(){

'.$justified_script.'

  $(".qhm-bs-nav ul.list2").each(function(){
    var $ul = $(this);
    var $li = $ul.parent();

    $ul.removeClass("list2").addClass("dropdown-menu");

    if ($li.children("a").length) {
      $li.children("a").addClass("dropdown-toggle").attr("data-toggle", "dropdown").append("<b class=\"caret\"></b>");
    }
    else {
      $("body").append($ul);
      var $child = $li.contents();

      $li.prepend("<a href=\"#\"></a>").children("a").append($child).addClass("dropdown-toggle").attr("data-toggle", "dropdown").append("<b class=\"caret\"></b>");
      $li.append($ul);
    }
  });

});
</script>
';
    $qt->appendv_once("plugin_bs_nav", "lastscript", $addscript);

    return $body;
}
