/*
  Generated File by Grunt
  Sourcepath: web/js
*/
var Program = function(status_url, create_url, apk_preparing, apk_text, waiting_gif, update_app_header, update_app_text, btn_close_popup)
{
    var self = this;
    
    self.status_url = status_url;
    self.create_url = create_url;
    self.apk_preparing = apk_preparing;
    self.apk_text = apk_text;
    self.waiting_gif = waiting_gif;
    self.update_app_header = update_app_header;
    self.update_app_text = update_app_text;
    self.btn_close_popup = btn_close_popup;
    self.apk_url = null;
    self.apk_download_timeout = false;

    self.getApkStatus = function()
    {
        $.get(self.status_url, null, self.onResult);
    };
    
    self.createApk = function()
    {
        $('#apk-generate').hide();
        $('#apk-pending').show().css("display", "inline-block");
        $('#replace-me').html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true">');
        $.get(self.create_url, null, self.onResult);
        self.showPreparingApkPopup();
    };

    self.onResult = function(data)
    {
        $('#apk-pending').hide();
        if (data.status == 'ready')
        {
            self.apk_url = data.url;
            $('#apk-download').show();
            $('#apk-download').click(function() {
                if(!self.apk_download_timeout) {
                    self.apk_download_timeout = true;

                    setTimeout(function() {
                      self.apk_download_timeout = false;
                    }, 5000);

                    top.location.href = self.apk_url;
              }
            });
        }
        else if (data.status == "pending") 
        {
            $('#apk-pending').show().css("display", "inline-block");
            $('#replace-me').html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true">');
            setTimeout(self.getApkStatus, 5000);
        }
        else if (data.status == "none")
        {
            $('#apk-generate').show();
            $('#apk-generate').click(self.createApk);
        }

        if ($('#bg-dark, #popup-info').length > 0 && data.status == 'ready')
        {
            $('#bg-dark, #popup-info').remove();
        }
    };

    self.createLinks = function()
    {
        $('#description').each(function(){
            $(this).html( $(this).html().replace(/((http|https|ftp):\/\/[\w?=&.\/\+-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g, '<a href="$1" target="_blank">$1</a> ') );
        });
    };

    self.setReportListener = function(program_id, report_url)
    {
        $('#report, #report-cancel').click(function() { $('#report-container').toggle(); });

        $('#report-report').click(function() {
          if($('#reportReason').val() == ''){
              $('#reportReason').addClass('text-area-empty');
              return;
          }
          $('#reportReason').removeClass('text-area-empty');
          $.get(report_url, {
              program : program_id,
              note : $('#reportReason').val()
          }).success(function() {
              $('#report-form').hide();
              $('#report-done').show();
          }).fail(function() {
              alert('ERROR'); // display better error message
          });
        });
    };

    self.showUpdateAppPopup = function() {
        var popup_background = self.createPopupBackgroundDiv();
        var popup_div = self.createPopupDiv();
        popup_div.append("<h2>" + self.update_app_header + "</h2><br>");
        popup_div.append("<p>" + self.update_app_text + "</p>");

        var close_popup_button = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btn_close_popup + '</button>';
        popup_div.append(close_popup_button);

        $('body').append(popup_background);
        $('body').append(popup_div);

        $('#popup-background, #btn-close-popup').click(function() {
            popup_div.remove();
            popup_background.remove();
        });
    };

    self.showPreparingApkPopup = function() {
        var popup_background = self.createPopupBackgroundDiv();
        var popup_div = self.createPopupDiv();

        popup_div.append("<h2>" + self.apk_preparing + "</h2><br>");
        popup_div.append('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true">');
        popup_div.append("<p>" + self.apk_text + "</p>");

        var close_popup_button = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btn_close_popup + '</button>';
        popup_div.append(close_popup_button);

        $('body').append(popup_background);
        $('body').append(popup_div);

        $('#popup-background, #btn-close-popup').click(function() {
            popup_div.remove();
            popup_background.remove();
        });
    };

    self.createPopupDiv = function() {
        var popup_div = $('<div id="popup-info" class="popup-div"></div>');
        return popup_div;
    };

    self.createPopupBackgroundDiv = function() {
        var popup_background = $('<div id="popup-background" class="popup-bg"></div>');

        return popup_background;
    };

    self.create_cookie = function create_cookie(name, value, days2expire, path) {
        var date = new Date();
        date.setTime(date.getTime() + (days2expire * 24 * 60 * 60 * 1000));
        var expires = date.toUTCString();
        document.cookie = name + '=' + value + ';' +
            'expires=' + expires + ';' +
            'path=' + path + ';';
    }

    self.create_cookie('referrer', document.referrer, 1, '/');
};
