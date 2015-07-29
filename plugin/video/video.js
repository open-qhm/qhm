/**
 *   video.js
 *   -------------------------------------------
 *   video.js
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014/08/20
 *   modified :
 *
 *   Description
 *
 *
 *   Usage :
 *
 */
$(document).ready(function() {

  var setSizeAttributes = function(){
      var $parent = $(this).closest("div:visible");
      var width = $parent.width(),
        ratio = parseFloat($(this).attr("data-aspect-ratio")),
        height = parseInt(width * ratio, 10);
      $(this).attr("width", width).attr("height", height);
  };

  var mepOption = {
    videoWidth: -1,
    videoHeight: -1,
    enableAutosize: true
  };

  $("video").not("[data-popup]")
  .each(function(){
      //for IE9
      if ($(this).attr("width") === "100" && $(this).attr("height") === "100") {
          setSizeAttributes.call(this);
      }
  })
  .mediaelementplayer(mepOption);

  $('.qhm-plugin-video.modal[data-type!=youtube]')
  .on('shown.bs.modal', function (e) {
    $("video", this)
    .each(function(){
        if ($(this).attr("width") === "100" && $(this).attr("height") === "100") {
            setSizeAttributes.call(this);
        }
    })
    .mediaelementplayer(mepOption);
  })
  .on('hide.bs.modal', function(e){
    if ($(this).is("[data-type=video]")) {
      $("video", this).data("mediaelementplayer").pause();
    }
    else if ($(this).is("[data-type=vimeo]")) {
      var iframe = $('iframe', this)[0];
      var player = $f(iframe);
      player.api("pause");
    }
  });

  if (typeof $.fn.prettyEmbed !== "undefined") {
    var peOption = {
      useFitVids: true,
      playerControls: false,
      playerInfo: false
    };

    $(".pretty-embed").not("[data-popup]").prettyEmbed(peOption);

    $(".qhm-plugin-video.modal[data-type=youtube]")
    .on("shown.bs.modal", function (e) {
      $(".pretty-embed", this).prettyEmbed(peOption);
      if ($.fn.prettyEmbed.mobile) {
        $(".pretty-embed", this).trigger("click");
      }
    })
    .on("hidden.bs.modal", function(e) {
      $(".pretty-embed", this).html("");
    });
  }

});
