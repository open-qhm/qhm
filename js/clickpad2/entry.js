import { buttonData } from './buttonData'
import { makeButtonVariantInsert } from './variant-insert'

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
      if (buttonDefinition.variant !== 'insert') {
        const button = document.createElement('button')
        button.classList.add('clickpad2__pallet-button')
        button.dataset.id = buttonId
        button.textContent = buttonData[buttonId].caption
        button.type = 'button'
        rowElem.appendChild(button)
      } else {
        const button = makeButtonVariantInsert(buttonId, buttonDefinition)
        rowElem.appendChild(button)
      }
    })
  })
}

document.addEventListener('DOMContentLoaded', () => {
  showPallet(document.querySelector('#msg'))
})
