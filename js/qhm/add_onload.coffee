QHM.add_onload = (callback) ->
  old_onload = window.onload
  if typeof old_onload != "function"
    window.onload = callback
  else
    window.onload = ->
      if old_onload
        old_onload()
      callback()
