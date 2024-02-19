/**
 * @param { string } buttonId
 * @param { import("./types").ButtonDefinition } buttonDefinition 
 */
export const makeButtonVariantDialog = (buttonId, buttonDefinition) => {
  if (buttonDefinition.variant !== 'dialog') {
    throw new Error('variant is not dialog')
  }

  const button = document.createElement('button')
  button.classList.add('clickpad2__pallet-button')
  button.dataset.id = buttonId
  button.dataset.variant = 'dialog'
  button.textContent = buttonDefinition.caption
  button.type = 'button'
  button.onclick = () => {
    // dialog を生成する
    const dialog = document.createElement('dialog')
    dialog.classList.add('clickpad2__dialog')
    // dialog の中身を生成する
    const content = document.createElement('div')
    content.classList.add('clickpad2__dialog-content')
    buttonDefinition.dialog.forEach(({ message, option }) => {
      const label = document.createElement('label')
      label.textContent = message
      content.appendChild(label)
      const input = document.createElement('input')
      input.onkeydown = (e) => {
        e.stopPropagation()
      }
      input.type = option.type
      if (option.useSelection) {
        const textarea = document.querySelector('#msg')
        const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
        input.value = selectedText
      }
      content.appendChild(input)
    })
    dialog.appendChild(content)

    // 閉じるボタンを生成する
    const close = document.createElement('button')
    close.type = 'button'
    close.textContent = '閉じる'
    close.onclick = () => {
      dialog.close()
    }
    dialog.appendChild(close)

    // 挿入ボタンを生成する
    const insert = document.createElement('button')
    insert.type = 'button'
    insert.textContent = '挿入'
    insert.onclick = () => {
      const textarea = document.querySelector('#msg')
      const { selectionStart, selectionEnd } = textarea
      const selectedText = textarea.value.substring(selectionStart, selectionEnd)
      const textBefore = textarea.value.substring(0, selectionStart)
      const textAfter = textarea.value.substring(selectionEnd)
      // テンプレート文字列を展開する
      let insertText = buttonDefinition.value
      content.querySelectorAll('input').forEach((input, index) => {
        insertText = insertText.replace('${'+`${index + 1}` + '}', input.value)
      })
      insertText = insertText.replace('${selection}', selectedText)
      textarea.value = textBefore + insertText + textAfter
      textarea.setSelectionRange(selectionStart, selectionStart + insertText.length)
      dialog.close()
      textarea.focus()
    }
    dialog.appendChild(insert)

    // dialog を body に追加する
    document.body.appendChild(dialog)

    // dialog を表示する
    dialog.showModal()
  }

  return button
}