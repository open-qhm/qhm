export type ButtonData = Record<string, ButtonDefinition>
export type ButtonDefinition = {
  caption: string
} & (InsertButtonVariant | WrapButtonVariant | DialogButtonVariant)
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
    value: string
    checked: boolean
  }[]
}
