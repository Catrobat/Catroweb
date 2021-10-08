import { Modal } from 'bootstrap'

for (const el of document.querySelectorAll('.modal')) {
  new Modal(el)
}
