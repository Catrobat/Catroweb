function MediaLib(categories, flavor, assetsDir)
{
  $(function() {
    getCategoryFiles(categories, flavor, assetsDir);
    let $content = $("#content");
    $content.find('#thumbsize-control input[type=radio]').change(function() {
      $content.attr("size", this.value);
    });
    initTilePinchToZoom();
  });
  
  function getCategoryFiles(categories, flavor, assetsDir)
  {
    categories.forEach(function(category) {
      
      if (category.displayID === 'theme-special')
      {
        return;
      }
      
      let url = Routing.generate('api_media_lib_category', {category: category.name}, false);
      $.get(url, {}, function(data) {
        
        if (data.statusCode === 200)
        {
          let counter = 0;
          let f_counter = 0;
          const categoryFiles = data.data;
          
          categoryFiles.forEach(function(file) {
            
            if (file.flavor === 'pocketcode' || file.flavor === flavor)
            {
              let catID = (file.flavor === 'pocketcode') ? category.displayID : 'theme-special';
              let mediafileContainer = '<a class="mediafile" id="mediafile-' + file.id + '" href="' + file.download_url + '" onclick="medialib_onDownload(this)">';
              
              switch (file.extension)
              {
                case "adp":
                case "au":
                case "mid":
                case "mp4a":
                case "mpga":
                case "oga":
                case "s3m":
                case "sil":
                case "uva":
                case "eol":
                case "dra":
                case "dts":
                case "dtshd":
                case "lvp":
                case "pya":
                case "ecelp4800":
                case "ecelp7470":
                case "ecelp9600":
                case "rip":
                case "weba":
                case "aac":
                case "aif":
                case "caf":
                case "flac":
                case "mka":
                case "m3u":
                case "wax":
                case "wma":
                case "ram":
                case "rmp":
                case "wav":
                case "xm":
                  mediafileContainer += '<i class="fas fa-file-audio" title="' + file.name + '"></i>';
                  break;
                case "3gp":
                case "3g2":
                case "h261":
                case "h263":
                case "h264":
                case "jpgv":
                case "jpm":
                case "mj2":
                case "mp4":
                case "mpeg":
                case "ogv":
                case "qt":
                case "uvh":
                case "uvm":
                case "uvp":
                case "uvs":
                case "uvv":
                case "dvb":
                case "fvt":
                case "mxu":
                case "pyv":
                case "uvu":
                case "viv":
                case "webm":
                case "f4v":
                case "fli":
                case "flv":
                case "m4v":
                case "mkv":
                case "mng":
                case "asf":
                case "vob":
                case "wm":
                case "wmv":
                case "wmx":
                case "wvx":
                case "avi":
                case "movie":
                case "smv":
                  mediafileContainer += '<i class="fas fa-file-video" title="' + file.name + '"></i>';
                  break;
                case "pdf":
                  mediafileContainer += '<i class="fas fa-file-pdf" title="' + file.name + '"></i>';
                  break;
                case "txt":
                case "rtx":
                  mediafileContainer += '<i class="fas fa-file-alt" title="' + file.name + '"></i>';
                  break;
                case "zip":
                case "7z":
                  mediafileContainer += '<i class="fas fa-file-archive" title="' + file.name + '"></i>';
                  break;
                default:
                  mediafileContainer += '<img src="' + assetsDir + 'thumbs/' + file.id + '.jpeg" title="' + file.name + '" alt="' + file.name + '" onerror="medialib_onThumbError(event, \'' + file.extension + '\')"/>';
                  break;
              }
              mediafileContainer += '</a>';
              
              $('#category-' + catID + " .files").prepend(mediafileContainer);
              
              if (flavor !== 'pocketcode' && file.flavor === flavor)
              {
                f_counter++;
              }
              else
              {
                counter++;
              }
            }
          });
          
          if ($("#category-" + category.displayID + " .files > div").length + counter > 0)
          {
            $('#category-' + category.displayID).show();
            $('#sidebar #menu-mediacat-' + category.displayID).show();
          }
          
          if ($("#category-theme-special .files > div").length + f_counter > 0)
          {
            $('#category-theme-special').show();
            $('#sidebar #menu-mediacat-theme-special').show();
          }
        }
        else
        {
          console.error("Error loading category " + category.name);
        }
      })
    });
  }
  
  function initTilePinchToZoom()
  {
    let $mediafiles = null;
    
    let active = false;
    let start_distance = null;
    let current_size = null;
    
    const border_spacing = 8;
    
    function refreshStyle()
    {
      $mediafiles.css('width', current_size).css('height', current_size);
      let inner_size = current_size - border_spacing;
      $mediafiles.find('> img').attr('style', 'max-width:' + inner_size + 'px !important; max-height:' + inner_size + 'px;');
      $mediafiles.find('.fas, .far').css('font-size', current_size - 15);
    }
    
    document.addEventListener('touchstart', function(e) {
      reset();
      
      if (e.touches.length === 2)
      {
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        
        const x_diff = touch2.clientX - touch1.clientX;
        const y_diff = touch2.clientY - touch1.clientY;
        
        start_distance = Math.sqrt((x_diff * x_diff) + (y_diff * y_diff));
        active = true;
        $("#thumbsize-control").hide();
        
        if ($mediafiles == null || current_size == null)
        {
          $mediafiles = $(".category > .files .mediafile");
          current_size = $mediafiles.outerWidth();
        }
      }
    });
    
    document.addEventListener('touchmove', function(e) {
      if (active && !!start_distance && e.touches.length === 2)
      {
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        
        const x_diff = touch2.clientX - touch1.clientX;
        const y_diff = touch2.clientY - touch1.clientY;
        
        const distance = Math.sqrt((x_diff * x_diff) + (y_diff * y_diff));
        const scale = distance / start_distance;
        
        current_size *= scale;
        
        if (current_size < 40)
        {
          current_size = 40;
        }
        else if (current_size > 200)
        {
          current_size = 200;
        }
        refreshStyle();
      }
    });
    
    document.addEventListener('touchend', function(e) {
      reset();
    });
    
    function reset()
    {
      active = false;
      start_distance = null;
    }
    
  }
}

function medialib_onDownload(link)
{
  if (link.href !== 'javascript:void(0)')
  {
    var download_href = link.href;
    link.href = 'javascript:void(0)';
    
    setTimeout(function() {
      link.href = download_href;
    }, 5000);
    
    window.location = download_href;
  }
  return false;
}

function medialib_onThumbError(event, file_extension)
{
  let container = $(event.target);
  let title = container.prop('title');
  container = container.parent();
  switch (file_extension)
  {
    case "bmp":
    case "cgm":
    case "g3":
    case "gif":
    case "ief":
    case "jpeg":
    case "ktx":
    case "png":
    case "btif":
    case "sgi":
    case "svg":
    case "tiff":
    case "psd":
    case "uvi":
    case "sub":
    case "djvu":
    case "dwg":
    case "dxf":
    case "fbs":
    case "fpx":
    case "fst":
    case "mmr":
    case "rlc":
    case "mdi":
    case "wdp":
    case "npx":
    case "wbmp":
    case "xif":
    case "webp":
    case "3ds":
    case "ras":
    case "cmx":
    case "fh":
    case "ico":
    case "sid":
    case "pcx":
    case "pic":
    case "pnm":
    case "pbm":
    case "pgm":
    case "ppm":
    case "rgb":
    case "tga":
    case "xbm":
    case "xpm":
    case "xwd":
      container.html('<i class="fas fa-file-image" title="' + title + '"></i>');
      break;
    default:
      container.html('<i class="fas fa-file" title="' + title + '"></i>');
      break;
  }
}