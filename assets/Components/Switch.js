import '@material/web/switch/switch.js'

for (const switchControl of document.querySelectorAll('md-switch')) {
  const nativeInput = document.getElementById(`${switchControl.id}-native`)

  switchControl.addEventListener('change', () => {
    if (nativeInput) nativeInput.value = String(switchControl.selected)
  })
}
