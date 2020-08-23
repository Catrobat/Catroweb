/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function FetchNotifications (countNotificationsUrl, maxAmountToFetch, refreshRate) {
  const self = this
  self.countNotificationsUrl = countNotificationsUrl
  self.maxAmountToFetch = maxAmountToFetch
  self.refreshRate = refreshRate

  self.run = function (fetchType) {
    $.ajax({
      url: self.countNotificationsUrl,
      type: 'get',
      success: function (data) {
        for (const notificationType in data) {
          const userNotificationBadge = $('#sidebar-notifications .badge-pill.' + notificationType)
          const numOfNotifications = data[notificationType]
          if (numOfNotifications > 0) {
            const text = (numOfNotifications <= self.maxAmountToFetch)
              ? numOfNotifications.toString() : (self.maxAmountToFetch + '+')
            userNotificationBadge.text(text)
            userNotificationBadge.show()
          } else {
            userNotificationBadge.text('')
            userNotificationBadge.hide()
          }
        }
        if (fetchType !== 'markAsRead') {
          setTimeout(self.run, refreshRate)
        }
      },
      error: function () {
        console.error('Unable to fetch user notifications!')
      }
    })
  }
}
