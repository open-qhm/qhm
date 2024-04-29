export type ButtonData = Record<string, ButtonDefinition>
export type ButtonDefinition = {
  caption: string
  cover?: CoverOption
} & (
  InsertButtonVariant | WrapButtonVariant | DialogButtonVariant
)

type Style = Record<string, string>

type InsertButtonVariant = {
  variant: "insert"
  value: string
}

type WrapButtonVariant = {
  variant: "wrap"
  prefix: string
  suffix: string
}

type DialogButtonVariant = {
  variant: "dialog"
  dialog: Dialog
  value: string
}
type Dialog = string | DialogSetting[]
type DialogSetting = string | {
  message?: string
  option: DialogOption
}
type DialogOption = {
  css?: Style
} & (
  DialogOptionTextVariant
  | DialogOptionCheckboxVariant
  | DialogOptionSelectionVariant
  | DialogOptionFontSizeGuide
  | DialogOptionSectionHeader
  | DialogOptionDecoFontStyle
  | DialogOptionDecoFontColor
  | DialogOptionDecoBgColor
  | DialogOptionIconHeader
)
type DialogOptionTextVariant = {
  type: "text"
  width: string | number
  defaultValue?: string
  useSelection?: boolean
  prefix?: string
}
type DialogOptionCheckboxVariant = {
  type: "checkbox"
  values: {
    label: string
    value: string
  }[]
}
type DialogOptionSelectionVariant = {
  type: "radio" | "select"
  values: ({
    label: string
    color?: string
  } | {
    label: string
    icon: string
  } | {
    label: string
    labelStyle: Style
  }) & {
    value: string
    checked: boolean
  }[]
}
type DialogOptionFontSizeGuide = {
  type: "font-size-guide"
}
type DialogOptionSectionHeader = {
  type: "section-header"
}
/* deco dialog 内でのみ使う */
type DialogOptionDecoFontStyle = {
  type: "deco-font"
}
type DialogOptionDecoFontColor = {
  type: "deco-color"
}
type DialogOptionDecoBgColor = {
  type: "deco-bg-color"
}

/* icon dialog 内でのみ使う */
type DialogOptionIconHeader = {
  type: "icon-header"
}

type IconCover = {
  kind: "icon"
  provider: "google"
  name: string
}
type ColorCover = {
  kind: "color"
  color: string
}
type TextCover = {
  kind: "text"
  text: string
}
export type CoverOption = IconCover | ColorCover | TextCover
