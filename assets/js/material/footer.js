import { MDCSelect } from '@material/select'

const select = new MDCSelect(document.querySelector('.mdc-select'))

select.listen('MDCSelect:change', () => {
  document.cookie = `hl= ${select.value}; path=/`
  window.location.reload()
})
