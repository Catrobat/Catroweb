function MediaLib(package_name, flavor, assetsDir)
{
  $(function() {
    getPackageFiles(package_name, flavor, assetsDir);
    let $content = $("#content");
    $content.find('#thumbsize-control input[type=radio]').change(function() {
      $content.attr("size", this.value);
    });
    initTilePinchToZoom();
  });
  
  function getPackageFiles(package_name, flavor, assetsDir)
  {
    let url = Routing.generate('api_media_lib_package_bynameurl', {flavor: flavor, package: package_name}, false);
    $.get(url, {}, pkgFiles => {
      pkgFiles.forEach(file => {
        if (file.flavor !== 'pocketcode' && file.flavor !== flavor)
        {
          return; // don't display files of other flavors
        }
        
        const mediafileContainer = $('<a class="mediafile" id="mediafile-' + file.id + '"/>');
        mediafileContainer.attr("href", file.download_url);
        mediafileContainer.attr('data-extension', file.extension);
        mediafileContainer.click(function() {
          medialib_onDownload(this);
        });
        
        if (flavor !== "pocketcode" && file.flavor === flavor)
        {
          mediafileContainer.addClass('flavored');
        }
        
        let name = file.name                      // make word breaks easier:
          .replace(/([a-z])([A-Z])/g, '$1​$2')     // insert zero-width space between CamelCase
          .replace(/([A-Za-z])([0-9])/g, '$1​$2')  // insert zero-width space between letters and numbers
          .replace(/_([A-Za-z0-9])/g, '_​$1');     // insert zero-width space between underline and letters
        mediafileContainer.append($('<div class="name" />').text(name));
        mediafileContainer.addClass("showName");
        
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
            mediafileContainer.attr('data-filetype', 'audio');
            mediafileContainer.append($('<i class="fas fa-file-audio"/>'));
            const audio = new Audio(file.download_url);
            const $previewBtn = $('<div class="audio-control fas fa-play" />');
            $previewBtn.click(function() {
              if (audio.paused)
              {
                $previewBtn.removeClass("fa-play").addClass("fa-pause");
                audio.play();
              }
              else
              {
                $previewBtn.removeClass("fa-pause").addClass("fa-play");
                audio.pause();
              }
              return false;
            });
            audio.onended = function() {
              $previewBtn.removeClass("fa-pause").addClass("fa-play");
            };
            
            mediafileContainer.append($previewBtn);
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
            mediafileContainer.attr('data-filetype', 'video');
            mediafileContainer.append($('<i class="fas fa-file-video"/>'));
            break;
          case "pdf":
            mediafileContainer.attr('data-filetype', 'pdf');
            mediafileContainer.append($('<i class="fas fa-file-pdf"/>'));
            break;
          case "txt":
          case "rtx":
            mediafileContainer.attr('data-filetype', 'text');
            mediafileContainer.append($('<i class="fas fa-file-alt"/>'));
            break;
          case "zip":
          case "7z":
            mediafileContainer.attr('data-filetype', 'archive');
            mediafileContainer.append($('<i class="fas fa-file-archive"/>'));
            break;
          default:
            const $image = $('<img src="' + assetsDir + 'thumbs/' + file.id + '.jpeg"/>');
            $image.attr('title', file.name);
            $image.attr('alt', file.name);
            $image.on('error', function() {
              mediafileContainer.addClass("showName");
              
              const pictureExtensions = ["bmp", "cgm", "g3", "gif", "ief", "jpeg", "ktx", "png", "btif", "sgi", "svg", "tiff", "psd", "uvi", "sub", "djvu", "dwg", "dxf", "fbs", "fpx", "fst", "mmr", "rlc", "mdi", "wdp", "npx", "wbmp", "xif", "webp", "3ds", "ras", "cmx", "fh", "ico", "sid", "pcx", "pic", "pnm", "pbm", "pgm", "ppm", "rgb", "tga", "xbm", "xpm", "xwd"];
              $image.remove();
              
              if (pictureExtensions.indexOf(file.extension) !== -1)
              {
                mediafileContainer.prepend($('<i class="fas fa-file-image"/>'));
              }
              else
              {
                mediafileContainer.prepend($('<i class="fas fa-file"/>'));
              }
            });
            mediafileContainer.removeClass("showName");
            mediafileContainer.append($image);
            break;
        }
        
        if (file.category.startsWith("ThemeSpecial"))
        {
          if (file.flavor === flavor)
          {
            $('#content #category-theme-special .files').prepend(mediafileContainer);
          } // else ignore
        }
        else
        {
          const catEscaped = file.category.replace(/"/g, '\\"');
          $('#content .category[data-name="' + catEscaped + '"] .files').prepend(mediafileContainer);
        }
      });
      
      $("#content .category").each(function() {
        if ($(this).find(".files").children().length === 0)
        {
          return;
        }
        
        const catId = /^category-(.+)$/.exec(this.id)[1];
        
        $(this).show();
        $('#sidebar #menu-mediacat-' + catId).show();
      });
    }).fail(function() {
      console.error("Error loading media lib package " + package_name);
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
