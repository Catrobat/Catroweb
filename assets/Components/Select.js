import '@material/web/select/outlined-select.js'
import '@material/web/select/select-option.js'

for (const select of document.querySelectorAll('md-outlined-select')) {
  const nativeInput = document.getElementById(`${select.id}-native`)

  select.addEventListener('change', () => {
    if (nativeInput) nativeInput.value = select.value
  })
}
