/*
  Generated File by Grunt
  Sourcepath: web/js
*/
let Program = function (status_url, create_url, apk_preparing, apk_text, update_app_header, update_app_text, btn_close_popup) {
  let self = this
  
  self.status_url = status_url
  self.create_url = create_url
  self.apk_preparing = apk_preparing
  self.apk_text = apk_text
  self.update_app_header = update_app_header
  self.update_app_text = update_app_text
  self.btn_close_popup = btn_close_popup
  self.apk_url = null
  self.apk_download_timeout = false
  
  self.getApkStatus = function () {
    console.log("getApkStatus")
    $.get(self.status_url, null, self.onResult)
  }
  
  self.createApk = function () {
    console.log("createApk")
    $('#apk-generate').addClass('d-none')
    $('#apk-pending').removeClass('d-none')
    $.get(self.create_url, null, self.onResult)
    self.showPreparingApkPopup()
  }
  
  self.onResult = function (data) {
    console.log("onResult: " + JSON.stringify(data))
    let apkPending = $('#apk-pending')
    let apkDownload = $('#apk-download')
    let apkGenerate = $('#apk-generate')
    apkGenerate.addClass('d-none')
    apkDownload.addClass('d-none')
    apkPending.addClass('d-none')
    if (data.status === 'ready')
    {
      self.apk_url = data.url
      apkDownload.removeClass('d-none')
      apkDownload.click(function () {
        if (!self.apk_download_timeout)
        {
          self.apk_download_timeout = true
          
          setTimeout(function () {
            self.apk_download_timeout = false
          }, 5000)
          
          top.location.href = self.apk_url
        }
      })
      console.log("ready")
    }
    else if (data.status === 'pending')
    {
      apkPending.removeClass('d-none')
      setTimeout(self.getApkStatus, 5000)
      console.log("pending")
    }
    else if (data.status === 'none')
    {
      apkGenerate.removeClass('d-none')
      apkGenerate.click(self.createApk)
      console.log("none")
    }
    else {
      apkGenerate.removeClass('d-none')
      console.log("else")
    }
    
    let bgDarkPopupInfo = $('#bg-dark, #popup-info')
    if (bgDarkPopupInfo.length > 0 && data.status === 'ready')
    {
      bgDarkPopupInfo.remove()
    }
  }
  
  self.createLinks = function () {
    $('#description').each(function () {
      $(this).html($(this).html().replace(/((http|https|ftp):\/\/[\w?=&.\/+-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g, '<a href="$1" target="_blank">$1</a> '))
    })
  }
  
  self.showUpdateAppPopup = function () {
    let popup_background = self.createPopupBackgroundDiv()
    let popup_div = self.createPopupDiv()
    let body = $('body');
    popup_div.append('<h2>' + self.update_app_header + '</h2><br>')
    popup_div.append('<p>' + self.update_app_text + '</p>')
    
    let close_popup_button = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btn_close_popup + '</button>'
    popup_div.append(close_popup_button)
    
    body.append(popup_background)
    body.append(popup_div)
    
    $('#popup-background, #btn-close-popup').click(function () {
      popup_div.remove()
      popup_background.remove()
    })
  }
  
  self.showPreparingApkPopup = function () {
    let popup_background = self.createPopupBackgroundDiv()
    let popup_div = self.createPopupDiv()
    let body = $('body');
    
    popup_div.append('<h2>' + self.apk_preparing + '</h2><br>')
    popup_div.append('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true">')
    popup_div.append('<p>' + self.apk_text + '</p>')
    
    let close_popup_button = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btn_close_popup + '</button>'
    popup_div.append(close_popup_button)
    
    body.append(popup_background)
    body.append(popup_div)
    
    $('#popup-background, #btn-close-popup').click(function () {
      popup_div.remove()
      popup_background.remove()
    })
  }
  
  self.createPopupDiv = function () {
    return $('<div id="popup-info" class="popup-div"></div>')
  }
  
  self.createPopupBackgroundDiv = function () {
    return $('<div id="popup-background" class="popup-bg"></div>')
  }
  
  self.create_cookie = function create_cookie (name, value, days2expire, path) {
    let date = new Date()
    date.setTime(date.getTime() + (days2expire * 24 * 60 * 60 * 1000))
    let expires = date.toUTCString()
    document.cookie = name + '=' + value + ';' +
      'expires=' + expires + ';' +
      'path=' + path + ';'
  }
  
  self.create_cookie('referrer', document.referrer, 1, '/')
}
