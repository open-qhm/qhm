#
# Utility Functions

return unless QHM

QHM.type_is_array = Array.isArray || (value) -> return {}.toString.call(value) is '[object Array]'

QHM.unique_id = (length=8) ->
  id = ""
  id += Math.random().toString(36).substr(2) while id.length < length
  id.substr 0, length
