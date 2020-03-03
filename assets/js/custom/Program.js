const Program = function(project_id, csrf_token, user_role, my_program, status_url, create_url, like_url,
                         like_detail_url, apk_preparing, apk_text, update_app_header, update_app_text,
                         btn_close_popup, like_action_add, like_action_remove, profile_url) {
  const self = this
  
  self.project_id = project_id
  self.csrf_token = csrf_token
  self.user_role = user_role
  self.my_program = my_program
  self.status_url = status_url
  self.create_url = create_url
  self.apk_preparing = apk_preparing
  self.apk_text = apk_text
  self.update_app_header = update_app_header
  self.update_app_text = update_app_text
  self.btn_close_popup = btn_close_popup
  self.like_action_add = like_action_add
  self.like_action_remove = like_action_remove
  self.apk_url = null
  self.apk_download_timeout = false
  
  self.getApkStatus = function () {
    $.get(self.status_url, null, self.onResult)
  }
  
  self.createApk = function () {
    $('#apk-generate').addClass('d-none')
    $('#apk-pending').removeClass('d-none')
    $.get(self.create_url, null, self.onResult)
    self.showPreparingApkPopup()
  }
  
  self.onResult = function (data) {
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
    }
    else if (data.status === 'pending')
    {
      apkPending.removeClass('d-none')
      setTimeout(self.getApkStatus, 5000)
    }
    else if (data.status === 'none')
    {
      apkGenerate.removeClass('d-none')
      apkGenerate.click(self.createApk)
    }
    else
    {
      apkGenerate.removeClass('d-none')
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
    let body = $('body')
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
    let body = $('body')
    
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
  
  self.showErrorAlert = function(message) {
    if (typeof message !== 'string' || message === '')
    {
      message = 'Something went wrong! Please try again later.';
    }
    
    swal({
      type : 'error',
      title: 'Oops...',
      text : message
    });
  };
  
  self.$projectLikeCounter = undefined;
  self.$projectLikeButtons = undefined;
  self.$projectLikeDetail = undefined;
  
  self.initProjectLike = function() {
    let detail_opened = false;
    
    const $container = $("#project-like");
    const $buttons = $("#project-like-buttons", $container);
    const $detail = $("#project-like-detail", $container);
    const $counter = $("#project-like-counter", $container);
    self.$projectLikeCounter = $counter;
    self.$projectLikeButtons = $buttons;
    self.$projectLikeDetail = $detail;
    
    $buttons.on('click', function() {
      if ($detail.css('display') === 'flex')
      {
        return;
      }
      $detail.css('display', 'flex').hide().fadeIn();
      detail_opened = true;
    });
    
    $("body").on('mousedown', function() {
      if (detail_opened)
      {
        $detail.fadeOut();
        detail_opened = false;
      }
    });
    
    $counter.on('click', function() {
      $.getJSON(like_detail_url,
        /** @param {{user: {id: string, name: string}, types: string[]}[]} data */
        function(data) {
          if (!Array.isArray(data))
          {
            self.showErrorAlert();
            console.error("Invalid data returned by like_detail_url", data);
            return;
          }
          
          const $modal = $("#project-like-modal");
          
          const thumbsUpData = data.filter(x => x.types.indexOf('thumbs_up') !== -1);
          const smileData = data.filter(x => x.types.indexOf('smile') !== -1);
          const loveData = data.filter(x => x.types.indexOf('love') !== -1);
          const wowData = data.filter(x => x.types.indexOf('wow') !== -1);
          
          /**
           * @param type string
           * @param data {{user: {id: string, name: string}, types: string[]}[]}
           */
          const fnUpdateContent = (type, data) => {
            const $tab = /** @type jQuery */ $modal.find('a#' + type + '-tab');
            const $content = $modal.find('#' + type + '-tab-content');
            $content.empty();
            
            // count
            $tab.find(' > span').text(data.length);
            
            if (data.length === 0 && type !== 'all')
            {
              $tab.parent().hide();
              return;
            }
            else
            {
              $tab.parent().show();
            }
            
            // tab content
            data.forEach(function(like) {
              const $like = $("<div/>").addClass("reaction");
              $like.append($("<a/>").attr('href', profile_url.replace('USERID', like.user.id)).text(like.user.name));
              const $like_types = $("<div/>").addClass("types");
              $like.append($like_types);
              
              const iconMapping = {
                thumbs_up: "fa-thumbs-up",
                smile    : "fa-grin-squint",
                love     : "fa-heart",
                wow      : "fa-surprise"
              };
              
              like.types.forEach((type) => {
                $like_types.append($("<i/>").addClass("fas").addClass(iconMapping[type]));
              });
              
              $content.append($like);
            });
            
          };
          
          fnUpdateContent('all', data);
          fnUpdateContent('thumbs-up', thumbsUpData);
          fnUpdateContent('smile', smileData);
          fnUpdateContent('love', loveData);
          fnUpdateContent('wow', wowData);
          
          $modal.modal('show');
          
        }).fail(function(jqXHR, textStatus, errorThrown) {
        self.showErrorAlert();
        console.error("Failed fetching like list", jqXHR, textStatus, errorThrown);
      });
    });
    
    $detail.find('.btn').on('click', function(event) {
      event.preventDefault();
      const action = this.classList.contains("active") ? like_action_remove : like_action_add;
      self.sendProjectLike($(this).data('like-type'), action);
    });
    
  };
  
  self.sendProjectLike = function(like_type, like_action) {
    
    const url = like_url +
      "?type=" + encodeURIComponent(like_type) +
      "&action=" + encodeURIComponent(like_action) +
      "&token=" + encodeURIComponent(csrf_token);
    
    if (self.user_role === 'guest')
    {
      window.location.href = url;
      return false;
    }
    
    $.ajax({
      url    : url,
      type   : 'get',
      success: function(data) {
        // update .active of button
        const $type_btn = self.$projectLikeDetail.find('.btn[data-like-type=' + like_type + ']');
        if (like_action === like_action_add)
        {
          $type_btn.addClass('active');
        }
        else
        {
          $type_btn.removeClass('active');
        }
        
        // update like count
        self.$projectLikeCounter.text(data.totalLikeCount.stringValue);
        if (data.totalLikeCount.value === 0)
        {
          self.$projectLikeCounter.addClass('d-none');
        }
        else
        {
          self.$projectLikeCounter.removeClass('d-none');
        }
        
        // update like buttons (behavior like in program.html.twig)
        if (!Array.isArray(data.activeLikeTypes) || data.activeLikeTypes.length === 0)
        {
          self.$projectLikeButtons.html('<div class="btn btn-primary btn-round"><i class="fas fa-thumbs-up"></i></div>');
        }
        else
        {
          let html = "";
          
          if (data.activeLikeTypes.indexOf('thumbs_up') !== -1)
          {
            html += '<div class="btn btn-primary btn-round"><i class="fas fa-thumbs-up"></i></div>';
          }
          
          if (data.activeLikeTypes.indexOf('smile') !== -1)
          {
            html += '<div class="btn icon-only"><i class="fas fa-grin-squint"></i></div>';
          }
          
          if (data.activeLikeTypes.indexOf('love') !== -1)
          {
            html += '<div class="btn btn-primary btn-round"><i class="fas fa-heart"></i></div>';
          }
          
          if (data.activeLikeTypes.indexOf('wow') !== -1)
          {
            html += '<div class="btn icon-only"><i class="fas fa-surprise"></i></div>';
          }
          
          self.$projectLikeButtons.html(html);
        }
  
      }
    }).fail(function(jqXHR, textStatus, errorThrown) {
      // on 401 redirect to url to log in
      if (jqXHR.status === 401)
      {
        window.location.href = url;
      }
      else
      {
        console.error("Like failure", jqXHR, textStatus, errorThrown);
        self.showErrorAlert();
      }
    });
  };
  
  $(function() {
    self.initProjectLike();
  })
};