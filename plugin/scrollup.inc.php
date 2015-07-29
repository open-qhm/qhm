<?php
/**
 *   トップに戻るリンク
 *   -------------------------------------------
 *   scrollup.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/08/26
 *   modified :
 *
 *   Usage :
 *     #scrollup
 *
 */
function plugin_scrollup_convert()
{
    $args = func_get_args();

    $qt = get_qt();

    $target = 'body';
    $title = 'トップ';
    if (count($args) > 0)
    {
        $target = h(trim($args[0]));
        if (isset($args[1]))
        {
            $title = h(trim($args[1]));
        }
    }

    if (exist_plugin('icon'))
    {
        plugin_icon_set_font_awesome();
    }

    $add_style = <<< EOD
<style data-qhm-plugin="scrollup">
.qhm-plugin-scrollup {
  color: inherit;
  bottom: 10px;
  right: 10px;
  cursor: pointer;
}
.qhm-plugin-scrollup.affix:hover {
  color: inherit;
  opacity: .8;
}
</style>
EOD;
    $qt->appendv_once('plugin_scrollup_style', 'beforescript', $add_style);

    $add_script = <<< EOD
<script data-qhm-plugin="scrollup">
$(function() {
    $("body").append('<a class="qhm-plugin-scrollup"></a>').find(".qhm-plugin-scrollup")
    .html('<i class="fa fa-arrow-up fa-2x"></i>')
    .attr({
      'data-target': "{$target}",
      'title': "{$title}"
    })
    .affix({
      offset: {
        top: 50
      }
    });

    $(".qhm-plugin-scrollup").on("click", function(e){
      QHM.scroll($(this).data("target"));
      e.preventDefault();
      return false;
    });
});
</script>
EOD;

    $qt->appendv_once('plugin_scrollup_script', 'lastscript', $add_script);

    return;
}
