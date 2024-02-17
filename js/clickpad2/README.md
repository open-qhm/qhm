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
| `replaces` | `Record<string, string>` | `undefined` | ボタン設定の中で使う、置換文字列のキーと値のセット |
| `css` | `Style` | `undefined` | ボタン全体を囲む div のスタイル設定 |


## ボタンデータの定義

```ts
type ButtonData = Record<string, ButtonDefinition>
type ButtonDefinition = {
  caption: string
} & (InsertButtonVariant | WrapButtonVariant | DialogButtonVariant | EvalButtonVariant)
```

* `caption` : ボタンの表示名です。マウスオーバーした時にポップアップされます
* `variant` : ボタンの挙動の種類です。
* `value` :
* `replaces`:

### variant と value について

#### `insert`
```ts
type InsertButtonVariant = {
  type: "insert"
  value: string
}
```
カーソル位置に `value` の値を挿入します。

例：
```js
{
  variant: "insert",
  value: "&hr;"
}
```

#### `wrap`

```ts
type WrapButtonVariant = {
  type: "wrap"
  prefix: string
  suffix: string
}
```
選択範囲を指定した文字列で囲みます。

例：
```js
{
  variant: "wrap",
  prefix: "##",
  suffix: "##"
}
```


#### dialog

ダイアログを表示し、細かい設定をしつつ文字列の挿入ができます。

```ts
type DialogButtonVariant = {
  variant: "dialog"
  dialog: Dialog
  prefix: string
  suffix?: string
}
type Dialog = string | DialogSetting[]
type DialogSetting = string | {
  message: string
  option: DialogOption
}
type DialogOption = {
  css?: Style
} & (DialogOptionTextVariant | DialogOptionCheckboxVariant | DialogOptionSelectionVariant)
type DialogOptionTextVariant = {
  type: "text"
  width: string | number
  defaultValue?: string
}
type DialogOptionCheckboxVariant = {
  type: "checkbox"
  value: string
}
type DialogOptionSelectionVariant = {
  type: "radio" | "select"
  values: {
    label: string
    value: string
    checked: boolean
  }[]
}
```

#### eval

```ts
type EvalButtonVariant = {
  script: string
  replace?: Record<key extends string, string>
}
```

指定した script を実行します。
`script` 内に `${textarea}` という文字列があれば、clickpad 実行中の textarea の id に置換します。
`replace` を指定している場合、 `script` 内の文字列を置換をしてから実行します。
