(() => {
  // js/clickpad2/buttonData.js
  var buttonData = {
    "add-image": {
      caption: "\u753B\u50CF\u8FFD\u52A0",
      variant: "insert",
      value: "&show(,,\u753B\u50CF\u306E\u8AAC\u660E);"
    },
    "title": {
      caption: "\u30BF\u30A4\u30C8\u30EB",
      variant: "insert",
      value: "\nTITLE:"
    },
    "delimiter:start": {
      caption: "\u7DE8\u96C6\u533A\u5207\u308A\u7DDA\uFF08\u958B\u59CB\u70B9\uFF09",
      variant: "insert",
      value: "\n//\u25BC \u2508 <\u5185\u5BB9\u540D\u30FB\u59CB\u70B9> \u2508 \u25BC\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u25BC\n"
    },
    "delimiter:end": {
      caption: "\u7DE8\u96C6\u533A\u5207\u308A\u7DDA\uFF08\u7D42\u4E86\u70B9\uFF09",
      variant: "insert",
      value: "\n//\u25B2 \u2508 <\u5185\u5BB9\u540D\u30FB\u7D42\u70B9> \u2508 \u25B2\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u2508\u25B2\n"
    },
    "comment-out": {
      caption: "\u30B3\u30E1\u30F3\u30C8\u30A2\u30A6\u30C8",
      variant: "insert",
      value: "\n//"
    },
    "br": {
      caption: "\u6539\u884C",
      variant: "insert",
      value: "&br;"
    },
    "margin": {
      caption: "\u4F59\u767D\u8ABF\u6574",
      variant: "insert",
      value: "#br(30)"
    },
    "hr": {
      caption: "\u533A\u5207\u308A\u7DDA",
      variant: "insert",
      value: "\n----\n"
    },
    "h1": {
      caption: "\u898B\u51FA\u30571",
      variant: "insert",
      value: "\n! "
    },
    "h2": {
      caption: "\u898B\u51FA\u30572",
      variant: "insert",
      value: "\n* "
    },
    "h3": {
      caption: "\u898B\u51FA\u30573",
      variant: "insert",
      value: "\n** "
    },
    "h4": {
      caption: "\u898B\u51FA\u30574",
      variant: "insert",
      value: "\n*** "
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
      value: "[[${1}>${2}]]"
    },
    "bold": {
      caption: "\u592A\u5B57",
      variant: "wrap",
      prefix: "''",
      suffix: "''"
    },
    // TODO: dialog
    "deco": {
      caption: "\u88C5\u98FE",
      variant: "wrap",
      prefix: "%%",
      suffix: "%%"
    },
    "li": {
      caption: "\u7B87\u6761\u66F8\u304D",
      variant: "insert",
      value: "\n- \u203B\u7B87\u6761\u66F8\u304D"
    },
    "ol": {
      caption: "\u756A\u53F7\u4ED8\u304D\u7B87\u6761\u66F8\u304D",
      variant: "insert",
      value: "\n+ \u203B\u6570\u5B57\u7B87\u6761\u66F8\u304D\n+ \u203B\u6570\u5B57\u7B87\u6761\u66F8\u304D\n+ \u203B\u6570\u5B57\u7B87\u6761\u66F8\u304D\n"
    },
    "anchor": {
      caption: "\u30A2\u30F3\u30AB\u30FC",
      variant: "insert",
      value: "&aname(ID);"
    },
    // TODO: dialog
    "button": {
      caption: "\u30DC\u30BF\u30F3",
      variant: "insert",
      value: "&button(\u30DC\u30BF\u30F3\u540D,URL);"
    },
    "html": {
      caption: "HTML\u633F\u5165",
      variant: "insert",
      value: "\n#html{{\n\u203BHTML\u30B3\u30FC\u30C9\n}}\n"
    },
    // TODO: dialog
    "label": {
      caption: "\u30E9\u30D9\u30EB",
      variant: "insert",
      value: "&label(label);"
    },
    // TODO: dialog
    "icon": {
      caption: "\u30A2\u30A4\u30B3\u30F3",
      variant: "insert",
      value: "&icon(icon);"
    },
    "center": {
      caption: "\u4E2D\u592E\u5BC4\u305B",
      variant: "insert",
      value: "\nCENTER:\n"
    },
    "right": {
      caption: "\u53F3\u5BC4\u305B",
      variant: "insert",
      value: "\nRIGHT:\n"
    },
    // TODO: dialog
    "layout": {
      caption: "\u30EC\u30A4\u30A2\u30A6\u30C8",
      variant: "insert",
      value: "\n#layout(\u30EC\u30A4\u30A2\u30A6\u30C8\u540D);"
    },
    // TODO: dialog
    "section": {
      caption: "\u30BB\u30AF\u30B7\u30E7\u30F3",
      variant: "insert",
      value: "\n#section(\u30BB\u30AF\u30B7\u30E7\u30F3\u540D);"
    },
    //
    "column:2": {
      caption: "\u6BB5\u7D44\u307F2\u5217",
      variant: "insert",
      value: "\n#cols{{\n\u203B\u5DE6\u306E\u5185\u5BB9\n====\n\u203B\u53F3\u306E\u5185\u5BB9\n}}"
    },
    "column:3": {
      caption: "\u6BB5\u7D44\u307F3\u5217",
      variant: "insert",
      value: "\n#cols{{\n\u203B\u5DE6\u306E\u5185\u5BB9\n====\n\u203B\u4E2D\u592E\u306E\u5185\u5BB9\n====\n\u203B\u53F3\u306E\u5185\u5BB9\n}}"
    },
    "table": {
      caption: "\u8868",
      variant: "insert",
      value: "\n//|STYLE:class=table table-striped|\n|LEFT:50|LEFT:50|c\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n|~\u898B\u51FA\u3057|\u5185\u5BB9|\n"
    },
    "viewport:pc": {
      caption: "PC\u8868\u793A",
      variant: "insert",
      value: "\n#only_pc{{\n\u203B\u5185\u5BB9\n}}\n"
    },
    "viewport:sp": {
      caption: "SP\u8868\u793A",
      variant: "insert",
      value: "\n#only_mobile{{\n\u203B\u5185\u5BB9\n}}\n"
    },
    "color:red": {
      caption: "\u8D64\u8272",
      variant: "insert",
      value: "#DC143C"
    },
    "color:blue": {
      caption: "\u9752\u8272",
      variant: "insert",
      value: "#4169E1"
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
      dialog.classList.add("clickpad2__dialog");
      const form = document.createElement("form");
      const content = document.createElement("div");
      content.classList.add("clickpad2__dialog-content");
      buttonDefinition.dialog.forEach(({ message, option }, index) => {
        const wrapper = document.createElement("div");
        const id = `dialog-control-${index + 1}`;
        const label = document.createElement("label");
        label.textContent = message;
        label.htmlFor = id;
        content.appendChild(label);
        const input = document.createElement("input");
        input.id = id;
        input.onkeydown = (e) => {
          e.stopPropagation();
        };
        input.type = option.type;
        if (option.useSelection) {
          const textarea = document.querySelector("#msg");
          const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
          input.value = selectedText;
        }
        wrapper.appendChild(input);
        content.appendChild(wrapper);
      });
      form.appendChild(content);
      dialog.appendChild(form);
      const close = document.createElement("button");
      close.type = "button";
      close.textContent = "\u9589\u3058\u308B";
      close.onclick = () => {
        dialog.close();
        document.querySelector("#msg").focus();
      };
      dialog.appendChild(close);
      const insert = document.createElement("button");
      insert.type = "submit";
      insert.textContent = "\u633F\u5165";
      insert.onclick = (e) => {
        e.preventDefault();
        const textarea = document.querySelector("#msg");
        const { selectionStart, selectionEnd } = textarea;
        const selectedText = textarea.value.substring(selectionStart, selectionEnd);
        const textBefore = textarea.value.substring(0, selectionStart);
        const textAfter = textarea.value.substring(selectionEnd);
        let insertText = buttonDefinition.value;
        content.querySelectorAll("input").forEach((input, index) => {
          insertText = insertText.replace(`\${${index + 1}}`, input.value);
        });
        insertText = insertText.replace("${selection}", selectedText);
        textarea.value = textBefore + insertText + textAfter;
        textarea.setSelectionRange(selectionStart, selectionStart + insertText.length);
        dialog.close();
        textarea.focus();
      };
      form.appendChild(insert);
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
        if (buttonDefinition.variant === "insert") {
          const button = makeButtonVariantInsert(buttonId, buttonDefinition);
          rowElem.appendChild(button);
        } else if (buttonDefinition.variant === "wrap") {
          const button = makeButtonVariantWrap(buttonId, buttonDefinition);
          rowElem.appendChild(button);
        } else if (buttonDefinition.variant === "dialog") {
          const button = makeButtonVariantDialog(buttonId, buttonDefinition);
          rowElem.appendChild(button);
        }
      });
    });
  }
  document.addEventListener("DOMContentLoaded", () => {
    showPallet(document.querySelector("#msg"));
  });
})();
//# sourceMappingURL=clickpad2.js.map
