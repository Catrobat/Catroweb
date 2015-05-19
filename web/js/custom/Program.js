var Program = function(status_url, create_url)
{
    var self = this;
    
    self.status_url = status_url;
    self.create_url = create_url;
    
    self.getApkStatus = function()
    {
        $.get(self.status_url, null, self.onResult);
    };
    
    self.createApk = function()
    {
        $('.btn-apk').hide();
        $('#apk-pending').show();
        $.get(self.create_url, null, self.onResult);
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
            $('#apk-pending').show();
            console.log('pending');
            setTimeout(self.getApkStatus, 5000);
        }
        else
        {
            $('#apk-generate').show();
            $('#apk-generate').click(self.createApk);
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
    
};