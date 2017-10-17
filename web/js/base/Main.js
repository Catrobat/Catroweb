var Main = function(search_url)
{
  var self = this;
  self.search_url = search_url.replace(0, '');
  
  $(window).ready(function()
  {
    self.setClickListener();
    self.setWindowResizeListener();
    
    $("#feature-slider").owlCarousel({
      responsive: true,
      items     : 1,
      singleItem: true,
      autoPlay  : 5000
    });
  });
  $(document).ready(function()
  {
    //var s = 'script';
    //var id = 'facebook-jssdk';
    //var js, fjs = document.getElementsByTagName(s)[0];
    //if (document.getElementById(id)) return;
    //js = document.createElement(s);
    //js.id = id;
    //js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.5";
    //fjs.parentNode.insertBefore(js, fjs);
    $.ajaxSetup({cache: true});
    $.getScript('//connect.facebook.net/en_US/sdk.js', function()
    {
      var $appid = '';
      var $ajaxGetFBAppId = Routing.generate(
        'catrobat_oauth_login_get_facebook_appid', {flavor: 'pocketcode'}
      );
      $.get($ajaxGetFBAppId,
        function(data)
        {
          console.log(data);
          $appid = data['fb_appid'];
          FB.init({
            appId  : $appid,
            xfbml  : true,
            status : true,
            cookie : true,  //allow the server to access the session
            version: 'v2.6'
          });
        });
    });
    
    //Google+ JS API:
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.async = true;
    po.src = 'https://apis.google.com/js/client:plusone.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
  });
  
  self.setClickListener = function()
  {
    var nav = $('nav');
    var navDropdown = $('#nav-dropdown');
    
    // toggle footer view
    $('#footer-more-less').click(function()
    {
      $('body').toggleClass('footer-expand');
      $(window).scrollTop($(document).height());
    });
    
    // toggle searchbar
    $('#menu-mobile').find('.btn-search').click(function()
    {
      nav.toggleClass('searchbar-visible');
      nav.find('input').focus();
    });
    
    // toggle navigation dropdown (when logged in)
    $('.show-nav-dropdown').click(function()
    {
      var newPosition = nav.position().left + nav.outerWidth() - navDropdown.width();
      navDropdown.css('left', newPosition).toggle();
    });
    
    $('#copy-link').click(function()
    {
      $(this).find('tr').first().hide();
      $(this).find('tr').last().show();
      $('#url-link').focus().select();
    });
    
    self.setSearchBtnListener();
    self.setLanguageSwitchListener();
  };
  
  self.setWindowResizeListener = function()
  {
    $(window).resize(function()
    {
      $('#nav-dropdown').hide();
    });
  };
  
  self.setSearchBtnListener = function()
  {
    // search enter pressed
    $('.input-search').keypress(function(event)
    {
      if (event.which == 13)
      {
        self.searchPrograms($(this).val());
      }
    });
    
    // search button clicked (header)
    $('.btn-search').click(function()
    {
      self.searchPrograms($(this).prev().find('input').val());
    });
    
    // search button clicked (footer)
    // TODO: when applying bootstrap to the footer this has to be changed to make it work
    $('#footer-menu-desktop').find('.img-magnifying-glass').click(function()
    {
      self.searchPrograms($(this).prev().find('input').val());
    });
  };
  
  self.searchPrograms = function(string)
  {
    window.location.href = self.search_url + encodeURIComponent(string.trim());
  };
  
  self.setLanguageSwitchListener = function()
  {
    var select = $('#switch-language');
    select.change(function()
    {
      document.cookie = 'hl=' + $(this).val() + "; path=/";
      location.reload();
    });
  };
  
  self.getCookie = function(cname)
  {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++)
    {
      var c = ca[i];
      while (c.charAt(0) == ' ')
      {
        c = c.substring(1);
      }
      if (c.indexOf(name) != -1)
      {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  };
  
  
};
