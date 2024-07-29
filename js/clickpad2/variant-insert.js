/**
 * @param { string } buttonId
 * @param { import("./types").ButtonDefinition } buttonDefinition 
 */
export const makeButtonVariantInsert = (buttonId, buttonDefinition) => {
  if (buttonDefinition.variant !== 'insert') {
    throw new Error('variant is not insert')
  }

  const button = document.createElement('button')
  button.classList.add('clickpad2__pallet-button')
  button.dataset.id = buttonId
  button.dataset.variant = 'insert'
  button.textContent = buttonDefinition.caption
  button.type = 'button'
  button.onclick = () => {
    const textarea = document.querySelector('#msg')
    const cursorPos = textarea.selectionStart
    const textBefore = textarea.value.substring(0, cursorPos)
    const textAfter = textarea.value.substring(cursorPos)
    textarea.value = textBefore + buttonDefinition.value + textAfter
    textarea.setSelectionRange(cursorPos + buttonDefinition.value.length, cursorPos + buttonDefinition.value.length)
    textarea.focus()
  }

  return button
}