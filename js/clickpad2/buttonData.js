/** @type { import("./types.ts").ButtonData } */
export const buttonData = {
  'add-image': {
    caption: '画像追加',
    variant: 'insert',
    value: '&show(,,画像の説明);'
  },
  'title': {
    caption: 'タイトル',
    variant: 'insert',
    value: '\nTITLE:',
  },
  'delimiter:start': {
    caption: '編集区切り線（開始点）',
    variant: 'insert',
    value: '\n//▼ ┈ <内容名・始点> ┈ ▼┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈▼\n'
  },
  'delimiter:end': {
    caption: '編集区切り線（終了点）',
    variant: 'insert',
    value: '\n//▲ ┈ <内容名・終点> ┈ ▲┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈┈▲\n'
  },
  'comment-out': {
    caption: 'コメントアウト',
    variant: 'insert',
    value: '\n//'
  },
  'br': {
    caption: '改行',
    variant: 'insert',
    value: '&br;'
  },
  'margin': {
    caption: '余白調整',
    variant: 'insert',
    value: '#br(30)'
  },
  'hr': {
    caption: '区切り線',
    variant: 'insert',
    value: '\n----\n'
  },
  'h1': {
    caption: '見出し1',
    variant: 'insert',
    value: '\n! '
  },
  'h2': {
    caption: '見出し2',
    variant: 'insert',
    value: '\n* ',
  },
  'h3': {
    caption: '見出し3',
    variant: 'insert',
    value: '\n** ',
  },
  'h4': {
    caption: '見出し4',
    variant: 'insert',
    value: '\n*** ',
  },
  // TODO: dialog
  'link': {
    caption: 'リンク',
    variant: 'wrap',
    prefix: '[[',
    suffix: ']]'
  },
  'bold': {
    caption: '太字',
    variant: 'wrap',
    prefix: "''",
    suffix: "''"
  },
  // TODO: dialog
  'deco': {
    caption: '装飾',
    variant: 'wrap',
    prefix: '%%',
    suffix: '%%'
  },
  'li': {
    caption: '箇条書き',
    variant: 'insert',
    value: '\n- ※箇条書き'
  },
  'ol': {
    caption: '番号付き箇条書き',
    variant: 'insert',
    value: '\n+ ※数字箇条書き\n+ ※数字箇条書き\n+ ※数字箇条書き\n'
  },
  'anchor': {
    caption: 'アンカー',
    variant: 'insert',
    value: '&aname(ID);'
  },
  // TODO: dialog
  'button': {
    caption: 'ボタン',
    variant: 'insert',
    value: '&button(ボタン名,URL);'
  },
  'html': {
    caption: 'HTML挿入',
    variant: 'insert',
    value: '\n#html{{\n※HTMLコード\n}}\n'
  },
  // TODO: dialog
  'label': {
    caption: 'ラベル',
    variant: 'insert',
    value: '&label(label);'
  },
  // TODO: dialog
  'icon': {
    caption: 'アイコン',
    variant: 'insert',
    value: '&icon(icon);'
  },
  'center': {
    caption: '中央寄せ',
    variant: 'insert',
    value: '\nCENTER:\n'
  },
  'right': {
    caption: '右寄せ',
    variant: 'insert',
    value: '\nRIGHT:\n'
  },
  // TODO: dialog
  'layout': {
    caption: 'レイアウト',
    variant: 'insert',
    value: '\n#layout(レイアウト名);'
  },
  // TODO: dialog
  'section': {
    caption: 'セクション',
    variant: 'insert',
    value: '\n#section(セクション名);'
  },
  //
  'column:2': {
    caption: '段組み2列',
    variant: 'insert',
    value: '\n#cols{{\n※左の内容\n====\n※右の内容\n}}'
  },
  'column:3': {
    caption: '段組み3列',
    variant: 'insert',
    value: '\n#cols{{\n※左の内容\n====\n※中央の内容\n====\n※右の内容\n}}'
  },
  'table': {
    caption: '表',
    variant: 'insert',
    value: '\n//|STYLE:class=table table-striped|\n|LEFT:50|LEFT:50|c\n|~見出し|内容|\n|~見出し|内容|\n|~見出し|内容|\n|~見出し|内容|\n'
  },
  'viewport:pc': {
    caption: 'PC表示',
    variant: 'insert',
    value: '\n#only_pc{{\n※内容\n}}\n'
  },
  'viewport:sp': {
    caption: 'SP表示',
    variant: 'insert',
    value: '\n#only_mobile{{\n※内容\n}}\n'
  },
  'color:red': {
    caption: '赤色',
    variant: 'insert',
    value: '#DC143C',
  },
  'color:blue': {
    caption: '青色',
    variant: 'insert',
    value: '#4169E1',
  },
}

export const buttonNames = Object.keys(buttonData)
