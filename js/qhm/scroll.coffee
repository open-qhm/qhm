QHM.scroll = (target, delay) ->
  delay = delay || 300
  scrollTop = $(target).offset().top
  $("html, body").animate({scrollTop: scrollTop}, delay)
