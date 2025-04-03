/**
 * テキストエリアにテキストを挿入する
 * @param {HTMLTextAreaElement} textarea - テキストエリア
 * @param {string} text - 挿入するテキスト
 */
export const insertText = (textarea, text) => {
  const target = textarea || document.querySelector('#msg')
  const cursorPos = target.selectionStart
  const textBefore = target.value.substring(0, cursorPos)
  const textAfter = target.value.substring(cursorPos)
  target.value = textBefore + text + textAfter
  target.setSelectionRange(cursorPos + text.length, cursorPos + text.length)
  target.focus()
}

window.insertText = insertText
