(function ($) {
    /**
     * Create a couple private variables.
    **/
    var selectorOwner,
        activePalette,
        cItterate       = 0,
        templates       = {
            palette : $('<div id="colorPicker_palette" class="colorPicker-palette" />'),
            swatch  : $('<div class="colorPicker-swatch">&nbsp;</div>'),
            hexLabel: $('<label for="colorPicker_hex" class="colorPicker-hex-label"></label>')
        },
        transparent     = "transparent",
        lastColor;

    /**
     * Create our colorPicker function
    **/
    $.fn.colorPicker = function (options) {

        return this.each(function () {
            // Setup time. Clone new elements from our templates, set some IDs, make shortcuts, jazzercise.
            var element      = $(this),
                opts         = $.extend({}, $.fn.colorPicker.defaults, options),
                newPalette   = templates.palette.clone().attr('id', 'colorPicker_palette-' + cItterate),
                newHexLabel  = templates.hexLabel.clone(),
                paletteId    = newPalette[0].id,
                swatch, controlText;
                
                defaultColor = (element.val().length > 0) ? element.val() : opts.pickerDefault;
                if (defaultColor.match(/^#?([0-9A-F]{6}|[0-9A-F]{3})$/i)) {
                  defaultColor = $.fn.colorPicker.toHex(defaultColor);
                }


            /**
             * Build a color palette.
            **/
            $.each(opts.colors, function (iSet) {
                var $wrapSwatch = $('<div class="colorPicker-wrap-swatch"></div>');

                $.each(opts.colors[iSet].color, function(i) {
                  
                  swatch = templates.swatch.clone();
  
                  if (opts.colors[i] === transparent) {
                      swatch.addClass(transparent).text('X');
                       $.fn.colorPicker.bindPalette(newHexLabel, swatch, transparent);
                  } else {
                      swatch.css({
                        "backgroundColor": "#" + this.toString(),
                        "borderColor": "#" + darken(this.toString(), 0.9)
                      });

                      $.fn.colorPicker.bindPalette(newHexLabel, swatch);
                  }
                  swatch.appendTo($wrapSwatch);

                });
                $wrapSwatch.appendTo(newPalette);
            });


            newHexLabel.attr('for', 'colorPicker_hex-' + cItterate);
            newHexLabel.appendTo(newPalette);

            $("body").append(newPalette);

            newPalette.hide();


            /**
             * Build replacement interface for original color input.
            **/
            element.css("background-color", defaultColor);
            
            element.bind("click", function () {
              $.fn.colorPicker.togglePalette($('#' + paletteId), $(this));
            });

            if( options && options.onColorChange ) {
              element.data('onColorChange', options.onColorChange);
            } else {
              element.data('onColorChange', function() {} );
            }

            element.on("change", function () {
                var color = element.val();
                if (color.match(/^#?([0-9A-F]{6}|[0-9A-F]{3})$/i)) {
                  color = $.fn.colorPicker.toHex(RegExp.$1);
                }
                else {
                  element.val(color);
                }

                element.css({
                  backgroundColor: color,
                  color: font_color(color)
                });
                $.fn.colorPicker.hidePalette();
            })
            .on("keydown", function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    event.stopPropagation();
                    element.change();
                }
                if (event.keyCode === 27) {
                    $.fn.colorPicker.hidePalette();
                }
            });

            element.val(defaultColor);

            cItterate++;
        });
    };

    /**
     * Extend colorPicker with... all our functionality.
    **/
    $.extend(true, $.fn.colorPicker, {
        /**
         * Return a Hex color, convert an RGB value and return Hex, or return false.
         *
         * Inspired by http://code.google.com/p/jquery-color-utils
        **/
        toHex : function (color) {
            // If we have a standard or shorthand Hex color, return that value.
            if (color.match(/[0-9A-F]{6}|[0-9A-F]{3}$/i)) {
                return (color.charAt(0) === "#") ? color : ("#" + color);

            // Alternatively, check for RGB color, then convert and return it as Hex.
            } else if (color.match(/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/)) {
                var c = ([parseInt(RegExp.$1, 10), parseInt(RegExp.$2, 10), parseInt(RegExp.$3, 10)]),
                    pad = function (str) {
                        if (str.length < 2) {
                            for (var i = 0, len = 2 - str.length; i < len; i++) {
                                str = '0' + str;
                            }
                        }

                        return str;
                    };

                if (c.length === 3) {
                    var r = pad(c[0].toString(16)),
                        g = pad(c[1].toString(16)),
                        b = pad(c[2].toString(16));

                    return '#' + r + g + b;
                }

            // Otherwise we wont do anything.
            } else {
                return false;

            }
        },

        /**
         * Check whether user clicked on the selector or owner.
        **/
        checkMouse : function (event, paletteId) {
            var selector = activePalette,
                selectorParent = $(event.target).parents("#" + selector.attr('id')).length;

            if (event.target === $(selector)[0] || event.target === selectorOwner[0] || selectorParent > 0) {
                return;
            }

            $.fn.colorPicker.hidePalette();
        },

        /**
         * Hide the color palette modal.
        **/
        hidePalette : function () {
            $(document).off("mousedown", $.fn.colorPicker.checkMouse);

            $('.colorPicker-palette').hide();
        },

        /**
         * Show the color palette modal.
        **/
        showPalette : function (palette) {
            var hexColor = selectorOwner.prev("input").val();

            palette.css({
                top: selectorOwner.offset().top - $(window).scrollTop() - 50,
                left: selectorOwner.offset().left + selectorOwner.outerWidth()
            });
            palette.show();

            $(document).on("mousedown", $.fn.colorPicker.checkMouse);
        },

        /**
         * Toggle visibility of the colorPicker palette.
        **/
        togglePalette : function (palette, origin) {
            // selectorOwner is the clicked .colorPicker-picker.
            if (origin) {
                selectorOwner = origin;
            }

            activePalette = palette;

            if (activePalette.is(':visible')) {
                $.fn.colorPicker.hidePalette();

            } else {
                $.fn.colorPicker.showPalette(palette);

            }
        },

        /**
         * Update the input with a newly selected color.
        **/
        changeColor : function (value) {

/*
          selectorOwner.css({
            backgroundColor: value,
            color: font_color(value)
          });
*/

          selectorOwner.val(value);
          selectorOwner.change();
          $.fn.colorPicker.hidePalette();
        },


        /**
         * Preview the input with a newly selected color.
        **/
/*
        previewColor : function (value) {
            selectorOwner.css("background-color", value);
        },
*/

        /**
         * Bind events to the color palette swatches.
        */
        bindPalette : function (paletteInput, element, color) {
            color = color ? color : $.fn.colorPicker.toHex(element.css("background-color"));

            element.on("click",function (ev) {
                    lastColor = color;

                    $.fn.colorPicker.changeColor(color);
                })
                .on("mouseover",function (ev) {
                    lastColor = paletteInput.val();
                     $(this).css("border-color", "#" + darken(color, 0.7));
                    
                    if (paletteInput.is("input")) {
                      paletteInput.val(color);
                    }
                    else {
                      paletteInput.text(color);
                      paletteInput.css({
                          top: element.offset().top - $(window).scrollTop() + (element.outerHeight()),
                          left: element.offset().left + (element.outerWidth())
                      });
                    }
                })
                .on("mouseout",function (ev) {
                     $(this).css("border-color", "#" + darken(color, 0.9));

                    if (paletteInput.is("input")) {

                      paletteInput.val(selectorOwner.css("background-color"));
                      paletteInput.val(lastColor);
                    }
                    else {
                      paletteInput.text(lastColor);
                    }
                });
        }
    });

    /**
     * Default colorPicker options.
     *
     * These are publibly available for global modification using a setting such as:
     *
     * $.fn.colorPicker.defaults.colors = ['151337', '111111']
     *
     * They can also be applied on a per-bound element basis like so:
     *
     * $('#element1').colorPicker({pickerDefault: 'efefef', transparency: true});
     * $('#element2').colorPicker({pickerDefault: '333333', colors: ['333333', '111111']});
     *
    **/
    $.fn.colorPicker.defaults = {
        // colorPicker default selected color.
        pickerDefault : "FFFFFF",

        // Default color set.
        colors : [
          {
            note: "1",
            color : [
                '000000', '993300', '333300', '000080', '333399', '333333', '800000', 'FF6600'
            ]
          },
          {
            not: "2",
            color: [
              '808000', '008000', '008080', '0000FF', '666699', '808080', 'FF0000', 'FF9900'
            ]
          },
          {
            not: "3",
            color: [
              '99CC00', '339966', '33CCCC', '3366FF', '800080', '999999', 'FF00FF', 'FFCC00'
            ]
          },
          {
            not: "4",
            color: [
              'FFFF00', '00FF00', '00FFFF', '00CCFF', '993366', 'C0C0C0', 'FF99CC', 'FFCC99'
            ]
          },
          {
            not: "5",
            color: [
              'FFFF99', 'CCFFFF', '99CCFF', 'FFFFFF'
            ]
          }
        ],

        // If we want to simply add more colors to the default set, use addColors.
        addColors : [],

        // Show hex field
        showHexField: true,
        
        // border color
        hoverBorderColor: "EEEEEE"
    };



  function darken(hexstr, scalefactor) {
    var r = scalefactor;
    var a, i;
    if (typeof(hexstr) != 'string') {
      return hexstr;
    }
    hexstr = hexstr.replace(/[^0-9a-f]+/ig, '');
    if (hexstr.length == 3) {
      a = hexstr.split('');
    } else if (hexstr.length == 6) {
      a = hexstr.match(/(\w{2})/g);
    } else {
      return hexstr;
    }
    for (i=0; i<a.length; i++) {
      if (a[i].length == 2)
        a[i] = parseInt(a[i], 16);
      else {
        a[i] = parseInt(a[i], 16);
        a[i] = a[i]*16 + a[i];
      }
    }
   
    var maxColor = parseInt('ff', 16);
   
    function _darken(a) {
      return a * r;
    }
   
    for (i=0; i<a.length; i++) {
      a[i] = _darken(a[i]);
      a[i] = Math.floor(a[i]).toString(16);
      if (a[i].length == 1) {
        a[i] = '0' + a[i];
      }
    }
    return a.join('');
  }
  
  function font_color(bgcolor) {
  
    if ( ! bgcolor.match(/[0-9A-F]{6}|[0-9A-F]{3}$/i)) {
      return "#bfbfbf";
    }

    var cols = bgcolor.split("#")[1],
		    fcol = "#555555";
    var R = 0, G = 0, B = 0;

    cols = cols || "#000000";
    if (cols.length == 3) {
    		R = parseInt(cols.charAt(0) + cols.charAt(0), 16);
    		G = parseInt(cols.charAt(1) + cols.charAt(0), 16);
    		B = parseInt(cols.charAt(2) + cols.charAt(0), 16);
    } else if (cols.length == 6) {
    		R = parseInt(cols.substr(0, 2), 16);
    		G = parseInt(cols.substr(2, 2), 16);
    		B = parseInt(cols.substr(4, 2), 16);
    }

    var brightness = R + G + B;
    if (brightness < 382) {
    		fcol = "#ffffff";
    }

    return fcol;
  }
  
  window.darken = darken;

})(jQuery);