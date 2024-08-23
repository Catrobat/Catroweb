import { MDCTabBar } from '@material/tab-bar'

require('./TabBar.scss')

for (const el of document.querySelectorAll('.mdc-tab-bar')) {
  const tabBar = new MDCTabBar(el)
  const tabPaneElements = document.querySelectorAll('.tab-pane')

  tabBar.listen('MDCTabBar:activated', function (event) {
    const lastActiveEl = document.querySelector('.show.active')
    if (lastActiveEl) {
      lastActiveEl.classList.remove('show', 'active')
    }
    tabPaneElements[event.detail.index].classList.add('show', 'active')
  })
}
