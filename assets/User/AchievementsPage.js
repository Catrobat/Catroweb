/* global showNewAchievementAnimation */
/* global assetPathBadgeSVG */
/* global assetPathBannerSVG */
/* global bannerColor */
/* global bannerText */
/* global ltmAchievementsPopupNewTitle */
/* global ltmAchievementsPopupNewSubtitle */
/* global ltmAchievementsPopupNewConfirm */
/* global assetPathNewAchievementPopupBackgroundSVG */
/* global readUnseenAchievementsUrl */
import Swal from 'sweetalert2'
import 'external-svg-loader'

import '../Components/TabBar'

import './Achievements.scss'

handleNewAchievementAnimation()

function handleNewAchievementAnimation() {
  if (showNewAchievementAnimation) {
    Swal.fire({
      html: getNewAchievementAnimationHtml(),
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      confirmButtonText: ltmAchievementsPopupNewConfirm,
      buttonsStyling: false,
      allowOutsideClick: false,
    }).then((result) => {
      if (result.value) {
        readUnseenAchievements()
        hideAchievementsSidebarBadge()
      }
    })
  }
}

function getNewAchievementAnimationHtml() {
  return (
    '<svg class="popup__new-achievement__background" ' +
    '     data-src="' +
    assetPathNewAchievementPopupBackgroundSVG +
    '" data-unique-ids="disabled">' +
    '<h2 class="h1">' +
    ltmAchievementsPopupNewTitle +
    '</h2>' +
    '<h3>' +
    ltmAchievementsPopupNewSubtitle +
    '</h3>' +
    '<div class="achievement__badge achievement__badge--popup">' +
    '  <svg class="achievement__badge__coin achievement__badge__coin--popup"' +
    '       data-src="' +
    assetPathBadgeSVG +
    '" data-unique-ids="disabled"/>' +
    '  <svg class="achievement__badge__banner achievement__badge__banner--popup"' +
    '       style="color: ' +
    bannerColor +
    '" ' +
    '       data-src="' +
    assetPathBannerSVG +
    '"' +
    '       data-unique-ids="disabled"/>' +
    '  <div class="achievement__badge__banner__text achievement__badge__banner__text--popup">' +
    bannerText +
    '' +
    '  </div>' +
    '</div>'
  )
}

function hideAchievementsSidebarBadge() {
  const badge = document.getElementById('sidebar_badge--unseen-achievements')
  badge.style.display = 'none'
}

function readUnseenAchievements() {
  const xhr = new XMLHttpRequest()
  xhr.open('PUT', readUnseenAchievementsUrl, true)
  xhr.setRequestHeader('Content-Type', 'application/json')
  xhr.send()
}
