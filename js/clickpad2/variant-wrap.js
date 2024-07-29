/**
 * @param { string } buttonId
 * @param { import("./types").ButtonDefinition } buttonDefinition 
 */
export const makeButtonVariantWrap = (buttonId, buttonDefinition) => {
  if (buttonDefinition.variant !== 'wrap') {
    throw new Error('variant is not wrap')
  }

  const button = document.createElement('button')
  button.classList.add('clickpad2__pallet-button')
  button.dataset.id = buttonId
  button.dataset.variant = 'wrap'
  button.textContent = buttonDefinition.caption
  button.type = 'button'
  button.onclick = () => {
    const textarea = document.querySelector('#msg')
    const { selectionStart, selectionEnd } = textarea
    const textBefore = textarea.value.substring(0, selectionStart)
    const textSelected = textarea.value.substring(selectionStart, selectionEnd)
    const textAfter = textarea.value.substring(selectionEnd)
    textarea.value = textBefore + buttonDefinition.prefix + textSelected + buttonDefinition.suffix + textAfter
    textarea.setSelectionRange(selectionStart, selectionEnd + buttonDefinition.prefix.length + buttonDefinition.suffix.length)
    textarea.focus()
  }

  return button
}