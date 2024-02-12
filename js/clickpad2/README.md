# JQuery Plugin ClickPad

テキストエリアに様々な拡張機能を付けます。
QHM 用のプラグイン・Wiki書式の挿入支援jQueryプラグインです。
ボタン設定を拡張することで様々なボタンを作成可能です。

## Usage
```
$("textarea").clickpad([option]);
```

### Option

|オプション|型|デフォルト|説明|
|---|---|---|---|
| `buttons` | `ButtonGroup[] | string` | `qhm` | ボタングループ配列かボタンプリセット名（ `qhmpro` or `commu` or `qnews` ） |
| `autoGrow` | `boolean` | `true` | テキストエリアが文章の入力量に従い、伸び縮みするかどうか |
| `minLine` | `number` | | autoGrow を有効にした場合、この行数以下に縮まない |
| `maxLine` | `number` | | autoGrow を有効にした場合、この行数以上に伸びない |
| `replaces` | `Record<string, string>` | `undefined` | ボタン設定の中で使う、置換文字列のキーと値のセット |
| `css` | `Style` | `undefined` | ボタン全体を囲む div のスタイル設定 |
| `showAtFocus` | `boolean` | `false` | 普段はボタンを隠し、textarea にフォーカスが当たったら表示するようにします。デフォルトで false |

#### buttons

ボタングループ配列を指定する場合、ボタンセットの型に従ってください。

|オプション|型|説明|
|---|---|---|
| `buttons` | `ButtonName[][]` | ボタン配列とその行数 |
| `margin` | `number | [number, number]` | 余白。配列の場合は水平、垂直方向の余白をそれぞれ指定する |
| `css` | `Style` | カスタムスタイル |
| `backgroundImage` | `string` | 背景画像URL |
| `clear` | `boolean` | ボタングループの後にclear bothを挿入する。デフォルト `false` |

##### ボタン配列について

```js
[
  ["name1", "name2"],
  ["name3"]
]
```

上記の例では2行のボタン配列が定義されています。

## ボタンデータの定義


Appendix :
  ■ ボタン定義の構造について
  ボタン定義には決まった構造があります。
  新しくボタンを定義する場合、定義したボタンデータを jQuery.clickpad.buttonData にマージしてください。
  ※ 既にあるボタン名に被らないよう、注意が必要です。
 *
  ボタンデータは、ボタン名:{ボタン定義} の集まりです。
  簡略化したものは次のように表せます。
    {buttonName: {buttoanDefinition}, buttonName2: {buttonDefinition2}}
  ※ このプラグインを読んだ状態で、既に、QHMプロ、モバイル、コミュ、Qニュースのほとんどのボタンが使用可能です。
 *
  ■ ボタン定義の構造
  まず、英数字のみでボタンの役割を表現してください、それがボタン名となります。
    例）header, link など
 *
  次に、ボタンの細かい挙動を指定します。
  基本的に 6-7 つの項目を設定します。
    caption, width, height, background, func, value, replaces
  の 7 つです。
  ※ replaces はほとんど使用しません
 *
  これらを合わした場合、次のような状態となります。
  buttonName: {caption:"Caption", width:25, height:25, background:"url(hoge.png)", func:"cpInsert", value:"fuga"}
 *
 *
  一つずつ説明します。
  caption <string>: ボタンのフルネームです。マウスオーバーした時にポップアップされます
  width  <integer>: ボタンの幅です。ピクセル数を指定します。
  height <integer>: ボタンの高さです。ピクセル数を指定します。
  background <Array or string>: 背景指定のCSS を書きます。配列にし、二つ書いた場合、ホバーイベントが設定されます。
  func <string>: ボタンが要求する動作です。cpInsert と cpEnclose, cpDialog, cpEval が用意されています。詳細は後で。
  value <Array or string>: func に渡す引数です。こちらも func の詳細説明の項目で説明します。
  replaces <Array>: ダイアログなどの入力以外で、文字列を操作したい場合などに使用します。これも詳しくは後々。
 *
 *
  ■ 機能
  func と value について、説明します。
 *
  cpInsert(value<string>): "Insert value"
    カーソル位置に文字列 value を挿入します。
 *
  cpEnclose(value<Array>): ["enclose start", "enclose end"]
    選択範囲を指定した文字列で囲みます。
    value には、長さ 2 の配列を指定します。
    例）["##", "##"]
 *
  cpDialog(value<Array>): [promptSettings, "enclose start", "enclose end"]
    ダイアログウィンドウを出し、入力を促します。入力値を適用した文字列を、カーソル位置、または選択範囲を囲むように挿入します。
    value には、長さ 2-3 の配列を指定します。
    "enclose end" は必要ない場合、指定しなくとも結構です。
    例）["Please input font-size", "&size(${1}){", "};"]
    例）["Please input style", "#style(${1}){{\n\n}}\n"]
 *
    promptSettings <Array or string>: [promptSetting, promptSetting2, ...] or "Prompt message"
      入力値の説明を書きます。配列に入れた場合、promptSetting の集合として、複数の入力をさせることができます。
      promptSettings の要素数と、start で使う置換マーカーの数は合わせてください。
 *
      promptSetting <Object or string>: {msg:"Prompt message", option:{promptOptions}} or "Prompt message"
        promptSetting では、プロンプトの種類をテキスト入力欄以外にも、チェックボックスとセレクトボックスにすることができます。
        種類を変える場合、promptSetting をオブジェクトにする必要があります。
        例）{msg:"Bold Font", option:{type:"checkbox", value:"b"}}
 *
        promptOption <Object>: {type:"Prompt type", defval:"default value", value:"insert value", values:"select and radio options", and more...}
          promptOption では、プロンプトの種類とそれに伴う必須設定を書きます。
          現在では、input:text と、input:checkbox、input:radio、select に対応しています。
          type を省略した場合、input:text が自動的に選択されます。
 *
          ■ 共通のプロパティ
            css: <Object> {css-property: value, ...}
              部品1つを囲む DIV のスタイルを指定することができます。
 *
          ■ それぞれの種類ごとの特徴とプロパティ
          type:"text"
            テキスト入力欄を表示します。デフォルト値を設定できます。
            inputWidthRatio を 0.1〜1 で設定することで入力欄の長さを伸縮させることができます。
            defval: "Default string"
            inputWidthRatio: 0.5
 *
          type:"checkbox"
            チェックボックスを表示します。チェックされた際の値を設定できます。
            value: "Checked string"
 *
          type:"radio"
            ラジオボタンを表示します。選択された際の値を設定できます。
            checked を真にすることで最初から選択されている項目を設定できます。
            オブジェクトの配列は以下の構造で指定してください。
            values: [{label: "Label of radio", value: "Input String", checked: true}, {label: "", value: ""}, ...]
 *
          type:"select"
            セレクトボックスを表示します。セレクトオプションを指定できます。
            selected を真にすることで最初から選択されている項目を設定できます。
            オブジェクトの配列は以下の構造で指定してください。
            values: [{key: "Display String", value: "Input String"}, {key: "", value: ""}, ...]
 *
 *
    enclose start <string>: "encloseStart"
      選択した文字列の前に挿入される文字列です。
      テンプレート機能を持ち、プロンプトにて入力された値を差し込むことができます。
      一番初めのプロンプトの値を ${1}、それ以降、2, 3 と増えていきます。
      例）[["prompt 1", "prompt2", "prompt3"], "&hoge(${1},${2}-${3}){", "};"]
 *
    enclose end <string>: "encloseEnd"
      選択した文字列の後ろに挿入される文字列です。
      囲む必要のない場合、無指定にしてください。
      例）["prompt", "#hoge(${1}){{\n\n}}\n"]
 *
  cpEval(value<string>): "eval string"
    指定した javascript を実行します。
    また、buttonData にて replace を指定している場合、置換をしてから実行します。
    また、${textarea} という文字列があれば、そこを関連づけられた textarea の id に置換します。
 *
 *
 *
  ■ replaces の活用
    option.replaces = {"key":"value"}
    と指定しておけば、
    buttonData の replaces に ["key"] と書くことで、
    cpEval に渡す文字列の、${"key"} を "value" に置換できます。
    この方法は、セッションID など、CGI 経由でしか渡せない値などを使う際に便利です。
 *
    buttonData.replaces = [{"key":"value"}]
    の指定では、
    cpEval に渡す文字列の、${"key"} を "value" に置換できます。
    作った僕もいまいち使いどころがわかりません。