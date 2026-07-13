import { MDCTabBar } from '@material/tab-bar'

import './TabBar.css'

for (const el of document.querySelectorAll('.mdc-tab-bar')) {
  const tabBar = new MDCTabBar(el)
  const tabPaneElements = document.querySelectorAll('.tab-pane')

  tabBar.listen('MDCTabBar:activated', (event) => {
    const lastActiveEl = document.querySelector('.show.active')
    if (lastActiveEl) {
      lastActiveEl.classList.remove('show', 'active')
    }
    tabPaneElements[event.detail.index].classList.add('show', 'active')
  })
}
