/* global userIsLoggedIn */
/* global countNotificationUrl */
/* global countUnseenAchievementsUrl */
/* global unseenAchievementsBadgeText */
if (userIsLoggedIn) {
  const sidebarNotificationBadgeUpdater = new BadgeUpdater(
    countNotificationUrl, 'sidebar_badge--unseen-notifications'
  )
  sidebarNotificationBadgeUpdater.run()

  const sidebarAchievementsBadgeUpdater = new BadgeUpdater(
    countUnseenAchievementsUrl, 'sidebar_badge--unseen-achievements', unseenAchievementsBadgeText
  )
  sidebarAchievementsBadgeUpdater.run()
}

/**
 * Sidebar badge Updater
 *
 * @param url
 * @param badgeID
 * @param badgeText
 * @param maxAmountToFetch
 * @param refreshRate
 * @constructor
 */
function BadgeUpdater (url, badgeID, badgeText = null, maxAmountToFetch = 99, refreshRate = 10000) {
  const self = this
  self.url = url
  self.maxAmountToFetch = maxAmountToFetch
  self.refreshRate = refreshRate

  const badge = document.getElementById(badgeID)

  self.run = function () {
    fetch(self.url)
      .then(response => response.json())
      .then(data => {
        const count = data.count
        if (count > 0) {
          if (badgeText === null) {
            badge.innerHTML = (count <= self.maxAmountToFetch) ? count.toString() : (self.maxAmountToFetch + '+')
          } else {
            badge.innerHTML = badgeText
          }
          badge.style.display = 'block'
        } else {
          badge.innerHTML = ''
          badge.style.display = 'none'
        }
        setTimeout(self.run, refreshRate)
      })
      .catch((error) => {
        console.error('Unable to update badge! Error: ', error)
      })
  }
}
