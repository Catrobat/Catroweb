/*
  Generated File by Grunt
  Sourcepath: web/js
*/
var MyProfile = function(url, delete_url, deleteProgramString, upload_url) {
  var self = this;
  self.save_url = url;
  self.delete_url = delete_url;
  self.upload_url = upload_url;
  self.newPassword = null;
  self.repeatPassword = null;
  self.firstMail = null;
  self.secondMail = null;
  self.country = null;
  self.regex_email = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  self.data_changed = false;
  self.deleteProgramString = deleteProgramString;

  self.tmp_newPassword = null;
  self.tmp_repeatPassword = null;
  self.tmp_firstMail = null;
  self.tmp_secondMail = null;
  self.tmp_country = null;

  self.init = function() {
    self.newPassword = $('#password').val();
    self.repeatPassword = $('#repeat-password').val();
    self.firstMail = $('#email').val();
    self.secondMail = $('#additional-email').val();
    self.country = $('#select-country').find('select').val();

    $('#profile-form').find('form').submit(function() {
      self.tmp_newPassword = $('#password').val();
      self.tmp_repeatPassword = $('#repeat-password').val();
      self.tmp_firstMail = $('#email').val();
      self.tmp_secondMail = $('#additional-email').val();
      self.tmp_country = $('#select-country').find('select').val();

      $('.input-error').hide();
      $('#password').parent().removeClass('password-failed');
      $('#repeat-password').parent().removeClass('password-failed');
      $('#email').parent().removeClass('mail-failed');
      $('#additional-email').parent().removeClass('mail-failed');

      $('#submit-done').hide();
      if(!self.checkPasswords() || !self.checkFirstMail() || !self.checkSecondMail() || !self.checkCountry())
        return false;
      self.submit();
      return false;
    });

    self.setAvatarUploadListener();
  };

  self.checkPasswords = function() {
    if(self.tmp_newPassword != self.tmp_repeatPassword) {
      $('#password').val('').parent().addClass('password-failed');
      $('#repeat-password').val('').parent().addClass('password-failed');
      $('.text-password-nomatch').show();
      return false;
    }
    if(self.newPassword != self.tmp_newPassword) self.data_changed = true;
    return true;
  };

  self.checkFirstMail = function() {
    var emailChanged = self.firstMail != self.tmp_firstMail;
    if(emailChanged && self.tmp_firstMail.length > 0 && !self.regex_email.test(self.tmp_firstMail)) {
      $('#email').val(self.firstMail).parent().addClass('mail-failed');
      $('.text-email-notvalid').show();
      return false;
    }
    if(self.firstMail != self.tmp_firstMail) self.data_changed = true;
    return true;
  };

  self.checkSecondMail = function() {
    if(self.secondMail != self.tmp_secondMail && self.tmp_secondMail != '' && !self.regex_email.test(self.tmp_secondMail)) {
      $('#additional-email').val(self.secondMail).parent().addClass('mail-failed').addClass('additional-mail-failed');
      $('.text-email-notvalid').show();
      return false;
    }
    if(self.secondMail != self.tmp_secondMail) self.data_changed = true;
    return true;
  };

  self.checkCountry = function() {
    if(self.country != self.tmp_country) self.data_changed = true;
    return true;
  };

  self.submit = function() {
    $('.input-error').hide();
    $('#password').val('').parent().removeClass('password-failed');
    $('#repeat-password').val('').parent().removeClass('password-failed');
    $('#email').parent().removeClass('mail-failed');
    $('#additional-email').parent().removeClass('mail-failed').removeClass('additional-mail-failed');

    if(!self.data_changed) return;
    $('.img-load-ajax').show();
    $.post(self.save_url, {
      newPassword: self.tmp_newPassword,
      repeatPassword: self.tmp_repeatPassword,
      firstEmail: self.tmp_firstMail,
      secondEmail: self.tmp_secondMail,
      country: self.tmp_country
    }, function(data) {
      switch (parseInt(data.statusCode)) {
        case 200:
          //todo: when email was changed ... show alert that email was send to validate new email adress
          self.data_changed = false;
          self.firstMail = self.tmp_firstMail;
          self.secondMail = self.tmp_secondMail;
          self.country = self.tmp_country;

          if(self.firstMail === '' && self.secondMail !== '') {
            self.firstMail = self.secondMail;
            self.secondMail = '';
            $('#email').val(self.firstMail);
            $('#additional-email').val(self.secondMail);
          }

          $('#submit-done').show();
          break;

        case 300:
          // email already exists
          if(parseInt(data.email) == 1) {
            $('#email').val(self.firstMail).parent().addClass('mail-failed');
            $('.text-email-exists').show();
          }
          if(parseInt(data.email) == 2) {
            $('#additional-email').val(self.secondMail).parent().addClass('mail-failed');
            $('.text-email-exists').show();
          }
          break;

        case 752:
          // username and password same !
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-isusername').show();
          break;

        case 753:
          // password too short
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-tooshort').show();
          break;

        case 754:
          // password too long
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-toolong').show();
          break;

        case 756:
          // there's no email
          $('#email').val(self.firstMail).parent().addClass('mail-failed');
          $('.text-email-missing').show();
          break;

        case 765:
          // invalid email
          $('#email').val(self.firstMail).parent().addClass('mail-failed');
          $('.text-email-notvalid').show();
          break;

        case 766:
          // invalid country code
          alert('invalid country');
          break;

        case 774:
          // passwords didn't match
          $('#password').val('').parent().addClass('password-failed');
          $('#repeat-password').val('').parent().addClass('password-failed');
          $('.text-password-nomatch').show();
          break;

        default:
          // ^^
      }
      $('.img-load-ajax').hide();
    });
    self.data_changed = false;
  };

  self.deleteProgram = function(id) {
    var programName = $('#program-' + id).find('.program-name').text();
    if(confirm(self.deleteProgramString + ' \'' + programName + '\'?')) {
      window.location.href = self.delete_url + '/' + id;
    }
  };

  self.setAvatarUploadListener = function() {
    $('#avatar-upload').find('input[type=file]').change(function(data) {
      var avatarContainer = $('#profile-avatar');
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
              $('#profile-avatar').find('img').attr('src', data.image_base64);
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