(() => {
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __commonJS = (cb, mod) => function __require() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };

  // js/clickpad2/jquery.clickpad2.js
  var require_jquery_clickpad2 = __commonJS({
    "js/clickpad2/jquery.clickpad2.js"(exports, module) {
      $(document).ready(function() {
        var browser = 2;
        var $textarea = $("textarea[id]");
        var id2;
        if ($textarea.length > 0) {
          id2 = $textarea.eq(0).attr("id");
          if (document.getElementById(id2).setSelectionRange) {
          } else if (document.selection.createRange) {
            browser = 1;
          }
          $.clickpad.browser = browser;
        }
      });
      (function() {
        if (!jQuery.clickpad)
          jQuery.clickpad = {};
        jQuery.clickpad.total = 0;
        jQuery.clickpad.b_total = 0;
        jQuery.clickpad.bs_total = 0;
        jQuery.clickpad.buttonData = {
          "header": {
            caption: "\u898B\u51FA\u3057",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat 0 0",
            func: "cpInsert",
            value: "\n* \u898B\u51FA\u3057\uFF11\n"
          },
          "contents": {
            caption: "\u76EE\u6B21",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -47px 0",
            func: "cpInsert",
            value: "\n#contents\n"
          },
          "link": {
            caption: "\u30EA\u30F3\u30AF",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -94px 0",
            func: "cpDialog",
            value: [
              [
                { msg: "\u30EA\u30F3\u30AF\u540D\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002", option: {
                  type: "text",
                  useSelection: true
                } },
                { msg: "\u30EA\u30F3\u30AF\u5148\uFF08\u30DA\u30FC\u30B8\u540D, URL\uFF09\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002", option: {
                  type: "text"
                } }
              ],
              "[[${1}",
              ">${2}]]"
            ]
          },
          "htmllink": {
            // link のクローン
            caption: "\u30EA\u30F3\u30AF",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -94px 0",
            func: "cpDialog",
            value: [["\u30EA\u30F3\u30AF\u540D\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002", { msg: "URL \u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044", option: { defval: "http://" } }], "[[${1}>${2}]]"]
          },
          "title": {
            caption: "\u30BF\u30A4\u30C8\u30EB\u5909\u66F4",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -141px 0",
            func: "cpInsert",
            value: "\nTITLE:\u3053\u3053\u306B\u30BF\u30A4\u30C8\u30EB\u3092\u5165\u308C\u308B\n"
          },
          "counter": {
            caption: "\u30A2\u30AF\u30BB\u30B9\u30AB\u30A6\u30F3\u30BF\u30FC",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -188px 0",
            func: "cpInsert",
            value: "&deco(gray,12){a:&counter(total); t:&counter(today); y:&counter(yesterday);};"
          },
          "comment": {
            caption: "\u30B3\u30E1\u30F3\u30C8\u6A5F\u80FD",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -235px 0",
            func: "cpInsert",
            value: "\n#comment2\n"
          },
          "ul": {
            caption: "\u7B87\u6761\u66F8\u304D",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -282px 0",
            func: "cpInsert",
            value: "\n- \u7B87\u6761\u66F8\u304D1\n-- \u7B87\u6761\u66F8\u304D2\n--- \u7B87\u6761\u66F8\u304D3\n- \u7B87\u6761\u66F8\u304D1\n"
          },
          "ol": {
            caption: "\u756A\u53F7\u4ED8\u304D\u7B87\u6761\u66F8\u304D",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -310px 0",
            func: "cpInsert",
            value: "\n+ \u7B87\u6761\u66F8\u304D1\n+ \u7B87\u6761\u66F8\u304D2\n+ \u7B87\u6761\u66F8\u304D3\n"
          },
          "attach": {
            caption: "\u6DFB\u4ED8\uFF08\u753B\u50CF\u306A\u3069\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -338px 0",
            func: "cpInsert",
            value: "&show(,,\u753B\u50CF\u306E\u8AAC\u660E);"
          },
          "br": {
            caption: "\u6539\u884C",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -366px 0",
            func: "cpInsert",
            value: "&br;"
          },
          "b": {
            caption: "\u592A\u5B57",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -394px 0",
            func: "cpEnclose",
            value: ["''", "''"]
          },
          "u": {
            caption: "\u4E0B\u7DDA",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -450px 0",
            func: "cpEnclose",
            value: ["%%%", "%%%"]
          },
          "i": {
            caption: "\u659C\u4F53",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -422px 0",
            func: "cpEnclose",
            value: ["'''", "'''"]
          },
          "handline": {
            caption: "\u624B\u66F8\u304D\u4E0B\u7DDA",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -450px 0",
            func: "cpEnclose",
            value: ["##", "##"]
          },
          "size": {
            caption: "\u6587\u5B57\u30B5\u30A4\u30BA",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -478px 0",
            func: "cpDialog",
            value: ["\u6587\u5B57\u30B5\u30A4\u30BA\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002(\u5C11\u3057\u5927\u304D\u304F:18\u3001\u5C0F\u3055\u304F:12)", "&size(${1}){", "};"]
          },
          "sizeD": {
            //deco
            caption: "\u6587\u5B57\u30B5\u30A4\u30BA",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -478px 0",
            func: "cpDialog",
            value: ["\u6587\u5B57\u30B5\u30A4\u30BA\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002(\u5C11\u3057\u5927\u304D\u304F:18\u3001\u5C0F\u3055\u304F:12)", "&deco(${1}){", "};"]
          },
          "sizeM": {
            caption: "\u6587\u5B57\u30B5\u30A4\u30BA",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -478px 0",
            func: "cpDialog",
            value: [
              [
                {
                  msg: "\u6587\u5B57\u30B5\u30A4\u30BA",
                  option: {
                    type: "select",
                    values: [
                      { key: "xx-small", value: "xx-small" },
                      { key: "x-small", value: "x-small" },
                      { key: "small", value: "small" },
                      { key: "medium", value: "medium", selected: true },
                      { key: "large", value: "large" },
                      { key: "x-large", value: "x-large" },
                      { key: "xx-large", value: "xx-large" }
                    ]
                  }
                }
              ],
              "&size(${1}){",
              "};"
            ]
          },
          "penYellow": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u9EC4\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -506px 0",
            func: "cpEnclose",
            value: ["&color(,yellow){''", "''};"]
          },
          "penYellowD": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u9EC4\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -506px 0",
            func: "cpEnclose",
            value: ["&deco(b,,yellow){", "};"]
          },
          "penRed": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u8D64\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -534px 0",
            func: "cpEnclose",
            value: ["&color(,pink){''", "''};"]
          },
          "penRedD": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u8D64\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -534px 0",
            func: "cpEnclose",
            value: ["&deco(b,,pink){", "};"]
          },
          "penBlue": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u9752\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -562px 0",
            func: "cpEnclose",
            value: ["&color(,paleturquoise){''", "''};"]
          },
          "penBlueD": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u9752\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -562px 0",
            func: "cpEnclose",
            value: ["&deco(b,,paleturquoise){", "};"]
          },
          "penGreen": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u7DD1\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -590px 0",
            func: "cpEnclose",
            value: ["&color(,palegreen){''", "''};"]
          },
          "penGreenD": {
            caption: "\u86CD\u5149\u30DA\u30F3\uFF08\u7DD1\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -590px 0",
            func: "cpEnclose",
            value: ["&deco(b,,palegreen){", "};"]
          },
          "left": {
            caption: "\u5DE6\u63C3\u3048",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -618px 0",
            func: "cpInsert",
            value: "LEFT:"
          },
          "center": {
            caption: "\u4E2D\u592E\u63C3\u3048",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -646px 0",
            func: "cpInsert",
            value: "CENTER:"
          },
          "right": {
            caption: "\u53F3\u63C3\u3048",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -674px 0",
            func: "cpInsert",
            value: "RIGHT:"
          },
          "table": {
            caption: "\u8868",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -702px 0",
            func: "cpInsert",
            value: "\n|~\u9805\u76EE\u540D1 |~\u9805\u76EE\u540D2 |~\u9805\u76EE\u540D3 |\n| \u9805\u76EE1 | \u9805\u76EE2 | \u9805\u76EE3 |\n"
          },
          "HTML": {
            caption: "HTML\u30BF\u30B0",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -730px 0",
            func: "cpInsert",
            value: "\n#html{{\n(\u3053\u3053\u306BHTML\u30BF\u30B0\u3092\u633F\u5165)\n}}\n"
          },
          "stylebox": {
            caption: "\u67A0",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -758px 0",
            func: "cpInsert",
            value: "\n#style(class=bluebox2){{\n(\u3053\u3053\u306B\u5185\u5BB9\u3092\u66F8\u304F)\n}}\n"
          },
          "styleboxp": {
            caption: "\u67A0",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -758px 0",
            func: "cpDialog",
            value: [
              [
                {
                  msg: "\u67A0\u306E\u8272",
                  option: {
                    type: "radio",
                    values: [
                      { value: "blue", label: '<span style="color:blue;">\u9752</span>', checked: true },
                      { value: "purple", label: '<span style="color:purple;">\u7D2B</span>' },
                      { value: "red", label: '<span style="color:red;">\u8D64</span>' },
                      { value: "brown", label: '<span style="color:brown;">\u8336</span>' },
                      { value: "orange", label: '<span style="color:orange;">\u6A59</span>', br: true },
                      { value: "yellow", label: '<span style="color:#F3DF81;">\u9EC4</span>' },
                      { value: "green", label: '<span style="color:green;">\u7DD1</span>' },
                      { value: "black", label: '<span style="color:black;">\u9ED2</span>' },
                      { value: "gray", label: '<span style="color:gray;">\u7070</span>' }
                    ]
                  }
                },
                { msg: "\u7DDA\u306E\u7A2E\u985E\uFF08\u76F4\u7DDA\u3001\u7834\u7DDA\uFF09", option: { type: "select", values: [{ key: "\u2500\u2500\u2500\u2500", value: "s" }, { key: "-------", value: "d" }] } },
                { msg: "\u80CC\u666F\u8272", option: { type: "radio", values: [{ label: "\u540C\u7CFB\u8272", value: "s", checked: true }, { label: "\u767D\u8272", value: "w" }] } },
                { msg: "\u67A0\u306E\u30B5\u30A4\u30BA", option: { type: "radio", values: [{ label: "100%", value: "l" }, { label: "80%", value: "m", checked: true }, { label: "60%", value: "s" }] } }
              ],
              "\n#style(class=box_${1}_${2}${3}${4}){{\n\uFF08\u3053\u3053\u306B\u5185\u5BB9\u3092\u66F8\u304F\uFF09\n}}\n"
            ]
          },
          "onepage": {
            caption: "\u30BB\u30FC\u30EB\u30B9\u30EC\u30BF\u30FC\u578B\u30C7\u30B6\u30A4\u30F3",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -786px 0",
            func: "cpDialog",
            value: [
              [
                { msg: "\u898B\u51FA\u3057\u8272", option: {
                  type: "text",
                  defval: "red",
                  inputWidthRatio: 0.3,
                  css: { clear: "both" }
                } },
                { msg: "\u898B\u51FA\u3057\u30D5\u30A9\u30F3\u30C8", option: { type: "radio", values: [{ label: "\u30B4\u30B7\u30C3\u30AF", value: "g", checked: true }, { label: "\u660E\u671D", value: "m" }] } },
                { msg: "\u672C\u6587\u30D5\u30A9\u30F3\u30C8", option: { type: "radio", values: [{ label: "\u30B4\u30B7\u30C3\u30AF", value: "g", checked: true }, { label: "\u660E\u671D", value: "m" }] } },
                { msg: "\u80CC\u666F\u8272", option: {
                  type: "text",
                  defval: "gray",
                  inputWidthRatio: 0.3,
                  css: { clear: "both" }
                } }
              ],
              "\nKILLERPAGE2:${1}${2}${3},${4}\n"
            ]
          },
          "bullet": {
            caption: "\u30D6\u30EC\u30C3\u30C8",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -814px 0",
            func: "cpInsert",
            value: "\n:>>|\u3053\u3053\u306B\u30D6\u30EC\u30C3\u30C8\u3092\u5165\u308C\u308B\n"
          },
          "check": {
            caption: "\u30EC\u6CE8\u76EE",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -842px 0",
            func: "cpInsert",
            value: "&check;"
          },
          "strike": {
            caption: "\u53D6\u308A\u6D88\u3057\u7DDA",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -870px 0",
            func: "cpEnclose",
            value: ["%%", "%%"]
          },
          "whiteRed": {
            caption: "\u767D\u629C\u304D\u6587\u5B57\uFF08\u8D64\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -898px 0",
            func: "cpEnclose",
            value: ["&color(white,red){", "};"]
          },
          "whiteRedD": {
            caption: "\u767D\u629C\u304D\u6587\u5B57\uFF08\u8D64\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -898px 0",
            func: "cpEnclose",
            value: ["&deco(white,red){", "};"]
          },
          "whiteBlack": {
            caption: "\u767D\u629C\u304D\u6587\u5B57\uFF08\u9ED2\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -926px 0",
            func: "cpEnclose",
            value: ["&color(white,black){", "};"]
          },
          "whiteBlackD": {
            caption: "\u767D\u629C\u304D\u6587\u5B57\uFF08\u9ED2\uFF09",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -926px 0",
            func: "cpEnclose",
            value: ["&deco(white,black){", "};"]
          },
          "deco": {
            caption: "\u6587\u5B57\u88C5\u98FE",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -1104px 0",
            func: "cpDialog",
            value: [["\u88C5\u98FE\u30AA\u30D7\u30B7\u30E7\u30F3\u3092\u30AB\u30F3\u30DE\u533A\u5207\u308A\u3067\u66F8\u3044\u3066\u304F\u3060\u3055\u3044"], "&deco(${1}){", "};"]
          },
          "decop": {
            //deco ダイアログ
            caption: "\u88C5\u98FE",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -1104px 0",
            func: "cpDialog",
            value: [
              [
                { msg: "<b>\u592A\u5B57</b>", option: {
                  type: "checkbox",
                  value: "b,",
                  css: { float: "left", width: "30%" }
                } },
                { msg: "<i>\u659C\u4F53</i>", option: {
                  type: "checkbox",
                  value: "i,",
                  css: { float: "left", width: "30%" }
                } },
                { msg: "<u>\u4E0B\u7DDA</u>", option: {
                  type: "checkbox",
                  value: "u,",
                  css: { float: "left", width: "auto", marginRight: 10 }
                } },
                { msg: '<span style="color:#2C48BF">\u6587\u5B57\u8272</span>\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044', option: {
                  type: "text",
                  inputWidthRatio: 0.3,
                  css: { clear: "both" }
                } },
                { msg: '<span style="background-color:#2C48BF;color:#fff">\u80CC\u666F\u8272</span>\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044', option: {
                  type: "text",
                  inputWidthRatio: 0.3
                } },
                { msg: '\u6587\u5B57\u30B5\u30A4\u30BA<span style="font-size:11px">\uFF08\u6570\u5024\u3001\u5358\u4F4D\u4ED8\u304D\u6307\u5B9A\uFF08ex:2em\uFF09\u3001small\u3001large\u306A\u3069\uFF09</span>', option: {
                  type: "text",
                  inputWidthRatio: 0.3
                } }
              ],
              "&deco(${1}${2}${3}${4},${5},${6}){",
              "};"
            ]
          },
          "plugins": {
            caption: "\u305D\u306E\u4ED6\u306E\u6A5F\u80FD",
            width: 102,
            height: 26,
            background: "url(image/hokuken/otherplugin.png) no-repeat 0 0",
            func: "cpEval",
            value: "otherplugin()"
          },
          // !qhm-haik buttons
          "haikHeader": {
            caption: "\u898B\u51FA\u3057",
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpInsert",
            value: "\n* \u898B\u51FA\u3057\uFF11\n"
          },
          "haikLink": {
            caption: '<i class="glyphicon glyphicon-link"></i><span class="sr-only">\u30EA\u30F3\u30AF</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpDialog",
            value: [
              [
                { msg: "\u30EA\u30F3\u30AF\u540D\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002", option: {
                  type: "text",
                  useSelection: true
                } },
                { msg: "\u30EA\u30F3\u30AF\u5148\uFF08\u30DA\u30FC\u30B8\u540D, URL\uFF09\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002", option: {
                  type: "text"
                } }
              ],
              "[[${1}",
              ">${2}]]"
            ]
          },
          "haikBr": {
            caption: "\u6539\u884C",
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpInsert",
            value: "&br;"
          },
          "haikB": {
            caption: '<span style="font-weight:bold">\u592A\u5B57</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpEnclose",
            value: ["''", "''"]
          },
          "haikHandline": {
            caption: '<span style="background-color: yellow">\u5F37\u8ABF</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpEnclose",
            value: ["##", "##"]
          },
          "haikDecop": {
            //deco ダイアログ
            caption: "\u88C5\u98FE",
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpDialog",
            value: [
              [
                { msg: "<b>\u592A\u5B57</b>", option: {
                  type: "checkbox",
                  value: "b,",
                  css: { float: "left", width: "30%" }
                } },
                { msg: "<i>\u659C\u4F53</i>", option: {
                  type: "checkbox",
                  value: "i,",
                  css: { float: "left", width: "30%" }
                } },
                { msg: "<u>\u4E0B\u7DDA</u>", option: {
                  type: "checkbox",
                  value: "u,",
                  css: { float: "left", width: "auto", marginRight: 10 }
                } },
                { msg: '<span style="color:#2C48BF">\u6587\u5B57\u8272</span>\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044', option: {
                  type: "text",
                  inputWidthRatio: 0.3,
                  css: { clear: "both" }
                } },
                { msg: '<span style="background-color:#2C48BF;color:#fff">\u80CC\u666F\u8272</span>\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044', option: {
                  type: "text",
                  inputWidthRatio: 0.3
                } },
                { msg: '\u6587\u5B57\u30B5\u30A4\u30BA<span style="font-size:11px">\uFF08\u6570\u5024\u3001\u5358\u4F4D\u4ED8\u304D\u6307\u5B9A\uFF08ex:2em\uFF09\u3001small\u3001large\u306A\u3069\uFF09</span>', option: {
                  type: "text",
                  inputWidthRatio: 0.3
                } }
              ],
              "&deco(${1}${2}${3}${4},${5},${6}){",
              "};"
            ]
          },
          "haikUl": {
            caption: '<i class="glyphicon glyphicon-list"></i><span class="sr-only">\u7B87\u6761\u66F8\u304D</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left",
            func: "cpInsert",
            value: "\n- \u7B87\u6761\u66F8\u304D1\n-- \u7B87\u6761\u66F8\u304D2\n--- \u7B87\u6761\u66F8\u304D3\n- \u7B87\u6761\u66F8\u304D1\n"
          },
          "haikHr": {
            caption: "\u6C34\u5E73\u7DDA",
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpInsert",
            value: "\n----\n"
          },
          "haikAttach": {
            caption: '<i class="glyphicon glyphicon-picture"></i><span class="sr-only">\u6DFB\u4ED8</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left qhm-btn-separate-right",
            func: "cpInsert",
            value: "&show(,,\u753B\u50CF\u306E\u8AAC\u660E);"
          },
          "haikIcon": {
            caption: '<i class="fas fa-icons"></i><span class="sr-only">\u30A2\u30A4\u30B3\u30F3</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpDialog",
            value: [
              [
                { msg: '\u30A2\u30A4\u30B3\u30F3\u7528HTML\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002<br /><strong>\u5BFE\u5FDC\u30A2\u30A4\u30B3\u30F3</strong><ul style="margin-bottom: 10px"><li><a href="https://fonts.google.com/icons?icon.set=Material+Icons" target="_blank">Google Material Icons</a></li><li><a href="https://fonts.google.com/icons" target="_blank">Google Material Symbols</a></li></ul>', option: {
                  type: "text",
                  inputWidthRatio: 0.9,
                  css: { clear: "both" }
                } },
                { msg: '<span style="color:#2C48BF">\u6587\u5B57\u8272</span>\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044', option: {
                  type: "text",
                  inputWidthRatio: 0.3,
                  css: { clear: "both" }
                } },
                { msg: '\u6587\u5B57\u30B5\u30A4\u30BA<span style="font-size:11px">\uFF08\u6570\u5024\u3001\u5358\u4F4D\u4ED8\u304D\u6307\u5B9A\uFF08ex:2em\uFF09\u3001small\u3001large\u306A\u3069\uFF09</span>', option: {
                  type: "text",
                  inputWidthRatio: 0.3
                } }
              ],
              "&deco(${2},${3}){&icon(${1});};"
            ]
          },
          "haikOl": {
            caption: "\u756A\u53F7\u4ED8\u304D\u7B87\u6761\u66F8\u304D",
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpInsert",
            value: "\n+ \u7B87\u6761\u66F8\u304D1\n+ \u7B87\u6761\u66F8\u304D2\n+ \u7B87\u6761\u66F8\u304D3\n"
          },
          "haikLeft": {
            caption: '<i class="glyphicon glyphicon-align-left"></i><span class="sr-only">\u5DE6\u63C3\u3048</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-right",
            func: "cpInsert",
            value: "LEFT:"
          },
          "haikCenter": {
            caption: '<i class="glyphicon glyphicon-align-center"></i><span class="sr-only">\u4E2D\u592E\u63C3\u3048</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm",
            func: "cpInsert",
            value: "CENTER:"
          },
          "haikRight": {
            caption: '<i class="glyphicon glyphicon-align-right"></i><span class="sr-only">\u53F3\u63C3\u3048</span>',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left",
            func: "cpInsert",
            value: "RIGHT:"
          },
          "haikColors": {
            caption: '<i class="glyphicon glyphicon-th"></i> \u8272',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-right",
            func: "cpDialog",
            value: [
              function() {
                if (typeof jQuery.clickpad.haikColors === "undefined") {
                  var date = /* @__PURE__ */ new Date();
                  if (typeof localStorage.qhmHaikColors === "undefined" || typeof localStorage.qhmHaikColorsUpdated !== "undefined" && localStorage.qhmHaikColorsUpdated != date.getMonth() + "/" + date.getDate()) {
                    jQuery.clickpad.haikColors = $.ajax({
                      url: "plugin/skin_customizer/colors.json",
                      async: false,
                      dataType: "json"
                    }).responseJSON;
                    localStorage.qhmHaikColors = JSON.stringify(jQuery.clickpad.haikColors);
                    localStorage.qhmHaikColorsUpdated = date.getMonth() + "/" + date.getDate();
                  } else {
                    jQuery.clickpad.haikColors = JSON.parse(localStorage.qhmHaikColors);
                  }
                }
                var colorSets = jQuery.clickpad.haikColors;
                var content = "<div>";
                for (var i2 in colorSets) {
                  var colorSet = colorSets[i2];
                  content += '<div style="text-align:center;">';
                  for (var j in colorSet.color) {
                    var color2 = colorSet.color[j];
                    content += '<button type="button" class="qhm-btn qhm-btn-default qhm-haik-color-btn" style="background-color: #' + color2 + ";border-color:#" + darken(color2) + `;margin-right:2px;margin-bottom: 2px;" onclick="$('#cpPromptHaikColor').val(this.dataset.color);$('#cp_popup_ok').click()" data-color="#` + color2 + '">&nbsp;&nbsp;</button>';
                  }
                  content += "</div>";
                }
                content += "</div>";
                content += '<input type="hidden" name="color_0" id="cpPromptHaikColor" class="cp_popup_prompt">';
                return content;
              },
              function(sel_length2) {
                var value2 = "${1}";
                if (sel_length2 > 0) {
                  value2 = "&deco(" + value2 + "){";
                }
                return value2;
              },
              function(sel_length2) {
                var value2 = "";
                if (sel_length2 > 0) {
                  value2 = "};";
                }
                return value2;
              }
            ]
          },
          "haikParts": {
            caption: '<i class="glyphicon glyphicon-cog"></i> \u30D1\u30FC\u30C4',
            classAttribute: "qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left",
            func: "cpEval",
            value: "showHaikParts()"
          },
          // !mobile buttons
          "tel": {
            caption: "\u96FB\u8A71\u756A\u53F7",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -954px 0",
            func: "cpDialog",
            value: ["\u96FB\u8A71\u756A\u53F7\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3000\uFF08\u203B \u300C-\uFF08\u30CF\u30A4\u30D5\u30F3\uFF09\u300D\u306F\u7701\u3044\u3066\u304F\u3060\u3055\u3044\uFF09", "&tel(${1}", ");"]
          },
          "mailto": {
            caption: "\u30E1\u30FC\u30EB\u30A2\u30C9\u30EC\u30B9",
            width: 28,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -982px 0",
            func: "cpDialog",
            value: [["\u30E1\u30FC\u30EB\u30A2\u30C9\u30EC\u30B9\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044", "\u4EF6\u540D\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044", "\u672C\u6587\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044", "\u30E1\u30FC\u30EB\u30A2\u30C9\u30EC\u30B9\u306A\u3069\u753B\u9762\u306B\u8868\u793A\u3059\u308B\u6587\u5B57\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044"], "&mailto(${1},${2},${3}){${4}", "};"]
          },
          "marquee": {
            caption: "\u30DE\u30FC\u30AD\u30FC",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -1010px 0",
            func: "cpEnclose",
            value: ["&scroll(){", "};"]
          },
          "marquee2": {
            caption: "\u30DE\u30FC\u30AD\u30FC\uFF08\u80CC\u666F\uFF09",
            width: 47,
            height: 28,
            background: "url(image/hokuken/toolbox2.png) no-repeat -1057px 0",
            func: "cpDialog",
            value: [["\u80CC\u666F\u8272\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044(ex. black yellow pink blue)", "\u30B9\u30AF\u30ED\u30FC\u30EB\u30B9\u30D4\u30FC\u30C9\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\uFF08slow,normal,fast\uFF09\n\uFF08\u7701\u7565\u3057\u305F\u5834\u5408\u306F\u6A19\u6E96\u30B9\u30D4\u30FC\u30C9\uFF08normal\uFF09\u306B\u8A2D\u5B9A\u3057\u307E\u3059\u3002\uFF09"], "&scroll(${1},${2}){", "};"]
          },
          // !mobile ruled lines buttons
          "lineLT": {
            caption: "\u250F",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat 0 0",
            func: "cpInsert",
            value: "\u250F"
          },
          "lineCT": {
            caption: "\u2533",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -13px 0",
            func: "cpInsert",
            value: "\u2533"
          },
          "lineRT": {
            caption: "\u2513",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -26px 0",
            func: "cpInsert",
            value: "\u2513"
          },
          "lineLM": {
            caption: "\u2523",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat 0 -13px",
            func: "cpInsert",
            value: "\u2523"
          },
          "lineCM": {
            caption: "\u254B",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -13px -13px",
            func: "cpInsert",
            value: "\u254B"
          },
          "lineRM": {
            caption: "\u252B",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -26px -13px",
            func: "cpInsert",
            value: "\u252B"
          },
          "lineLB": {
            caption: "\u2517",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat 0 -26px",
            func: "cpInsert",
            value: "\u2517"
          },
          "lineCB": {
            caption: "\u253B",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -13px -26px",
            func: "cpInsert",
            value: "\u253B"
          },
          "lineRB": {
            caption: "\u251B",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -26px -26px",
            func: "cpInsert",
            value: "\u251B"
          },
          "lineH": {
            caption: "\u2501",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -39px 0",
            func: "cpInsert",
            value: "\u2501"
          },
          "lineV": {
            caption: "\u2503",
            width: 13,
            height: 13,
            background: "url(img/btn_lines.png) no-repeat -39px -13px",
            func: "cpInsert",
            value: "\u2503"
          },
          // !commu, qnews 用、特殊タグ挿入ボタン
          "lastname": {
            caption: "\u59D3",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat 0 0",
            func: "cpInsert",
            value: "<%lastname%>"
          },
          "firstname": {
            caption: "\u540D",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -28px 0",
            func: "cpInsert",
            value: "<%firstname%>"
          },
          "email": {
            caption: "\u30E1\u30FC\u30EB\u30A2\u30C9\u30EC\u30B9",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -56px 0",
            func: "cpInsert",
            value: "<%email%>"
          },
          "encLastname": {
            caption: "\u59D3\uFF08URL\u30A8\u30F3\u30B3\u30FC\u30C9\uFF09",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -84px 0",
            func: "cpInsert",
            value: "<%enc_lastname%>"
          },
          "encFirstname": {
            caption: "\u540D\uFF08URL\u30A8\u30F3\u30B3\u30FC\u30C9\uFF09",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -112px 0",
            func: "cpInsert",
            value: "<%enc_firstname%>"
          },
          "encEmail": {
            caption: "\u30E1\u30FC\u30EB\u30A2\u30C9\u30EC\u30B9\uFF08URL\u30A8\u30F3\u30B3\u30FC\u30C9\uFF09",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -140px 0",
            func: "cpInsert",
            value: "<%enc_email%>"
          },
          "zip": {
            caption: "\u90F5\u4FBF\u756A\u53F7",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -168px 0",
            func: "cpInsert",
            value: "<%zip%>"
          },
          "state": {
            caption: "\u90FD\u9053\u5E9C\u770C",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -196px 0",
            func: "cpInsert",
            value: "<%state%>"
          },
          "address": {
            caption: "\u4F4F\u6240",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -224px 0",
            func: "cpInsert",
            value: "<%address1%>"
          },
          "telnum": {
            caption: "\u96FB\u8A71\u756A\u53F7",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -252px 0",
            func: "cpInsert",
            value: "<%tel%>"
          },
          "job": {
            caption: "\u8077\u696D",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -280px 0",
            func: "cpInsert",
            value: "<%job%>"
          },
          "birthday": {
            caption: "\u751F\u5E74\u6708\u65E5",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -308px 0",
            func: "cpInsert",
            value: "<%birthday%>"
          },
          "custom1": {
            caption: "\u30AB\u30B9\u30BF\u30E01",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -336px 0",
            func: "cpInsert",
            value: "<%custom1%>"
          },
          "custom2": {
            caption: "\u30AB\u30B9\u30BF\u30E02",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -364px 0",
            func: "cpInsert",
            value: "<%custom2%>"
          },
          "custom3": {
            caption: "\u30AB\u30B9\u30BF\u30E03",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -392px 0",
            func: "cpInsert",
            value: "<%custom3%>"
          },
          "custom4": {
            caption: "\u30AB\u30B9\u30BF\u30E04",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -420px 0",
            func: "cpInsert",
            value: "<%custom4%>"
          },
          "custom5": {
            caption: "\u30AB\u30B9\u30BF\u30E05",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -448px 0",
            func: "cpInsert",
            value: "<%custom5%>"
          },
          "regist": {
            caption: "\u672C\u767B\u9332URL",
            width: 47,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -476px 0",
            func: "cpInsert",
            value: "<%regist_url%>"
          },
          "quit": {
            caption: "\u9000\u4F1AURL",
            width: 47,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -523px 0",
            func: "cpInsert",
            value: "<%quit%>"
          },
          "cancel": {
            caption: "\u89E3\u9664URL",
            width: 47,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -570px 0",
            func: "cpInsert",
            value: "<%cancel%>"
          },
          "cancelAll": {
            caption: "\u4E00\u767A\u89E3\u9664URL",
            width: 47,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -617px 0",
            func: "cpInsert",
            value: "<%cancel%>"
          },
          "userInfo": {
            caption: "\u30E6\u30FC\u30B6\u30FC\u60C5\u5831URL",
            width: 47,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -664px 0",
            func: "cpInsert",
            value: "<%info%>"
          },
          "scenario": {
            caption: "\u30B7\u30CA\u30EA\u30AA\u30BF\u30A4\u30C8\u30EB",
            width: 47,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -711px 0",
            func: "cpInsert",
            value: "<%title%>"
          },
          "password": {
            caption: "\u30D1\u30B9\u30EF\u30FC\u30C9",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -758px 0",
            func: "cpInsert",
            value: "<%password%>"
          },
          "expiration": {
            caption: "\u6709\u52B9\u671F\u9593",
            width: 28,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -786px 0",
            func: "cpInsert",
            value: "<%expiration%>"
          },
          "privacypolicy": {
            caption: "\u30D7\u30E9\u30A4\u30D0\u30B7\u30FC\u30DD\u30EA\u30B7\u30FCURL",
            width: 62,
            height: 28,
            background: "url(img/commu_icons.png) no-repeat -814px 0",
            func: "cpInsert",
            value: "<%privacypolicy%>"
          },
          // !forum 用、特殊タグ挿入ボタン
          "nickname": {
            caption: "\u30CB\u30C3\u30AF\u30CD\u30FC\u30E0",
            width: 47,
            height: 28,
            background: "url(img/forum_icons.png) no-repeat 0 0",
            func: "cpInsert",
            value: "%nickname%"
          },
          "forumtitle": {
            caption: "\u30D5\u30A9\u30FC\u30E9\u30E0\u30BF\u30A4\u30C8\u30EB",
            width: 47,
            height: 28,
            background: "url(img/forum_icons.png) no-repeat -47px 0",
            func: "cpInsert",
            value: "%title%"
          },
          "posturl": {
            caption: "\u8CEA\u554F\u8868\u793AURL",
            width: 47,
            height: 28,
            background: "url(img/forum_icons.png) no-repeat -94px 0",
            func: "cpInsert",
            value: "%url%"
          },
          "postediturl": {
            caption: "\u8CEA\u554F\u7DE8\u96C6URL",
            width: 47,
            height: 28,
            background: "url(img/forum_icons.png) no-repeat -141px 0",
            func: "cpInsert",
            value: "%url_edit%"
          },
          "resbody": {
            caption: "\u8FD4\u4FE1\u306E\u5185\u5BB9\uFF08\u5168\u6587\uFF09",
            width: 47,
            height: 28,
            background: "url(img/forum_icons.png) no-repeat -188px 0",
            func: "cpInsert",
            value: "%body%"
          },
          // !ThickBox が必要なボタン
          //replaces オプションの指定必須
          "fwd": {
            //replaces:{htmlmail:<boolean> isHTMLMail, relatedId:<string> relatedMailId}
            caption: "\u30AF\u30EA\u30C3\u30AF\u8A08\u6E2CURL",
            width: 117,
            height: 33,
            background: ["url(img/btn_fwd_maneger_117x33.gif) no-repeat 0 0", "url(img/btn_fwd_maneger_117x33.gif) no-repeat 0 -33px"],
            func: "cpEval",
            value: "tb_show('ForwardingURLManager', 'fwd_manager.php?taid=${textarea}&h=${htmlmail}${relatedId}${query}&TB_iframe=true&height=480&width=640');",
            replaces: ["htmlmail", "relatedId", "query"]
          },
          "ot": {
            //replaces:{htmlmail:<boolea> isHTMLMail}
            caption: "\u30EF\u30F3\u30BF\u30A4\u30E0\u30AA\u30D5\u30A1\u30FC",
            width: 117,
            height: 33,
            background: ["url(img/btn_ot_maneger_117x33.gif) no-repeat 0 0", "url(img/btn_ot_maneger_117x33.gif) no-repeat 0 -33px"],
            func: "cpEval",
            value: "tb_show('OneTimeOfferManager', 'ot_manager.php?taid=${textarea}&h=${htmlmail}${query}&TB_iframe=true&height=480&width=640');",
            replaces: ["htmlmail", "query"]
          },
          "image": {
            caption: "\u30A4\u30E1\u30FC\u30B8\u30DE\u30CD\u30FC\u30B8\u30E3\u30FC",
            width: 117,
            height: 33,
            background: ["url(img/btn_image_manager_117x33.gif) no-repeat 0 0", "url(img/btn_image_manager_117x33.gif) no-repeat 0 -33px"],
            func: "cpEval",
            value: "tb_show('ImageManager', 'image_manager.php?taid=${textarea}${query}&TB_iframe=true&height=480&width=640');",
            replaces: ["query"]
          },
          "forumImage": {
            caption: "\u30A4\u30E1\u30FC\u30B8\u30DE\u30CD\u30FC\u30B8\u30E3\u30FC",
            width: 117,
            height: 33,
            background: ["url(../commu/img/btn_image_manager_117x33.gif) no-repeat 0 0", "url(../commu/img/btn_image_manager_117x33.gif) no-repeat 0 -33px"],
            func: "cpEval",
            value: "tb_show('ImageManager', '../commu/image_manager.php?taid=${textarea}${query}&&TB_iframe=true&height=480&width=640');",
            replaces: ["query"]
          },
          "preview": {
            //replaces:{prvFunc:<string> javascriptFunctionName}
            caption: "\u30D7\u30EC\u30D3\u30E5\u30FC",
            width: 117,
            height: 33,
            background: ["url(img/btn_preview_117x33.gif) no-repeat 0 0", "url(img/btn_preview_117x33.gif) no-repeat 0 -33px"],
            func: "cpEval",
            value: "${prvFunc}",
            replaces: ["prvFunc"]
          },
          "forumPreview": {
            //replaces:{prvFunc:<string> javascriptFunctionName}
            caption: "\u30D7\u30EC\u30D3\u30E5\u30FC",
            width: 117,
            height: 33,
            background: ["url(../commu/img/btn_preview_117x33.gif) no-repeat 0 0", "url(../commu/img/btn_preview_117x33.gif) no-repeat 0 -33px"],
            func: "cpEval",
            value: "${prvFunc}",
            replaces: ["prvFunc"]
          },
          //特殊な用途に使うもの
          "-": {}
        };
        jQuery.clickpad.colors = [
          "black",
          "gray",
          "silver",
          "lightgray",
          "white",
          "navy",
          "blue",
          "cyan",
          "green",
          "lime",
          "lightgreen",
          "purple",
          "magenta",
          "pink",
          "red",
          "orange",
          "yellow"
        ];
        for (var i = 0; i < jQuery.clickpad.colors.length; i++) {
          var color = jQuery.clickpad.colors[i];
          addButton(color, {
            caption: color,
            width: 13,
            height: 13,
            background: color,
            func: "cpEnclose",
            value: ["&deco(" + color + "){", "};"]
          });
        }
        jQuery.clickpad.palettes = {
          //default color palette
          color: [
            ["black", "gray", "silver", "lightgray", "white", "white"],
            ["navy", "blue", "cyan", "green", "lime", "lightgreen"],
            ["purple", "magenta", "pink", "red", "orange", "yellow"]
          ],
          //QHM toolbox
          qhm: [
            ["header", "contents", "ul", "ol", "attach", "br", "link", "b", "i", "handline", "size", "penYellow", "penRed", "penBlue", "penGreen", "decop"],
            ["left", "center", "right", "table", "HTML", "styleboxp", "title", "onepage", "bullet", "check", "strike", "whiteRed", "whiteBlack", "counter", "comment"]
          ],
          qhmHaik: [
            ["haikHeader", "haikB", "haikHandline", "haikDecop", "haikBr", "haikHr", "haikLink", "haikUl", "haikLeft", "haikCenter", "haikRight", "haikIcon", "haikAttach", "haikColors", "haikParts"]
          ],
          //QBlog
          qblog: [
            ["header", "ul", "ol", "attach", "br", "link", "b", "i", "handline", "penYellow", "penRed", "decop"],
            ["left", "center", "right", "table", "styleboxp", "check", "strike", "whiteRed", "counter", "plugins"]
          ],
          qblogcolors: [
            ["black", "gray", "silver", "lightgray", "white", "white", "navy", "blue", "cyan"],
            ["green", "lime", "lightgreen", "purple", "magenta", "pink", "red", "orange", "yellow"]
          ],
          qhmHaikQBlog: [
            ["haikHeader", "haikB", "haikHandline", "haikDecop", "haikBr", "haikHr", "haikLink", "haikUl", "haikLeft", "haikCenter", "haikRight", "haikIcon", "haikAttach"],
            ["haikColors", "haikParts"]
          ],
          //QHM minimize for commu and qnews
          qhmmin: [
            ["header", "ul", "ol", "br", "link", "b", "u", "sizeD", "penYellowD", "penRedD", "penBlueD", "penGreenD", "decop"],
            ["left", "center", "right", "table", "HTML", "styleboxp", "strike", "whiteRedD", "whiteBlackD"]
          ],
          qhmminL: [
            [
              "header",
              "ul",
              "ol",
              "br",
              "link",
              "b",
              "u",
              "sizeD",
              "penYellowD",
              "penRedD",
              "penBlueD",
              "penGreenD",
              "decop",
              "left",
              "center",
              "right",
              "table",
              "HTML",
              "styleboxp",
              "strike",
              "whiteRedD",
              "whiteBlackD"
            ]
          ],
          //mobile toolbox
          mobile: [
            ["link", "tel", "mailto", "attach", "br", "marquee", "marquee2"],
            ["left", "center", "right", "HTML", "counter", "comment"]
          ],
          //ruled lines
          lines: [
            ["lineLT", "lineCT", "lineRT", "lineH"],
            ["lineLM", "lineCM", "lineRM", "lineV"],
            ["lineLB", "lineCB", "lineRB"]
          ],
          //commu
          commu: [
            ["lastname", "firstname", "email", "encLastname", "encFirstname", "encEmail", "privacypolicy"]
          ],
          forum: [
            ["nickname", "forumtitle", "posturl", "postediturl", "resbody"]
          ],
          //qnews
          qnews: [
            ["lastname", "firstname", "email", "encLastname", "encFirstname", "encEmail"]
          ],
          //thickbox
          commuTB: [
            ["image", "preview"]
          ],
          qnewsTB: [
            ["image", "fwd", "ot", "preview"]
          ],
          qnewsTextTB: [
            ["fwd", "ot", "preview"]
          ]
        };
        jQuery.clickpad.buttonSetData = {
          "qhm": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.qhm,
              margin: 1,
              css: {}
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1, marginRight: 3 }
            },
            {
              //other plugins
              buttons: [["plugins"]],
              margin: 0,
              css: { float: "left", marginBottom: 13 },
              clear: true
            }
          ],
          "qhmHaik": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.qhmHaik,
              margin: 1,
              css: {},
              clear: true
            }
          ],
          "qhmHaikQBlog": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.qhmHaikQBlog,
              margin: 1,
              css: {},
              clear: true
            }
          ],
          "qblog": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.qblog,
              margin: 1,
              css: { float: "left" },
              clear: false
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1, marginLeft: 3 },
              clear: false
            }
          ],
          "mobile": [
            //絵文字のためのケアが必要
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.mobile,
              margin: 1,
              css: {}
            },
            {
              //ruled lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginRight: 3 }
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1 },
              clear: true
            }
          ],
          "qnewsText": [
            {
              //special tag
              buttons: jQuery.clickpad.palettes.qnews,
              margin: 1,
              css: { float: "left" }
            },
            {
              //lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginLeft: 5 },
              clear: true
            },
            {
              //thick box
              buttons: jQuery.clickpad.palettes.qnewsTextTB,
              margin: [5, 0],
              css: {
                margin: "10px 0"
              }
            }
          ],
          "qnewsTextLinear": [
            {
              //special tag
              buttons: jQuery.clickpad.palettes.qnews,
              margin: 1,
              css: { float: "left" }
            },
            {
              //lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginLeft: 5 }
            },
            {
              //thick box
              buttons: jQuery.clickpad.palettes.qnewsTextTB,
              margin: [10, 0],
              css: {
                float: "left",
                marginLeft: 5
              }
            }
          ],
          "qnewsHTML": [
            {
              //special tag
              buttons: jQuery.clickpad.palettes.qnews,
              margin: 1
            },
            {
              //QHM
              buttons: jQuery.clickpad.palettes.qhmmin,
              margin: 1
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1 },
              clear: true
            },
            {
              //thick box
              buttons: jQuery.clickpad.palettes.qnewsTB,
              margin: [5, 0],
              css: {
                margin: "10px 0"
              }
            }
          ],
          "qnewsHTMLLinear": [
            {
              //special tag
              buttons: jQuery.clickpad.palettes.qnews,
              margin: 1
            },
            {
              //QHM
              buttons: jQuery.clickpad.palettes.qhmminL,
              margin: 1,
              css: { float: "left" },
              clear: true
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left" }
            },
            {
              //thick box
              buttons: jQuery.clickpad.palettes.qnewsTB,
              margin: [5, 0],
              css: {
                float: "left",
                margin: "5px 0 5px 10px"
              }
            }
          ],
          "qnewsSubject": [
            //メールの件名で使う
            {
              buttons: [["lastname", "firstname", "email"]],
              margin: [1, 0]
            }
          ],
          "commuHTML": [
            {
              //QHM
              buttons: jQuery.clickpad.palettes.qhmmin,
              margin: 1
            },
            {
              //thick box
              buttons: [["image", "preview"]],
              margin: [5, 1],
              css: {
                margin: "5px 0 0"
              }
            }
          ],
          "commuAdmin": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.qhmmin,
              margin: 1
            },
            {
              //toolbox
              buttons: [
                ["lastname", "firstname", "email"]
              ],
              margin: 1,
              css: {}
            },
            {
              //thick box
              buttons: [["image", "preview"]],
              margin: [5, 1],
              css: {
                margin: "5px 0"
              }
            }
          ],
          "commuAdminRegist": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.qhmmin,
              margin: 1
            },
            {
              //toolbox
              buttons: [
                ["lastname", "firstname", "email", "password"]
              ],
              margin: 1,
              css: {}
            },
            {
              //thick box
              buttons: [["image", "preview"]],
              margin: [5, 1],
              css: {
                margin: "5px 0"
              }
            }
          ],
          "commuMobile": [
            //絵文字のためのケアが必要
            {
              //toolbox
              buttons: [
                ["link", "tel", "mailto", "br", "sizeM", "decop", "marquee", "marquee2", "left", "center", "right", "HTML"]
              ],
              margin: 1
            },
            {
              //ruled lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginRight: 3 }
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1 },
              clear: true
            }
          ],
          "commuMobileUser": [
            //絵文字のためのケアが必要
            {
              //toolbox
              buttons: [
                ["link", "tel", "mailto", "br", "sizeM", "decop", "marquee", "marquee2", "left", "center", "right", "HTML"]
              ],
              margin: 1
            },
            {
              //commutoolbox
              buttons: [
                ["lastname", "firstname", "email"]
              ],
              margin: 1
            },
            {
              //ruled lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginRight: 3 }
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1 },
              clear: true
            }
          ],
          "commuMobileUserRegist": [
            //絵文字のためのケアが必要
            {
              //toolbox
              buttons: [
                ["link", "tel", "mailto", "br", "sizeM", "decop", "marquee", "marquee2", "left", "center", "right", "HTML"]
              ],
              margin: 1
            },
            {
              //commutoolbox
              buttons: [
                ["lastname", "firstname", "email", "password"]
              ],
              margin: 1
            },
            {
              //ruled lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginRight: 3 }
            },
            {
              //color palette
              buttons: jQuery.clickpad.palettes.color,
              margin: 0,
              css: { float: "left", marginBottom: 1 },
              clear: true
            }
          ],
          "commuSubject": [
            {
              //toolbox
              buttons: [
                ["lastname", "firstname", "email"]
              ],
              margin: 1,
              css: {}
            }
          ],
          "commuMail": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.commu,
              margin: 1,
              css: { float: "left" }
            },
            {
              //lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginLeft: 5 },
              clear: true
            }
          ],
          "commuMailUser": [
            {
              //toolbox
              buttons: [
                ["lastname", "firstname", "email", "encLastname", "encFirstname", "encEmail", "cancelAll", "quit", "userInfo", "privacypolicy"]
              ],
              margin: 1,
              css: { float: "left" }
            },
            {
              //lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginLeft: 5 },
              clear: true
            }
          ],
          "commuMailRegist": [
            {
              //toolbox
              buttons: [
                ["lastname", "firstname", "email", "password", "encLastname", "encFirstname", "encEmail", "cancelAll", "quit", "userInfo", "privacypolicy"]
              ],
              margin: 1,
              css: { float: "left" }
            },
            {
              //lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: { float: "left", marginLeft: 5 },
              clear: true
            }
          ],
          "forumSubject": [
            {
              //toolbox
              buttons: [
                ["nickname", "forumtitle"]
              ],
              margin: 1,
              css: {
                margin: "2px 0"
              }
            }
          ],
          "forumMail": [
            {
              //toolbox
              buttons: jQuery.clickpad.palettes.forum,
              margin: 1,
              css: {
                float: "left",
                margin: "2px 0"
              }
            },
            {
              //lines
              buttons: jQuery.clickpad.palettes.lines,
              margin: 0,
              css: {
                float: "left",
                margin: "2px 2px"
              },
              clear: true
            }
          ],
          "forumHTML": [
            {
              //QHM
              buttons: jQuery.clickpad.palettes.qhmmin,
              margin: 1
            },
            {
              //thick box
              buttons: [["forumImage", "forumPreview"]],
              margin: [5, 1],
              css: { margin: "5px 0" }
            }
          ]
        };
        function addButton(name2, data) {
          data = jQuery.extend({
            caption: name2,
            width: 13,
            height: 13,
            background: "#fff",
            classAttribute: "",
            func: "cpEval",
            value: ""
          }, data);
          jQuery.clickpad.buttonData[name2] = data;
        }
        function getBrowser() {
          return jQuery.clickpad.browser;
        }
        var cpPrompt = jQuery.clickpad.cpPrompt = function(msgs, options, callback) {
          var removeCpPrompt = function() {
            $("#cp_popup_overlay").remove();
            $("#cp_popup_container").remove();
          };
          removeCpPrompt();
          var content = "";
          if (typeof msgs === "function") {
            content = msgs.call();
          } else {
            msgs = msgs instanceof Array ? msgs : [msgs];
            options = options instanceof Array ? options : [options];
            if (msgs.length > options.length) {
              for (var i2 = options.length; i2 < msgs.length; i2++) {
                options[i2] = {};
              }
            }
          }
          $("body").append('<div id="cp_popup_overlay"></div>');
          $("#cp_popup_overlay").css({
            position: "absolute",
            zIndex: 99998,
            top: "0px",
            left: "0px",
            width: "100%",
            height: $(document).height(),
            backgroundColor: "rgba(255,255,255,.5)"
          }).click(function() {
            removeCpPrompt();
            return false;
          });
          $("body").append(
            '<div id="cp_popup_container"><div id="cp_popup_content"></div></div>'
          );
          var pos = "fixed", w = "auto";
          var $popup = $("#cp_popup_container").css({
            position: pos,
            zIndex: 99999,
            padding: "1em",
            margin: 0,
            fontSize: 12,
            width: w,
            minWidth: 300,
            maxWidth: 600,
            background: "#FFF",
            border: "solid 5px #999",
            color: "#000",
            "-moz-border-radius": 5,
            "-webkit-border-radius": 5,
            "border-radius": 5
          });
          if (content.length === 0) {
            for (var i2 = 0; i2 < msgs.length; i2++) {
              content += '<div class="cp_popup_message" style="line-height:1.5em;text-align:left">' + msgs[i2].replace(/\n/g, "<br />") + "</div>";
            }
          }
          $("#cp_popup_content", $popup).html(content);
          $popup.css({
            minWidth: $popup.outerWidth(),
            maxWidth: $popup.outerWidth()
          });
          var top = $(window).height() / 2 - $popup.outerHeight() / 2 + 0;
          var left = $(window).width() / 2 - $popup.outerWidth() / 2 + 0;
          if (top < 0)
            top = 0;
          if (left < 0)
            left = 0;
          $popup.css({
            top: top + "px",
            left: left + "px"
          });
          $("#cp_popup_overlay").height($(document).height());
          $msgs = $("div.cp_popup_message", $popup).append("<br />").css({
            marginBottom: "10px"
          });
          $("#cp_popup_content").append('<div id="cp_popup_panel"><input type="button" value="OK" id="cp_popup_ok" class="qhm-btn-primary" /> <input type="button" value="Cancel" id="cp_popup_cancel" class="btn-link" /></div>');
          $("#cp_popup_panel").css({
            textAlign: "center",
            margin: "1em 0em 0em 1em"
          });
          $msgs.each(function(i3) {
            var opt = options[i3];
            var $$2 = $(this);
            switch (opt.type) {
              case "checkbox":
                var checkbox = '<input type="checkbox" value="' + opt.value + '" name="cpPopupPrompt_' + i3 + '" class="cp_popup_prompt" /><input type="hidden" value="" name="cpPopupPrompt_' + i3 + '" class="cp_popup_prompt" />&nbsp;';
                $$2.prepend(checkbox).html("<label>" + $$2.html() + "</label>");
                break;
              case "select":
                var select = '&nbsp;&nbsp;<select class="cp_popup_prompt" name="cpPopupPrompt_' + i3 + '">';
                for (var j in opt.values) {
                  if (!$.isPlainObject(opt.values[j]))
                    continue;
                  var value2 = opt.values[j];
                  if (typeof value2 == "string") {
                    value2 = { key: value2, value: value2, selected: false };
                  } else if (typeof value2.key == "undefined") {
                    value2.key = value2.value;
                  }
                  select += '<option value="' + value2.value + '"' + (value2.selected ? ' selected="selected"' : "") + ">" + value2.key + "</option>";
                }
                select += "</select>";
                $$2.append(select).html("<label>" + $$2.html() + "</label>");
                break;
              case "radio":
                var rdname = "cpPopupPrompt_" + i3;
                var radio = "&nbsp;&nbsp;";
                for (var j in opt.values) {
                  if (!$.isPlainObject(opt.values[j]))
                    continue;
                  var value2 = opt.values[j];
                  if (typeof value2 == "string") {
                    value2 = { label: value2, value: value2, checked: false };
                  } else if (typeof value2.label == "undefined") {
                    value2.label = value2.value;
                  }
                  radio += '<label style="display:inline;padding:0;"><input type="radio" class="cp_popup_prompt" name="' + rdname + '" value="' + value2.value + '"' + (value2.checked ? ' checked="checked"' : "") + " />&nbsp;" + value2.label + "</label>&nbsp;&nbsp;";
                  if (value2.br) {
                    radio += "<br />&nbsp;&nbsp;";
                  }
                }
                $$2.append(radio);
                break;
              default:
                var defval = opt.defval || "";
                var size = opt.size || 30;
                var inputWidth = opt.inputWidthRatio * $$2.width() || $$2.width() * 0.9;
                $$2.append('&nbsp;&nbsp;<input type="text" size="' + size + '" class="cp_popup_prompt" name="cpPopupPrompt_' + i3 + '" value="' + defval + '" />').html("<label>" + $$2.html() + "</label>").find("input.cp_popup_prompt").width(inputWidth).val(opt.defval).on("keydown", (e) => {
                  e.stopPropagation();
                });
            }
            var css = opt.css || {};
            if (i3 < $msgs.length - 1) {
              css = jQuery.extend({
                marginBottom: 10
              }, css);
            }
            $$2.css(css);
          });
          $("#cp_popup_ok").click(function() {
            var values = [];
            var cbskip = false;
            $(".cp_popup_prompt", $popup).each(function() {
              var $$2 = $(this);
              var i3 = parseInt($$2.attr("name").split("_")[1]);
              if ($$2.attr("type") == "checkbox") {
                if ($$2.is(":checked")) {
                  cbskip = true;
                  values[i3] = $$2.val();
                }
              } else if ($$2.attr("type") == "radio") {
                if ($$2.is(":checked")) {
                  values[i3] = $$2.val();
                }
              } else {
                if (!cbskip) {
                  values[i3] = $$2.val();
                } else {
                  cbskip = false;
                }
              }
            });
            removeCpPrompt();
            if (callback)
              callback(values);
          });
          $("#cp_popup_cancel").click(function() {
            removeCpPrompt();
          });
          $("input.cp_popup_prompt, #cp_popup_ok, #cp_popup_cancel", $popup).keypress(function(e) {
            if (e.keyCode == 13) {
              $("#cp_popup_ok").trigger("click");
              return false;
            }
            if (e.keyCode == 27)
              $("#cp_popup_cancel").trigger("click");
          });
          $("input.cp_popup_prompt:first", $popup).focus().select();
        };
        jQuery.fn.clickpad = function(option) {
          var name = "clickpad";
          option = jQuery.extend({
            buttons: "qnews",
            autoGrow: true,
            minLine: 5,
            maxLine: 20,
            replaces: {},
            css: {},
            wrappercss: {},
            showAtFocus: false
          }, option);
          if (typeof option.buttons == "string") {
            option.buttons = jQuery.clickpad.buttonSetData[option.buttons];
          }
          return this.each(function(i) {
            var sel_length = 0, end_length = 0, start_length = 0, start_length2 = 0;
            var scrollPos = 0;
            var eventObj = this;
            var $$ = jQuery(this);
            var total = ++jQuery.clickpad.total;
            var isTextInput = false;
            if ($$.is("input:text")) {
              isTextInput = true;
            }
            var cpEval = jQuery.clickpad.cpEval = function(value, replaces) {
              for (var i in replaces) {
                var rep = replaces[i];
                if (typeof rep == "string") {
                  var str = option.replaces[rep] || "";
                  value = value.replace("${" + rep + "}", str);
                } else if (typeof rep.key != "undefined") {
                  value = value.replace("${" + rep.key + "}", rep.value);
                } else {
                }
              }
              value = value.replace("${textarea}", $$.attr("id"));
              value = value.replace(/\$\{\w+?\}/, "");
              if (document.selection) {
                $$.focus();
              }
              eval(value);
            };
            var cpInsert = jQuery.clickpad.cpInsert = function(value2) {
              var browser = getBrowser(), s = value2;
              if (browser == 2) {
                scrollPos = eventObj.scrollTop;
              }
              var itext = eventObj.value;
              var slen = 0;
              if (browser == 4) {
                eventObj.value = itext + s;
              } else if (browser == 1 && isTextInput) {
                var r = eventObj.createTextRange();
                r.collapse();
                r.moveStart("character", eventObj.value.length - sel_length);
                r.text = value2;
                return;
              } else if (browser) {
                var len = start_length2 == itext.length ? start_length2 : start_length;
                var click_s = itext.substr(0, len);
                var click_m = itext.substr(start_length, sel_length);
                var click_e = itext.substr(start_length + sel_length, end_length);
                if (click_s == "" && click_m == "" && click_e == "") {
                  click_e = itext;
                }
                eventObj.value = click_s + s + click_m + click_e;
                if (false) {
                  var sarr = s.split("\n");
                  if (sarr.length - 1 > 0) {
                    slen = sarr.length;
                    slen = sarr[slen - 1] == "" ? slen - 2 : slen - 1;
                  }
                }
              }
              cpAttachFocus(s.length + slen + len + sel_length);
            };
            var cpEnclose = jQuery.clickpad.cpEnclose = function(values) {
              var s = values[0], e = values[1], browser = getBrowser();
              if (browser == 2) {
                scrollPos = eventObj.scrollTop;
              }
              var itext = eventObj.value;
              if (browser == 4) {
                eventObj.value = itext + s + e;
              } else if (browser) {
                var len = start_length2 == itext.length ? start_length2 : start_length;
                var click_s = itext.substr(0, len);
                var click_m = itext.substr(len, sel_length);
                var click_e = itext.substr(len + sel_length, end_length);
                if (click_s == "" && click_m == "" && click_e == "") {
                  click_e = itext;
                }
                eventObj.value = click_s + s + click_m + e + click_e;
              }
              cpAttachFocus(s.length + e.length + len + sel_length);
            };
            var cpDialog = jQuery.clickpad.cpDialog = function(values) {
              var browser = getBrowser(), prompts = values[0], tmpl = values[1], closer = values[2] || "";
              if (browser == 2) {
                scrollPos = eventObj.scrollTop;
              }
              if (typeof prompts == "function") {
                var msgs = prompts;
                var options = [];
              } else {
                if (typeof prompts == "string") {
                  prompts = [prompts];
                } else {
                  prompts = Array.apply(null, prompts);
                }
                var values = [];
                var cnt = 0;
                var msgs = [], options = [];
                for (var i2 = 0; i2 < prompts.length; i2++) {
                  var promptmsg = prompts[i2];
                  if (typeof promptmsg == "string") {
                    msgs.push(promptmsg);
                    options.push({});
                  } else {
                    msgs.push(prompts[i2].msg || "error: prompt message undefined");
                    options.push(prompts[i2].option || {});
                  }
                }
                var useSelection = false;
                for (var i2 = 0; i2 < options.length; i2++) {
                  if (typeof options[i2] !== "string" && typeof options[i2].type !== "undefined" && options[i2].type === "text" && typeof options[i2].useSelection !== "undefined" && options[i2].useSelection) {
                    options[i2].defval = eventObj.value.substr(start_length, sel_length);
                    useSelection = true;
                  }
                }
              }
              if (typeof tmpl == "function") {
                tmpl = tmpl.call(this, sel_length);
              }
              if (typeof closer == "function") {
                closer = closer.call(this, sel_length);
              }
              cpPrompt(msgs, options, function(values2) {
                for (var i3 = 0; i3 < values2.length; i3++) {
                  var cnt2 = i3 + 1;
                  tmpl = tmpl.replace("${" + cnt2 + "}", values2[i3]);
                  closer = closer.replace("${" + cnt2 + "}", values2[i3]);
                }
                var s = tmpl, e = closer;
                var slen = 0;
                var itext = eventObj.value;
                if (browser == 4) {
                  eventObj.value = itext + s + e;
                } else if (browser) {
                  var click_s = itext.substr(0, start_length);
                  var click_m = itext.substr(start_length, sel_length);
                  var click_e = itext.substr(start_length + sel_length, end_length);
                  if (click_s == "" && click_m == "" && click_e == "") {
                    click_e = itext;
                  }
                  if (useSelection) {
                    click_m = "";
                    sel_length = 0;
                  }
                  eventObj.value = click_s + s + click_m + e + click_e;
                  if (false) {
                    var sarr = s.split("\n");
                    if (sarr.length - 1 > 0) {
                      slen = sarr.length;
                      slen = sarr[slen - 1] == "" ? slen - 2 : slen - 1;
                    }
                  }
                }
                cpAttachFocus(s.length + slen + e.length + start_length + sel_length);
              });
            };
            var cpAttachFocus = jQuery.clickpad.cpAttachFocus = function(ln) {
              var browser = getBrowser();
              if (browser == 1) {
                var e = eventObj.createTextRange();
                var tx = eventObj.value.substr(0, ln);
                var pl = tx.split(/\n/);
                e.collapse(true);
                e.moveStart("character", ln - pl.length + 1);
                e.text = e.text + "";
                e.collapse(false);
                e.select();
                eventObj.focus();
              } else if (browser == 2) {
                eventObj.setSelectionRange(ln, ln);
                eventObj.focus();
                eventObj.scrollTop = scrollPos;
              } else if (browser == 3) {
              }
            };
            var cpGetPos = jQuery.clickpad.cpGetPos = function() {
              var d = eventObj;
              var ret = 0, browser = getBrowser();
              if (browser == 1) {
                if (isTextInput) {
                  var r = document.selection.createRange();
                  r.moveEnd("textedit");
                  sel_length = r.text.length;
                  return;
                }
                var sel = document.selection.createRange();
                sel_length = sel.text.length;
                var r = d.createTextRange();
                var all = r.text.length;
                var all2 = d.value.length;
                var ol = sel.offsetLeft, ot = sel.offsetTop;
                try {
                  r.moveToPoint(ol, ot);
                } catch (e) {
                  r.move("textedit");
                }
                r.moveEnd("textedit");
                end_length = r.text.length;
                start_length = all - end_length;
                start_length2 = all2 - end_length;
              } else if (browser == 2) {
                start_length = d.selectionStart;
                end_length = d.value.length - d.selectionEnd;
                sel_length = d.selectionEnd - start_length;
              } else if (browser == 3) {
                var ln = new String(d.value);
                start_length = ln.length;
                end_length = start_length;
                sel_length = 0;
              }
            };
            var $doc = $(document);
            if (typeof $doc.data("buttonTotal." + name) == "undefined") {
              $doc.data("buttonTotal." + name, 0);
            }
            var toolbox = '<div id="cpWrapper_' + total + '" class="cpWrapper">', hoverables = [];
            for (var setIdx in option.buttons) {
              var btnSetData = option.buttons[setIdx], btnSetCss = btnSetData.css || {}, btnSet = btnSetData.buttons, btnMargin = btnSetData.margin || [0, 0], bs_total = ++jQuery.clickpad.bs_total, bs_html = "", backgroundImage = btnSetData.backgroundImage || false, clear = btnSetData.clear || false;
              if (typeof btnMargin == "number") {
                btnMargin = [btnMargin, btnMargin];
              }
              bs_html += '<div><div id="cpButtonSet_' + bs_total + '" class="cpButtonSet">';
              for (var lineIdx in btnSet) {
                var btnLine = btnSet[lineIdx];
                for (var btnIdx in btnLine) {
                  try {
                    var total = ++jQuery.clickpad.b_total, btnName = btnLine[btnIdx], button = jQuery.clickpad.buttonData[btnName], id = id = "cpButton_" + btnName + "_" + total;
                    if (typeof button.classAttribute !== "undefined" && button.classAttribute.length > 0) {
                      var title = button.caption.replace(/<.*?>/g, "");
                      bs_html += '<button type="button" id="' + id + '" class="cpButton ' + button.classAttribute + '" title="' + title + '">' + button.caption + "</button>";
                    } else {
                      var bg;
                      if (typeof button.background == "string") {
                        bg = button.background;
                      } else {
                        bg = button.background[0];
                        hoverables.push({ id, backgrounds: button.background });
                      }
                      bs_html += '<div id="' + id + '" class="cpButton" title="' + button.caption + '" style="width:' + button.width + "px;height:" + button.height + "px;background:" + bg + ";float:left;margin:0;margin-right:" + btnMargin[0] + "px;margin-bottom:" + btnMargin[1] + "px;padding:0;line-height:" + button.height + 'px;"></div>';
                    }
                  } catch (e) {
                    alert(btnLine[btnIdx] + " is undefined");
                  }
                }
                bs_html += '<div style="clear:both"></div>\n';
              }
              bs_html += "</div></div>";
              toolbox += $(bs_html).children("div.cpButtonSet").css(btnSetCss).each(function() {
                if (backgroundImage) {
                  $(this).find(".cpButton").css({
                    backgroundImage: "url(" + backgroundImage + ")"
                  });
                }
              }).end().html();
              if (clear) {
                toolbox += '<div style="clear:both;"></div>\n';
              }
            }
            toolbox += "</div>";
            var $toolbox = $(toolbox).css(option.css).find(".cpButton").each(function() {
              var $div = $(this), btnName = $div.attr("id").split("_")[1];
              var btnData = jQuery.clickpad.buttonData[btnName];
              $div.click(function() {
                $$.data("continue", true);
                if (typeof btnData.replaces != "undefined") {
                  eval(btnData.func + "(btnData.value, btnData.replaces)");
                } else {
                  eval(btnData.func + "(btnData.value)");
                }
                setTimeout(function() {
                  $$.data("continue", false);
                }, 500);
                return false;
              });
            }).css({
              cursor: "pointer"
            }).end();
            if ($$.next("div.cpWrapper").length) {
              $$.next("div.cpWrapper").remove();
              $$.unbind(".clickpad");
            }
            $$.after($toolbox).bind("focus.clickpad", function() {
              cpGetPos(this);
            }).bind("mouseup.clickpad", function() {
              cpGetPos(this);
            }).bind("mouseup.clickpad", function() {
              cpGetPos(this);
            }).bind("keyup.clickpad", function() {
              cpGetPos(this);
            }).bind("keydown.clickpad", function() {
              cpGetPos(this);
            });
            for (var i in hoverables) {
              var hoverId = hoverables[i].id, backgrounds = hoverables[i].backgrounds;
              $("#" + hoverId).data("mouseenter.clickpad", backgrounds[1] + "").data("mouseleave.clickpad", backgrounds[0] + "").hover(
                function() {
                  $(this).css({ background: $(this).data("mouseenter.clickpad") });
                },
                function() {
                  $(this).css({ background: $(this).data("mouseleave.clickpad") });
                }
              );
            }
            if (option.autoGrow) {
              $$.bind("fit.clickpad", function(ev) {
                var id2 = $$.attr("id");
                if (ev == null) {
                  var textarea = $("#" + id2).get(0);
                } else {
                  var textarea = ev.target || ev.srcElement;
                }
                var value2 = textarea.value;
                var lines = value2.split("\n");
                var lineNum = lines.length + 1;
                var cols = textarea.getAttributeNode("cols") ? textarea.getAttributeNode("cols").nodeValue : 60;
                for (var i2 in lines) {
                  var line = lines[i2];
                  if (line.length > cols)
                    lineNum += Math.ceil(line.length / cols) - 1;
                }
                if (option.minLine >= 0 && lineNum < option.minLine) {
                  lineNum = option.minLine;
                } else if (option.maxLine > 0 && lineNum > option.maxLine) {
                  lineNum = option.maxLine;
                }
                textarea.setAttribute("rows", lineNum);
              }).bind("keydown.clickpad", function() {
                $$.trigger("fit.clickpad");
              }).bind("focus.clickpad", function() {
                $$.trigger("fit.clickpad");
              }).trigger("fit.clickpad");
            }
            if (option.showAtFocus) {
              $$.bind("focus.clickpad", function() {
                if ($toolbox.is(":not(:visible)")) {
                  $toolbox.fadeIn();
                }
              }).bind("blur.clickpad", function() {
                setTimeout(function() {
                  if (!$$.data("continue")) {
                    $toolbox.fadeOut();
                  }
                  ;
                }, 500);
              });
              $toolbox.hide();
            }
          });
        };
      })(jQuery);
    }
  });
  require_jquery_clickpad2();
})();
//# sourceMappingURL=jquery.clickpad2.js.map
