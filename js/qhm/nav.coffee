return unless jQuery?

$ = jQuery

$ ->
  $('#navigator.haik-nav ul.list1').each ->
    $(@).removeClass('list1').addClass('qhm-bs-nav nav navbar-nav')
