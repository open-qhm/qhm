(() => {
  // js/clickpad2/button-cover.js
  var coverButton = (button, coverOption) => {
    if (coverOption === void 0) {
      return button;
    }
    if (coverOption.kind === "icon") {
      button.title = button.textContent;
      button.textContent = "";
      const icon = document.createElement("span");
      icon.classList.add("material-icons-outlined");
      icon.textContent = coverOption.name;
      button.appendChild(icon);
    } else if (coverOption.kind === "color") {
      button.title = button.textContent;
      button.textContent = "";
      button.style.backgroundColor = coverOption.color;
      button.style.aspectRatio = "1 / 1";
    } else if (coverOption.kind === "text") {
      button.title = button.textContent;
      button.textContent = coverOption.text;
    }
    return button;
  };

  // js/clickpad2/button-data.js
  var buttonData = {
    "add-image": {
      caption: "\u753B\u50CF\u8FFD\u52A0",
      variant: "insert",
      value: "&show(,,\u753B\u50CF\u306E\u8AAC\u660E);",
      cover: {
        kind: "icon",
        provider: "google",
        name: "add_photo_alternate"
      }
    },
    "title": {
      caption: "\u30BF\u30A4\u30C8\u30EB",
      variant: "insert",
      value: "\nTITLE:",
      cover: {
        kind: "icon",
        provider: "google",
        name: "title"
      }
    },
    "delimiter:start": {
      caption: "\u7DE8\u96C6\u533A\u5207\u308A\u7DDA\uFF08\u958B\u59CB\u70B9\uFF09",
      variant: "insert",
      value: "\n//\u25BC \u2508 <\u5185\u5BB9\u540D\u30FB\u59CB\u70B9> \u2508 \u25BC\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u25BC\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "arrow_drop_down"
      }
    },
    "delimiter:end": {
      caption: "\u7DE8\u96C6\u533A\u5207\u308A\u7DDA\uFF08\u7D42\u4E86\u70B9\uFF09",
      variant: "insert",
      value: "\n//\u25B2 \u2508 <\u5185\u5BB9\u540D\u30FB\u7D42\u70B9> \u2508 \u25B2\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u25B2\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "arrow_drop_up"
      }
    },
    "comment-out": {
      caption: "\u30B3\u30E1\u30F3\u30C8\u30A2\u30A6\u30C8",
      variant: "insert",
      value: "\n//",
      cover: {
        kind: "icon",
        provider: "google",
        name: "visibility_off"
      }
    },
    "br": {
      caption: "\u6539\u884C",
      variant: "insert",
      value: "&br;",
      cover: {
        kind: "icon",
        provider: "google",
        name: "subdirectory_arrow_left"
      }
    },
    "margin": {
      caption: "\u4F59\u767D\u8ABF\u6574",
      variant: "insert",
      value: "#br(30)",
      cover: {
        kind: "icon",
        provider: "google",
        name: "expand"
      }
    },
    "hr": {
      caption: "\u533A\u5207\u308A\u7DDA",
      variant: "insert",
      value: "\n----\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "horizontal_rule"
      }
    },
    "h1": {
      caption: "\u898B\u51FA\u30571",
      variant: "insert",
      value: "\n! ",
      cover: {
        kind: "text",
        text: "h1"
      }
    },
    "h2": {
      caption: "\u898B\u51FA\u30572",
      variant: "insert",
      value: "\n* ",
      cover: {
        kind: "text",
        text: "h2"
      }
    },
    "h3": {
      caption: "\u898B\u51FA\u30573",
      variant: "insert",
      value: "\n** ",
      cover: {
        kind: "text",
        text: "h3"
      }
    },
    "h4": {
      caption: "\u898B\u51FA\u30574",
      variant: "insert",
      value: "\n*** ",
      cover: {
        kind: "text",
        text: "h4"
      }
    },
    "link": {
      caption: "\u30EA\u30F3\u30AF",
      variant: "dialog",
      dialog: [
        {
          message: "\u30EA\u30F3\u30AF\u540D\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002",
          option: {
            type: "text",
            useSelection: true
          }
        },
        {
          message: "\u30EA\u30F3\u30AF\u5148\uFF08\u30DA\u30FC\u30B8\u540D, URL\uFF09\u3092\u5165\u529B\u3057\u3066\u304F\u3060\u3055\u3044\u3002",
          option: {
            type: "text"
          }
        }
      ],
      value: "[[${1}>${2}]]",
      cover: {
        kind: "icon",
        provider: "google",
        name: "link"
      }
    },
    "bold": {
      caption: "\u592A\u5B57",
      variant: "wrap",
      prefix: "''",
      suffix: "''",
      cover: {
        kind: "icon",
        provider: "google",
        name: "format_bold"
      }
    },
    "deco": {
      caption: "\u88C5\u98FE",
      variant: "dialog",
      cover: {
        kind: "icon",
        provider: "google",
        name: "border_color"
      },
      dialog: [
        {
          option: {
            type: "checkbox",
            values: [
              {
                label: "\u592A\u5B57",
                value: "b"
              },
              {
                label: "\u4E0B\u7DDA",
                value: "u"
              },
              {
                label: "\u659C\u4F53",
                value: "i"
              }
            ]
          }
        },
        {
          message: "\u6587\u5B57\u8272",
          tip: "\uFF08\u30AB\u30E9\u30FC\u30B3\u30FC\u30C9/\u30AB\u30E9\u30FC\u30CD\u30FC\u30E0\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "\u80CC\u666F\u8272",
          tip: "\uFF08\u30AB\u30E9\u30FC\u30B3\u30FC\u30C9/\u30AB\u30E9\u30FC\u30CD\u30FC\u30E0\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "\u6587\u5B57\u30B5\u30A4\u30BA",
          tip: "\uFF08\u6570\u5024/em/\u30AD\u30FC\u30EF\u30FC\u30C9\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "",
          option: {
            type: "font-size-guide"
          }
        }
      ],
      value: "&deco(${1},${2},${3},${4}){${selection}};"
    },
    "li": {
      caption: "\u7B87\u6761\u66F8\u304D",
      variant: "insert",
      value: "\n- \u203B\u7B87\u6761\u66F8\u304D",
      cover: {
        kind: "icon",
        provider: "google",
        name: "format_list_bulleted"
      }
    },
    "ol": {
      caption: "\u756A\u53F7\u4ED8\u304D\u7B87\u6761\u66F8\u304D",
      variant: "insert",
      value: "\n+ \u203B\u6570\u5B57\u7B87\u6761\u66F8\u304D\n+ \u203B\u6570\u5B57\u7B87\u6761\u66F8\u304D\n+ \u203B\u6570\u5B57\u7B87\u6761\u66F8\u304D\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "format_list_numbered"
      }
    },
    "anchor": {
      caption: "\u30A2\u30F3\u30AB\u30FC",
      variant: "insert",
      value: "&aname(ID);",
      cover: {
        kind: "icon",
        provider: "google",
        name: "add_location"
      }
    },
    "button": {
      caption: "\u30DC\u30BF\u30F3",
      variant: "dialog",
      dialog: [
        {
          message: "\u8868\u793A\u6587\u5B57",
          option: {
            type: "text",
            width: 200,
            useSelection: true
          }
        },
        {
          message: "\u30EA\u30F3\u30AF",
          option: {
            type: "text",
            width: 200
          }
        },
        {
          message: "\u30B5\u30A4\u30BA",
          option: {
            type: "radio",
            values: [
              {
                label: "\u6A19\u6E96",
                value: "",
                checked: true
              },
              {
                label: "\u5927\u304D\u3044",
                value: "lg",
                checked: false
              },
              {
                label: "\u5C0F\u3055\u3044",
                value: "sm",
                checked: false
              },
              {
                label: "\u6975\u5C0F",
                value: "xs",
                checked: false
              }
            ]
          }
        },
        {
          message: "\u8272",
          option: {
            type: "radio",
            values: [
              {
                label: "\u7070\u8272",
                color: "#babcbc",
                value: "",
                checked: true
              },
              {
                label: "\u9752\u7DD1",
                color: "#4ecdc4",
                value: "info",
                checked: false
              },
              {
                label: "\u9752",
                color: "#3bafda",
                value: "primary",
                checked: false
              },
              {
                label: "\u7DD1",
                color: "#9fd85d",
                value: "success",
                checked: false
              },
              {
                label: "\u9EC4",
                color: "#ffc551",
                value: "warning",
                checked: false
              },
              {
                label: "\u8D64",
                color: "#fc5f62",
                value: "danger",
                checked: false
              }
            ]
          }
        },
        {
          message: "\u30AA\u30D7\u30B7\u30E7\u30F3",
          option: {
            type: "select",
            values: [
              {
                label: "\u30D6\u30ED\u30C3\u30AF\uFF08\u6A2A\u5E45\u4E00\u676F\uFF09",
                value: "block",
                checked: false
              },
              {
                label: "\u30B0\u30E9\u30C7\u30FC\u30B7\u30E7\u30F3",
                value: "gradient",
                checked: false
              },
              {
                label: "\u7E01\u53D6\u308A",
                value: "ghost",
                checked: false
              },
              {
                label: "\u7E01\u53D6\u308A\uFF08\u80CC\u666F\u767D\u4EE5\u5916\uFF09",
                value: "ghost-w",
                checked: false
              }
            ]
          }
        }
      ],
      value: "&button(${2},${3},${4},${5}){${1}};",
      cover: {
        kind: "icon",
        provider: "google",
        name: "edit_attributes"
      }
    },
    "html": {
      caption: "HTML\u633F\u5165",
      variant: "insert",
      value: "\n#html{{\n\u203BHTML\u30B3\u30FC\u30C9\n}}\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "html"
      }
    },
    "label": {
      caption: "\u30E9\u30D9\u30EB",
      variant: "dialog",
      value: "&label(${2}){${1}};",
      cover: {
        kind: "icon",
        provider: "google",
        name: "pin"
      },
      dialog: [
        {
          message: "\u8868\u793A\u6587\u5B57",
          option: {
            type: "text",
            useSelection: true
          }
        },
        {
          message: "\u8272",
          option: {
            type: "radio",
            values: [
              {
                label: "\u7070\u8272",
                color: "#777777",
                value: "",
                checked: true
              },
              {
                label: "\u6C34\u8272",
                color: "#5bc0de",
                value: "info",
                checked: false
              },
              {
                label: "\u9752",
                color: "#337ab7",
                value: "primary",
                checked: false
              },
              {
                label: "\u7DD1",
                color: "#5cb85c",
                value: "success",
                checked: false
              },
              {
                label: "\u9EC4",
                color: "#f0ad4e",
                value: "warning",
                checked: false
              },
              {
                label: "\u8D64",
                color: "#d9534f",
                value: "danger",
                checked: false
              }
            ]
          }
        }
      ]
    },
    // TODO: dialog
    "icon": {
      caption: "\u30A2\u30A4\u30B3\u30F3",
      variant: "dialog",
      cover: {
        kind: "icon",
        provider: "google",
        name: "interests"
      },
      value: "&icon(${2},${3},${4});",
      dialog: [
        {
          message: "",
          option: {
            type: "icon-header"
          }
        },
        {
          message: "\u30A2\u30A4\u30B3\u30F3\u30B3\u30FC\u30C9",
          option: {
            type: "text"
          }
        },
        {
          message: "\u8272",
          tip: "\uFF08\u30AB\u30E9\u30FC\u30B3\u30FC\u30C9/\u30AB\u30E9\u30FC\u30CD\u30FC\u30E0\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "\u30B5\u30A4\u30BA",
          tip: "\uFF08\u6570\u5024/em/\u30AD\u30FC\u30EF\u30FC\u30C9\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "",
          option: {
            type: "font-size-guide"
          }
        }
      ]
    },
    "center": {
      caption: "\u4E2D\u592E\u5BC4\u305B",
      variant: "insert",
      value: "\nCENTER:\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "format_align_center"
      }
    },
    "right": {
      caption: "\u53F3\u5BC4\u305B",
      variant: "insert",
      value: "\nRIGHT:\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "format_align_right"
      }
    },
    "layout": {
      caption: "\u30EC\u30A4\u30A2\u30A6\u30C8",
      variant: "dialog",
      value: "\n#layout(${1})\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "dashboard"
      },
      dialog: [
        {
          message: "\u30EC\u30A4\u30A2\u30A6\u30C8\u540D",
          option: {
            type: "radio",
            values: [
              {
                label: "\u30E9\u30F3\u30C7\u30A3\u30F3\u30B0",
                icon: "crop_din",
                value: "landing",
                checked: true
              },
              {
                label: "\u30D5\u30EB",
                icon: "padding",
                value: "fullpage",
                checked: false
              },
              {
                label: "\u30CE\u30FC\u30E1\u30CB\u30E5\u30FC",
                icon: "web_asset",
                value: "nomenu",
                checked: false
              },
              {
                label: "\u30EF\u30A4\u30C9",
                icon: "view_sidebar",
                value: "wide",
                checked: false
              }
            ]
          }
        }
      ]
    },
    "section": {
      caption: "\u30BB\u30AF\u30B7\u30E7\u30F3",
      variant: "dialog",
      value: "\n#section(jumbotron,${1},${2},${3},${4},${5},${6},${8},${9},${10}){{\n${selection}\n}}\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "crop_3_2"
      },
      dialog: [
        {
          message: "\u6C34\u5E73\u4F4D\u7F6E",
          option: {
            type: "radio",
            values: [
              {
                label: "\u5DE6",
                value: "left"
              },
              {
                label: "\u4E2D\u592E",
                value: "center"
              },
              {
                label: "\u53F3",
                value: "right"
              }
            ]
          }
        },
        {
          message: "\u5782\u76F4\u4F4D\u7F6E",
          option: {
            type: "radio",
            values: [
              {
                label: "\u4E0A",
                value: "top"
              },
              {
                label: "\u4E2D\u592E",
                value: "middle"
              },
              {
                label: "\u4E0B",
                value: "bottom"
              }
            ]
          }
        },
        {
          message: "\u6587\u5B57\u8272",
          tip: "\uFF08\u30AB\u30E9\u30FC\u30B3\u30FC\u30C9/\u30AB\u30E9\u30FC\u30CD\u30FC\u30E0\uFF09",
          option: {
            type: "text",
            prefix: "color="
          }
        },
        {
          message: "\u80CC\u666F\u8272",
          tip: "\uFF08\u30AB\u30E9\u30FC\u30B3\u30FC\u30C9/\u30AB\u30E9\u30FC\u30CD\u30FC\u30E0\uFF09",
          option: {
            type: "text",
            prefix: "bgcolor="
          }
        },
        {
          message: "\u9AD8\u3055",
          tip: "\uFF08\u4F8B\uFF1A500\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "\u80CC\u666F\u753B\u50CF",
          tip: "\uFF08\u4F8B\uFF1Aimage/jpg\uFF09",
          option: {
            type: "text"
          }
        },
        {
          message: "\u80CC\u666F\u753B\u50CF\u30AA\u30D7\u30B7\u30E7\u30F3",
          option: {
            type: "section-header"
          }
        },
        {
          message: "\u660E\u308B\u304F\u3059\u308B",
          tip: "\uFF080\u301C100\uFF09",
          option: {
            type: "text",
            prefix: "light="
          }
        },
        {
          message: "\u6697\u304F\u3059\u308B",
          tip: "\uFF080\u301C100\uFF09",
          option: {
            type: "text",
            prefix: "dark="
          }
        },
        {
          message: "",
          option: {
            type: "checkbox",
            values: [
              {
                label: "\u56FA\u5B9A",
                value: "fixed"
              },
              {
                label: "\u307C\u304B\u3059",
                value: "blur"
              },
              {
                label: "\u30D5\u30A3\u30C3\u30C8",
                value: "fit"
              },
              {
                label: "\u30D5\u30EB",
                value: "page"
              }
            ]
          }
        }
      ]
    },
    //
    "column:2": {
      caption: "\u6BB5\u7D44\u307F2\u5217",
      variant: "insert",
      value: "\n#cols{{\n\u203B\u5DE6\u306E\u5185\u5BB9\n====\n\u203B\u53F3\u306E\u5185\u5BB9\n}}",
      cover: {
        kind: "icon",
        provider: "google",
        name: "chrome_reader_mode"
      }
    },
    "column:3": {
      caption: "\u6BB5\u7D44\u307F3\u5217",
      variant: "insert",
      value: "\n#cols{{\n\u203B\u5DE6\u306E\u5185\u5BB9\n====\n\u203B\u4E2D\u592E\u306E\u5185\u5BB9\n====\n\u203B\u53F3\u306E\u5185\u5BB9\n}}",
      cover: {
        kind: "icon",
        provider: "google",
        name: "view_week"
      }
    },
    "table": {
      caption: "\u8868",
      variant: "insert",
      value: "\n//|STYLE:class=table table-striped|\n|LEFT:50|LEFT:50|c\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "window"
      }
    },
    "viewport:pc": {
      caption: "PC\u8868\u793A",
      variant: "insert",
      value: "\n#only_pc{{\n\u203B\u5185\u5BB9\n}}\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "desktop_windows"
      }
    },
    "viewport:sp": {
      caption: "SP\u8868\u793A",
      variant: "insert",
      value: "\n#only_mobile{{\n\u203B\u5185\u5BB9\n}}\n",
      cover: {
        kind: "icon",
        provider: "google",
        name: "smartphone"
      }
    },
    "color:red": {
      caption: "\u8D64\u8272",
      variant: "insert",
      value: "#DC143C",
      cover: {
        kind: "color",
        color: "#DC143C"
      }
    },
    "color:blue": {
      caption: "\u9752\u8272",
      variant: "insert",
      value: "#4169E1",
      cover: {
        kind: "color",
        color: "#4169E1"
      }
    }
  };
  var buttonNames = Object.keys(buttonData);

  // js/clickpad2/variant-dialog.js
  var makeButtonVariantDialog = (buttonId, buttonDefinition) => {
    if (buttonDefinition.variant !== "dialog") {
      throw new Error("variant is not dialog");
    }
    const button = document.createElement("button");
    button.classList.add("clickpad2__pallet-button");
    button.dataset.id = buttonId;
    button.dataset.variant = "dialog";
    button.textContent = buttonDefinition.caption;
    button.type = "button";
    button.onclick = () => {
      const dialog = document.createElement("dialog");
      dialog.classList.add("clickpad2__dialog", `clickpad2__dialog--${buttonId}`);
      const form = document.createElement("form");
      const content = document.createElement("div");
      content.classList.add("clickpad2__dialog-content");
      buttonDefinition.dialog.forEach(({ message, tip, option }, index) => {
        const wrapper = document.createElement("div");
        wrapper.classList.add("clickpad2__dialog-item", `clickpad2__dialog-item--${option.type}`);
        const id = `dialog-control-${index + 1}`;
        switch (option.type) {
          case "text": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-text-item");
            if (message !== void 0) {
              const label = document.createElement("label");
              label.classList.add("clickpad2__dialog-item-label");
              label.textContent = message;
              label.htmlFor = id;
              if (tip !== void 0) {
                const small = document.createElement("small");
                small.textContent = tip;
                label.appendChild(small);
              }
              item.appendChild(label);
            }
            const input = document.createElement("input");
            input.id = id;
            input.name = id;
            input.onkeydown = (e) => {
              e.stopPropagation();
            };
            input.type = "text";
            if (option.useSelection) {
              const textarea = document.querySelector("#msg");
              const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
              input.value = selectedText;
            }
            if (option.prefix !== void 0) {
              input.dataset.prefix = option.prefix;
            }
            item.appendChild(input);
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
          case "checkbox": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-checkbox-item");
            if (message !== void 0) {
              const label = document.createElement("label");
              label.classList.add("clickpad2__dialog-item-label");
              label.textContent = message;
              if (tip !== void 0) {
                const small = document.createElement("small");
                small.textContent = tip;
                label.appendChild(small);
              }
              item.appendChild(label);
            }
            option.values.forEach(({ label, value }, index2) => {
              const _id = `${id}-${index2 + 1}`;
              const labelElement = document.createElement("label");
              labelElement.classList.add("clickpad2__dialog-item-label");
              const input = document.createElement("input");
              input.id = _id;
              input.type = "checkbox";
              input.name = id;
              input.value = value;
              labelElement.appendChild(input);
              labelElement.appendChild(document.createTextNode(label));
              item.appendChild(labelElement);
            });
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
          case "radio": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-radio-item");
            if (message !== void 0) {
              const label = document.createElement("label");
              label.classList.add("clickpad2__dialog-item-label");
              label.textContent = message;
              if (tip !== void 0) {
                const small = document.createElement("small");
                small.textContent = tip;
                label.appendChild(small);
              }
              item.appendChild(label);
            }
            option.values.forEach(({ label, color, icon, value, checked }, index2) => {
              const _id = `${id}-${index2 + 1}`;
              const labelElement = document.createElement("label");
              labelElement.classList.add("clickpad2__dialog-radio-item-label");
              const input = document.createElement("input");
              input.id = _id;
              input.type = "radio";
              input.name = id;
              input.value = value;
              input.checked = checked;
              labelElement.appendChild(input);
              if (color !== void 0) {
                labelElement.classList.add("clickpad2__dialog-radio-item-label--color");
                const colorBox = document.createElement("span");
                colorBox.classList.add("clickpad2__dialog-radio-color-box");
                colorBox.title = label;
                colorBox.style.backgroundColor = color;
                labelElement.appendChild(colorBox);
              } else if (icon !== void 0) {
                labelElement.classList.add("clickpad2__dialog-radio-item-label--icon");
                const iconElement = document.createElement("span");
                iconElement.classList.add("material-icons-outlined");
                iconElement.textContent = icon;
                labelElement.appendChild(iconElement);
                const text = document.createElement("span");
                text.textContent = label;
                text.classList.add("clickpad2__dialog-radio-item-label-text");
                labelElement.appendChild(text);
              } else {
                labelElement.appendChild(document.createTextNode(label));
              }
              item.appendChild(labelElement);
            });
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
          case "select": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-select-item");
            if (message !== void 0) {
              const label = document.createElement("label");
              label.classList.add("clickpad2__dialog-item-label");
              label.textContent = message;
              if (tip !== void 0) {
                const small = document.createElement("small");
                small.textContent = tip;
                label.appendChild(small);
              }
              item.appendChild(label);
            }
            option.values.forEach(({ label, value, checked }, index2) => {
              const _id = `${id}-${index2 + 1}`;
              const labelElement = document.createElement("label");
              const input = document.createElement("input");
              input.id = _id;
              input.type = "checkbox";
              input.name = id;
              input.value = value;
              input.checked = checked;
              labelElement.appendChild(input);
              labelElement.appendChild(document.createTextNode(label));
              item.appendChild(labelElement);
            });
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
          case "font-size-guide": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-font-size-guide-item");
            const legend = document.createElement("div");
            legend.classList.add("clickpad2__dialog-font-size-guide-item-legend");
            const legendTitle = document.createElement("span");
            legendTitle.textContent = "[ \u6587\u5B57\u30B5\u30A4\u30BA\u6307\u5B9A\u30AD\u30FC\u30EF\u30FC\u30C9 ]";
            legend.appendChild(legendTitle);
            const br = document.createElement("br");
            legend.appendChild(br);
            const legendTip = document.createElement("span");
            legendTip.textContent = "xx-small / x-small / small / medium\uFF08\u521D\u671F\u5024\uFF09/ large / x-large / xx-large";
            legend.appendChild(legendTip);
            item.appendChild(legend);
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
          case "section-header": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-section-header-item");
            const heading = document.createElement("h2");
            heading.textContent = message;
            item.appendChild(heading);
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
          case "icon-header": {
            const item = document.createElement("div");
            item.classList.add("clickpad2__dialog-icon-header-item");
            const title = document.createElement("h3");
            title.textContent = "Google \u30A2\u30A4\u30B3\u30F3\u691C\u7D22\u30EA\u30F3\u30AF";
            title.classList.add("clickpad2__dialog-icon-header-item-title");
            item.appendChild(title);
            const materialIconsLink = document.createElement("a");
            materialIconsLink.textContent = "Material Icons";
            materialIconsLink.href = "https://fonts.google.com/icons?icon.set=Material+Icons";
            materialIconsLink.target = "_blank";
            item.appendChild(materialIconsLink);
            const materialSymbolsLink = document.createElement("a");
            materialSymbolsLink.textContent = "Material Symbols";
            materialSymbolsLink.href = "https://fonts.google.com/icons?icon.set=Material+Symbols";
            materialSymbolsLink.target = "_blank";
            item.appendChild(materialSymbolsLink);
            const description = document.createElement("p");
            description.textContent = "(\u30A2\u30A4\u30B3\u30F3\u3092\u30AF\u30EA\u30C3\u30AF\u3057\u3066\u8868\u793A\u3055\u308C\u308B\u53F3\u5074\u30A6\u30A3\u30F3\u30C9\u30A6\u5185\u306E<span ...\u304B\u3089\u59CB\u307E\u308B\u67A0\u5185\u306E\u30B3\u30FC\u30C9\u3092 [\u30A2\u30A4\u30B3\u30F3\u30B3\u30FC\u30C9] \u306B\u5165\u529B\u3057\u307E\u3059\uFF09";
            item.appendChild(description);
            wrapper.appendChild(item);
            content.appendChild(wrapper);
            break;
          }
        }
      });
      form.appendChild(content);
      dialog.appendChild(form);
      const action = document.createElement("div");
      action.classList.add("clickpad2__dialog-action");
      const close = document.createElement("button");
      close.classList.add("btn", "btn-text");
      close.type = "button";
      close.textContent = "\u30AD\u30E3\u30F3\u30BB\u30EB";
      close.onclick = () => {
        dialog.close();
        document.querySelector("#msg").focus();
      };
      action.appendChild(close);
      const insert = document.createElement("button");
      insert.classList.add("btn", "btn-primary");
      insert.type = "submit";
      insert.textContent = "OK";
      insert.onclick = (e) => {
        e.preventDefault();
        const textarea = document.querySelector("#msg");
        const { selectionStart, selectionEnd } = textarea;
        const selectedText = textarea.value.substring(selectionStart, selectionEnd);
        const textBefore = textarea.value.substring(0, selectionStart);
        const textAfter = textarea.value.substring(selectionEnd);
        let insertText = buttonDefinition.value;
        const form2 = dialog.querySelector("form");
        const formData = new FormData(form2);
        const formValues = Array.from(formData.entries()).reduce((memo, [key, value]) => {
          if (memo[key] !== void 0) {
            memo[key].push(value);
          } else {
            memo[key] = [value];
          }
          return memo;
        }, {});
        for (const [key, values] of Object.entries(formValues)) {
          const index = key.match(/(\d+)/)[1];
          console.log({ index, values });
          const prefix = form2.querySelector(`[name="${key}"]`)?.dataset?.prefix ?? "";
          const joinedValue = values.join(",");
          const valueText = joinedValue.length > 0 ? prefix + joinedValue : joinedValue;
          insertText = insertText.replace("${" + index + "}", valueText);
        }
        insertText = insertText.replace(/\$\{\d+\}/g, "");
        insertText = insertText.replace("${selection}", selectedText);
        textarea.value = textBefore + insertText + textAfter;
        textarea.setSelectionRange(selectionStart, selectionStart + insertText.length);
        dialog.close();
        textarea.focus();
      };
      action.appendChild(insert);
      form.appendChild(action);
      document.body.appendChild(dialog);
      dialog.showModal();
      dialog.onclose = () => {
        dialog.remove();
      };
    };
    return button;
  };

  // js/clickpad2/variant-insert.js
  var makeButtonVariantInsert = (buttonId, buttonDefinition) => {
    if (buttonDefinition.variant !== "insert") {
      throw new Error("variant is not insert");
    }
    const button = document.createElement("button");
    button.classList.add("clickpad2__pallet-button");
    button.dataset.id = buttonId;
    button.dataset.variant = "insert";
    button.textContent = buttonDefinition.caption;
    button.type = "button";
    button.onclick = () => {
      const textarea = document.querySelector("#msg");
      const cursorPos = textarea.selectionStart;
      const textBefore = textarea.value.substring(0, cursorPos);
      const textAfter = textarea.value.substring(cursorPos);
      textarea.value = textBefore + buttonDefinition.value + textAfter;
      textarea.setSelectionRange(cursorPos + buttonDefinition.value.length, cursorPos + buttonDefinition.value.length);
      textarea.focus();
    };
    return button;
  };

  // js/clickpad2/variant-wrap.js
  var makeButtonVariantWrap = (buttonId, buttonDefinition) => {
    if (buttonDefinition.variant !== "wrap") {
      throw new Error("variant is not wrap");
    }
    const button = document.createElement("button");
    button.classList.add("clickpad2__pallet-button");
    button.dataset.id = buttonId;
    button.dataset.variant = "wrap";
    button.textContent = buttonDefinition.caption;
    button.type = "button";
    button.onclick = () => {
      const textarea = document.querySelector("#msg");
      const { selectionStart, selectionEnd } = textarea;
      const textBefore = textarea.value.substring(0, selectionStart);
      const textSelected = textarea.value.substring(selectionStart, selectionEnd);
      const textAfter = textarea.value.substring(selectionEnd);
      textarea.value = textBefore + buttonDefinition.prefix + textSelected + buttonDefinition.suffix + textAfter;
      textarea.setSelectionRange(selectionStart, selectionEnd + buttonDefinition.prefix.length + buttonDefinition.suffix.length);
      textarea.focus();
    };
    return button;
  };

  // js/clickpad2/entry.js
  var pallets = [
    ["add-image"],
    ["title", "delimiter:start", "delimiter:end", "comment-out"],
    ["br", "margin", "hr"],
    ["h1", "h2", "h3", "h4"],
    ["link", "bold", "deco"],
    ["li", "ol"],
    ["anchor", "button", "html", "label", "icon"],
    ["center", "right"],
    ["layout", "section", "column:2", "column:3", "table"],
    ["viewport:pc", "viewport:sp"],
    ["color:red", "color:blue"]
  ];
  function showPallet(textarea) {
    const pallet = document.createElement("div");
    pallet.classList.add("clickpad2__container");
    textarea.parentNode.insertBefore(pallet, textarea.nextSibling);
    pallets.forEach((row) => {
      const rowElem = document.createElement("div");
      rowElem.classList.add("clickpad2__pallet-row");
      pallet.appendChild(rowElem);
      row.forEach((buttonId) => {
        const buttonDefinition = buttonData[buttonId];
        let button;
        if (buttonDefinition.variant === "insert") {
          button = makeButtonVariantInsert(buttonId, buttonDefinition);
        } else if (buttonDefinition.variant === "wrap") {
          button = makeButtonVariantWrap(buttonId, buttonDefinition);
        } else if (buttonDefinition.variant === "dialog") {
          button = makeButtonVariantDialog(buttonId, buttonDefinition);
        }
        rowElem.appendChild(coverButton(button, buttonDefinition.cover));
      });
    });
  }
  document.addEventListener("DOMContentLoaded", () => {
    showPallet(document.querySelector("#msg"));
  });
})();
//# sourceMappingURL=clickpad2.js.map
