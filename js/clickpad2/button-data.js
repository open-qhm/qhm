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
  'deco': {
    caption: '装飾',
    variant: 'dialog',
    cover: {
      kind: "icon",
      provider: "google",
      name: "border_color"
    },
    dialog: [
      {
        option: {
          type: 'checkbox',
          values: [
            {
              label: '太字',
              value: 'b',
            },
            {
              label: '下線',
              value: 'u',
            },
            {
              label: '斜体',
              value: 'i',
            }
          ]
        },
      },
      {
        message: '文字色',
        tip: '（カラーコード/カラーネーム）',
        option: {
          type: 'text',
        },
      },
      {
        message: '背景色',
        tip: '（カラーコード/カラーネーム）',
        option: {
          type: 'text',
        },
      },
      {
        message: '文字サイズ',
        tip: '（数値/em/キーワード）',
        option: {
          type: 'text',
        },
      },
      {
        message: '',
        option: {
          type: 'font-size-guide',
        }
      }
    ],
    value: '&deco(${1},${2},${3},${4}){${selection}};',
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
  'button': {
    caption: 'ボタン',
    variant: 'dialog',
    dialog: [
      {
        message: "表示文字",
        option: {
          type: 'text',
          width: 200,
          useSelection: true,
        }
      },
      {
        message: "リンク",
        option: {
          type: 'text',
          width: 200,
        }
      },
      {
        message: "サイズ",
        option: {
          type: 'radio',
          values: [
            {
              label: '標準',
              value: '',
              checked: true
            },
            {
              label: '大きい',
              value: 'lg',
              checked: false,
            },
            {
              label: '小さい',
              value: 'sm',
              checked: false,
            },
            {
              label: '極小',
              value: 'xs',
              checked: false
            }
          ]
        }
      },
      {
        message: '色',
        option: {
          type: 'radio',
          values: [
            {
              label: '灰色',
              color: '#babcbc',
              value: '',
              checked: true,
            },
            {
              label: '青緑',
              color: '#4ecdc4',
              value: 'info',
              checked: false,
            },
            {
              label: '青',
              color: '#3bafda',
              value: 'primary',
              checked: false,
            },
            {
              label: '緑',
              color: '#9fd85d',
              value: 'success',
              checked: false,
            },
            {
              label: '黄',
              color: '#ffc551',
              value: 'warning',
              checked: false,
            },
            {
              label: '赤',
              color: '#fc5f62',
              value: 'danger',
              checked: false,
            },
          ]
        }
      },
      {
        message: 'オプション',
        option: {
          type: 'select',
          values: [
            {
              label: 'ブロック（横幅一杯）',
              value: 'block',
              checked: false,
            },
            {
              label: 'グラデーション',
              value: 'gradient',
              checked: false,
            },
            {
              label: '縁取り',
              value: 'ghost',
              checked: false,
            },
            {
              label: '縁取り（背景白以外）',
              value: 'ghost-w',
              checked: false,
            }
          ]
        }
      }
    ],
    value: '&button(${2},${3},${4},${5}){${1}};',
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
  'label': {
    caption: 'ラベル',
    variant: 'dialog',
    value: '&label(${2}){${1}};',
    cover: {
      kind: "icon",
      provider: "google",
      name: "pin"
    },
    dialog: [
      {
        message: '表示文字',
        option: {
          type: 'text',
          useSelection: true
        }
      },
      {
        message: '色',
        option: {
          type: 'radio',
          values: [
            {
              label: '灰色',
              color: '#777777',
              value: '',
              checked: true,
            },
            {
              label: '水色',
              color: '#5bc0de',
              value: 'info',
              checked: false,
            },
            {
              label: '青',
              color: '#337ab7',
              value: 'primary',
              checked: false,
            },
            {
              label: '緑',
              color: '#5cb85c',
              value: 'success',
              checked: false,
            },
            {
              label: '黄',
              color: '#f0ad4e',
              value: 'warning',
              checked: false,
            },
            {
              label: '赤',
              color: '#d9534f',
              value: 'danger',
              checked: false,
            }
          ]
        }
      }
    ]
  },
  // TODO: dialog
  'icon': {
    caption: 'アイコン',
    variant: 'dialog',
    cover: {
      kind: "icon",
      provider: "google",
      name: "interests"
    },
    value: '&icon(${2},${3},${4});',
    dialog: [
      {
        message: '',
        option: {
          type: 'icon-header',
        },
      },
      {
        message: 'アイコンコード',
        option: {
          type: 'text',
        }
      },
      {
        message: '色',
        tip: '（カラーコード/カラーネーム）',
        option: {
          type: 'text',
        }
      },
      {
        message: 'サイズ',
        tip: '（数値/em/キーワード）',
        option: {
          type: 'text'
        }
      },
      {
        message: '',
        option: {
          type: 'font-size-guide'
        }
      }
    ]
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
  'layout': {
    caption: 'レイアウト',
    variant: 'dialog',
    value: '\n#layout(${1})\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "dashboard"
    },
    dialog: [
      {
        message: 'レイアウト名',
        option: {
          type: 'radio',
          values: [
            {
              label: 'ランディング',
              icon: 'crop_din',
              value: 'landing',
              checked: true
            },
            {
              label: 'フル',
              icon: 'padding',
              value: 'fullpage',
              checked: false,
            },
            {
              label: 'ノーメニュー',
              icon: 'web_asset',
              value: 'nomenu',
              checked: false,
            },
            {
              label: 'ワイド',
              icon: 'view_sidebar',
              value: 'wide',
              checked: false,
            }
          ]
        }
      }
    ]
  },
  'section': {
    caption: 'セクション',
    variant: 'dialog',
    value: '\n#section(jumbotron,${1},${2},${3},${4},${5},${6},${8},${9},${10}){{\n${selection}\n}}\n',
    cover: {
      kind: "icon",
      provider: "google",
      name: "crop_3_2"
    },
    dialog: [
      {
        message: '水平位置',
        option: {
          type: 'radio',
          values: [
            {
              label: '左',
              value: 'left',
            },
            {
              label: '中央',
              value: 'center',
            },
            {
              label: '右',
              value: 'right',
            }
          ]
        }
      },
      {
        message: '垂直位置',
        option: {
          type: 'radio',
          values: [
            {
              label: '上',
              value: 'top',
            },
            {
              label: '中央',
              value: 'middle',
            },
            {
              label: '下',
              value: 'bottom',
            }
          ]
        }
      },
      {
        message: '文字色',
        tip: '（カラーコード/カラーネーム）',
        option: {
          type: 'text',
          prefix: 'color='
        }
      },
      {
        message: '背景色',
        tip: '（カラーコード/カラーネーム）',
        option: {
          type: 'text',
          prefix: 'bgcolor='
        }
      },
      {
        message: '高さ',
        tip: '（例：500）',
        option: {
          type: 'text',
        }
      },
      {
        message: '背景画像',
        tip: '（例：image/jpg）',
        option: {
          type: 'text',
        }
      },
      {
        message: '背景画像オプション',
        option: {
          type: 'section-header',
        }
      },
      {
        message: '明るくする',
        tip: '（0〜100）',
        option: {
          type: 'text',
          prefix: 'light=',
        }
      },
      {
        message: '暗くする',
        tip: '（0〜100）',
        option: {
          type: 'text',
          prefix: 'dark=',
        }
      },
      {
        message: '',
        option: {
          type: 'checkbox',
          values: [
            {
              label: '固定',
              value: 'fixed',
            },
            {
              label: 'ぼかす',
              value: 'blur',
            },
            {
              label: 'フィット',
              value: 'fit',
            },
            {
              label: 'フル',
              value: 'page',
            }
          ]
        }
      }
    ]
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
