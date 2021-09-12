import { MDCTabBar } from '@material/tab-bar'
import 'external-svg-loader'

// Material Tab bar
const tabBar = new MDCTabBar(document.querySelector('.mdc-tab-bar'))
const tabPaneElements = document.querySelectorAll('.tab-pane')

tabBar.listen('MDCTabBar:activated', function (event) {
  document.querySelector('.show.active').classList.remove('show', 'active')
  tabPaneElements[event.detail.index].classList.add('show', 'active')
})
