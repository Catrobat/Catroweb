import {MDCTabBar} from '@material/tab-bar';

const tabBar = new MDCTabBar(document.querySelector('.mdc-tab-bar'));

var contentEls = document.querySelectorAll('.tab-pane');

tabBar.listen('MDCTabBar:activated', function(event) {
  document.querySelector('.show.active').classList.remove('show', 'active');
  contentEls[event.detail.index].classList.add('show', 'active');
});