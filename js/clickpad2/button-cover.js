/** 
 * @param { HTMLButtonElement } button 
 * @param { import("./types").CoverOption | undefined } coverOption
 */
export const coverButton = (button, coverOption) => {
  if (coverOption === undefined) {
    return button
  }

  if (coverOption.kind === 'icon') {
    // button のテキストコンテントを title に退避する
    button.title = button.textContent
    // button のテキストコンテントを空にする
    button.textContent = ''
    // button の下に下記要素を追加する
    // <span class="material-icons-outlined">[Icon Name]</span>
    const icon = document.createElement('span')
    icon.classList.add('material-icons-outlined')
    icon.textContent = coverOption.name
    button.appendChild(icon)
  } else if (coverOption.kind === 'color') {
    // button のテキストコンテントを title に退避する
    button.title = button.textContent
    // button のテキストコンテントを空にする
    button.textContent = ''
    // 背景色を変更する
    button.style.backgroundColor = coverOption.color
    // 正方形にする
    button.style.aspectRatio = '1 / 1'
  } else if (coverOption.kind === 'text') {
    // button のテキストコンテントを title に退避する
    button.title = button.textContent
    // button のテキストコンテントに coverOption.text を設定する
    button.textContent = coverOption.text
  }

  return button
}