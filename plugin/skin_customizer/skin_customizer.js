$(function(){


  if ($("#preview_bar").length > 0)
  {
    $(".toolbar_upper").hide();

    /* check save before leave */
    $(".qhm-skin-customizer-menu form").on("change", function(){
        $(window).on('beforeunload', function(event) {
          	return 'カスタマイズに変更があります。先に保存を行ってください';
        });
    })
    .on("submit", function(){
      $(window).off('beforeunload');
    });

    if (skin_customizer.open) {
      $(".qhm-skin-customizer-menu-toggle").click();
    }

    /* set color picker */
    $(".color-picker", "#preview_bar").colorPicker({colors: skin_customizer.palette});

    /* set color*/
    $("[data-font-color]", "#preview_bar").each(function(){
      $(this).css("color", $(this).data("font-color"));
    });

    $("[data-color]", "#preview_bar").each(function(){
      $(this).css("background-color", $(this).data("color"));
    })
    .on("change", function(){
      var name = $(this).attr("name");
      var color = $(this).val();
      if ($("[data-paint-target="+name+"]").length) {
        var $paint = $("[data-paint-target="+name+"]");
        $paint.find('.btn').eq(0).css('color', color);
        $paint.find('.btn').eq(1).css('background-color', color);
      }
    });

    /* set theme color */
    if ($(".theme-color-picker").length > 0) {
      var $element = $(".theme-color-picker");
      var $pallet = $(".theme-color-picker").closest("div");

      $.each(skin_customizer.theme_colors, function (iSet) {
        var $wrapSwatch = $('<div class="themeColorPicker-wrap-swatch"></div>');
        var $swatch = $('<div class="colorPicker-swatch">&nbsp;</div>');
        var objColor = skin_customizer.theme_colors[iSet];

        $swatch.css({
            "background": objColor.color
        })
        .attr("data-value", objColor.name);

        if (typeof objColor.subcolor != 'undefined') {
          $swatch.css({
            background: 'linear-gradient(to bottom right, ' + objColor.color+','+ objColor.color + ' 50%, ' + objColor.subcolor + ' 51%, ' + objColor.subcolor + ' 100%)'
          });
        }
        
        $swatch.on("click", function(){
          var value = $(this).attr("data-value");
          $element.val(value);
          $(this).parent().addClass("active").siblings().removeClass("active");
        });

        $swatch.appendTo($wrapSwatch);
        $wrapSwatch.appendTo($pallet);

        if (objColor.name == $element.val()) {
            $wrapSwatch.addClass("active");
        }
      });
    }

    /* set collapse */
    $("[data-type=if] .btn", "#preview_bar").each(function(){
      var $input = $(this).find('input:radio');
      var name = $input.attr("name");
      if ($(this).hasClass("active")) {
        if ($input.attr("value") == 1) {
          $("[data-follow="+name+"]", "#preview_bar").addClass('in');
        }
      }
      $("[data-follow="+name+"]", "#preview_bar").collapse({toggle: false});
    })
    .on("click", function(e){
      var $input = $(this).find('input:radio');
      var name = $input.attr("name");

      if ($input.attr("value") == 1) {
        $("[data-follow="+name+"]", "#preview_bar").collapse('show');
      }
      else {
        $("[data-follow="+name+"]", "#preview_bar").collapse('hide');
      }
    });

    /* set image*/
    $("[data-image]", "#preview_bar").each(function(){
      var bgImg = $(this).data("image");
      $(this).css({
        position: "relative",
        width: '32px',
        marginRight: "5px",
        backgroundImage: "url("+bgImg+")",
        backgroundPosition: "center center",
        backgroundSize: "cover",
        backgroundRepeat: "no-repeat"
      });
      if (bgImg.length == 0)
      {
        $(this).parent().addClass("hide");
      }
    })
    .on("click", function(){
      $($(this).data("target")).click();
    });

    /* file upload */
    $("[data-file-upload]", ".qhm-skin-customizer-modal").fileupload({
      url: skin_customizer.script,
      dataType: 'json',
      acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
      done: function (e, data) {
          var param = data.sync;

          if (data.parentType == 'modal') {
            if ($("[data-custom]", data.parent).length) {
              var $custom = $("[data-custom]" ,data.parent);
              $custom.siblings().find("[data-image-select]").removeClass("active");
              $custom.removeClass("hidden").find("[data-image-select]").addClass("active").find("img").attr("src", data.result[param][0].url);
            }
          }
          else {
            var $input = $('input:hidden[name='+param+']');
            $input.val(data.result[param][0].url);
            $input.parent().prev().removeClass("hide").find('span').css({
              backgroundImage: "url("+data.result[param][0].url+")"
            });
          }
      },
      fail: function(e, data){
/* console.log(data.jqXHR.responseText); */
      },
      progressall: function (e, data) {
          var progress = parseInt(data.loaded / data.total * 100, 10);
          $('#progress .progress-bar').css(
              'width',
              progress + '%'
          );
      },
      processfail: function (e, data) {
          alert(data.files[data.index].name + "\n" + data.files[data.index].error);
      }
    })
    .on("click", function(e){
      // prevent click loop
      e.stopPropagation()
    })
    .prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled')
    .on("click", function(){
      // fire file dialog for firefox
      $(this).children("input:file").click();
    });

    $(".qhm-skin-customizer-select-img-modal, .qhm-skin-customizer-select-texture-modal").on("click", "[data-image-select]", function(){
      $(this).parent().siblings().find("[data-image-select]").removeClass("active");
      $(this).addClass("active");
    })
    .on("click", "[data-update]", function(){
        var imagesrc = $("[data-image-select].active").find("img").attr("src");
        var param = $(this).data("sync");
        var $input = $('input:hidden[name='+param+']');

        $input.val(imagesrc);
        $input.parent().prev().removeClass("hide").find('span').css({
            backgroundImage: "url("+imagesrc+")"
        });

        $(this).closest(".modal").modal("hide");
    });
  }
});
