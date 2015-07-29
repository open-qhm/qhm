#
# Change target of link that link to external host
#

QHM.external_link = (exclude_host_name_regex, default_target) ->
  host_name = location.hostname
  host_name += "|#{exclude_host_name_regex}" if exclude_host_name_regex

  host_name = host_name.replace(/^www\./, '')
  regex = new RegExp('^(www\.|)' + host_name)

  for i, link of document.links
    continue unless link.href?

    href = link.host
    # get Attribute
    target = link.getAttribute 'data-target'

    if target == 'nowin'
      # do nothing
    else if target != null
      link.target = target
    else if href.match(regex) or href.length == 0
      # do nothing
    else
      link.target = default_target

QHM.add_onload ->
  if QHM.window_open
    QHM.external_link QHM.exclude_host_name_regex, QHM.default_target
