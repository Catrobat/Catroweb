const _escapeDiv = document.createElement('div')

export function escapeHtml(str) {
  _escapeDiv.textContent = str
  return _escapeDiv.innerHTML
}

export function escapeAttr(str) {
  _escapeDiv.textContent = str
  return _escapeDiv.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;')
}
