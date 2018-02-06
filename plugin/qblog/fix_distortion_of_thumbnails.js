/**
 * QBlog の古いリストテンプレートの構造を最新のものに変換する。
 */
$(function(){
  $(".qblog-list-table .qblog_thumbnail").each(function(){
    var $img  = $(this);
    var $a    = $(this).closest("a");
    var $date = $a.find('.qblog_date');

    var thumbnailUrl = $img.attr("src");

    var $box = $('<div></div>').addClass("qblog_thumbnail_box").css({backgroundImage: "url('" + thumbnailUrl + "')"});
    var $newDate = $('<div></div>').addClass("qblog_date").text($date.text());
    $box.append($newDate);
    $a.append($box);
    $img.remove();
    $date.remove();
  });
});
