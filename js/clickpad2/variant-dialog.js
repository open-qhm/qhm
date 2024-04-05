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
    // form で囲む
    const form = document.createElement('form')

    const content = document.createElement('div')
    content.classList.add('clickpad2__dialog-content')
    buttonDefinition.dialog.forEach(({ message, option }, index) => {
      const wrapper = document.createElement('div')

      const id = `dialog-control-${index + 1}`

      switch (option.type) {
        case 'text': {
          const label = document.createElement('label')
          label.textContent = message
          label.htmlFor = id
          content.appendChild(label)
          const input = document.createElement('input')
          input.id = id
          input.name = id
          input.onkeydown = (e) => {
            e.stopPropagation()
          }
          input.type = 'text'
          if (option.useSelection) {
            const textarea = document.querySelector('#msg')
            const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
            input.value = selectedText
          }
          wrapper.appendChild(input)
          content.appendChild(wrapper)
          break
        }
        case 'checkbox': {
          const label = document.createElement('label')
          label.textContent = message
          label.htmlFor = id
          content.appendChild(label)
          const input = document.createElement('input')
          input.id = id
          input.name = id
          input.type = 'checkbox'
          wrapper.appendChild(input)
          content.appendChild(wrapper)
          break
        }
        case 'radio': {
          const label = document.createElement('label')
          label.textContent = message
          content.appendChild(label)

          // option.values ごとに label>input を生成する
          option.values.forEach(({ label, color, value, checked }, index2) => {
            const _id = `${id}-${index2 + 1}`
            const labelElement = document.createElement('label')
            const input = document.createElement('input')
            input.id = _id
            input.type = 'radio'
            input.name = id
            input.value = value
            input.checked = checked
            labelElement.appendChild(input)
            if (color !== undefined) {
              // 色名をlabel, color を背景色にする
              const colorBox = document.createElement('span')
              colorBox.title = label
              colorBox.style.backgroundColor = color
              colorBox.style.aspectRatio = '1 / 1'
              colorBox.style.display = 'inline-block'
              colorBox.style.width = '1em'
              colorBox.style.marginLeft = '5px'
              colorBox.style.marginRight = '10px'
              labelElement.appendChild(colorBox)
            } else {
              labelElement.appendChild(document.createTextNode(label))
            }
            wrapper.appendChild(labelElement)
            content.appendChild(wrapper)
          })
          break
        }
        case 'select': {
          const label = document.createElement('label')
          label.textContent = message
          content.appendChild(label)

          // option.values ごとに label>input を生成する
          option.values.forEach(({ label, value, checked }, index2) => {
            const _id = `${id}-${index2 + 1}`
            const labelElement = document.createElement('label')
            const input = document.createElement('input')
            input.id = _id
            input.type = 'checkbox'
            input.name = id
            input.value = value
            input.checked = checked
            labelElement.appendChild(input)
            labelElement.appendChild(document.createTextNode(label))
            wrapper.appendChild(labelElement)
            content.appendChild(wrapper)
          })
          break
        }
      }
    })
    form.appendChild(content)
    dialog.appendChild(form)

    // 閉じるボタンを生成する
    const close = document.createElement('button')
    close.type = 'button'
    close.textContent = '閉じる'
    close.onclick = () => {
      dialog.close()
      document.querySelector('#msg').focus()
    }
    dialog.appendChild(close)

    // 挿入ボタンを生成する
    const insert = document.createElement('button')
    insert.type = 'submit'
    insert.textContent = '挿入'
    insert.onclick = (e) => {
      e.preventDefault()
      const textarea = document.querySelector('#msg')
      const { selectionStart, selectionEnd } = textarea
      const selectedText = textarea.value.substring(selectionStart, selectionEnd)
      const textBefore = textarea.value.substring(0, selectionStart)
      const textAfter = textarea.value.substring(selectionEnd)
      // テンプレート文字列を展開する
      let insertText = buttonDefinition.value
      // フォームから FormData を取得する
      const form = dialog.querySelector('form')
      const formData = new FormData(form)

      // formData の内容を使って置換する
      const formValues = Array.from(formData.entries()).reduce((memo, [key, value]) => {
        // key 毎にvalueを格納する
        if (memo[key] !== undefined) {
          memo[key].push(value)
        } else {
          memo[key] = [value]
        }
        return memo
      }, {})

      for (const [key, values] of Object.entries(formValues)) {
        const index = key.match(/(\d+)/)[1]
        console.log({ index, values })
        insertText = insertText.replace('${'+ index + '}', values.join(','))
      }

      insertText = insertText.replace('${selection}', selectedText)
      textarea.value = textBefore + insertText + textAfter
      textarea.setSelectionRange(selectionStart, selectionStart + insertText.length)
      dialog.close()
      textarea.focus()
    }
    form.appendChild(insert)

    // dialog を body に追加する
    document.body.appendChild(dialog)

    // dialog を表示する
    dialog.showModal()
    dialog.onclose = () => {
      dialog.remove()
    }
  }

  return button
}