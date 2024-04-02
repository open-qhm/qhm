import { coverButton } from './button-cover'
import { buttonData } from './button-data'
import { makeButtonVariantDialog } from './variant-dialog'
import { makeButtonVariantInsert } from './variant-insert'
import { makeButtonVariantWrap } from './variant-wrap'

const pallets = [
  ['add-image'],
  ['title', 'delimiter:start', 'delimiter:end', 'comment-out'],
  ['br', 'margin', 'hr'],
  ['h1', 'h2', 'h3', 'h4'],
  ['link', 'bold', 'deco'],
  ['li', 'ol'],
  ['anchor', 'button', 'html', 'label', 'icon'],
  ['center', 'right'],
  ['layout', 'section', 'column:2', 'column:3', 'table'],
  ['viewport:pc', 'viewport:sp'],
  ['color:red', 'color:blue'],
]

// 指定したテキストエリアの下にボタンパレットを表示する
export function showPallet(textarea) {
  const pallet = document.createElement('div')
  pallet.classList.add('clickpad2__container')
  textarea.parentNode.insertBefore(pallet, textarea.nextSibling)

  pallets.forEach((row) => {
    const rowElem = document.createElement('div')
    rowElem.classList.add('clickpad2__pallet-row')
    pallet.appendChild(rowElem)

    row.forEach((buttonId) => {
      const buttonDefinition = buttonData[buttonId]
      let button
      if (buttonDefinition.variant === 'insert') {
        button = makeButtonVariantInsert(buttonId, buttonDefinition)
      } else if (buttonDefinition.variant === 'wrap') {
        button = makeButtonVariantWrap(buttonId, buttonDefinition)
      } else if (buttonDefinition.variant === 'dialog') {
        button = makeButtonVariantDialog(buttonId, buttonDefinition)
      }
      rowElem.appendChild(coverButton(button, buttonDefinition.cover))
    })
  })
}

document.addEventListener('DOMContentLoaded', () => {
  showPallet(document.querySelector('#msg'))
})
