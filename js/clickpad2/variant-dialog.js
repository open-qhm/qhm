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
      wrapper.classList.add('clickpad2__dialog-item')

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
          option.values.forEach(({ label, color, icon, value, checked }, index2) => {
            const _id = `${id}-${index2 + 1}`
            const labelElement = document.createElement('label')
            labelElement.classList.add('clickpad2__dialog-radio-item-label')
            const input = document.createElement('input')
            input.id = _id
            input.type = 'radio'
            input.name = id
            input.value = value
            input.checked = checked
            labelElement.appendChild(input)
            if (color !== undefined) {
              labelElement.classList.add('clickpad2__dialog-radio-item-label--color')
              // 色名をlabel, color を背景色にする
              const colorBox = document.createElement('span')
              colorBox.classList.add('clickpad2__dialog-radio-color-box')
              colorBox.title = label
              colorBox.style.backgroundColor = color
              labelElement.appendChild(colorBox)
            } else if (icon !== undefined) {
              labelElement.classList.add('clickpad2__dialog-radio-item-label--icon')
              // icon をラベルの前に配置する
              const iconElement = document.createElement('span')
              iconElement.classList.add('material-icons-outlined')
              iconElement.textContent = icon
              labelElement.appendChild(iconElement)
              const text = document.createElement('span')
              text.textContent = label
              text.classList.add('clickpad2__dialog-radio-item-label-text')
              labelElement.appendChild(text)
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
        case 'deco-font': {
          const item = document.createElement('div')
          item.classList.add('clickpad2__dialog-deco-font-item')

          const fontStyles = [
            { name: 'bold', label: '太字', value: 'b' },
            { name: 'underline', label: '下線', value: 'u' },
            { name: 'italic', label: '斜体', value: 'i' }
          ]
          fontStyles.forEach((fontStyle, index2) => {
            const label = document.createElement('label')
            label.classList.add('clickpad2__dialog-deco-font-item-label', `clickpad2__dialog-deco-font-item-label-${fontStyle.name}`)
            const labelTitle = document.createElement('span')
            labelTitle.textContent = fontStyle.label

            const _id = `${id}-${index + 1}`
            const input = document.createElement('input')
            input.type = 'checkbox'
            input.id = _id
            input.name = id
            input.value = fontStyle.value

            label.appendChild(input)
            label.appendChild(labelTitle)
            item.appendChild(label)
          })

          wrapper.appendChild(item)
          content.appendChild(wrapper)
          break
        }
        case 'deco-color': {
          const item = document.createElement('div')
          item.classList.add('clickpad2__dialog-deco-color-item')
          const label = document.createElement('label')
          const title = document.createElement('span')
          title.classList.add('clickpad2__dialog-deco-color-item-title')
          title.textContent = '文字色'
          const tip = document.createElement('span')
          tip.classList.add('clickpad2__dialog-deco-color-item-tip')
          tip.textContent = '（カラーコード/カラーネーム）'
          label.appendChild(title)
          label.appendChild(tip)
          label.htmlFor = id
          item.appendChild(label)

          const input = document.createElement('input')
          input.id = id
          input.name = id
          input.type = 'text'
          item.appendChild(input)

          wrapper.appendChild(item)
          content.appendChild(wrapper)
          break
        }
        case 'deco-bg-color': {
          const item = document.createElement('div')
          item.classList.add('clickpad2__dialog-deco-bg-color-item')
          const label = document.createElement('label')
          const title = document.createElement('span')
          title.classList.add('clickpad2__dialog-deco-bg-color-item-title')
          title.textContent = '背景色'
          const tip = document.createElement('span')
          tip.classList.add('clickpad2__dialog-deco-bg-color-item-tip')
          tip.textContent = '（カラーコード/カラーネーム）'
          label.appendChild(title)
          label.appendChild(tip)
          label.htmlFor = id
          content.appendChild(label)

          const input = document.createElement('input')
          input.id = id
          input.name = id
          input.type = 'text'
          item.appendChild(input)

          wrapper.appendChild(item)
          content.appendChild(wrapper)
          break
        }
        case 'font-size-guide': {
          const item = document.createElement('div')
          item.classList.add('clickpad2__dialog-font-size-guide-item')

          // 凡例
          const legend = document.createElement('div')
          legend.classList.add('clickpad2__dialog-font-size-guide-item-legend')
          const legendTitle = document.createElement('span')
          legendTitle.textContent = '[ 文字サイズ指定キーワード ]'
          legend.appendChild(legendTitle)
          const br = document.createElement('br')
          legend.appendChild(br)
          const legendTip = document.createElement('span')
          legendTip.textContent = 'xx-small / x-small / small / medium（初期値）/ large / x-large / xx-large'
          legend.appendChild(legendTip)
          item.appendChild(legend)

          wrapper.appendChild(item)
          content.appendChild(wrapper)
          break
        }
        case 'icon-header': {
          const item = document.createElement('div')
          item.classList.add('clickpad2__dialog-icon-header-item')

          // タイトル
          const title = document.createElement('h3')
          title.textContent = 'Google アイコン検索リンク'
          title.classList.add('clickpad2__dialog-icon-header-item-title')
          item.appendChild(title)

          // Material Icons へのリンク
          const materialIconsLink = document.createElement('a')
          materialIconsLink.textContent = 'Material Icons'
          materialIconsLink.href = 'https://fonts.google.com/icons?icon.set=Material+Icons'
          materialIconsLink.target = '_blank'
          item.appendChild(materialIconsLink)

          // Material Symbold へのリンク
          const materialSymbolsLink = document.createElement('a')
          materialSymbolsLink.textContent = 'Material Symbols'
          materialSymbolsLink.href = 'https://fonts.google.com/icons?icon.set=Material+Symbols'
          materialSymbolsLink.target = '_blank'
          item.appendChild(materialSymbolsLink)

          // 説明文
          const description = document.createElement('p')
          description.textContent = '(アイコンをクリックして表示される右側ウィンドウ内の<span ...から始まる枠内のコードを [アイコンコード] に入力します）'
          item.appendChild(description)

          wrapper.appendChild(item)
          content.appendChild(wrapper)
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