var Program = function(status_url, create_url, apk_preparing, apk_text, waiting_gif)
{
    var self = this;
    
    self.status_url = status_url;
    self.create_url = create_url;
    self.apk_preparing = apk_preparing;
    self.apk_text = apk_text;
    self.waiting_gif = waiting_gif;
    
    self.getApkStatus = function()
    {
        $.get(self.status_url, null, self.onResult);
    };
    
    self.createApk = function()
    {
        $('.btn-apk').hide();
        $('#apk-pending').show().css("display", "inline-block");
        $.get(self.create_url, null, self.onResult);
        self.showPopup();
    };

    self.onResult = function(data)
    {
        $('.btn-apk').hide();
        if (data.status == 'ready')
        {
            $('#apk-download').show();
            $('#apk-download').click(function() { top.location.href = data.url; });
        }
        else if (data.status == "pending") 
        {
            $('#apk-pending').show().css("display", "inline-block");
            console.log('pending');
            setTimeout(self.getApkStatus, 5000);
        }
        else
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
            $(this).html( $(this).html().replace(/((http|https|ftp):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g, '<a href="$1" target="_blank">$1</a> ') );
        });
    };

    self.setReportListener = function(program_id, report_url)
    {
        $('#report, #report-cancel').click(function() { $('#report-container').toggle(); });

        $('#report-report').click(function() {
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

    self.showPopup = function() {
        var popup_div = $('<div id="popup-info"></div>');
        var dark_background = $('<div id="bg-dark"></div>');
        dark_background.css({
            'position': 'fixed',
            'width': '100%',
            'height': '100%',
            'background-color': 'black',
            'left': '0',
            'top': '0',
            'opacity': '0.5'
        });

        popup_div.css({
            'position': 'fixed',
            'width': '320px',
            'height': '250px',
            'background-color': '#05222a',
            'left': '50%',
            'top': '50%',
            'border': '3px solid #17A5B8',
            'border-radius': '10px',
            'padding': '20px 5px 20px 5px',
            'margin': '-125px 0 0 -160px',
            'text-align': 'center',
            'font-size': '15px'
        });

        popup_div.append("<h2>" + self.apk_preparing + " <span class='blink-one'>.</span> <span class='blink-two'>.</span> <span class='blink-three'>.</span> </h2><br>");
        popup_div.append('<img class="pending-icon" src="' + waiting_gif + '">');
        popup_div.append("<p>" + self.apk_text + "</p>");

        var ok_button = '<button id="ok-button" class="btn btn-primary" style="min-width: 50%; margin-top: 2px">OK</button>';
        popup_div.append(ok_button);

        $('body').append(dark_background);
        $('body').append(popup_div);

        $('#bg-dark, #ok-button').click(function() {
            popup_div.remove();
            dark_background.remove();
        });
    };
    
};