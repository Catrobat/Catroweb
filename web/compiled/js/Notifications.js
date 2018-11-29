/*
  Generated File by Grunt
  Sourcepath: web/js
*/
function Notification (notifications, unseenRemixesGroupedLength, markAsReadUrl, markAllAsReadUrl,
                       popUpClearedAllMessagesTitle, popUpClearedAllMessagesText,
                       somethingWentWrongError, notificationsClearError)
{
  let self = this
  self.notifications = notifications
  self.unseenRemixesGroupedLength = unseenRemixesGroupedLength
  self.markAsReadUrl = markAsReadUrl
  self.popUpClearedAllMessagesTitle = popUpClearedAllMessagesTitle
  self.popUpClearedAllMessagesText = popUpClearedAllMessagesText
  self.somethingWentWrongError = somethingWentWrongError
  self.notificationsClearError = notificationsClearError
  
  self.init = function () {
    let markAllAsSeenButton = $('#mark-all-as-seen')
    
    let totalAmountOfNotifcations = self.unseenRemixesGroupedLength + self.notifications
    if (totalAmountOfNotifcations === 0)
    {
      markAllAsSeenButton.hide()
      $('.no-notifications-placeholder').show()
    }
    else
    {
      $('.no-notifications-placeholder').hide()
    }
    
    markAllAsSeenButton.click(self.markAllNotificationAsRead)
    
    $('.notification-link').click(function () {
      let programLayer = $(this).parent('.program')
      let parentProgramsLayer = programLayer.parent('.programs')
      let remixedProgramsLayer = parentProgramsLayer.parent('.remixed-programs')
      programLayer.remove()
      
      if (parentProgramsLayer.children().length === 0)
      {
        remixedProgramsLayer.remove()
        if ($('#notifications').children().length === 0)
        {
          $('#mark-all-as-seen').hide()
          $('.no-notifications-placeholder').show()
        }
      }
      window.location = $(this).attr('href')
      return true
    })
  }
  
  self.markAllNotificationAsRead = function () {
    $.ajax({
      url    : markAllAsReadUrl,
      type   : 'get',
      success: function (data) {
        if (!data.success)
        {
          swal(somethingWentWrongError, notificationsClearError, 'error')
          return
        }
        self.clearAll()
        self.showAllClearedPopUp()
      },
      error  : function () {
        swal(somethingWentWrongError, notificationsClearError, 'error')
      }
    })
  }
  
  self.markAsRead = function (id) {
    $.ajax({
      url    : self.markAsReadUrl + '/' + id,
      type   : 'get',
      success: function (data) {
        if (data.success)
        {
          self.notifications--
          
          self.updateNotificationAmountText()
          
          self.updateBadgeNumber()
          
          $('#catro-notification-' + id).fadeOut(function () {
            $('#catro-notification-' + id).parent().remove()
            
            let notificationsContainer = $('#notifications-container')
            if (notificationsContainer.children().length === 0)
            {
              self.clearAll()
              self.showAllClearedPopUp()
            }
          })
        }
        else
        {
          swal(somethingWentWrongError, notificationsClearError, 'error')
        }
      },
      error  : function () {
        swal(somethingWentWrongError, notificationsClearError, 'error')
      }
    })
  }
  
  self.updateBadgeNumber = function () {
    let userNotificationBadge = $('.user-notification-badge')
    let current_number = userNotificationBadge.data('badge')
    if (current_number > 1)
    {
      userNotificationBadge.data('badge', current_number - 1)
    }
  }
  
  self.updateNotificationAmountText = function () {
    let translations = []
    translations.push({
      key  : '%amount%',
      value: self.notifications
    })
    let url = Routing.generate('translate_choice', {
      'word'  : 'catro-notifications.summary',
      'count' : self.notifications,
      'array' : JSON.stringify(translations),
      'domain': 'catroweb'
    })
    $.get(url, function (data) {
      $('#notifications-summary').show()
      $('#total_amount_of_notifications').text(data)
    })
  }
  
  self.clearAll = function () {
    $('#notifications').children().remove()
    $('#notifications-container').children().remove()
    $('#notifications-summary').hide()
    $('#mark-all-as-seen').hide()
    $('.user-notification-badge').removeAttr('data-badge')
    $('.no-notifications-placeholder').show()
  }
  
  self.showAllClearedPopUp = function () {
    swal(
      {
        title             : self.popUpClearedAllMessagesTitle,
        text              : self.popUpClearedAllMessagesText,
        type              : 'success',
        confirmButtonClass: 'btn btn-success',
      }
    )
  }
}
