import { insertText } from './insert'

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
    insertText(
      document.querySelector('#msg'),
      buttonDefinition.value,
    )
  }

  return button
}