/*
  Generated File by Grunt
  Sourcepath: web/js
*/
function FetchNotifications(countNotificationsUrl, maxAmountToFetch, refreshRate)
{
  let self = this;
  self.countNotificationsUrl = countNotificationsUrl;
  self.maxAmountToFetch = maxAmountToFetch;
  self.refreshRate = refreshRate;
  
  
  self.init = function()
  {
    let userNotificationBadge = $('.user-notification-badge')
    userNotificationBadge.hide();
    
    $.ajax({
      url    : self.countNotificationsUrl,
      type   : 'get',
      success: function (data) {
        let numOfNotifications = data.count
        if (numOfNotifications > 0)
        {
          let text = (numOfNotifications <= self.maxAmountToFetch) ?
            numOfNotifications.toString() : (self.maxAmountToFetch + '+')
          userNotificationBadge.text(text)
          userNotificationBadge.show();
        }
        else
        {
          userNotificationBadge.text('')
          userNotificationBadge.hide();
          
        }
        setTimeout(fetchUserNotifications, refreshRate)
      },
      error  : function () {
        console.error('Unable to fetch user notifications!')
      }
    })
  }
  
}

