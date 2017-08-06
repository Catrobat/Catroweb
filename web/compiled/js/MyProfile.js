/*
  Generated File by Grunt
  Sourcepath: web/js
*/
var MyProfile = function(profile_url, email_edit_url, profile_edit_url, avatar_edit_url, password_edit_url,
                         country_edit_url, save_email_url, save_country_url, save_password_url, delete_url,
                         toggle_visibility_url, deleteProgramString, upload_url) {
  var self = this;
  self.profile_url = profile_url;
  self.email_edit_url = email_edit_url;
  self.profile_edit_url = profile_edit_url;
  self.avatar_edit_url = avatar_edit_url;
  self.password_edit_url = password_edit_url;
  self.country_edit_url = country_edit_url;
  self.save_email_url = save_email_url;
  self.save_country_url = save_country_url;
  self.save_password_url =save_password_url;
  self.delete_url = delete_url;
  self.upload_url = upload_url;
  self.toggle_visibility_url = toggle_visibility_url;
  self.country = null;
  self.regex_email = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  self.data_changed = false;
  self.deleteProgramString = deleteProgramString;

  self.init = function() {
    self.setAvatarUploadListener();
  };

  self.deleteProgram = function(id) {
    var programName = $('#program-' + id).find('.program-name').text();
    if(confirm(self.deleteProgramString + ' \'' + programName + '\'?')) {
      window.location.href = self.delete_url + '/' + id;
    }
  };

  self.toggleVisibility = function(id) {
    var programName = $('#program-' + id).find('.program-name').text();
    window.location.href = self.toggle_visibility_url + '/' + id;
  };

  $(document).on("click", ".btn-edit", function() {

    $("#finished-button").hide();
    $("#profile-edit-wrapper").hide();

    switch($(this).attr('id')){
      case "avatar-button":
        window.location.href = self.avatar_edit_url;
        break;
      case "username-button":
        window.location.href = self.name_edit_url;
        break;
      case "country-button":
        window.location.href = self.country_edit_url;
        break;
      case "email-button":
        window.location.href = self.email_edit_url;
        break;
      case "password-button":
        window.location.href = self.password_edit_url;
        break;
    }
  });

  $(document).on("click", "#avatar-button-done", function () {
    window.location.href = self.profile_edit_url;
  });

  $(document).on("click", "#finished-button", function () {
    window.location.href = self.profile_url;
  });

  $(document).on("click", "#save-name", function(){
    var new_username = $('#username').val();
    $.post(self.save_name_url, {
      new_user_name: new_username
    }, function(data){
      switch (parseInt(data.statusCode)) {
        case 776:
          $('.text-username-invalid').show();
          break;
        case 777:
          $('.text-username-in-use').show();
          break;
        default:
          window.location.href = self.profile_edit_url;
      }
    });
  });

  $(document).on("click", "#save-email", function(){
    $('#email').parent().removeClass('mail-failed');
    $('#additional-email').parent().removeClass('mail-failed').removeClass('additional-mail-failed');

    $('.img-load-ajax').show();
    var new_email = $('#email').val();
    var additional_email = $('#additional-email').val();
    $.post(self.save_email_url, {
      firstEmail: new_email,
      secondEmail:additional_email
    }, function(data) {
      switch (parseInt(data.statusCode)) {
        case 200:
          //todo: when email was changed ... show alert that email was send to validate new email adress
          window.location.href = self.profile_edit_url;

          break;

        case 300:
          // email already exists
          if(parseInt(data.email) == 1) {
            $('#email').val(self.firstMail).parent().addClass('mail-failed');
            $('.text-email-exists').removeClass('hide');
          }
          if(parseInt(data.email) == 2) {
            $('#additional-email').val(self.secondMail).parent().addClass('mail-failed');
            $('.text-email-exists').removeClass('hide');
          }
          break;

        case 756:
          // there's no email
          $('#email').val(self.firstMail).parent().addClass('mail-failed');
          $('.text-email-missing').removeClass('hide');
          break;

        case 765:
          // invalid email
          $('#email').val(self.firstMail).parent().addClass('mail-failed');
          $('.text-email-notvalid').removeClass('hide');
          break;

        default:
          window.location.href = self.profile_edit_url;
      }
      $('.img-load-ajax').hide();
    });
    self.data_changed = false;
  });

  $(document).on("click", "#save-country", function(){
    $('.input-error').hide();

    $('.img-load-ajax').show();
    var country = $('#select-country').find('select').val();
    $.post(self.save_country_url, {
      country: country
    }, function(data) {
      switch (parseInt(data.statusCode)) {
        case 766:
          // invalid country code
          alert('invalid country');
          break;

        default:
          window.location.href = self.profile_edit_url;
          break;
      }
      $('.img-load-ajax').hide();
    });
  });

  $(document).on("click", "#save-password", function() {
    $('.input-error').hide();
    $('#password').parent().removeClass('password-failed');
    $('#repeat-password').parent().removeClass('password-failed');
    var new_password = $('#password').val();
    var old_password = $('#old-password').val();
    var repeat_password = $('#repeat-password').val();

    $.post(self.save_password_url, {
      oldPassword: old_password,
      newPassword: new_password,
      repeatPassword: repeat_password
    }, function(data) {
      switch (parseInt(data.statusCode)) {
        case 752:
          // username and password same !
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-isusername').removeClass('hide');
          break;

        case 753:
          // password too short
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-tooshort').removeClass('hide');
          break;

        case 754:
          // password too long
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-toolong').removeClass('hide');
          break;

        case 774:
          // passwords didn't match
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-nomatch').removeClass('hide');
          break;

        case 777:
          // old password wrong
          $('#old-password').val('').addClass('password-failed');
          $('.text-password-wrongpassword').removeClass('hide');
          break;

        default:
          window.location.href = self.profile_edit_url;
          break;
      }
      $('.img-load-ajax').hide();
    });
  });

  self.setAvatarUploadListener = function() {
    $('#avatar-upload').find('input[type=file]').change(function(data) {
      var avatarContainer = $('#profile-avatar-change');
      avatarContainer.find('.avatar-error').hide();

      var file = data.target.files[0];
      if(file.size > 5 * 1024 * 1024) {
        avatarContainer.find('.text-avatar-toolarge').show();
        return;
      }

      var avatarUpload = $('#avatar-upload');
      avatarUpload.find('span').hide();
      avatarUpload.find('.button-show-ajax').show();

      var reader = new FileReader();

      reader.onerror = function() {
        avatarContainer.find('.text-avatar-uploadError').show();
      };

      reader.onload = function(evt) {
        self.filename = evt.currentTarget.result;
        $.post(self.upload_url, { image: evt.currentTarget.result }, function(data) {
          switch (parseInt(data.statusCode)) {

            case 200:
              $('#avatar-img').attr('src', data.image_base64);
              $('#custom-avatar').find('div').first().css('background-image', 'url('+data.image_base64+')');
              $('.img-avatar').css({
                'background-image': 'url('+data.image_base64+')',
                'background-size': 'cover',
                'outline': '1px solid #FFF'
              });
              break;

            case 502:
              avatarContainer.find('.text-avatar-toolarge').show();
              break;

            case 516:
              avatarContainer.find('.text-avatar-noSupport').show();
              break;

            default:
              avatarContainer.find('.text-avatar-uploadError').show();
          }

          var avatarUpload = $('#avatar-upload');
          avatarUpload.find('span').show();
          avatarUpload.find('.button-show-ajax').hide();
        });
      };
      reader.readAsDataURL(file);
    });
  };
};
