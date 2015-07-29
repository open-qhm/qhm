#
# View of Contents Plugin
#
# Copyright (c) 2014 hokuken
# http://hokuken.com

return unless jQuery? and QHM?.enable_toc?

$ = jQuery

# Node of Table of Contents
class TocNode

  constructor: (options) ->
    {
      parent: @parent,
      children: @children,
      level: @level,
      depth: @depth,
      title: @title,
      id:    @id
    } = $.extend {
      parent: null,
      children: [],
      level: 0,
      depth: 0,
      title: "",
      id: "",
    }, options

  find_parent: (level) ->
    if ! @parent
      return this
    else if @parent.level < level
      return @parent
    else
      return @parent.find_parent level


  count_parent: (cnt) ->
    cnt = cnt || 0
    if ! @parent
      cnt
    else
      @parent.count_parent cnt+1

  to_s: ->
    str = ""

    if @children.length == 0
      cnt = @count_parent()
      str += " " * cnt
      str += @level.toString() + "\n"
    else
      cnt = @count_parent()
      str += " " * cnt
      str += @level.toString() + "\n"
      for i in [0..@children.length-1]
        str += @children[i].to_s()

  to_html: (flat) ->
    str = ""
    flat = flat || false

    if flat && !@parent
      str += '<ul>'

    if (@children.length == 0)
      cnt = @count_parent()
      str += '<li><a href="#' + @id + '">' + @title + '</a></li>\n'
    else
      cnt = @count_parent()
      unless flat
        str += '<ul>'
      for i in [0..@children.length-1]
        str += @children[i].to_html flat
      unless flat
        str += '</ul>\n'

      if @parent
        str = '<li><a href="#' + @id + '">' + @title + '</a>' + str + '</li>\n'

    if flat && !@parent
      str += '</ul>'

    str

# Scan headings and Build Table of Contents
class Toc

  @heading_id_prefix = "toc_element_"
  @headings_count = 0

  @options = {
    ignoreClass: 'no-toc',
    elementTitleContent: 'text',
    elementUseTitleAttr: true,
    elementIsNotDescendantOf: false,
    customClass: ""
  }

  constructor: (element, options) ->
    @$element = $ element
    @options = $.extend({}, Toc.options, options)
    @root = new TocNode
    @heading_selector = options.selector
    @target_selector = options.target
    @ignore_class = options.ignore || Toc.options.ignoreClass

    if QHM.type_is_array @target_selector
      for selector in @target_selector
        if $(selector).length > 0
          @target_selector = selector
          break
    @$headings = $(@target_selector)
      .find(@heading_selector).not(".#{@ignore_class}").get()

    if (@$headings.length > 0)
      @scan()
      @$element.html @to_html()
      if @options.customClass?
        @$element.children("ul").addClass @options.customClass

  scan: ->
    current_node = @root
    current_level = 0

    for heading in @$headings
      $heading = $ heading
      continue if @options.elementIsNotDescendantOf and
        $heading.closest(@options.elementIsNotDescendantOf).length > 0

      level = $heading.data "level"
      if !level
        level = 1
        if heading.tagName.match /^h(\d)$/i
          level = parseInt RegExp.$1, 10

      id = $heading.attr("id") || false

      if @options.elementUseTitleAttr and
        ($heading.attr("title")?.length > 0 or
         $heading.attr("data-title")?.length > 0)
        title = $heading.attr("data-title") || $heading.attr "title"
      else
        if @options.elementTitleContent is "html"
          title = $heading.html()
        else
          title = $heading.text()
      title = title.replace /^\s+|\s+$/g, ""

      if title.length == 0
        continue

      node = null

      unless id
        Toc.headings_count++
        id = "#{Toc.heading_id_prefix}#{QHM.unique_id()}#{Toc.headings_count}"
        $heading.attr "id", id

      break if level < @options.level

      if current_level < level
        current_level = level
        node = new TocNode parent: current_node, level: level, title: title, id: id
        current_node.children.push node
        current_node = node
      else if current_level == level
        # compare parent node and insert same level
        node = new TocNode parent: current_node.parent, level: level, title: title, id: id
        current_node.parent?.children.push node
        current_node = node
      else
        current_level = level
        parent = current_node.find_parent level
        node = new TocNode parent: parent, level: level, title: title, id: id
        parent.children.push node
        current_node = node

  to_html: ->
    html = @root.to_html @options.flat

    if @options.title.length > 0
      title_html = $('<div><h2></h2></div>')
        .children().addClass(@ignore_class).text(@options.title)
        .end().html()
      html = "#{title_html}\n#{html}"

    return html

# Add jQuery plugin: qhmtoc
$.fn.qhmtoc = (options) ->
  this.each ->
    options = $.extend {}, options, $(this).data()

    if options.qhmcontents?
      $(this).data "toc"
    else
      toc = new Toc this, options
      $(this).data "toc", toc

# call qhmtoc jQuery plugin when load
$ ->
  $toc = $(".plugin-contents").qhmtoc()

  #smooth scroll
  if history?.pushState?
    $toc.on "click", "a", (e) ->
      target = $(this).attr "href"
      return if target.substr(0, 1) != "#"
      e.preventDefault()
      QHM.scroll target
      history.pushState "", "", target
