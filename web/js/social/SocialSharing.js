function getTwitterShareUrl ()
{
  $twitterShareBaseUrl = 'http://twitter.com/share?url='
  $twitterShareBaseUrl += window.location.href
  console.log($twitterShareBaseUrl)
  return $twitterShareBaseUrl
}

function triggerShareOnTwitter ()
{
  window.open(getTwitterShareUrl(), 'Twitter', 'width=490,height=530')
}

function triggerShareViaMail ($programName, $programDescription, $checkoutThisProgramMessage)
{
  var newLine = '%0D%0A'
  var subject = $programName
  var body = $checkoutThisProgramMessage + ':' + newLine + window.location.href + newLine + newLine + $programDescription
  
  var link = 'mailto:'
    + '?subject=' + subject
    + '&body=' + body
  
  window.location.href = link
}

function appendFacebookAppIdToShareLink ($facebookPlusShareBaseUrl)
{
  var $ajaxGetFBAppId = Routing.generate(
    'catrobat_oauth_login_get_facebook_appid', {flavor: 'pocketcode'}
  )
  $.get($ajaxGetFBAppId,
    function (data) {
      console.log(data)
      
      $facebookPlusShareBaseUrl += data['fb_appid']
      $facebookPlusShareBaseUrl += '&display=popup&href='
      $facebookPlusShareBaseUrl += window.location.href
      console.log($facebookPlusShareBaseUrl)
      
      window.open($facebookPlusShareBaseUrl, 'Facebook', 'width=490,height=530')
    })
}

function triggerShareOnFacebook ()
{
  $facebookPlusShareBaseUrl = 'https://www.facebook.com/dialog/share?app_id='
  appendFacebookAppIdToShareLink($facebookPlusShareBaseUrl)
}

function getGooglePlusShareUrl ()
{
  $googlePlusShareBaseUrl = 'https://plus.google.com/share?url='
  $googlePlusShareBaseUrl += window.location.href
  console.log($googlePlusShareBaseUrl)
  return $googlePlusShareBaseUrl
}

function triggerShareOnGooglePlus ()
{
  window.open(getGooglePlusShareUrl(), 'Google+', 'width=490,height=530')
}
