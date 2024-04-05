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
  useSelection?: boolean
}
type DialogOptionCheckboxVariant = {
  type: "checkbox"
  value: string
}
type DialogOptionSelectionVariant = {
  type: "radio" | "select"
  values: {
    label: string
    color?: string
    value: string
    checked: boolean
  }[]
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
