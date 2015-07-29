(function() {
  QHM.add_onload = function(callback) {
    var old_onload;
    old_onload = window.onload;
    if (typeof old_onload !== "function") {
      return window.onload = callback;
    } else {
      return window.onload = function() {
        if (old_onload) {
          old_onload();
        }
        return callback();
      };
    }
  };

}).call(this);

(function() {
  var $, Toc, TocNode;

  if (!((typeof jQuery !== "undefined" && jQuery !== null) && ((typeof QHM !== "undefined" && QHM !== null ? QHM.enable_toc : void 0) != null))) {
    return;
  }

  $ = jQuery;

  TocNode = (function() {
    function TocNode(options) {
      var _ref;
      _ref = $.extend({
        parent: null,
        children: [],
        level: 0,
        depth: 0,
        title: "",
        id: ""
      }, options), this.parent = _ref.parent, this.children = _ref.children, this.level = _ref.level, this.depth = _ref.depth, this.title = _ref.title, this.id = _ref.id;
    }

    TocNode.prototype.find_parent = function(level) {
      if (!this.parent) {
        return this;
      } else if (this.parent.level < level) {
        return this.parent;
      } else {
        return this.parent.find_parent(level);
      }
    };

    TocNode.prototype.count_parent = function(cnt) {
      cnt = cnt || 0;
      if (!this.parent) {
        return cnt;
      } else {
        return this.parent.count_parent(cnt + 1);
      }
    };

    TocNode.prototype.to_s = function() {
      var cnt, i, str, _i, _ref, _results;
      str = "";
      if (this.children.length === 0) {
        cnt = this.count_parent();
        str += " " * cnt;
        return str += this.level.toString() + "\n";
      } else {
        cnt = this.count_parent();
        str += " " * cnt;
        str += this.level.toString() + "\n";
        _results = [];
        for (i = _i = 0, _ref = this.children.length - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; i = 0 <= _ref ? ++_i : --_i) {
          _results.push(str += this.children[i].to_s());
        }
        return _results;
      }
    };

    TocNode.prototype.to_html = function(flat) {
      var cnt, i, str, _i, _ref;
      str = "";
      flat = flat || false;
      if (flat && !this.parent) {
        str += '<ul>';
      }
      if (this.children.length === 0) {
        cnt = this.count_parent();
        str += '<li><a href="#' + this.id + '">' + this.title + '</a></li>\n';
      } else {
        cnt = this.count_parent();
        if (!flat) {
          str += '<ul>';
        }
        for (i = _i = 0, _ref = this.children.length - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; i = 0 <= _ref ? ++_i : --_i) {
          str += this.children[i].to_html(flat);
        }
        if (!flat) {
          str += '</ul>\n';
        }
        if (this.parent) {
          str = '<li><a href="#' + this.id + '">' + this.title + '</a>' + str + '</li>\n';
        }
      }
      if (flat && !this.parent) {
        str += '</ul>';
      }
      return str;
    };

    return TocNode;

  })();

  Toc = (function() {
    Toc.heading_id_prefix = "toc_element_";

    Toc.headings_count = 0;

    Toc.options = {
      ignoreClass: 'no-toc',
      elementTitleContent: 'text',
      elementUseTitleAttr: true,
      elementIsNotDescendantOf: false,
      customClass: ""
    };

    function Toc(element, options) {
      var selector, _i, _len, _ref;
      this.$element = $(element);
      this.options = $.extend({}, Toc.options, options);
      this.root = new TocNode;
      this.heading_selector = options.selector;
      this.target_selector = options.target;
      this.ignore_class = options.ignore || Toc.options.ignoreClass;
      if (QHM.type_is_array(this.target_selector)) {
        _ref = this.target_selector;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          selector = _ref[_i];
          if ($(selector).length > 0) {
            this.target_selector = selector;
            break;
          }
        }
      }
      this.$headings = $(this.target_selector).find(this.heading_selector).not("." + this.ignore_class).get();
      if (this.$headings.length > 0) {
        this.scan();
        this.$element.html(this.to_html());
        if (this.options.customClass != null) {
          this.$element.children("ul").addClass(this.options.customClass);
        }
      }
    }

    Toc.prototype.scan = function() {
      var $heading, current_level, current_node, heading, id, level, node, parent, title, _i, _len, _ref, _ref1, _ref2, _ref3, _results;
      current_node = this.root;
      current_level = 0;
      _ref = this.$headings;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        heading = _ref[_i];
        $heading = $(heading);
        if (this.options.elementIsNotDescendantOf && $heading.closest(this.options.elementIsNotDescendantOf).length > 0) {
          continue;
        }
        level = $heading.data("level");
        if (!level) {
          level = 1;
          if (heading.tagName.match(/^h(\d)$/i)) {
            level = parseInt(RegExp.$1, 10);
          }
        }
        id = $heading.attr("id") || false;
        if (this.options.elementUseTitleAttr && (((_ref1 = $heading.attr("title")) != null ? _ref1.length : void 0) > 0 || ((_ref2 = $heading.attr("data-title")) != null ? _ref2.length : void 0) > 0)) {
          title = $heading.attr("data-title") || $heading.attr("title");
        } else {
          if (this.options.elementTitleContent === "html") {
            title = $heading.html();
          } else {
            title = $heading.text();
          }
        }
        title = title.replace(/^\s+|\s+$/g, "");
        if (title.length === 0) {
          continue;
        }
        node = null;
        if (!id) {
          Toc.headings_count++;
          id = "" + Toc.heading_id_prefix + (QHM.unique_id()) + Toc.headings_count;
          $heading.attr("id", id);
        }
        if (level < this.options.level) {
          break;
        }
        if (current_level < level) {
          current_level = level;
          node = new TocNode({
            parent: current_node,
            level: level,
            title: title,
            id: id
          });
          current_node.children.push(node);
          _results.push(current_node = node);
        } else if (current_level === level) {
          node = new TocNode({
            parent: current_node.parent,
            level: level,
            title: title,
            id: id
          });
          if ((_ref3 = current_node.parent) != null) {
            _ref3.children.push(node);
          }
          _results.push(current_node = node);
        } else {
          current_level = level;
          parent = current_node.find_parent(level);
          node = new TocNode({
            parent: parent,
            level: level,
            title: title,
            id: id
          });
          parent.children.push(node);
          _results.push(current_node = node);
        }
      }
      return _results;
    };

    Toc.prototype.to_html = function() {
      var html, title_html;
      html = this.root.to_html(this.options.flat);
      if (this.options.title.length > 0) {
        title_html = $('<div><h2></h2></div>').children().addClass(this.ignore_class).text(this.options.title).end().html();
        html = "" + title_html + "\n" + html;
      }
      return html;
    };

    return Toc;

  })();

  $.fn.qhmtoc = function(options) {
    return this.each(function() {
      var toc;
      options = $.extend({}, options, $(this).data());
      if (options.qhmcontents != null) {
        return $(this).data("toc");
      } else {
        toc = new Toc(this, options);
        return $(this).data("toc", toc);
      }
    });
  };

  $(function() {
    var $toc;
    $toc = $(".plugin-contents").qhmtoc();
    if ((typeof history !== "undefined" && history !== null ? history.pushState : void 0) != null) {
      return $toc.on("click", "a", function(e) {
        var target;
        target = $(this).attr("href");
        if (target.substr(0, 1) !== "#") {
          return;
        }
        e.preventDefault();
        QHM.scroll(target);
        return history.pushState("", "", target);
      });
    }
  });

}).call(this);

(function() {
  QHM.external_link = function(exclude_host_name_regex, default_target) {
    var host_name, href, i, link, regex, target, _ref, _results;
    host_name = location.hostname;
    if (exclude_host_name_regex) {
      host_name += "|" + exclude_host_name_regex;
    }
    host_name = host_name.replace(/^www\./, '');
    regex = new RegExp('^(www\.|)' + host_name);
    _ref = document.links;
    _results = [];
    for (i in _ref) {
      link = _ref[i];
      if (link.href == null) {
        continue;
      }
      href = link.host;
      target = link.getAttribute('data-target');
      if (target === 'nowin') {

      } else if (target !== null) {
        _results.push(link.target = target);
      } else if (href.match(regex) || href.length === 0) {

      } else {
        _results.push(link.target = default_target);
      }
    }
    return _results;
  };

  QHM.add_onload(function() {
    if (QHM.window_open) {
      return QHM.external_link(QHM.exclude_host_name_regex, QHM.default_target);
    }
  });

}).call(this);

(function() {
  QHM.scroll = function(target, delay) {
    var scrollTop;
    delay = delay || 300;
    scrollTop = $(target).offset().top;
    return $("html, body").animate({
      scrollTop: scrollTop
    }, delay);
  };

}).call(this);

(function() {
  if (!QHM) {
    return;
  }

  QHM.type_is_array = Array.isArray || function(value) {
    return {}.toString.call(value) === '[object Array]';
  };

  QHM.unique_id = function(length) {
    var id;
    if (length == null) {
      length = 8;
    }
    id = "";
    while (id.length < length) {
      id += Math.random().toString(36).substr(2);
    }
    return id.substr(0, length);
  };

}).call(this);
