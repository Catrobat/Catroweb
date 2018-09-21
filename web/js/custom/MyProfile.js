let MyProfile = function (profile_url,
                          save_email_url, save_country_url, save_password_url,
                          delete_url, delete_account_url,
                          toggle_visibility_url, upload_url,
                          statusCode_OK,
                          statusCode_USER_EMAIL_ALREADY_EXISTS,
                          statusCode_USER_EMAIL_MISSING,
                          statusCode_USER_EMAIL_INVALID,
                          statusCode_USER_COUNTRY_INVALID,
                          statusCode_USER_USERNAME_PASSWORD_EQUAL,
                          statusCode_USER_PASSWORD_TOO_SHORT,
                          statusCode_USER_PASSWORD_TOO_LONG,
                          statusCode_USER_PASSWORD_NOT_EQUAL_PASSWORD2,
                          statusCode_PASSWORD_INVALID,
                          statusCode_UPLOAD_EXCEEDING_FILESIZE,
                          statusCode_UPLOAD_UNSUPPORTED_MIME_TYPE,
                          MAX_UPLOAD_SIZE,
                          successText, checkMailText, passwordUpdatedText,
                          programCanNotChangeVisibilityTitle,
                          programCanNotChangeVisibilityText) {
  let self = this
  self.profile_url = profile_url
  self.profile_edit_url = profile_url
  self.save_email_url = save_email_url
  self.save_country_url = save_country_url
  self.save_password_url = save_password_url
  self.delete_url = delete_url
  self.upload_url = upload_url
  self.toggle_visibility_url = toggle_visibility_url
  self.country = null
  self.regex_email = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
  self.data_changed = false
  self.delete_account_url = delete_account_url
  
  self.init = function () {
    self.setAvatarUploadListener()
  }
  
  let passwordEditContainer = $('#password-edit-container')
  let emailEditContainer = $('#email-edit-container')
  let emailData = $('#email-wrapper > .profile-data')
  let countryEditContainer = $('#country-edit-container')
  let countryData = $('#country-wrapper > .profile-data')
  let accountSettingsContainer = $('#account-settings-container')
  let profileSections = [
    [passwordEditContainer, null],
    [emailEditContainer, emailData], [countryEditContainer, countryData],
    [accountSettingsContainer, null]
  ]
  
  $(function () {
    $('.edit-container').hide()
  })
  
  $('#edit-password-button').on('click', function () {
    toggleEditSection(passwordEditContainer)
  })
  
  $('#edit-email-button').on('click', function () {
    toggleEditSection(emailEditContainer, emailData)
  })
  
  $('#edit-country-button').on('click', function () {
    toggleEditSection(countryEditContainer, countryData)
  })
  
  $('#account-settings-button').on('click', function () {
    toggleEditSection(accountSettingsContainer)
  })
  
  function toggleEditSection (container, data = null)
  {
    let fadeTime = 250
    if (container.is(':visible'))
    {
      container.slideUp()
      if (data)
      {
        data.fadeIn(fadeTime)
      }
    }
    else
    {
      container.slideDown()
      if (data)
      {
        data.fadeOut(fadeTime)
      }
      profileSections.forEach(function (entry) {
        if (entry[0] !== container)
        {
          entry[0].slideUp()
          if (entry[1])
          {
            entry[1].fadeIn(fadeTime)
          }
        }
      })
    }
  }
  
  self.deleteProgram = function (id) {
    let programName = $('#program-' + id).find('.program-name').text()
    let translations = []
    translations.push({
      key  : '%programName%',
      value: programName
    })
    let url = Routing.generate('translate_word', {
      'word'  : 'programs.deleteConfirmation',
      'array' : JSON.stringify(translations),
      'domain': 'catroweb'
    }, false)
    $.get(url, function (data) {
      let split = data.split('\n')
      swal({
        title             : split[0],
        html              : split[1] + '<br><br>' + split[2],
        type              : 'warning',
        showCancelButton  : true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor : '#d33',
        confirmButtonText : split[3],
        cancelButtonText  : split[4]
      }).then(() => {
        window.location.href = self.delete_url + '/' + id
      })
    })
  }
  
  self.toggleVisibility = function (id) {
    $.get(self.toggle_visibility_url + '/' + id, {}, function (data) {
      let visibilityLockId = $('#visibility-lock-' + id)
      let visibilityLockOpenId = $('#visibility-lock-open-' + id)
      if (data === 'true')
      {
        if (visibilityLockId.is(':visible'))
        {
          visibilityLockId.hide()
          visibilityLockOpenId.show()
        }
        else
        {
          visibilityLockOpenId.hide()
          visibilityLockId.show()
        }
      }
      else if (data === 'false')
      {
        swal({
          title             : programCanNotChangeVisibilityTitle,
          text              : programCanNotChangeVisibilityText,
          type              : 'error',
          confirmButtonClass: 'btn btn-danger',
        })
      }
    })
  }
  
  $(document).on('click', '#delete-account-button', function () {
    let url = Routing.generate('translate_word', {
      'word': 'programs.deleteAccountConfirmation'
    }, false)
    $.get(url, function (data) {
      let split = data.split('\n')
      swal({
        title             : split[0],
        html              : split[1] + '<br><br>' + split[2],
        type              : 'warning',
        showCancelButton  : true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor : '#d33',
        confirmButtonText : split[3],
        cancelButtonText  : split[4],
      }).then(() => {
        $.post(self.delete_account_url, null, function (data) {
          switch (parseInt(data.statusCode))
          {
            case statusCode_OK:
              window.location.href = '../../'
          }
        })
      })
      $('.swal2-container.swal2-shown').css('background-color', 'rgba(255, 0, 0, 0.75)')//changes the color of the overlay
    })
  })
  
  //$(document).on("click", "#save-name", function(){
  //  let new_username = $('#username').val();
  //  $.post(self.save_name_url, {
  //    new_user_name: new_username
  //  }, function(data){
  //    switch (parseInt(data.statusCode)) {
  //      case 776:
  //        $('.text-username-invalid').show();
  //        break;
  //      case 777:
  //        $('.text-username-in-use').show();
  //        break;
  //      default:
  //        window.location.href = self.profile_edit_url;
  //    }
  //  });
  //});
  
  $(document).on('click', '#save-email', function () {
    $(this).hide()
    $('#email-ajax').show()
    
    let email = $('#email')
    let additionalEmail = $('#additional-email')
    $('.error-message').addClass('d-none')
    
    let new_email = email.val()
    let additional_email = additionalEmail.val()
    $.post(self.save_email_url, {
      firstEmail : new_email,
      secondEmail: additional_email
    }, function (data) {
      switch (parseInt(data.statusCode))
      {
        case statusCode_OK:
          swal({
            title             : successText,
            text              : checkMailText,
            type              : 'success',
            confirmButtonClass: 'btn btn-success',
          }).then(() => {
            window.location.href = self.profile_edit_url
          })
          break
        
        case statusCode_USER_EMAIL_ALREADY_EXISTS:
          if (parseInt(data.email) === 1)
          {
            $('.text-email1-exists').removeClass('d-none')
          }
          if (parseInt(data.email) === 2)
          {
            $('.text-email2-exists').removeClass('d-none')
          }
          break
        
        case statusCode_USER_EMAIL_MISSING:
          $('.text-email-missing').removeClass('d-none')
          break
        
        case statusCode_USER_EMAIL_INVALID:
          if (parseInt(data.email) === 1)
          {
            $('.text-email1-notvalid').removeClass('d-none')
          }
          if (parseInt(data.email) === 2)
          {
            $('.text-email2-notvalid').removeClass('d-none')
          }
          break
        
        default:
          window.location.href = self.profile_edit_url
      }
      $('#email-ajax').hide()
      $('#save-email').show()
    })
    self.data_changed = false
  })
  
  $(document).on('click', '#save-country', function () {
    
    $(this).hide()
    $('#country-ajax').show()
    let country = $('#select-country').find('select').val()
    $.post(self.save_country_url, {
      country: country
    }, function (data) {
      switch (parseInt(data.statusCode))
      {
        case statusCode_USER_COUNTRY_INVALID:
          alert('invalid country')
          break
        
        default:
          window.location.href = self.profile_edit_url
          break
      }
      $('#country-ajax').hide()
      $('#save-country').show()
    })
  })
  
  $(document).on('click', '#save-password', function () {
    
    $(this).hide()
    $('#password-ajax').show()
    
    let password = $('#password')
    let repeatPassword = $('#repeat-password')
    $('.error-message').addClass('d-none')
    password.parent().removeClass('password-failed')
    repeatPassword.parent().removeClass('password-failed')
    let new_password = password.val()
    let old_password = $('#old-password').val()
    let repeat_password = repeatPassword.val()
    
    $.post(self.save_password_url, {
      oldPassword   : old_password,
      newPassword   : new_password,
      repeatPassword: repeat_password
    }, function (data) {
      switch (parseInt(data.statusCode))
      {
        case statusCode_USER_USERNAME_PASSWORD_EQUAL:
          $('.text-password-isusername').removeClass('d-none')
          break
        
        case statusCode_USER_PASSWORD_TOO_SHORT:
          $('.text-password-tooshort').removeClass('d-none')
          break
        
        case statusCode_USER_PASSWORD_TOO_LONG:
          $('.text-password-toolong').removeClass('d-none')
          break
        
        case statusCode_USER_PASSWORD_NOT_EQUAL_PASSWORD2:
          $('.text-password-nomatch').removeClass('d-none')
          break
        
        case statusCode_PASSWORD_INVALID:
          $('.text-password-wrongpassword').removeClass('d-none')
          break
        
        default:
          swal({
            title             : successText,
            text              : passwordUpdatedText,
            type              : 'success',
            confirmButtonClass: 'btn btn-success',
          }).then(() => {
            window.location.href = self.profile_edit_url
          })
          break
      }
      $('#password-ajax').hide()
      $('#save-password').show()
    })
  })
  
  self.setAvatarUploadListener = function () {
    $('#avatar-upload').find('input[type=file]').change(function (data) {
      $('.error-message').addClass('d-none')
      
      let file = data.target.files[0]
      if (file.size > MAX_UPLOAD_SIZE)
      {
        $('.text-avatar-toolarge').removeClass('d-none')
        return
      }
      
      let avatarUpload = $('#avatar-upload')
      avatarUpload.find('span').hide()
      avatarUpload.find('.button-show-ajax').show()
      
      let reader = new FileReader()
      
      reader.onerror = function () {
        $('.text-avatar-uploadError').removeClass('d-none')
      }
      
      reader.onload = function (evt) {
        self.filename = evt.currentTarget.result
        $.post(self.upload_url, {image: evt.currentTarget.result}, function (data) {
          switch (parseInt(data.statusCode))
          {
            
            case statusCode_OK:
              $('#avatar-img').attr('src', data.image_base64)
              $('#custom-avatar').find('div').first().css('background-image', 'url(' + data.image_base64 + ')')
              $('.img-avatar').css({
                'background-image': 'url(' + data.image_base64 + ')',
                'background-size' : 'cover',
                'outline'         : '1px solid #FFF'
              })
              break
            
            case statusCode_UPLOAD_EXCEEDING_FILESIZE:
              $('.text-avatar-toolarge').removeClass('d-none')
              break
            
            case statusCode_UPLOAD_UNSUPPORTED_MIME_TYPE:
              $('.text-avatar-noSupport').removeClass('d-none')
              break
            
            default:
              $('.text-avatar-uploadError').removeClass('d-none')
          }
          
          let avatarUpload = $('#avatar-upload')
          avatarUpload.find('span').show()
          avatarUpload.find('.button-show-ajax').hide()
        })
      }
      reader.readAsDataURL(file)
    })
  }
}
