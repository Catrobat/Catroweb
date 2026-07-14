import '@material/web/tabs/primary-tab.js'
import './TabBar.css'
import '@material/web/tabs/tabs.js'

for (const tabBar of document.querySelectorAll('md-tabs')) {
  tabBar.addEventListener('change', () => {
    const tabs = Array.from(tabBar.querySelectorAll('md-primary-tab'))
    const activeTab = tabs[tabBar.activeTabIndex]
    const activePane = activeTab && document.getElementById(activeTab.getAttribute('aria-controls'))

    if (!activePane) return

    document.querySelector('.show.active')?.classList.remove('show', 'active')
    activePane.classList.add('show', 'active')
  })
}
