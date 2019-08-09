var Main = function (search_url) {
  var self = this
  self.search_url = search_url.replace(0, '')
  
  $(window).ready(function() {
    self.setClickListener()
    self.setWindowResizeListener()
    self.initSidebarSwipe()
  })
  $(document).ready(function() {
    //var s = 'script';
    //var id = 'facebook-jssdk';
    //var js, fjs = document.getElementsByTagName(s)[0];
    //if (document.getElementById(id)) return;
    //js = document.createElement(s);
    //js.id = id;
    //js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.5";
    //fjs.parentNode.insertBefore(js, fjs);
    $.ajaxSetup({cache: true})
    $.getScript('//connect.facebook.net/en_US/sdk.js', function () {
      var $appid = ''
      var $ajaxGetFBAppId = Routing.generate(
        'catrobat_oauth_login_get_facebook_appid', {flavor: 'pocketcode'}
      )
      $.get($ajaxGetFBAppId,
        function (data) {
          $appid = data['fb_appid']
          FB.init({
            appId  : $appid,
            xfbml  : true,
            status : true,
            cookie : true,  //allow the server to access the session
            version: 'v2.6'
          })
        })
    })
    
    //Google+ JS API:
    var po = document.createElement('script')
    po.type = 'text/javascript'
    po.async = true
    po.src = 'https://apis.google.com/js/client:plusone.js'
    var s = document.getElementsByTagName('script')[0]
    s.parentNode.insertBefore(po, s)
  })
  
  var sidebar, sidebarToggleBtn;
  var fnCloseSidebar = function() {
    sidebar.removeClass('active');
    sidebarToggleBtn.attr('aria-expanded', false)
  };
  var fnCloseSidebarDesktop = function() {
    sidebar.addClass('inactive');
    $("body").removeClass('new-nav');
    sidebarToggleBtn.attr('aria-expanded', false)
  };
  var fnOpenSidebar = function() {
    sidebar.addClass('active');
    sidebarToggleBtn.attr('aria-expanded', true)
  };
  var fnOpenSidebarDesktop = function() {
    sidebar.removeClass('inactive')
    $("body").addClass('new-nav')
    sidebarToggleBtn.attr('aria-expanded', true)
  };
  
  self.setClickListener = function() {
    sidebar = $("#sidebar")
    sidebarToggleBtn = $("#btn-sidebar-toggle")
    
    if ($(window).width() >= 768)
    {
      sidebarToggleBtn.attr('aria-expanded', true)
    }
    
    sidebarToggleBtn.on("click", function() {
      if ($(window).width() < 768)
      {
        // mobile mode
        if (sidebar.hasClass('active'))
        {
          fnCloseSidebar()
        }
        else
        {
          fnOpenSidebar()
        }
      }
      else
      {
        // desktop mode
        if (sidebar.hasClass('inactive'))
        {
          fnOpenSidebarDesktop()
        }
        else
        {
          fnCloseSidebarDesktop()
        }
      }
    });
    
    sidebar.find('a.nav-link').on("click", fnCloseSidebar);
    $('#sidebar-overlay').on("click", fnCloseSidebar);
    
    self.setSearchBtnListener()
    self.setLanguageSwitchListener()
  }
  
  self.setWindowResizeListener = function () {
    $(window).resize(function () {
      $('#nav-dropdown').hide()
    })
  }
  
  self.setSearchBtnListener = function () {
    
    // search enter pressed
    $('input.input-search').keypress(function (event) {
      if (event.which === 13)
      {
        const search_term = $(this).val()
        if (!search_term)
        {
          $(this).tooltip('show')
          return
        }
        self.searchPrograms(search_term)
      }
    })
    
    // search button clicked (header)
    $('.btn-search').click(function () {
      const search_field = $(this).parent().parent().find('input.input-search')
      const search_term = search_field.val()
      if (!search_term)
      {
        search_field.tooltip('show')
        return
      }
      self.searchPrograms(search_term)
    })
    
  }
  
  self.searchPrograms = function (string) {
    window.location.href = self.search_url + encodeURIComponent(string.trim())
  }
  
  self.setLanguageSwitchListener = function () {
    var select = $('#switch-language')
    select.change(function () {
      document.cookie = 'hl=' + $(this).val() + '; path=/'
      location.reload()
    })
  }
  
  self.getCookie = function (cname) {
    var name = cname + '='
    var ca = document.cookie.split(';')
    for (var i = 0; i < ca.length; i++)
    {
      var c = ca[i]
      while (c.charAt(0) == ' ')
      {
        c = c.substring(1)
      }
      if (c.indexOf(name) != -1)
      {
        return c.substring(name.length, c.length)
      }
    }
    return ''
  }
  
  self.initSidebarSwipe = function() {
    
    var sidebar = $("#sidebar");
    var sidebar_width = sidebar.width();
    var sidebar_overlay = $("#sidebar-overlay");
    
    var cur_x = null;
    var start_time = null;
    var start_x = null, start_y = null;
    
    var opening = false;
    var closing = false;
    
    var desktop = false;
    
    var touch_threshold = 25; // area where touch is possible
    
    function refrehSidebar()
    {
      var left = (cur_x >= sidebar_width) ? 0 : cur_x - sidebar_width;
      sidebar.css('transition', 'none').css('left', left);
      if (!desktop)
      {
        var opacity = (cur_x >= sidebar_width) ? 1 : cur_x / sidebar_width;
        sidebar_overlay.css('transition', 'all 10ms ease-in-out').css('display', 'block').css('opacity', opacity);
      }
    }
    
    document.addEventListener('touchstart', function(e) {
      cur_x = null;
      closing = false;
      opening = false;
      
      if (e.touches.length === 1)
      {
        var touch = e.touches[0];
        
        desktop = $(window).width() >= 768;
        
        var sidebar_opened = (desktop && !sidebar.hasClass('inactive')) || (!desktop && sidebar.hasClass('active'));
        if (sidebar_opened)
        {
          cur_x = touch.pageX;
          start_x = touch.pageX;
          start_y = touch.pageY;
          start_time = Date.now();
          closing = true;
        }
        else
        {
          if (touch.pageX < touch_threshold)
          {
            cur_x = touch.pageX;
            start_x = touch.pageX;
            start_y = touch.pageY;
            start_time = Date.now();
            opening = true;
            refrehSidebar();
          }
        }
      }
    });
    
    document.addEventListener('touchmove', function(e) {
      if (e.touches.length === 1 && (closing || opening) && !!cur_x)
      {
        cur_x = e.touches[0].pageX;
        
        if (closing)
        {
          var touch_y = e.touches[0].pageY;
          var y_diff = Math.abs(touch_y - start_y);
          var x_diff = Math.abs(cur_x - start_x);
          
          if (x_diff > y_diff * 1.25)
          {
            refrehSidebar();
          }
          else
          {
            reset();
          }
        }
        else
        {
          refrehSidebar();
        }
      }
    });
    
    
    document.addEventListener('touchend', function(e) {
      if (e.changedTouches.length === 1 && (closing || opening) && !!cur_x && start_time)
      {
        var touch_x = e.changedTouches[0].pageX;
        var touch_y = e.changedTouches[0].pageY;
        var time_diff = Date.now() - start_time;
        var slow = time_diff > 100; //100 ms
        
        if (closing)
        {
          if (
            (slow && touch_x < sidebar_width / 2) ||
            (!slow && touch_x < sidebar_width && touch_x < start_x && Math.abs(start_x - touch_x) > Math.abs(start_y - touch_y))
          )
          {
            if (desktop)
            {
              fnCloseSidebarDesktop();
            }
            else
            {
              fnCloseSidebar();
            }
          }
        }
        else if (opening)
        {
          if (
            (slow && touch_x > sidebar_width / 2) ||
            (!slow && touch_x > touch_threshold && touch_x > start_x && Math.abs(start_x - touch_x) > Math.abs(start_y - touch_y))
          )
          {
            if (desktop)
            {
              fnOpenSidebarDesktop();
            }
            else
            {
              fnOpenSidebar();
            }
          }
        }
        
      }
      
      reset();
      
    });
    
    function reset()
    {
      sidebar.css('left', '').css('transition', '');
      sidebar_overlay.css('display', '').css('opacity', '').css('transition', '');
      cur_x = null;
      start_time = null;
      start_x = null;
      start_y = null;
      
      opening = false;
      closing = false;
      
      desktop = false;
    }
    
  };
  
};

$(function() {
  
  // -------------------------------------------------------------------------------------------------------------------
  // Adjust heading font size or break word
  ['h1', '.h1', 'h2', '.h2', 'h3', '.h3'].forEach(function(element) {
    $(element + ':not(.no-textfill)').each(function() {
      textfillDefault(this);
    });
  });
  
  function textfillDefault(container)
  {
    const maxFontPixels = parseFloat($(container).css('font-size'));
    const minFontPixels = Math.round(maxFontPixels * 0.7);
    
    const html = $(container).html();
    $(container).empty();
    const $span = $("<span/>").html(html);
    $(container).append($span);
    
    $(container).textfill({
      maxFontPixels: maxFontPixels,
      minFontPixels: minFontPixels,
      widthOnly    : true,
      innerTag     : 'span',
      fail         : function() {
        $(container).addClass('force-word-break');
        $(container).html(html);
      },
      success      : function() {
        $(container).removeClass('force-word-break');
        const newFontSize = $span.css('font-size');
        $(container).html(html);
        if (parseFloat(newFontSize) < maxFontPixels)
        {
          $(container).css('font-size', newFontSize);
        }
      }
    });
  }
  
  // -------------------------------------------------------------------------------------------------------------------
  // Search field
  const search_input = $('.input-search');
  
  search_input.tooltip({
    trigger  : 'manual',
    placement: 'bottom'
  });
  
  search_input.on('shown.bs.tooltip', function() {
    setTimeout(function() {
      search_input.tooltip('hide');
    }, 1000)
  })
  
});
