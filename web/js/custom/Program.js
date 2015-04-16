var Program = function(status_url, create_url)
{
    var self = this;
    
    self.status_url = status_url;
    self.create_url = create_url;
    
    self.getApkStatus = function()
    {
        $.get(self.status_url, null, self.onResult);
    }
    
    self.createApk = function()
    {
        $.get(self.create_url, null, self.onResult);
    }

    self.onResult = function(data)
    {
        $('#apkDownloadButton').show();
        if (data.status == "ready")
        {
            $("#apkDownloadLabel").text(data.label);
            $("#apkDownloadLink").click(function() { top.location.href = data.url; });
        }
        else if (data.status == "pending") 
        {
            $("#apkDownloadLabel").text(data.label);
            $("#apkDownloadLabel").off('click');
            console.log("pending");
            setTimeout(self.getApkStatus, 5000);
        }
        else
        {
            $("#apkDownloadLabel").text(data.label);
            $("#apkDownloadLink").click(self.createApk);
        }
    }
    
}