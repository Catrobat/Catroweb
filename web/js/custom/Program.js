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
        if (data.status == "ready")
        {
            $("#apkDownloadLabel").text("download apk");
            $("#apkDownloadLink").click(function() { top.location.href = data.url; });
        }
        else if (data.status == "pending") 
        {
            $("#apkDownloadLabel").text("pending");
            $("#apkDownloadLabel").off('click');
            console.log("pending");
            setTimeout(self.getApkStatus, 5000);
        }
        else
        {
            $("#apkDownloadLabel").text("generate apk");
            $("#apkDownloadLink").click(self.createApk);
        }
    }
    
}