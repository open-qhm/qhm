/** @type { import("./types.ts").ButtonData } */
export const buttonData = {
  'add-image': {
    caption: '画像追加',
    variant: 'insert',
    value: '&show(,,画像の説明);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "add_photo_alternate"
    }
  },
  'title': {
    caption: 'タイトル',
    variant: 'insert',
    value: '\nTITLE:',
    cover: {
      kind: "icon",
      provider: "google",
      name: "title"
    }
  },
  'delimiter:start': {
    caption: '編集区切り線（開始点）',
    variant: 'insert',
    value: '\n//▼ ┈ <内容名・始点> ┈ ▼┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈▼\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "arrow_drop_down"
    }
  },
  'delimiter:end': {
    caption: '編集区切り線（終了点）',
    variant: 'insert',
    value: '\n//▲ ┈ <内容名・終点> ┈ ▲┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈▲\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "arrow_drop_up"
    }
  },
  'comment-out': {
    caption: 'コメントアウト',
    variant: 'insert',
    value: '\n//',
    cover: {
      kind: "icon",
      provider: "google",
      name: "visibility_off"
    }
  },
  'br': {
    caption: '改行',
    variant: 'insert',
    value: '&br;',
    cover: {
      kind: "icon",
      provider: "google",
      name: "subdirectory_arrow_left"
    }
  },
  'margin': {
    caption: '余白調整',
    variant: 'insert',
    value: '#br(30)',
    cover: {
      kind: "icon",
      provider: "google",
      name: "expand"
    }
  },
  'hr': {
    caption: '区切り線',
    variant: 'insert',
    value: '\n----\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "horizontal_rule"
    }
  },
  'h1': {
    caption: '見出し1',
    variant: 'insert',
    value: '\n! ',
    cover: {
      kind: "text",
      text: "h1"
    }
  },
  'h2': {
    caption: '見出し2',
    variant: 'insert',
    value: '\n* ',
    cover: {
      kind: "text",
      text: "h2"
    }
  },
  'h3': {
    caption: '見出し3',
    variant: 'insert',
    value: '\n** ',
    cover: {
      kind: "text",
      text: "h3"
    }
  },
  'h4': {
    caption: '見出し4',
    variant: 'insert',
    value: '\n*** ',
    cover: {
      kind: "text",
      text: "h4"
    }
  },
  'link': {
    caption: 'リンク',
    variant: 'dialog',
    dialog: [
      {
        message: 'リンク名を入力してください。',
        option: {
          type: 'text',
          useSelection: true
        }
      },
      {
        message: 'リンク先（ページ名, URL）を入力してください。',
        option: {
          type: 'text',
        }
      }
    ],
    value: '[[${1}>${2}]]',
    cover: {
      kind: "icon",
      provider: "google",
      name: "link"
    }
  },
  'bold': {
    caption: '太字',
    variant: 'wrap',
    prefix: "''",
    suffix: "''",
    cover: {
      kind: "icon",
      provider: "google",
      name: "format_bold"
    }
  },
  // TODO: dialog
  'deco': {
    caption: '装飾',
    variant: 'wrap',
    prefix: '%%',
    suffix: '%%',
    cover: {
      kind: "icon",
      provider: "google",
      name: "border_color"
    }
  },
  'li': {
    caption: '箇条書き',
    variant: 'insert',
    value: '\n- ※箇条書き',
    cover: {
      kind: "icon",
      provider: "google",
      name: "format_list_bulleted"
    }
  },
  'ol': {
    caption: '番号付き箇条書き',
    variant: 'insert',
    value: '\n+ ※数字箇条書き\n+ ※数字箇条書き\n+ ※数字箇条書き\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "format_list_numbered"
    }
  },
  'anchor': {
    caption: 'アンカー',
    variant: 'insert',
    value: '&aname(ID);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "add_location"
    }
  },
  // TODO: dialog
  'button': {
    caption: 'ボタン',
    variant: 'insert',
    value: '&button(ボタン名,URL);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "edit_attributes"
    }
  },
  'html': {
    caption: 'HTML挿入',
    variant: 'insert',
    value: '\n#html{{\n※HTMLコード\n}}\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "html"
    }
  },
  // TODO: dialog
  'label': {
    caption: 'ラベル',
    variant: 'insert',
    value: '&label(label);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "pin"
    }
  },
  // TODO: dialog
  'icon': {
    caption: 'アイコン',
    variant: 'insert',
    value: '&icon(icon);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "interests"
    }
  },
  'center': {
    caption: '中央寄せ',
    variant: 'insert',
    value: '\nCENTER:\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "format_align_center"
    }
  },
  'right': {
    caption: '右寄せ',
    variant: 'insert',
    value: '\nRIGHT:\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "format_align_right"
    }
  },
  // TODO: dialog
  'layout': {
    caption: 'レイアウト',
    variant: 'insert',
    value: '\n#layout(レイアウト名);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "dashboard"
    }
  },
  // TODO: dialog
  'section': {
    caption: 'セクション',
    variant: 'insert',
    value: '\n#section(セクション名);',
    cover: {
      kind: "icon",
      provider: "google",
      name: "crop_3_2"
    }
  },
  //
  'column:2': {
    caption: '段組み2列',
    variant: 'insert',
    value: '\n#cols{{\n※左の内容\n====\n※右の内容\n}}',
    cover: {
      kind: "icon",
      provider: "google",
      name: "chrome_reader_mode"
    }
  },
  'column:3': {
    caption: '段組み3列',
    variant: 'insert',
    value: '\n#cols{{\n※左の内容\n====\n※中央の内容\n====\n※右の内容\n}}',
    cover: {
      kind: "icon",
      provider: "google",
      name: "view_week"
    }
  },
  'table': {
    caption: '表',
    variant: 'insert',
    value: '\n//|STYLE:class=table table-striped|\n|LEFT:50|LEFT:50|c\n|~見出し|内容|\n|~見出し|内容|\n|~見出し|内容|\n|~見出し|内容|\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "window"
    }
  },
  'viewport:pc': {
    caption: 'PC表示',
    variant: 'insert',
    value: '\n#only_pc{{\n※内容\n}}\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "desktop_windows"
    }
  },
  'viewport:sp': {
    caption: 'SP表示',
    variant: 'insert',
    value: '\n#only_mobile{{\n※内容\n}}\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "smartphone"
    }
  },
  'color:red': {
    caption: '赤色',
    variant: 'insert',
    value: '#DC143C',
    cover: {
      kind: "color",
      color: "#DC143C"
    }
  },
  'color:blue': {
    caption: '青色',
    variant: 'insert',
    value: '#4169E1',
    cover: {
      kind: "color",
      color: "#4169E1"
    }
  },
}

export const buttonNames = Object.keys(buttonData)
